<script setup>
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, watch, onUpdated } from 'vue';

import Modal from '@/Jetstream/Modal.vue';
import CheckBox from '@/Shared/CheckBox.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import AutoComplete from '@/Shared/AutoComplete.vue';

const { t } = useI18n({});

const show = ref(false);
const dayData = ref(null);
const month = ref(null);
const status = ref(null);
const only_paid = ref(false);
const props = defineProps(['data', 'html', 'current_month', 'prev_month_link', 'next_month_link', 'only_status']);

month.value = props.current_month;
status.value = props.only_status || null;
only_paid.value = props.only_paid_invoices == 1;
onMounted(async () => {
  document.querySelectorAll('.amount').forEach(item => {
    item.addEventListener('click', event => {
      dayData.value = props.data[event.target.dataset.date] || {};
      if (dayData.value.date) {
        show.value = true;
      }
    });
  });
});

const monthChanged = selected => {
  let [year, month] = selected.target.value.split('-');
  router.visit(route('reports.daily_invoices', { year, month, only_paid: only_paid.value, status: status.value }), {
    preserveScroll: true,
  });
};

const reloadData = () => {
  router.visit(
    route('reports.daily_invoices', {
      status: status.value,
      only_paid: only_paid.value,
      year: month.value.split('-')[0],
      month: month.value.split('-')[1],
    }),
    { preserveScroll: true }
  );
};
</script>

<template>
  <AppLayout :title="t('Daily Invoices')">
    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-900 shadow-sm sm:rounded-lg">
          <div class="sm:rounded-lg">
            <div class="p-6 sm:rounded-t-lg border-b border-gray-200 dark:border-gray-700 block md:flex md:items-center md:justify-between">
              <div>
                <div class="text-2xl">
                  {{ t('Daily Invoices') }}
                </div>
                <div class="mt-1 text-gray-500 dark:text-gray-400">{{ t('Please review the result below') }}</div>
              </div>
              <div class="mt-4 md:mt-0">
                <AutoComplete
                  label=""
                  id="status"
                  :json="true"
                  v-model="status"
                  :searchable="false"
                  @update:modelValue="reloadData"
                  :placeholder="t('All')"
                  :suggestions="[
                    { value: '', label: t('All') },
                    { value: 'pending', label: t('pending') },
                    { value: 'paid', label: t('paid') },
                    { value: 'overdue', label: t('overdue') },
                    { value: 'canceled', label: t('canceled') },
                  ]"
                />
                <!-- <CheckBox v-model:checked="only_paid" @change="reloadData" class="mr-2" label="Only paid invoice" /> -->
              </div>
            </div>

            <div class="daily-invoices bg-white dark:bg-gray-900 border-b dark:border-gray-700 sm:rounded-b-lg">
              <div class="flex items-center justify-between px-4 mt-6">
                <Link
                  :href="prev_month_link"
                  class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-700 border-2 dark:border-gray-700 rounded-md"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="w-5 h-5"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                  </svg>
                </Link>
                <!-- v-model="month" -->
                <input
                  id="month"
                  type="month"
                  name="month"
                  :value="current_month"
                  @change="monthChanged"
                  class="appearance-none border-0 w-auto min-w-0 max-w-full bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 text-lg font-bold py-1 px-2 focus:outline-hidden focus:ring-1 focus:ring-inset focus:ring-blue-500"
                />
                <!-- <div class="flex items-center justify-center text-xl font-bold uppercase">{{ current_month }}</div> -->
                <Link
                  :href="next_month_link"
                  class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-700 border-2 dark:border-gray-700 rounded-md"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="w-5 h-5"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                  </svg>
                </Link>
              </div>
              <div v-html="html"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <Modal :show="show" :closeable="true" @close="show = false" maxWidth="sm">
      <div class="px-6 pt-3 pb-6 print:px-0 bg-gray-100 dark:bg-gray-800">
        <div class="text-lg font-bold">
          {{ dayData.date }}
          <!-- {{ $date(dayData.date) }} -->
        </div>
        <!-- <p>{{ t('Please review the day data below') }}</p> -->
        <div>
          <div class="mt-4">
            <table class="w-full divide-y dark:divide-gray-700">
              <tbody>
                <tr>
                  <td>{{ t('Total') }}</td>
                  <td class="text-right">{{ $number(dayData.total) }}</td>
                </tr>
                <tr>
                  <td>{{ t('Tax') }}</td>
                  <td class="text-right">{{ $number(dayData.total_tax_amount) }}</td>
                </tr>
                <tr class="font-bold">
                  <td>{{ t('Grand Total') }}</td>
                  <td class="text-right">{{ $number(dayData.grand_total) }}</td>
                </tr>
                <tr>
                  <td>{{ t('Received') }}</td>
                  <td class="text-right">{{ $number(dayData.paid) }}</td>
                </tr>
                <tr class="font-bold">
                  <td>{{ t('Balance') }}</td>
                  <td class="text-right">{{ $number(dayData.grand_total - dayData.paid) }}</td>
                </tr>
                <!-- <tr>
                  <td></td>
                  <td></td>
                </tr>
                <tr class="font-bold text-lg">
                  <td>{{ t('Total Payment') }}</td>
                  <td class="text-right">{{ $number(dayData.payment) }}</td>
                </tr> -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>
