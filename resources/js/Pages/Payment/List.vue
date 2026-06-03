<script setup>
import { route } from 'ziggy-js';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

import Details from './Details.vue';
import Button from '@/Shared/Button.vue';
import Modal from '@/Jetstream/Modal.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Shared/Pagination.vue';
import RowActions from '@/Shared/RowActions.vue';
import PaymentForm from '@/Pages/Payment/Form.vue';
import SelectInput from '@/Shared/SelectInput.vue';
import SearchFilter from '@/Shared/SearchFilter.vue';
import DialogModal from '@/Jetstream/DialogModal.vue';
import DangerButton from '@/Jetstream/DangerButton.vue';
import SecondaryButton from '@/Jetstream/SecondaryButton.vue';

const { t } = useI18n({});

const edit = ref(null);
const show = ref(false);
const payment = ref(null);
const showEdit = ref(false);
const bulkDelete = ref(false);
const props = defineProps(['payments', 'fields', 'companies']);
const form = useForm({ selection: [], force: false });
const search = useForm({ search: '', trashed: null, company: null });
const indeterminate = computed(() => form.selection.length > 0 && form.selection.length < props.payments.data.length);

onMounted(() => {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  search.company = urlParams.has('company') ? urlParams.get('company') : null;
  search.trashed = urlParams.has('trashed') ? urlParams.get('trashed') : null;
});

const copyLink = () => {
  let link = route('views.payment', { id: payment.value.id, hash: payment.value.hash });
  usePage().props.flash.message = navigator.clipboard?.writeText(link) ? t('Payment linked copied.') : '';
  setTimeout(() => {
    usePage().props.flash.message = '';
  }, 2000);
};

const print = () => {
  window.print();
};

const editRow = row => {
  edit.value = row;
  show.value = false;
  showEdit.value = true;
};

const paymentUpdated = e => {
  showEdit.value = false;
  router.reload({ only: ['payments'] });
};

const showPayment = ch => {
  show.value = true;
  payment.value = ch;
};

const hidePayment = ch => {
  show.value = false;
  payment.value = null;
};

const emailPayment = id => {
  router.post(route('payment.notification', id));
};

const deleteRow = (id, force = false) => {
  router.delete(route('payments.destroy' + (force ? '.permanently' : ''), id));
};

const restoreRow = id => {
  router.put(route('payments.restore', id));
};

const closeModal = () => {
  bulkDelete.value = false;
};

const confirmDelete = force => {
  form.force = force || false;
  bulkDelete.value = true;
};

const deleteSelected = force => {
  form.delete(route('payments.destroy.many'), {
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
    .get(route('payments.index'), { preserveState: true });
};

const resetSearch = () => {
  search.search = '';
  search.trashed = null;
  searchNow();
};
</script>

<template>
  <AppLayout :title="t('List Payments')">
    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-900 shadow-sm sm:rounded-lg">
          <div class="sm:rounded-lg">
            <div class="p-6 sm:rounded-t-lg border-b border-gray-200 dark:border-gray-700 block md:flex md:items-center md:justify-between">
              <div>
                <div class="text-2xl">
                  {{ t('Payments') }}
                  <span class="text-base">
                    {{ { with: '(' + t('With Trashed') + ')', only: '(' + t('Only Trashed') + ')' }[search.trashed] }}
                  </span>
                </div>
                <div class="mt-1 text-gray-500 dark:text-gray-400">{{ t('Please review the result below') }}</div>
              </div>
              <div class="mt-4 sm:mt-0 block sm:flex sm:items-center sm:gap-4">
                <!-- <Button v-if="$can('create-payments')" :href="route('payments.create')">{{ t('Add New Payment') }}</Button> -->
                <form @submit.prevent="searchNow">
                  <SearchFilter @reset="resetSearch()" v-model="search.search">
                    <label class="block">{{ t('Trashed') }}:</label>
                    <SelectInput v-model="search.trashed" @change="searchNow()" class="mt-1 w-full">
                      <option :value="null">{{ t('Not Trashed') }}</option>
                      <option value="with">{{ t('With Trashed') }}</option>
                      <option value="only">{{ t('Only Trashed') }}</option>
                    </SelectInput>

                    <label class="block mt-4">{{ t('Company') }}:</label>
                    <SelectInput v-model="search.company" @change="searchNow()" class="mt-1 w-full">
                      <option :value="null">{{ t('All') }}</option>
                      <option v-for="c of companies" :value="c.id">{{ c.name }}</option>
                    </SelectInput>
                  </SearchFilter>
                </form>
              </div>
            </div>

            <div class="bg-gray-50 dark:bg-black/50 border-b dark:border-gray-700 sm:rounded-b-lg">
              <div v-if="payments && payments.data && payments.data.length">
                <div class="flex flex-col">
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
                                  :checked="indeterminate || form.selection.length === payments.data.length"
                                  @change="form.selection = $event.target.checked ? payments.data.map(p => p.id) : []"
                                  class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded-sm border-gray-300 text-blue-600 focus:ring-blue-500 sm:left-6 dark:bg-gray-700 dark:border-gray-700"
                                />
                              </th>
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
                              <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">{{ t('Actions') }}</span>
                              </th>
                            </tr>
                          </thead>
                          <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                            <tr
                              :key="payment.id"
                              v-for="(payment, pi) in payments.data"
                              :class="[
                                form.selection.includes(payment.id) && 'bg-gray-50 dark:bg-gray-800/50',
                                payment.deleted_at && 'bg-red-100 dark:bg-red-900',
                              ]"
                            >
                              <td class="relative w-12 px-6 sm:w-16 sm:px-8">
                                <div v-if="form.selection.includes(payment.id)" class="absolute inset-y-0 left-0 w-0.5 bg-blue-600"></div>
                                <input
                                  type="checkbox"
                                  :value="payment.id"
                                  v-model="form.selection"
                                  class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded-sm border-gray-300 text-blue-600 focus:ring-blue-500 sm:left-6 dark:bg-gray-700 dark:border-gray-700"
                                />
                              </td>
                              <td class="whitespace-nowrap cursor-pointer px-3 py-4 text-sm font-medium" @click="showPayment(payment)">
                                <div class="flex items-center gap-1">
                                  <span class="text-gray-500 dark:text-gray-400">{{ t('Number') }}: </span>
                                  {{
                                    ($page.props.settings?.use_company_number == 1 ? payment.company_number : payment.number) || payment.id
                                  }}
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
                                  {{
                                    ($page.props.settings?.use_company_number == 1
                                      ? payment.invoice.company_number
                                      : payment.invoice?.number) || payment.invoice_id
                                  }}
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
                                  <span class="text-base">{{ $currency(payment.amount) }}</span>
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
                              <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 w-20">
                                <row-actions
                                  pm="payments"
                                  property="name"
                                  :row="payment"
                                  :editFn="editRow"
                                  :deleteFn="deleteRow"
                                  :restoreFn="restoreRow"
                                />
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

    <Modal :show="showEdit" max-width="2xl" :closeable="false" @close="() => (showEdit = false)">
      <PaymentForm :edit="{ data: edit }" :fields="fields" @done="paymentUpdated" @close="() => (showEdit = false)" />
    </Modal>

    <Modal :show="show" max-width="4xl" :closeable="true" @close="hidePayment">
      <div class="px-6 pt-3 pb-6 print:px-0 bg-gray-100 dark:bg-gray-800">
        <div v-if="show && payment" class="flex items-center justify-between print:hidden">
          <div class="text-lg">
            {{ t('Payment No.') }}
            {{ ($page.props.settings?.use_company_number == 1 ? payment.company_number : payment.number) || payment.id }}
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
              @click="copyLink()"
              class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
            >
              <icons name="copy" class="h-5 w-5" />
            </button>
            <button
              @click="emailPayment(payment.id)"
              class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                <path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z" />
                <path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z" />
              </svg>
            </button>
            <button
              type="button"
              id="edit-payment"
              @click="editRow(payment)"
              v-if="$can('update-payments')"
              class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
            >
              <icons name="edit" class="h-5 w-5" />
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
