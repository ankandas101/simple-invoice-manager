<script setup>
import { route } from 'ziggy-js';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const { t } = useI18n({});
const props = defineProps(['data']);
const user = computed(() => usePage().props.auth.user);
const impersonate = computed(() => (usePage().props.is_impersonating ? usePage().props.user : null));
</script>

<template>
  <AppLayout :title="t('Dashboard')">
    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
          <div>
            <div class="p-6">
              <div class="text-2xl">
                {{ t('Welcome to') }}
                {{ $settings?.name || 'SSM' }} {{ t('management portal') }}.
              </div>
              <div v-if="!impersonate && !user?.roles.find(r => r.name == 'customer')" class="mt-2 text-gray-500 dark:text-gray-400">
                {{
                  t('Last 30 days invoices and quotations with total products & customers followed by invoice statistics and quick links.')
                }}
              </div>
            </div>

            <div v-if="data" class="p-6 bg-blue-50 dark:bg-blue-900/10 border-b border-t border-blue-100 dark:border-gray-700">
              <div v-if="!impersonate && !user?.roles.find(r => r.name == 'customer')" class="mb-6">
                <h3 class="text-lg leading-6 font-semibold">{{ t('Last 30 days') }}</h3>
                <dl
                  class="mt-2 grid grid-cols-1 rounded-lg bg-white dark:bg-gray-900 overflow-hidden border dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700 md:grid-cols-2 lg:grid-cols-4 md:divide-x lg:divide-y-0 lg:divide-x"
                >
                  <div class="px-4 py-5 sm:p-6">
                    <dt class="text-base font-normal">{{ t('Invoices Amount') }}</dt>
                    <dd class="mt-1 flex justify-between items-end">
                      <div class="flex items-baseline text-2xl font-semibold text-blue-600 dark:text-blue-400">
                        {{ $currency(data.invoices) }}
                      </div>
                      <span
                        v-if="data.invoices != 0 && data.previous_invoices != 0"
                        class="flex items-center space-x-1 text-sm font-medium leading-none"
                        :class="data.invoices > data.previous_invoices ? 'text-green-500' : 'text-red-500'"
                      >
                        <icons v-if="data.invoices > data.previous_invoices" name="up" />
                        <icons v-else name="down" />
                        <span>
                          {{ $number((data.invoices / data.previous_invoices) * 100 - 100, '', { maximumFractionDigits: 0 }) }}%
                        </span>
                      </span>
                    </dd>
                  </div>
                  <div class="px-4 py-5 sm:p-6">
                    <dt class="text-base font-normal">{{ t('Payments Amount') }}</dt>
                    <dd class="mt-1 flex justify-between items-center">
                      <div class="flex items-baseline text-2xl font-semibold text-blue-600 dark:text-blue-400">
                        {{ $currency(data.payments || 0) }}
                      </div>
                      <span
                        v-if="data.payments != 0 && data.previous_payments != 0"
                        class="flex items-center space-x-1 text-sm font-medium leading-none"
                        :class="data.payments > data.previous_payments ? 'text-green-500' : 'text-red-500'"
                      >
                        <icons v-if="data.payments > data.previous_payments" name="up" />
                        <icons v-else name="down" />
                        <span>
                          {{ $number((data.payments / data.previous_payments) * 100 - 100, '', { maximumFractionDigits: 0 }) }}%
                        </span>
                      </span>
                    </dd>
                  </div>
                  <div class="px-4 py-5 sm:p-6">
                    <dt class="text-base font-normal">{{ t('Quotations') }}</dt>
                    <dd class="mt-1 flex justify-between items-center">
                      <div class="flex items-baseline text-2xl font-semibold text-blue-600 dark:text-blue-400">
                        {{ data.quotations || 0 }}
                      </div>
                    </dd>
                  </div>
                  <div class="px-4 py-5 sm:p-6">
                    <dt class="text-base font-normal">{{ t('Total Customers') }}</dt>
                    <dd class="mt-1 text-2xl font-semibold text-blue-600 dark:text-blue-400">
                      {{ data.customers }}
                    </dd>
                  </div>
                </dl>
              </div>

              <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">{{ t('Invoice Statistics') }}</h3>
                <dl class="mt-2 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                  <div
                    class="overflow-hidden rounded-lg bg-green-100 dark:bg-green-700 border border-green-200 dark:border-green-800 px-4 py-5 sm:p-6"
                  >
                    <dt class="truncate text-sm font-medium text-green-700 dark:text-green-200">{{ t('Total Paid Invoices') }}</dt>
                    <dd
                      class="flex items-baseline justify-between mt-1 text-2xl font-semibold tracking-tight text-green-900 dark:text-green-100"
                    >
                      {{ data.paid || 0 }}
                      <p class="ml-2 flex items-baseline text-sm text-green-700 dark:text-green-200">
                        <span class="sr-only"> {{ t('Percentage') }} </span>
                        {{ $number(((data.paid || 0) / data.total) * 100) }}%
                      </p>
                    </dd>
                  </div>
                  <div
                    class="overflow-hidden rounded-lg bg-blue-100 dark:bg-blue-700 border border-blue-200 dark:border-blue-800 px-4 py-5 sm:p-6"
                  >
                    <dt class="truncate text-sm font-medium text-blue-700 dark:text-blue-200">{{ t('Total Pending Invoices') }}</dt>
                    <dd
                      class="flex items-baseline justify-between mt-1 text-2xl font-semibold tracking-tight text-blue-900 dark:text-blue-100"
                    >
                      {{ data.pending || 0 }}
                      <p class="ml-2 flex items-baseline text-sm text-blue-700 dark:text-blue-200">
                        <span class="sr-only"> {{ t('Percentage') }} </span>
                        {{ $number(((data.pending || 0) / data.total) * 100) }}%
                      </p>
                    </dd>
                  </div>
                  <div
                    class="overflow-hidden rounded-lg bg-yellow-100 dark:bg-yellow-700 border border-yellow-200 dark:border-yellow-800 px-4 py-5 sm:p-6"
                  >
                    <dt class="truncate text-sm font-medium text-yellow-700 dark:text-yellow-200">{{ t('Total Overdue Invoices') }}</dt>
                    <dd
                      class="flex items-baseline justify-between mt-1 text-2xl font-semibold tracking-tight text-yellow-900 dark:text-yellow-100"
                    >
                      {{ data.overdue || 0 }}
                      <p class="ml-2 flex items-baseline text-sm text-yellow-700 dark:text-yellow-200">
                        <span class="sr-only"> {{ t('Percentage') }} </span>
                        {{ $number(((data.overdue || 0) / data.total) * 100) }}%
                      </p>
                    </dd>
                  </div>
                  <div
                    class="overflow-hidden rounded-lg bg-red-100 dark:bg-red-700 border border-red-200 dark:border-red-800 px-4 py-5 sm:p-6"
                  >
                    <dt class="truncate text-sm font-medium text-red-700 dark:text-red-200">{{ t('Total Canceled Invoices') }}</dt>
                    <dd
                      class="flex items-baseline justify-between mt-1 text-2xl font-semibold tracking-tight text-red-900 dark:text-red-100"
                    >
                      {{ data.canceled || 0 }}
                      <p class="ml-2 flex items-baseline text-sm text-red-700 dark:text-red-200">
                        <span class="sr-only"> {{ t('Percentage') }} </span>
                        {{ $number(((data.canceled || 0) / data.total) * 100) }}%
                      </p>
                    </dd>
                  </div>
                </dl>
              </div>
            </div>

            <div v-if="!impersonate && $isAdmin" class="grid grid-cols-1 md:grid-cols-2">
              <div class="p-6">
                <div class="flex items-center">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-8 h-8 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                    />
                  </svg>
                  <div class="ml-4 text-lg leading-7 font-semibold">
                    <Link :href="route('products.index')">{{ t('Products') }}</Link>
                  </div>
                </div>

                <div class="ml-12 flex flex-wrap gap-y-4 gap-x-6">
                  <div class="w-full mt-2 text-sm text-gray-500 dark:text-gray-400">{{ t('Manage products for invoicing') }}</div>

                  <template
                    v-for="[title, rn] of [
                      ['List', 'products.index'],
                      ['Add', 'products.create'],
                      ['Import', 'products.import'],
                    ]"
                    :key="rn"
                  >
                    <Link
                      :href="route(rn)"
                      class="inline-flex items-center text-sm font-semibold text-blue-700 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                      {{ t(title) }}
                    </Link>
                  </template>
                </div>
              </div>

              <div class="p-6 border-t dark:border-gray-700 md:border-t-0 md:border-l">
                <div class="flex items-center">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="w-8 h-8 text-gray-400"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"
                    />
                  </svg>
                  <div class="ml-4 text-lg leading-7 font-semibold">
                    <Link :href="route('customers.index')">{{ t('Customers') }}</Link>
                  </div>
                </div>

                <div class="ml-12 flex flex-wrap gap-y-4 gap-x-6">
                  <div class="w-full mt-2 text-sm text-gray-500 dark:text-gray-400">{{ t('Manage your customers') }}</div>

                  <template
                    v-for="[title, rn] of [
                      ['List', 'customers.index'],
                      ['Add', 'customers.create'],
                      ['Import', 'customers.import'],
                    ]"
                    :key="rn"
                  >
                    <Link
                      :href="route(rn)"
                      class="inline-flex items-center text-sm font-semibold text-blue-700 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                      {{ t(title) }}
                    </Link>
                  </template>
                </div>
              </div>

              <div class="p-6 border-t dark:border-gray-700">
                <div class="flex items-center">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="w-8 h-8 text-gray-400"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"
                    />
                  </svg>
                  <div class="ml-4 text-lg leading-7 font-semibold">
                    <Link :href="route('invoices.index')">{{ t('Invoices') }}</Link>
                  </div>
                </div>

                <div class="ml-12 flex flex-wrap gap-y-4 gap-x-6">
                  <div class="w-full mt-2 text-sm text-gray-500 dark:text-gray-400">{{ t('Manage your invoices') }}</div>
                  <template
                    v-for="[title, rn] of [
                      ['List', 'invoices.index'],
                      ['Add', 'invoices.create'],
                    ]"
                    :key="rn"
                  >
                    <Link
                      :href="route(rn)"
                      class="inline-flex items-center text-sm font-semibold text-blue-700 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                      {{ t(title) }}
                    </Link>
                  </template>
                </div>
              </div>

              <div class="p-6 border-t dark:border-gray-700 md:border-l">
                <div class="flex items-center">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="w-8 h-8 text-gray-400"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"
                    />
                  </svg>
                  <div class="ml-4 text-lg leading-7 font-semibold">
                    <Link :href="route('quotations.index')">{{ t('Quotations') }}</Link>
                  </div>
                </div>

                <div class="ml-12 flex flex-wrap gap-y-4 gap-x-6">
                  <div class="w-full mt-2 text-sm text-gray-500 dark:text-gray-400">{{ t('Manage your quotations') }}</div>
                  <template
                    v-for="[title, rn] of [
                      ['List', 'quotations.index'],
                      ['Add', 'quotations.create'],
                    ]"
                    :key="rn"
                  >
                    <Link
                      :href="route(rn)"
                      class="inline-flex items-center text-sm font-semibold text-blue-700 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                      {{ t(title) }}
                    </Link>
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="hidden w-px h-px overflow-hidden">
      {{ t('List') }}
      {{ t('Add') }}
      {{ t('Import') }}
    </div>
  </AppLayout>
</template>
