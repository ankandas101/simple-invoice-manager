<script setup>
import { route } from 'ziggy-js';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import { ref, onMounted, onUpdated } from 'vue';
import { useForm } from '@inertiajs/vue3';

import Button from '@/Shared/Button.vue';
import Modal from '@/Jetstream/Modal.vue';
import CheckBox from '@/Shared/CheckBox.vue';
import TextInput from '@/Shared/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Shared/Pagination.vue';
import Details from '@/Pages/Invoice/Details.vue';
import PaymentForm from '@/Pages/Payment/Form.vue';
import SelectInput from '@/Shared/SelectInput.vue';
import AutoComplete from '@/Shared/AutoComplete.vue';
import SearchFilter from '@/Shared/SearchFilter.vue';
import SecondaryButton from '@/Jetstream/SecondaryButton.vue';

const { t } = useI18n({});

const form = ref(false);
const show = ref(false);
const invoice = ref(null);
const props = defineProps(['invoices', 'data', 'companies', 'users', 'settings']);
props.users.unshift({ value: '', label: 'All' });
props.companies.unshift({ value: '', label: 'All' });

const search = useForm({
  search: '',
  recurring: false,
  start_date: null,
  end_date: null,
  status: null,
  company: null,
  customer: null,
  user: null,
  product: null,
  trashed: null,
  fields: null,
});

onMounted(() => {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  search.search = urlParams.has('search') ? urlParams.get('search') : null;
  search.trashed = urlParams.has('trashed') ? urlParams.get('trashed') : null;
  search.end_date = urlParams.has('end_date') ? urlParams.get('end_date') : null;
  search.start_date = urlParams.has('start_date') ? urlParams.get('start_date') : null;
  search.recurring = urlParams.has('recurring') ? urlParams.get('recurring') : false;
  search.company = urlParams.has('company') ? urlParams.get('company') : null;
  search.status = urlParams.has('status') ? urlParams.get('status') : null;
  search.customer = urlParams.has('customer') ? urlParams.get('customer') : null;
  search.user = urlParams.has('user') ? urlParams.get('user') : null;
  search.product = urlParams.has('product') ? urlParams.get('product') : null;
  search.fields = urlParams.has('fields') ? urlParams.get('fields') : null;
});

onUpdated(() => {
  if (!props.users.find(u => u.label == 'All')) {
    props.users.unshift({ value: '', label: 'All' });
  }
  if (!props.companies.find(u => u.label == 'All')) {
    props.companies.unshift({ value: '', label: 'All' });
  }
});

const print = () => {
  window.print();
};

const showInvoice = ch => {
  show.value = true;
  invoice.value = ch;
};

const showForm = () => {
  form.value = true;
};

const resetForm = () => {
  search.search = '';
  search.recurring = false;
  search.start_date = null;
  search.end_date = null;
  search.status = null;
  search.company = null;
  search.customer = null;
  search.user = null;
  search.product = null;
  search.trashed = null;
  search.fields = null;
  form.value = false;
  searchNow();
};

const hideForm = () => {
  form.value = false;
};

const hideInvoice = ch => {
  show.value = false;
  invoice.value = null;
};

const searchNow = () => {
  hideForm();
  search
    .transform(data => {
      let obj = {
        ...data,
        remember: data.remember ? 'on' : '',
      };
      return Object.entries(obj).reduce((a, [k, v]) => (v ? ((a[k] = v), a) : a), {});
    })
    .get(route('reports.invoices'), { preserveState: true });
};

const form_data = ref({});
const getFormData = async () => {
  await search.transform(data => {
    let obj = { ...data };
    form_data.value = Object.entries(obj).reduce((a, [k, v]) => (v ? ((a[k] = v), a) : a), {});
  });

  return form_data.value;
};
</script>

<template>
  <AppLayout :title="t('Invoices Report')">
    <div class="contents">
      <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white dark:bg-gray-900 shadow-sm sm:rounded-lg">
            <div class="sm:rounded-lg">
              <div
                class="p-6 sm:rounded-t-lg border-b border-gray-200 dark:border-gray-700 block md:flex md:items-center md:justify-between"
              >
                <div>
                  <div class="text-2xl">
                    {{ t('Invoices Report') }}
                    <span class="text-base">
                      {{ search.start_date ? '(from: ' + $date(search.start_date) + ')' : '' }}
                      {{ search.end_date ? '(to: ' + $date(search.end_date) + ')' : '' }}
                      {{ { with: '(' + t('With Trashed') + ')', only: '(' + t('Only Trashed') + ')' }[search.trashed] }}
                    </span>
                  </div>
                  <div class="mt-1 text-gray-500 dark:text-gray-400">{{ t('Please review the result below') }}</div>
                </div>
                <div class="mt-4 sm:mt-0 block sm:flex sm:items-center sm:gap-4">
                  <Button away :href="route('report.port.invoices', { ...getFormData() })" class="mr-2">
                    {{ t('Export') }}
                  </Button>
                  <Button @click="showForm">{{ t('Customize') }}</Button>
                </div>
              </div>

              <div class="p-6 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                <dl
                  class="grid grid-cols-1 divide-y divide-gray-200 overflow-hidden rounded-lg bg-white dark:bg-gray-800 md:grid-cols-2 lg:grid-cols-4 md:divide-y-0 md:divide-x dark:divide-gray-700 border dark:border-gray-700"
                >
                  <div class="px-4 py-5 sm:p-6">
                    <dt class="text-base font-normal text-gray-900 dark:text-gray-100">{{ t('Total Invoices') }}</dt>
                    <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                      <div class="flex items-baseline text-2xl font-semibold text-blue-600 dark:text-blue-400">
                        {{ $number(data.count, null, { maximumFractionDigits: 0 }) }}
                      </div>
                    </dd>
                  </div>
                  <div class="px-4 py-5 sm:p-6">
                    <dt class="text-base font-normal text-gray-900 dark:text-gray-100">{{ t('Total Tax Amount') }}</dt>
                    <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                      <div class="flex items-baseline text-2xl font-semibold text-blue-600 dark:text-blue-400">
                        {{ $number(data.total_tax_amount) }}
                      </div>
                    </dd>
                  </div>
                  <div class="px-4 py-5 sm:p-6">
                    <dt class="text-base font-normal text-gray-900 dark:text-gray-100">{{ t('Total Amount with Tax') }}</dt>
                    <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                      <div class="flex items-baseline text-2xl font-semibold text-blue-600 dark:text-blue-400">
                        {{ $number(data.total) }}
                      </div>
                    </dd>
                  </div>
                  <div class="px-4 py-5 sm:p-6">
                    <dt class="text-base font-normal text-gray-900 dark:text-gray-100">{{ t('Total Received Amount') }}</dt>
                    <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                      <div class="flex items-baseline text-2xl font-semibold text-blue-600 dark:text-blue-400">
                        {{ $number(data.paid) }}
                      </div>
                    </dd>
                  </div>
                </dl>
              </div>

              <div class="bg-gray-50 dark:bg-black/50 border-b dark:border-gray-700 sm:rounded-b-lg">
                <div v-if="invoices && invoices.data && invoices.data.length">
                  <div class="flex flex-col">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                      <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <div class="relative overflow-hidden ring-1 ring-black/5">
                          <table class="min-w-full table-fixed divide-y divide-gray-300 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-black/50">
                              <tr>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                  {{ t('Invoice') }}
                                </th>
                                <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                  {{ t('Relations') }}
                                </th>
                                <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                  {{ t('Data') }}
                                </th>
                                <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                  {{ t('Total') }}
                                </th>
                              </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                              <tr :key="invoice.id" v-for="(invoice, ii) in invoices.data">
                                <td
                                  :class="'row-' + ii"
                                  @click="showInvoice(invoice)"
                                  class="whitespace-nowrap px-3 py-4 cursor-pointer text-sm font-medium"
                                >
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Number') }}: </span>
                                    {{ invoice.id }}
                                  </div>
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Date') }}: </span>
                                    {{ $date(invoice.date) }}
                                  </div>
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Created at') }}: </span>
                                    {{ $datetime(invoice.created_at) }}
                                  </div>
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Ref') }}: </span>
                                    {{ invoice.reference }}
                                  </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 cursor-pointer text-sm" @click="showInvoice(invoice)">
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Company') }}: </span>
                                    {{ invoice.company?.name }}
                                  </div>
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Customer') }}: </span>
                                    {{ invoice.customer?.name }}
                                  </div>
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('By user') }}: </span>
                                    {{ invoice.user?.name }}
                                  </div>
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Products') }}: </span>
                                    {{ invoice.items.length }}
                                  </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 cursor-pointer text-sm font-medium" @click="showInvoice(invoice)">
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Status') }}: </span>
                                    <span :class="'status-' + ii">{{ t($capitalize(invoice.status)) }}</span>
                                  </div>
                                  <div v-if="invoice.due_date" class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Due Date') }}: </span>
                                    {{ $date(invoice.due_date) }}
                                  </div>
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Shipping') }}: </span>
                                    {{ $number(invoice.shipping) }}
                                  </div>
                                  <div v-if="invoice.recurring" class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Repeat') }}: </span>
                                    {{ t(invoice.repeat) }}
                                  </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 cursor-pointer text-sm" @click="showInvoice(invoice)">
                                  <!-- <div class="flex items-center justify-end gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Total') }}: </span>
                                    {{ $number(invoice.total) }}
                                  </div> -->
                                  <div class="flex items-center justify-end gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Discount') }}: </span>
                                    {{ $number(invoice.total_discount_amount) }}
                                  </div>
                                  <div class="flex items-center justify-end gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Tax') }}: </span>
                                    {{ $number(invoice.total_tax_amount) }}
                                  </div>
                                  <div class="flex items-center justify-end gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Grand Total') }}: </span>
                                    <span :class="'grand-total-' + ii">{{ $number(invoice.grand_total) }}</span>
                                  </div>
                                  <div class="flex items-center justify-end gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('paid') }}: </span>
                                    <span :class="'payment-' + ii">{{ $number(invoice.paid) }}</span>
                                  </div>
                                </td>
                                <!-- <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 w-20">
                                  <div class="flex flex-col items-center justify-end gap-3">
                                    <div class="flex items-center justify-end gap-3">
                                      <template v-if="invoice.attachment">
                                        <a
                                          :href="invoice.attachment"
                                          target="_blank"
                                          class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                                        >
                                          <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="w-5 h-5"
                                          >
                                            <path
                                              stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"
                                            />
                                          </svg>
                                        </a>
                                      </template>
                                    </div>
                                  </div>
                                </td> -->
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>

                    <pagination class="m-4" :meta="invoices.meta" :links="invoices.links" />
                  </div>
                </div>
                <div v-else class="py-4 px-6">{{ t('There is no data to display.') }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Modal :show="form" max-width="xl" :closeable="true" @close="hideForm">
      <form @submit.prevent="searchNow">
        <div class="relative grid grid-cols-1 sm:grid-cols-2 p-6 gap-4 bg-white dark:bg-gray-800">
          <button
            @click="hideForm()"
            class="absolute right-3.5 top-3.5 flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
          >
            <icons name="cross" class="h-5 w-5" />
          </button>

          <div class="col-span-full bg-gray-100 dark:bg-gray-900 -m-6 px-6 py-4">
            <div class="text-lg">
              {{ t('Customize Invoices Report') }}
            </div>
          </div>
          <div class="col-span-full mt-4">
            <div class="mt-1 text-gray-500 dark:text-gray-400">{{ t('Please edit the form to customize the report') }}</div>
          </div>

          <div>
            <AutoComplete
              :json="true"
              id="company_id"
              :searchable="false"
              :label="t('Company')"
              :suggestions="companies"
              v-model="search.company"
            />
          </div>
          <div>
            <AutoComplete
              :json="true"
              id="product_id"
              :searchable="true"
              :label="t('Product')"
              v-model="search.product"
              :suggestions="route('search.products')"
            />
            <!-- :initial="search?.product" -->
          </div>

          <div>
            <AutoComplete
              :json="true"
              id="customer_id"
              :searchable="true"
              :label="t('Customer')"
              v-model="search.customer"
              :selected="search?.customer"
              :suggestions="route('search.customers')"
            />
          </div>

          <div>
            <AutoComplete
              :json="true"
              id="user_id"
              :label="t('User')"
              :searchable="false"
              :suggestions="users"
              v-model="search.user"
              :selected="search?.user"
            />
          </div>
          <div>
            <AutoComplete
              id="status"
              :json="true"
              :searchable="false"
              :label="t('Status')"
              v-model="search.status"
              :suggestions="[
                { value: '', label: 'All' },
                { value: 'pending', label: t('pending') },
                { value: 'paid', label: t('paid') },
                { value: 'overdue', label: t('overdue') },
                { value: 'canceled', label: t('canceled') },
              ]"
            />
          </div>
          <div>
            <AutoComplete
              id="trashed"
              :json="true"
              :searchable="false"
              :label="t('Trashed')"
              v-model="search.trashed"
              :suggestions="[
                { value: '', label: 'Not Trashed' },
                { value: 'with', label: 'With Trashed' },
                { value: 'only', label: 'Only Trashed' },
              ]"
            />
          </div>

          <div>
            <TextInput type="date" v-model="search.start_date" id="start_date" :label="t('Start Date')" />
          </div>
          <div>
            <TextInput type="date" v-model="search.end_date" id="end_date" :label="t('End Date')" />
          </div>

          <div>
            <TextInput type="text" v-model="search.fields" id="fields" :label="t('Custom Fields')" />
          </div>

          <div class="col-span-full">
            <CheckBox id="recurring" :label="t('Recurring invoices')" v-model:checked="search.recurring" />
          </div>

          <div class="mt-2 col-span-full flex gap-6 justify-end items-center bg-gray-100 dark:bg-gray-900 -m-6 px-6 py-4">
            <SecondaryButton @click="resetForm"> {{ t('Reset') }} </SecondaryButton>
            <Button type="submit"> {{ t('Update') }} </Button>
          </div>
          <!-- </SearchFilter> -->
        </div>
      </form>
    </Modal>

    <Modal :show="show" max-width="4xl" :closeable="true" @close="hideInvoice">
      <div class="px-6 pt-3 pb-6 print:px-0 bg-gray-100 dark:bg-gray-800">
        <div v-if="show && invoice" class="flex items-center justify-between print:hidden">
          <div class="text-lg">
            {{ t('Invoice No.') }} {{ invoice.id }}
            <!-- <span class="hidden sm:inline">({{ invoice.reference }})</span> -->
          </div>
          <div class="-mr-2 flex items- gap-2">
            <button
              @click="print()"
              class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
            >
              <icons name="printer" class="h-5 w-5" />
            </button>
            <button
              @click="hideInvoice()"
              class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
            >
              <icons name="cross" class="h-5 w-5" />
            </button>
          </div>
        </div>

        <div class="">
          <Details v-if="invoice" :invoice="invoice" :settings="settings" />
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>
