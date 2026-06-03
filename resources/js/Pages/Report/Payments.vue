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
import Details from '@/Pages/Payment/Details.vue';
import PaymentForm from '@/Pages/Payment/Form.vue';
import SelectInput from '@/Shared/SelectInput.vue';
import AutoComplete from '@/Shared/AutoComplete.vue';
import SearchFilter from '@/Shared/SearchFilter.vue';
import SecondaryButton from '@/Jetstream/SecondaryButton.vue';

const { t } = useI18n({});

const form = ref(false);
const show = ref(false);
const payment = ref(null);
const props = defineProps(['payments', 'data', 'companies', 'users']);
props.users.unshift({ value: '', label: 'All' });
props.companies.unshift({ value: '', label: 'All' });

const search = useForm({
  search: '',
  start_date: null,
  end_date: null,
  method: null,
  company: null,
  customer: null,
  user: null,
  invoice: null,
  trashed: null,
});

onMounted(() => {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  search.search = urlParams.has('search') ? urlParams.get('search') : null;
  search.trashed = urlParams.has('trashed') ? urlParams.get('trashed') : null;
  search.end_date = urlParams.has('end_date') ? urlParams.get('end_date') : null;
  search.start_date = urlParams.has('start_date') ? urlParams.get('start_date') : null;
  search.company = urlParams.has('company') ? urlParams.get('company') : null;
  search.method = urlParams.has('method') ? urlParams.get('method') : null;
  search.customer = urlParams.has('customer') ? urlParams.get('customer') : null;
  search.user = urlParams.has('user') ? urlParams.get('user') : null;
  search.invoice = urlParams.has('invoice') ? urlParams.get('invoice') : null;
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

const showPayment = ch => {
  show.value = true;
  payment.value = ch;
};

const showForm = () => {
  form.value = true;
};

const resetForm = () => {
  search.search = '';
  search.start_date = null;
  search.end_date = null;
  search.method = null;
  search.company = null;
  search.customer = null;
  search.user = null;
  search.invoice = null;
  search.trashed = null;
  form.value = false;
  searchNow();
};

const hideForm = () => {
  form.value = false;
};

const hidePayment = ch => {
  show.value = false;
  payment.value = null;
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
    .get(route('reports.payments'), { preserveState: true });
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
  <AppLayout :title="t('Payments Report')">
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
                    {{ t('Payments Report') }}
                    <span class="text-base">
                      {{ search.start_date ? '(from: ' + $date(search.start_date) + ')' : '' }}
                      {{ search.end_date ? '(to: ' + $date(search.end_date) + ')' : '' }}
                      {{ { with: '(' + t('With Trashed') + ')', only: '(' + t('Only Trashed') + ')' }[search.trashed] }}
                    </span>
                  </div>
                  <div class="mt-1 text-gray-500 dark:text-gray-400">{{ t('Please review the result below') }}</div>
                </div>
                <div class="mt-4 sm:mt-0 block sm:flex sm:items-center sm:gap-4">
                  <Button away :href="route('report.port.payments', { ...getFormData() })" class="mr-2">
                    {{ t('Export') }}
                  </Button>
                  <Button @click="showForm">{{ t('Customize') }}</Button>
                </div>
              </div>

              <div class="p-6 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                <dl
                  class="grid grid-cols-1 divide-y divide-gray-200 overflow-hidden rounded-lg bg-white dark:bg-gray-800 md:grid-cols-2 md:divide-y-0 md:divide-x dark:divide-gray-700 border dark:border-gray-700"
                >
                  <div class="px-4 py-5 sm:p-6">
                    <dt class="text-base font-normal text-gray-900 dark:text-gray-100">{{ t('Total Payments') }}</dt>
                    <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                      <div class="flex items-baseline text-2xl font-semibold text-blue-600 dark:text-blue-400">
                        {{ $number(data.count, null, { maximumFractionDigits: 0 }) }}
                      </div>
                    </dd>
                  </div>
                  <div class="px-4 py-5 sm:p-6">
                    <dt class="text-base font-normal text-gray-900 dark:text-gray-100">{{ t('Total Amount') }}</dt>
                    <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                      <div class="flex items-baseline text-2xl font-semibold text-blue-600 dark:text-blue-400">
                        {{ $number(data.amount) }}
                      </div>
                    </dd>
                  </div>
                </dl>
              </div>

              <div class="bg-gray-50 dark:bg-black/50 border-b dark:border-gray-700 sm:rounded-b-lg">
                <div v-if="payments && payments.data && payments.data.length">
                  <div class="flex flex-col">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                      <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <div class="relative overflow-hidden ring-1 ring-black/5">
                          <table class="min-w-full table-fixed divide-y divide-gray-300 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-black/50">
                              <tr>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                  {{ t('Payment') }}
                                </th>
                                <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                  {{ t('Relations') }}
                                </th>
                                <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                  {{ t('Amount') }}
                                </th>
                                <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                  {{ t('Custom Fields') }}
                                </th>
                              </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                              <tr :key="payment.id" v-for="(payment, pi) in payments.data">
                                <td class="whitespace-nowrap cursor-pointer px-3 py-4 text-sm font-medium" @click="showPayment(payment)">
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Number') }}: </span>
                                    {{ payment.id }}
                                  </div>
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Date') }}: </span>
                                    {{ $date(payment.date) }}
                                  </div>
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Created at') }}: </span>
                                    {{ $datetime(payment.created_at) }}
                                  </div>
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Ref') }}: </span>
                                    {{ payment.reference }}
                                  </div>
                                </td>
                                <td class="whitespace-nowrap cursor-pointer px-3 py-4 text-sm font-medium" @click="showPayment(payment)">
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Invoice No.') }}: </span>
                                    {{ payment.invoice_id }}
                                  </div>
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Customer') }}: </span>
                                    <span :class="'customer-' + pi">{{ payment.customer?.name }}</span>
                                  </div>
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Company') }}: </span>
                                    {{ payment.company?.name }}
                                  </div>
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('By user') }}: </span>
                                    {{ payment.user?.name }}
                                  </div>
                                </td>
                                <td
                                  class="whitespace-nowrap cursor-pointer px-3 py-4 text-sm font-bold text-right"
                                  @click="showPayment(payment)"
                                >
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Amount') }}: </span>
                                    <span class="text-base">{{ $number(payment.amount) }}</span>
                                  </div>
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Method') }}: </span>
                                    <span class="capitalize text-base">{{ payment.method }}</span>
                                  </div>
                                </td>
                                <td class="px-3 py-4 text-sm cursor-pointer" @click="showPayment(payment)">
                                  <div
                                    class="flex items-center flex-wrap gap-x-4 gap-y-2 min-w-[200px] max-w-xs"
                                    v-html="$extra_attributes(payment.extra_attributes)"
                                  ></div>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>

                    <pagination class="m-4" :meta="payments.meta" :links="payments.links" />
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
              {{ t('Customize Payments Report') }}
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
            <TextInput type="text" v-model="search.invoice" id="invoice" :label="t('Invoice No.')" />
          </div>
          <div>
            <TextInput type="text" v-model="search.method" id="method" :label="t('Method')" />
          </div>

          <div>
            <TextInput type="date" v-model="search.start_date" id="start_date" :label="t('Start Date')" />
          </div>
          <div>
            <TextInput type="date" v-model="search.end_date" id="end_date" :label="t('End Date')" />
          </div>

          <div class="mt-2 col-span-full flex gap-6 justify-end items-center bg-gray-100 dark:bg-gray-900 -m-6 px-6 py-4">
            <SecondaryButton @click="resetForm"> {{ t('Reset') }} </SecondaryButton>
            <Button type="submit"> {{ t('Update') }} </Button>
          </div>
          <!-- </SearchFilter> -->
        </div>
      </form>
    </Modal>

    <Modal :show="show" max-width="4xl" :closeable="true" @close="hidePayment">
      <div class="px-6 pt-3 pb-6 print:px-0 bg-gray-100 dark:bg-gray-800">
        <div v-if="show && payment" class="flex items-center justify-between print:hidden">
          <div class="text-lg">
            {{ t('Payment No.') }} {{ payment.id }}
            <!-- <span class="hidden sm:inline">({{ payment.reference }})</span> -->
          </div>
          <div class="-mr-2 flex items- gap-2">
            <button
              @click="print()"
              class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
            >
              <icons name="printer" class="h-5 w-5" />
            </button>
            <button
              @click="hidePayment()"
              class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
            >
              <icons name="cross" class="h-5 w-5" />
            </button>
          </div>
        </div>

        <div class="">
          <Details v-if="payment" :payment="payment" />
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>
