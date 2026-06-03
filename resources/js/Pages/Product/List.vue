<script setup>
import { route } from 'ziggy-js';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

import Button from '@/Shared/Button.vue';
import Modal from '@/Jetstream/Modal.vue';
import Barcode from '@/Shared/Barcode.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Shared/Pagination.vue';
import RowActions from '@/Shared/RowActions.vue';
import SelectInput from '@/Shared/SelectInput.vue';
import SearchFilter from '@/Shared/SearchFilter.vue';
import DialogModal from '@/Jetstream/DialogModal.vue';
import DangerButton from '@/Jetstream/DangerButton.vue';
import SecondaryButton from '@/Jetstream/SecondaryButton.vue';

const { t } = useI18n({});
const alerts = ref(null);
const details = ref(false);
const selected = ref(null);
const bulkDelete = ref(false);
const props = defineProps(['products']);
const form = useForm({ selection: [], force: false });
const search = useForm({ search: '', trashed: null });
const indeterminate = computed(() => form.selection.length > 0 && form.selection.length < props.products.data.length);

onMounted(() => {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  alerts.value = urlParams.has('alerts') ? urlParams.get('alerts') : null;
  search.trashed = urlParams.has('trashed') ? urlParams.get('trashed') : null;
});

const deleteRow = (id, force = false) => {
  router.delete(route('products.destroy' + (force ? '.permanently' : ''), id));
};

const restoreRow = id => {
  router.put(route('products.restore', id));
};

const showDetails = row => {
  //   selected.value = row;
  //   details.value = true;
};

const closeDetails = () => {
  details.value = false;
  selected.value = null;
};

const closeModal = () => {
  bulkDelete.value = false;
};

const confirmDelete = force => {
  form.force = force || false;
  bulkDelete.value = true;
};

const deleteSelected = force => {
  form.delete(route('products.destroy.many'), {
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
    .get(route('products.index'), { preserveState: true });
};

const resetSearch = () => {
  search.search = '';
  search.trashed = null;
  searchNow();
};

const print = () => {
  window.print();
};
</script>

<template>
  <AppLayout :title="t('List Products')">
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
                    {{ t('Products') }}
                    <span class="text-base">
                      {{ alerts == 'yes' ? '(' + t('Low Stock') + ')' : '' }}
                      {{ { with: '(' + t('With Trashed') + ')', only: '(' + t('Only Trashed') + ')' }[search.trashed] }}
                    </span>
                  </div>
                  <div class="mt-1 text-gray-500 dark:text-gray-400">{{ t('Please review the result below') }}</div>
                </div>
                <div class="mt-4 sm:mt-0 block sm:flex sm:items-center sm:gap-4">
                  <Button v-if="$can('create-products')" :href="route('products.create')">{{ t('Add New Product') }}</Button>
                  <form @submit.prevent="searchNow">
                    <SearchFilter @reset="resetSearch()" v-model="search.search">
                      <label class="block">{{ t('Trashed') }}:</label>
                      <SelectInput v-model="search.trashed" @change="searchNow()" class="mt-1 w-full">
                        <option :value="null">{{ t('Not Trashed') }}</option>
                        <option value="with">{{ t('With Trashed') }}</option>
                        <option value="only">{{ t('Only Trashed') }}</option>
                      </SelectInput>
                    </SearchFilter>
                  </form>
                </div>
              </div>

              <div class="bg-gray-50 dark:bg-black/50 border-b dark:border-gray-700 sm:rounded-b-lg">
                <div v-if="products && products.data && products.data.length">
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
                                    :checked="indeterminate || form.selection.length === products.data.length"
                                    @change="form.selection = $event.target.checked ? products.data.map(p => p.id) : []"
                                    class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded-sm border-gray-300 text-blue-600 focus:ring-blue-500 sm:left-6 dark:bg-gray-700 dark:border-gray-700"
                                  />
                                </th>
                                <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                  {{ t('Name') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                  {{ t('Price') }}
                                </th>
                                <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                  {{ t('Tax Rates') }}
                                </th>
                                <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                  {{ t('Tax Method') }}
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
                                :key="item.id"
                                v-for="item in products.data"
                                :class="[
                                  form.selection.includes(item.id) && 'bg-gray-50 dark:bg-gray-800/50',
                                  item.deleted_at && 'bg-red-100 dark:bg-red-900',
                                ]"
                              >
                                <td class="relative w-12 px-6 sm:w-16 sm:px-8">
                                  <div v-if="form.selection.includes(item.id)" class="absolute inset-y-0 left-0 w-0.5 bg-blue-600"></div>
                                  <input
                                    type="checkbox"
                                    :value="item.id"
                                    v-model="form.selection"
                                    class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded-sm border-gray-300 text-blue-600 focus:ring-blue-500 sm:left-6 dark:bg-gray-700 dark:border-gray-700"
                                  />
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                  {{ item.name }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium">
                                  {{ $currency(item.price) }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                  {{ item.taxes?.map(t => t.name).join(', ') || '' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm w-28">
                                  <span v-if="item.taxes && item.taxes.length">{{ t(item.tax_method) }}</span>
                                </td>
                                <td class="px-3 py-4 text-sm">
                                  <div
                                    class="flex items-center flex-wrap gap-x-4 gap-y-2 min-w-[200px] max-w-xs"
                                    v-html="$extra_attributes(item.extra_attributes)"
                                  ></div>
                                </td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 w-20">
                                  <row-actions
                                    :row="item"
                                    pm="products"
                                    property="name"
                                    :deleteFn="deleteRow"
                                    :restoreFn="restoreRow"
                                    :editStr="route('products.edit', item.id)"
                                  />
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>

                    <pagination class="m-4" :meta="products.meta" :links="products.links" />
                  </div>
                </div>
                <div v-else class="py-4 px-6">{{ t('There is no data to display.') }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Modal :show="details" max-width="sm" @close="closeDetails()">
      <div class="relative print:static print:w-full">
        <div
          class="absolute print:hidden top-0 right-0 hidden py-1.5 px-2 sm:flex sm:items-center sm:justify-center border-l border-b dark:border-gray-700 rounded-bl-lg hover:bg-gray-700 dark:hover:bg-gray-900 hover:text-gray-100 dark:hover:text-gray-100"
        >
          <button
            @click="print()"
            class="flex items-center justify-center h-8 w-8 mr-2 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
          >
            <icons name="printer" class="h-5 w-5" />
          </button>
          <button type="button" @click="closeDetails()" class="rounded-md focus:outline-hidden focus:ring-0">
            <span class="sr-only">{{ t('Close') }}</span>
            <svg
              class="h-6 w-6"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              aria-hidden="true"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-6 sm:flex print:hidden">
          <div class="w-full text-center">
            <h3 class="text-lg font-bold leading-6 text-gray-900 dark:text-gray-100" id="modal-title">{{ selected.name }}</h3>
            <Barcode
              tag="svg"
              :value="selected.code"
              :options="{ format: selected.symbology, height: 38, fontSize: 14 }"
              class="mt-4 mx-auto border rounded-md overflow-hidden text-left bg-white"
            />
            <div class="mt-4 border dark:border-gray-700 rounded-md overflow-hidden text-left">
              <dl>
                <div class="bg-white dark:bg-gray-900 px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 print:grid print:grid-cols-3">
                  <dt class="text-sm text-gray-500">{{ t('Tax Rate') }}</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:col-span-2 sm:mt-0">{{ selected.tax_rate?.name || '' }}</dd>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 print:grid print:grid-cols-3">
                  <dt class="text-sm text-gray-500">{{ t('Tax Method') }}</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:col-span-2 sm:mt-0">
                    {{ selected.tax_method }}
                  </dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
        <div class="hidden print:block print:w-full print:-mt-2">
          <table class="table-fixed print:w-full">
            <tr :key="i" v-for="i in [0, 1, 2, 3]">
              <td :key="i" v-for="i in [0, 1]" class="w-1/2 p-2">
                <div class="w-full text-center border rounded-md p-4">
                  <h3 class="text-lg font-bold leading-6 text-gray-900 dark:text-gray-100" id="modal-title">{{ selected.name }}</h3>
                  <Barcode
                    tag="svg"
                    :value="selected.code"
                    :options="{ format: selected.symbology, height: 38, fontSize: 14 }"
                    class="mt-2 mx-auto rounded-md overflow-hidden text-left bg-white"
                  />
                  <div class="mt-2 overflow-hidden text-left">
                    <dl>
                      <div class="bg-white dark:bg-gray-900 px-2 py-1 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 print:grid print:grid-cols-3">
                        <dt class="text-sm text-gray-500">{{ t('Tax Rate') }}</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 sm:col-span-2 print:col-span-2 sm:mt-0">
                          : {{ selected.tax_rate?.name || '' }}
                        </dd>
                      </div>
                      <div
                        class="bg-gray-50 dark:bg-gray-900/50 px-2 py-1 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 print:grid print:grid-cols-3"
                      >
                        <dt class="text-sm text-gray-500">{{ t('Tax Method') }}</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 sm:col-span-2 print:col-span-2 sm:mt-0">
                          : {{ selected.tax_method }}
                        </dd>
                      </div>
                    </dl>
                  </div>
                </div>
              </td>
            </tr>
          </table>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>
