<script setup>
import { route } from 'ziggy-js';
import { useI18n } from 'vue-i18n';
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

const details = ref(false);
const selected = ref(null);
const bulkDelete = ref(false);
const props = defineProps(['activities']);
const form = useForm({ selection: [], force: false });
const search = useForm({ search: '', trashed: null });
const indeterminate = computed(() => form.selection.length > 0 && form.selection.length < props.activities.data.length);

onMounted(() => {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  search.trashed = urlParams.has('trashed') ? urlParams.get('trashed') : '';
});

const showDetails = row => {
  selected.value = row;
  details.value = true;
};

const closeDetails = () => {
  details.value = false;
  selected.value = null;
};

const closeModal = () => {
  bulkDelete.value = false;
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
    .get(route('activity'), { preserveState: true });
};

const resetSearch = () => {
  search.search = '';
  search.trashed = null;
  searchNow();
};
</script>

<template>
  <AppLayout :title="t('Activity Log')">
    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-900 shadow-sm sm:rounded-lg">
          <div class="sm:rounded-lg">
            <div class="p-6 sm:rounded-t-lg border-b border-gray-200 dark:border-gray-700 block md:flex md:items-center md:justify-between">
              <div>
                <div class="text-2xl">{{ t('Activity Log') }}</div>
                <div class="mt-1 text-gray-500 dark:text-gray-400">{{ t('Please review the result below') }}</div>
              </div>
              <div class="mt-4 sm:mt-0 block sm:flex sm:items-center sm:gap-4">
                <form @submit.prevent="searchNow">
                  <SearchFilter @reset="resetSearch()" v-model="search.search" :dropdown="false"> </SearchFilter>
                </form>
              </div>
            </div>

            <div class="bg-gray-50 dark:bg-black/50 border-b dark:border-gray-700 sm:rounded-b-lg">
              <div v-if="activities && activities.data && activities.data.length">
                <div class="flex flex-col">
                  <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                      <div class="relative overflow-hidden ring-1 ring-black/5">
                        <table class="min-w-full table-fixed divide-y divide-gray-300 dark:divide-gray-700">
                          <thead class="bg-gray-50 dark:bg-black/50">
                            <tr>
                              <th scope="col" class="pl-5 pr-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ t('Created at') }}
                              </th>
                              <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ t('Name') }}
                              </th>
                              <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ t('Description') }}
                              </th>
                              <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ t('By') }}
                              </th>
                            </tr>
                          </thead>
                          <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                            <tr
                              :key="item.id"
                              v-for="item in props.activities.data"
                              :class="[
                                form.selection.includes(item.id) && 'bg-gray-50 dark:bg-gray-800/50',
                                item.deleted_at && 'bg-red-100 dark:bg-red-900',
                              ]"
                            >
                              <td class="whitespace-nowrap pl-5 pr-3 py-4 text-sm font-medium" @click="showDetails(item)">
                                {{ $datetime(item.created_at) }}
                              </td>
                              <td class="whitespace-nowrap px-3 py-4 text-sm" @click="showDetails(item)">{{ item.log_name }}</td>
                              <td class="min-w-lg px-3 py-4 text-sm" @click="showDetails(item)">
                                <div class="min-w-[400px]">{{ item.description }}</div>
                              </td>
                              <td class="whitespace-nowrap px-3 py-4 text-sm" @click="showDetails(item)">
                                {{ item.causer ? item.causer.name : '' }}
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                  <pagination class="m-4" :meta="props.activities.meta" :links="props.activities.links" />
                </div>
              </div>
              <div v-else class="py-4 px-6">{{ t('There is no data to display.') }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Modal :show="details" max-width="2xl" @close="closeDetails()">
      <div class="relative bg-gray-100 dark:bg-gray-800">
        <div
          class="absolute top-0 right-0 hidden py-1.5 px-2 sm:flex sm:items-center sm:justify-center border-l border-b dark:border-gray-700 rounded-bl-lg hover:bg-gray-700 dark:hover:bg-gray-900 hover:text-gray-100 dark:hover:text-gray-100"
        >
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

        <div class="p-6 sm:flex sm:items-start">
          <div v-if="selected" class="flex-1">
            <h3 class="-mt-2 text-lg font-bold leading-6 text-gray-900 dark:text-gray-100" id="modal-title">{{ selected.description }}</h3>
            <div class="p-4 -mx-6 -mb-6 md:rounded-b-lg">
              <div class="bg-white dark:bg-gray-900 w-full -mx-4 md:mx-0 md:rounded-md overflow-x-auto print:m-0">
                <table class="w-full my-4 table-fixed">
                  <tbody>
                    <tr class="bg-white dark:bg-gray-900">
                      <td class="px-6 py-2 w-32 whitespace-nowrap">{{ t('Subject Id') }}</td>
                      <td class="px-6 py-2">{{ selected.subject_id }}</td>
                    </tr>
                    <tr class="bg-gray-50 dark:bg-gray-900/50">
                      <td class="px-6 py-2 w-32 whitespace-nowrap">{{ t('Subject Type') }}</td>
                      <td class="px-6 py-2">{{ selected.subject_type }}</td>
                    </tr>
                    <tr class="bg-white dark:bg-gray-900">
                      <td class="px-6 py-2 w-32 whitespace-nowrap align-top">{{ t('Properties') }}</td>
                      <td class="px-6 py-2">
                        <pre class="text-sm font-mono tracking-wide whitespace-wrap">{{ selected.properties }}</pre>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>
