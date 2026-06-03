<script setup>
import { route } from 'ziggy-js';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

import Button from '@/Shared/Button.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Shared/Pagination.vue';
import RowActions from '@/Shared/RowActions.vue';
import SelectInput from '@/Shared/SelectInput.vue';
import SearchFilter from '@/Shared/SearchFilter.vue';
import DialogModal from '@/Jetstream/DialogModal.vue';
import DangerButton from '@/Jetstream/DangerButton.vue';
import SecondaryButton from '@/Jetstream/SecondaryButton.vue';

const { t } = useI18n({});

const bulkDelete = ref(false);
const props = defineProps(['customer', 'transactions']);
const form = useForm({ selection: [], force: false });
const search = useForm({ search: '', trashed: null });
const indeterminate = computed(() => form.selection.length > 0 && form.selection.length < props.transactions.data.length);
</script>

<template>
  <AppLayout :title="t('Customer Statement')">
    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-900 shadow-sm sm:rounded-lg">
          <div class="sm:rounded-lg">
            <div class="p-6 sm:rounded-t-lg border-b border-gray-200 dark:border-gray-700 block md:flex md:items-center md:justify-between">
              <div>
                <div class="text-2xl">
                  {{ t('Customer Statement') }} ( {{ customer.name }}: {{ $currency(customer.balance) }}
                  <!-- ({{ customer.balance >= 0 ? t('due') : t('adv.') }}) -->
                  )
                </div>
                <div class="mt-1 text-gray-500 dark:text-gray-400">{{ t('Please review the result below') }}</div>
              </div>
              <div class="mt-4 sm:mt-0 block sm:flex sm:items-center sm:gap-4">
                <Button :href="route('customers.index')">{{ t('Back to Customers') }}</Button>
              </div>
            </div>

            <div class="bg-gray-50 dark:bg-black/50 border-b dark:border-gray-700 sm:rounded-b-lg">
              <div v-if="transactions && transactions.data && transactions.data.length">
                <div class="flex flex-col">
                  <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                      <div class="relative overflow-hidden ring-1 ring-black/5">
                        <table class="min-w-full table-fixed divide-y divide-gray-300 dark:divide-gray-700">
                          <thead class="bg-gray-50 dark:bg-black/50">
                            <tr>
                              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ t('Created at') }}
                              </th>
                              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ t('Invoice No.') }}
                              </th>
                              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ t('Type') }}
                              </th>
                              <th scope="col" class="py-3.5 px-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ t('Debit') }}
                              </th>
                              <th scope="col" class="py-3.5 px-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ t('Credit') }}
                              </th>
                            </tr>
                          </thead>
                          <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                            <tr :key="transaction.id" v-for="transaction in transactions.data">
                              <td class="px-3 py-4 text-sm min-w-[200px] max-w-xs">
                                {{ $datetime(transaction.created_at) }}
                              </td>
                              <td class="whitespace-nowrap px-3 py-4 text-sm font-medium">
                                <div>{{ transaction.invoice_id }}</div>
                              </td>
                              <td class="whitespace-nowrap capitalize px-3 py-4 text-sm font-medium">
                                <div>
                                  {{ transaction.type.split('-').join(' ') }}
                                </div>
                              </td>
                              <td class="whitespace-nowrap px-3 py-4 text-sm font-bold text-right">
                                <span v-if="transaction.amount < 0">{{ $number(transaction.amount) }}</span>
                              </td>
                              <td class="whitespace-nowrap px-3 py-4 text-sm font-bold text-right">
                                <span v-if="transaction.amount >= 0">{{ $number(transaction.amount) }}</span>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <!-- <Pagination class="m-4" :meta="transactions.meta" :links="transactions.links" /> -->
                </div>
              </div>
              <div v-else class="py-4 px-6">{{ t('There is no data to display.') }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
