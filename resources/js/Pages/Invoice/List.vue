<script setup>
import { route } from 'ziggy-js';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

import Details from './Details.vue';
import Button from '@/Shared/Button.vue';
import Modal from '@/Jetstream/Modal.vue';
import CheckBox from '@/Shared/CheckBox.vue';
import TextInput from '@/Shared/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Shared/Pagination.vue';
import RowActions from '@/Shared/RowActions.vue';
import PaymentForm from '@/Pages/Payment/Form.vue';
import SelectInput from '@/Shared/SelectInput.vue';
import AutoComplete from '@/Shared/AutoComplete.vue';
import SearchFilter from '@/Shared/SearchFilter.vue';
import DialogModal from '@/Jetstream/DialogModal.vue';
import DangerButton from '@/Jetstream/DangerButton.vue';
import SecondaryButton from '@/Jetstream/SecondaryButton.vue';

const { t } = useI18n({});

const show = ref(false);
const invoice = ref(null);
const bulkDelete = ref(false);
const paymentModal = ref(false);
const user = computed(() => usePage().props.auth.user);
const form = useForm({ selection: [], force: false });
const props = defineProps(['invoices', 'payment_fields', 'companies']);
const impersonate = computed(() => (usePage().props.is_impersonating ? usePage().props.user : null));
const search = useForm({
  search: '',
  recurring: false,
  status: null,
  start_date: null,
  end_date: null,
  trashed: null,
  transfer: null,
  company: null,
});
const indeterminate = computed(() => form.selection.length > 0 && form.selection.length < props.invoices?.data?.length);

onMounted(() => {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  search.search = urlParams.has('search') ? urlParams.get('search') : null;
  search.status = urlParams.has('status') ? urlParams.get('status') : null;
  search.company = urlParams.has('company') ? urlParams.get('company') : null;
  search.trashed = urlParams.has('trashed') ? urlParams.get('trashed') : null;
  search.transfer = urlParams.has('transfer') ? urlParams.get('transfer') : null;
  search.end_date = urlParams.has('end_date') ? urlParams.get('end_date') : null;
  search.start_date = urlParams.has('start_date') ? urlParams.get('start_date') : null;
  search.recurring = urlParams.has('recurring') ? urlParams.get('recurring') : false;
});

const copyLink = () => {
  let link = route('views.invoice', { id: invoice.value.id, hash: invoice.value.hash });
  usePage().props.flash.message = navigator.clipboard?.writeText(link) ? t('Invoice linked copied.') : '';
  setTimeout(() => {
    usePage().props.flash.message = '';
  }, 2000);
};

const print = () => {
  window.print();
};

const addPayment = si => {
  invoice.value = si;
  paymentModal.value = true;
};

const paymentAdded = e => {
  show.value = false;
  paymentModal.value = false;
  router.reload({ only: ['invoices'] });
};

const emailInvoice = id => {
  router.post(route('invoice.notification', id));
};

const deleteRow = (id, force = false) => {
  router.delete(route('invoices.destroy' + (force ? '.permanently' : ''), id));
};

const restoreRow = id => {
  router.put(route('invoices.restore', id));
};

const closeModal = () => {
  bulkDelete.value = false;
};

const showInvoice = ch => {
  show.value = true;
  invoice.value = ch;
};

const hideInvoice = ch => {
  show.value = false;
  invoice.value = null;
};

const confirmDelete = force => {
  form.force = force || false;
  bulkDelete.value = true;
};

const deleteSelected = force => {
  form.delete(route('invoices.destroy.many'), {
    onSuccess: () => {
      form.reset();
    },
    onFinish: () => {
      bulkDelete.value = false;
    },
  });
};

const searchNow = () => {
  search
    .transform(data => {
      let obj = {
        ...data,
        remember: data.remember ? 'on' : '',
      };
      return Object.entries(obj).reduce((a, [k, v]) => (v ? ((a[k] = v), a) : a), {});
    })
    .get(route('invoices.index'), { preserveState: true });
};

const resetSearch = () => {
  search.search = '';
  search.trashed = null;
  searchNow();
};
</script>

<template>
  <AppLayout :title="t('List Invoices')">
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
                    {{ t('Invoices') }}
                    <span class="text-base">
                      {{ search.start_date ? '(from: ' + $date(search.start_date) + ')' : '' }}
                      {{ search.end_date ? '(to: ' + $date(search.end_date) + ')' : '' }}
                      {{ { with: '(' + t('With Trashed') + ')', only: '(' + t('Only Trashed') + ')' }[search.trashed] }}
                    </span>
                  </div>
                  <div class="mt-1 text-gray-500 dark:text-gray-400">{{ t('Please review the result below') }}</div>
                </div>
                <div class="mt-4 sm:mt-0 block sm:flex sm:items-center sm:gap-4">
                  <Button v-if="$can('create-invoices')" :href="route('invoices.create')">{{ t('Add New Invoice') }}</Button>
                  <form @submit.prevent="searchNow">
                    <SearchFilter @reset="resetSearch()" v-model="search.search">
                      <label class="block">{{ t('Status') }}:</label>
                      <SelectInput v-model="search.status" @change="searchNow()" class="mt-1 w-full">
                        <option :value="null">{{ t('All') }}</option>
                        <option value="pending">{{ t('pending') }}</option>
                        <option value="paid">{{ t('paid') }}</option>
                        <option value="overdue">{{ t('overdue') }}</option>
                        <option value="canceled">{{ t('canceled') }}</option>
                      </SelectInput>

                      <label class="block mt-4">{{ t('Company') }}:</label>
                      <SelectInput v-model="search.company" @change="searchNow()" class="mt-1 w-full">
                        <option :value="null">{{ t('All') }}</option>
                        <option v-for="c of companies" :value="c.id">{{ c.name }}</option>
                      </SelectInput>

                      <label class="block mt-4">{{ t('Start Date') }}:</label>
                      <TextInput type="date" v-model="search.start_date" @change="searchNow()" class="mt-1 w-full mb-4" />

                      <label class="block">{{ t('End Date') }}:</label>
                      <TextInput type="date" v-model="search.end_date" @change="searchNow()" class="mt-1 w-full mb-4" />

                      <label class="block">{{ t('Trashed') }}:</label>
                      <SelectInput v-model="search.trashed" @change="searchNow()" class="mt-1 w-full">
                        <option :value="null">{{ t('Not Trashed') }}</option>
                        <option value="with">{{ t('With Trashed') }}</option>
                        <option value="only">{{ t('Only Trashed') }}</option>
                      </SelectInput>

                      <div class="mt-4">
                        <CheckBox id="transfer" @change="searchNow()" :label="t('Bank transfers')" v-model:checked="search.transfer" />
                      </div>
                      <div class="mt-2">
                        <CheckBox
                          id="recurring"
                          @change="searchNow()"
                          :label="t('Recurring invoices')"
                          v-model:checked="search.recurring"
                        />
                      </div>
                    </SearchFilter>
                  </form>
                </div>
              </div>

              <div class="bg-gray-50 dark:bg-black/50 border-b dark:border-gray-700 sm:rounded-b-lg">
                <div v-if="invoices && invoices?.data && invoices.data.length">
                  <div class="flex flex-col overflow-hidden">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                      <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <div class="relative overflow-hidden ring-1 ring-black/5">
                          <div
                            v-if="form.selection && form.selection.length"
                            class="absolute top-0 left-12 flex h-12 items-center space-x-3 sm:left-16"
                          >
                            <button
                              type="button"
                              @click="confirmDelete()"
                              class="inline-flex items-center rounded-sm border text-white border-red-500 bg-red-500 px-2.5 py-1.5 text-xs font-medium shadow-xs hover:bg-red-600 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-30"
                            >
                              {{ t('Move to Trash') }}
                            </button>
                            <button
                              type="button"
                              @click="confirmDelete(true)"
                              class="inline-flex items-center rounded-sm border text-white border-red-500 bg-red-500 px-2.5 py-1.5 text-xs font-medium shadow-xs hover:bg-red-600 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-30"
                            >
                              {{ t('Delete Permanently') }}
                            </button>

                            <DialogModal :show="bulkDelete" @close="closeModal" maxWidth="sm">
                              <template #title> {{ t('Delete bulk records') }} </template>
                              <template #content>
                                <p>{{ t('Please confirm that you would like to delete the records?') }}</p>
                                <p v-if="form.force" class="mt-2 text-red-500">{{ t('This action will delete the data permanently.') }}</p>
                              </template>
                              <template #footer>
                                <SecondaryButton @click="closeModal"> {{ t('Cancel') }} </SecondaryButton>
                                <DangerButton @click="deleteSelected()"> {{ t('Yes, delete') }} </DangerButton>
                              </template>
                            </DialogModal>
                          </div>

                          <table class="min-w-full table-fixed divide-y divide-gray-300 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-black/50">
                              <tr>
                                <th scope="col" class="relative w-12 px-6 sm:w-16 sm:px-8">
                                  <input
                                    type="checkbox"
                                    :indeterminate="indeterminate"
                                    :checked="indeterminate || form.selection.length === invoices.data.length"
                                    @change="form.selection = $event.target.checked ? invoices.data.map(p => p.id) : []"
                                    class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded-sm border-gray-300 text-blue-600 focus:ring-blue-500 sm:left-6 dark:bg-gray-700 dark:border-gray-700"
                                  />
                                </th>
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
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                  <span class="sr-only">{{ t('Actions') }}</span>
                                </th>
                              </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                              <tr
                                :key="invoice.id"
                                v-for="(invoice, ii) in invoices.data"
                                :class="[
                                  form.selection.includes(invoice.id) && 'bg-gray-50 dark:bg-gray-800/50',
                                  invoice.deleted_at && 'bg-red-100 dark:bg-red-900',
                                  //   invoice.receipt && invoice.paid < invoice.grand_total && 'animate-bounce hover:animate-none',
                                  invoice.status == 'canceled' && 'bg-red-100 dark:bg-red-800',
                                  invoice.status == 'overdue' && 'bg-red-50 dark:bg-red-800',
                                  invoice.status == 'pending' && 'bg-yellow-50 dark:bg-yellow-800',
                                ]"
                              >
                                <td class="relative w-12 px-6 sm:w-16 sm:px-8">
                                  <div v-if="form.selection.includes(invoice.id)" class="absolute inset-y-0 left-0 w-0.5 bg-blue-600"></div>
                                  <input
                                    type="checkbox"
                                    :value="invoice.id"
                                    v-model="form.selection"
                                    class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded-sm border-gray-300 text-blue-600 focus:ring-blue-500 sm:left-6 dark:bg-gray-700 dark:border-gray-700"
                                  />
                                </td>
                                <td
                                  :class="'row-' + ii"
                                  @click="showInvoice(invoice)"
                                  class="whitespace-nowrap px-3 py-4 cursor-pointer text-sm font-medium"
                                >
                                  <div class="flex items-center gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Number') }}: </span>
                                    {{
                                      ($page.props.settings?.use_company_number == 1 ? invoice.company_number : invoice.number) ||
                                      invoice.id
                                    }}
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
                                  <div v-if="invoice.recurring && invoice.repeat" class="flex items-center gap-1">
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
                                    {{ $currency(invoice.total_discount_amount) }}
                                  </div>
                                  <div class="flex items-center justify-end gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Tax') }}: </span>
                                    {{ $currency(invoice.total_tax_amount) }}
                                  </div>
                                  <div class="flex items-center justify-end gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('Grand Total') }}: </span>
                                    <span :class="'grand-total-' + ii">{{ $currency(invoice.grand_total) }}</span>
                                  </div>
                                  <div class="flex items-center justify-end gap-1">
                                    <span class="text-gray-500 dark:text-gray-400">{{ t('paid') }}: </span>
                                    <span :class="'payment-' + ii">{{ $currency(invoice.paid) }}</span>
                                  </div>
                                </td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 w-20">
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
                                      <!-- <template v-if="invoice.paid < invoice.grand_total"> -->
                                      <button
                                        type="button"
                                        @click="addPayment(invoice)"
                                        v-if="!impersonate && !user.roles.find(r => r.name == 'customer')"
                                        :disabled="Number(invoice.paid) >= Number(invoice.grand_total) || invoice.status == 'canceled'"
                                        :class="
                                          Number(invoice.paid) >= Number(invoice.grand_total) || invoice.status == 'canceled'
                                            ? 'add-payment-' + ii
                                            : 'add-payment-' + ii + ' hover:text-gray-800 dark:hover:text-gray-200'
                                        "
                                        class="text-gray-500 dark:text-gray-400 disabled:opacity-50"
                                      >
                                        <svg
                                          xmlns="http://www.w3.org/2000/svg"
                                          fill="none"
                                          viewBox="0 0 24 24"
                                          stroke-width="1.5"
                                          stroke="currentColor"
                                          class="w-6 h-6"
                                        >
                                          <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"
                                          />
                                        </svg>
                                      </button>
                                      <!-- </template> -->
                                      <button
                                        type="button"
                                        @click="showInvoice(invoice)"
                                        class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                                      >
                                        <svg
                                          xmlns="http://www.w3.org/2000/svg"
                                          fill="none"
                                          viewBox="0 0 24 24"
                                          stroke-width="1.5"
                                          stroke="currentColor"
                                          class="w-6 h-6"
                                        >
                                          <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"
                                          />
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                      </button>
                                    </div>
                                    <div class="flex-1 w-full flex items-center justify-end justify-self-end gap-3">
                                      <row-actions
                                        pm="invoices"
                                        :row="invoice"
                                        property="reference"
                                        :deleteFn="deleteRow"
                                        :restoreFn="restoreRow"
                                        :editStr="route('invoices.edit', invoice.id)"
                                      />
                                    </div>
                                  </div>
                                </td>
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

    <Modal :show="paymentModal" max-width="2xl" :closeable="false" @close="() => (paymentModal = false)">
      <PaymentForm :invoice="invoice" :fields="payment_fields" @done="paymentAdded" @close="() => (paymentModal = false)" />
    </Modal>

    <Modal :show="show" max-width="4xl" :closeable="true" @close="hideInvoice">
      <div class="px-6 pt-3 pb-6 print:px-0 bg-gray-100 dark:bg-gray-800">
        <div v-if="show && invoice" class="flex items-center justify-between print:hidden">
          <div class="text-lg">
            {{ t('Invoice No.') }}
            {{ ($page.props.settings?.use_company_number == 1 ? invoice.company_number : invoice.number) || invoice.id }}
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
              @click="copyLink()"
              class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
            >
              <icons name="copy" class="h-5 w-5" />
            </button>
            <a
              target="_blank"
              v-if="invoice.customer?.phone"
              :href="`https://wa.me/${invoice.customer.phone}?text=${
                'Hello, Please view your invoice at ' +
                encodeURIComponent(
                  route('views.invoice', {
                    id: invoice.id,
                    hash: invoice.hash,
                  })
                ) +
                ', Thanks'
              }`"
              class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 308 308" stroke="currentColor" class="w-5 h-5">
                <g>
                  <path
                    d="M227.904,176.981c-0.6-0.288-23.054-11.345-27.044-12.781c-1.629-0.585-3.374-1.156-5.23-1.156 c-3.032,0-5.579,1.511-7.563,4.479c-2.243,3.334-9.033,11.271-11.131,13.642c-0.274,0.313-0.648,0.687-0.872,0.687 c-0.201,0-3.676-1.431-4.728-1.888c-24.087-10.463-42.37-35.624-44.877-39.867c-0.358-0.61-0.373-0.887-0.376-0.887 c0.088-0.323,0.898-1.135,1.316-1.554c1.223-1.21,2.548-2.805,3.83-4.348c0.607-0.731,1.215-1.463,1.812-2.153 c1.86-2.164,2.688-3.844,3.648-5.79l0.503-1.011c2.344-4.657,0.342-8.587-0.305-9.856c-0.531-1.062-10.012-23.944-11.02-26.348 c-2.424-5.801-5.627-8.502-10.078-8.502c-0.413,0,0,0-1.732,0.073c-2.109,0.089-13.594,1.601-18.672,4.802 c-5.385,3.395-14.495,14.217-14.495,33.249c0,17.129,10.87,33.302,15.537,39.453c0.116,0.155,0.329,0.47,0.638,0.922 c17.873,26.102,40.154,45.446,62.741,54.469c21.745,8.686,32.042,9.69,37.896,9.69c0.001,0,0.001,0,0.001,0 c2.46,0,4.429-0.193,6.166-0.364l1.102-0.105c7.512-0.666,24.02-9.22,27.775-19.655c2.958-8.219,3.738-17.199,1.77-20.458 C233.168,179.508,230.845,178.393,227.904,176.981z"
                  />
                  <path
                    d="M156.734,0C73.318,0,5.454,67.354,5.454,150.143c0,26.777,7.166,52.988,20.741,75.928L0.212,302.716 c-0.484,1.429-0.124,3.009,0.933,4.085C1.908,307.58,2.943,308,4,308c0.405,0,0.813-0.061,1.211-0.188l79.92-25.396 c21.87,11.685,46.588,17.853,71.604,17.853C240.143,300.27,308,232.923,308,150.143C308,67.354,240.143,0,156.734,0z M156.734,268.994c-23.539,0-46.338-6.797-65.936-19.657c-0.659-0.433-1.424-0.655-2.194-0.655c-0.407,0-0.815,0.062-1.212,0.188 l-40.035,12.726l12.924-38.129c0.418-1.234,0.209-2.595-0.561-3.647c-14.924-20.392-22.813-44.485-22.813-69.677 c0-65.543,53.754-118.867,119.826-118.867c66.064,0,119.812,53.324,119.812,118.867 C276.546,215.678,222.799,268.994,156.734,268.994z"
                  />
                </g>
              </svg>
            </a>
            <button
              @click="emailInvoice(invoice.id)"
              class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                <path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z" />
                <path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z" />
              </svg>
            </button>
            <Link
              id="edit-invoice"
              v-if="$can('update-invoices')"
              :href="route('invoices.edit', invoice.id)"
              class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
            >
              <icons name="edit" class="h-5 w-5" />
            </Link>
            <button
              @click="hideInvoice()"
              class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
            >
              <icons name="cross" class="h-5 w-5" />
            </button>
          </div>
        </div>

        <div class="">
          <Details
            v-if="invoice"
            :invoice="invoice"
            @done="paymentAdded"
            @close="hideInvoice"
            @payment="addPayment"
            :settings="$page.props.settings"
          />
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>
