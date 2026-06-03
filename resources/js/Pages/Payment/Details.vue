<script setup>
import { useI18n } from 'vue-i18n';
import { hasValueWithZero } from '@/Core/helpers';

const { t } = useI18n({});
const props = defineProps({ payment: Object });
</script>

<template>
  <Head
    :title="
      t('Payment No. {x}', { x: ($page.props.settings?.use_company_number == 1 ? payment.company_number : payment.number) || payment.id })
    "
  />
  <div
    v-if="payment"
    class="mt-auto max-w-4xl print:min-w-[700px] mx-auto h-full bg-gray-100 dark:bg-gray-800 -m-6 pt-3 pb-6 rounded-md leading-relaxed print:bg-white print:mt-0 print:pt-0 print:h-full print:overflow-visible"
  >
    <div
      class="relative bg-white dark:bg-gray-900 p-4 rounded-md overflow-x-auto border dark:border-gray-700 print:border-0 print:shadow-none print:pt-0"
    >
      <div class="flex justify-end print:flex">
        <div class="text-left sm:text-right max-w-md print:text-right">
          <div v-if="payment.company.logo || $page.props.logo" class="h-20 mb-2 flex items-center justify-end dark:hidden print:flex">
            <img v-if="payment.company.logo" :src="payment.company.logo" alt="" class="h-full max-h-48" />
            <img v-else-if="$page.props.logo" :src="$page.props.logo" alt="" class="h-full max-h-48" />
          </div>
          <div
            v-if="payment.company.logo_dark || $page.props.logo_dark"
            class="h-20 mb-2 items-center justify-end hidden dark:flex print:hidden"
          >
            <img v-if="payment.company.logo_dark" :src="payment.company.logo_dark" alt="" class="h-full max-h-48" />
            <img v-else-if="$page.props.logo" :src="$page.props.logo" alt="" class="h-full max-h-48" />
          </div>
          <h2 class="text-2xl font-extrabold" v-if="payment.company.show_name == 1">{{ payment.company.name }}</h2>
          <!-- <div class="font-bold" v-if="payment.company.contact_person">
            ({{ payment.company.contact_person }})
          </div> -->
          <div v-if="payment.company.address">
            {{ payment.company.address || '' }}
            {{ payment.company.city || '' }}
            {{ payment.company.postal_code || '' }}
            {{ payment.company.state || '' }}
            {{ payment.company.country || '' }}
          </div>
          <div v-if="payment.company.phone">{{ payment.company.phone }}</div>
          <div v-if="payment.company.email">{{ payment.company.email }}</div>
          <template v-if="payment.company.extra_attributes">
            <div v-for="field of Object.keys(payment.company.extra_attributes)" :key="field">
              <template v-if="hasValueWithZero(payment.company.extra_attributes[field])">
                <span class="text-gray-500 dark:text-gray-400">{{ t(field) }}:</span>
                {{ payment.company.extra_attributes[field] }}
              </template>
            </div>
          </template>
        </div>
      </div>

      <div class="mt-4 w-full">
        <h1 class="text-6xl uppercase font-extrabold">{{ t('Payment Note') }}</h1>
        <!-- <div class="mt-4 flex items-center justify-center">
          <div class="inline-flex bg-white p-2 rounded-md mx-auto">
            <svg id="barcode" class="mt-px"></svg>
          </div>
        </div> -->
      </div>

      <div class="mt-6 block sm:flex print:flex gap-x-6 gap-y-2">
        <div class="w-full min-w-[250px]">
          <div class="flex gap-1">
            <div class="w-24 md:w-28 print:w-28">{{ t('Payment No.') }}:</div>
            {{ $page.props.settings?.use_company_number == 1 ? payment.company_number : payment.number }}
          </div>
          <div class="flex gap-1">
            <div class="w-24 md:w-28 print:w-28">{{ t('Reference') }}:</div>
            {{ payment.reference }}
          </div>
          <div class="flex gap-1">
            <div class="w-24 md:w-28 print:w-28">{{ t('Invoice No.') }}:</div>
            {{
              ($page.props.settings?.use_company_number == 1 ? payment.invoice.company_number : payment.invoice?.number) ||
              payment.invoice_id
            }}
          </div>
        </div>
        <div class="w-full min-w-[250px]">
          <div class="flex gap-1">
            <div class="w-24 md:w-28 print:w-28">{{ t('Date') }}:</div>
            {{ $date(payment.date) }}
          </div>
          <div class="flex gap-1">
            <div class="w-24 md:w-28 print:w-28">{{ t('Created at') }}:</div>
            {{ $datetime(payment.created_at) }}
          </div>
        </div>
      </div>
      <div v-if="payment.extra_attributes" class="grid grid-cols-2 gap-x-6 gap-y-2">
        <div class="flex gap-1" v-for="field of Object.keys(payment.extra_attributes)" :key="field">
          <template v-if="hasValueWithZero(payment.extra_attributes[field])">
            <div class="w-24 md:w-28 print:w-28">{{ t(field) }}:</div>
            {{ payment.extra_attributes[field] }}
          </template>
        </div>
      </div>

      <div class="mt-6 text-left w-full flex items-start">
        <div class="w-24 md:w-28 print:w-28">{{ t('Received from') }}:</div>
        <div>
          <div>{{ payment.customer.name }}</div>
          <div v-if="payment.customer?.company" class="font-bold">{{ payment.customer?.company }}</div>
          <div v-if="payment.customer?.address">
            {{ payment.customer?.address || '' }}
            {{ payment.customer?.city || '' }}
            {{ payment.customer?.postal_code || '' }}
            {{ payment.customer?.state || '' }}
            {{ payment.customer?.country || '' }}
          </div>
          <div v-if="payment.customer.phone">{{ t('Tel') }}: {{ payment.customer.phone }}</div>
          <div v-if="payment.customer.email">{{ t('Email') }}: {{ payment.customer.email }}</div>
          <template v-if="payment.customer.extra_attributes">
            <div v-for="field of Object.keys(payment.customer.extra_attributes)" :key="field">
              <template v-if="hasValueWithZero(payment.customer.extra_attributes[field])">
                <span class="text-gray-500 dark:text-gray-400">{{ t(field) }}:</span>
                {{ payment.customer.extra_attributes[field] }}
              </template>
            </div>
          </template>
        </div>
      </div>

      <div v-if="payment.attachment" class="print:hidden mt-4 p-4 w-full border dark:border-gray-700 rounded-md">
        {{ t('This record has an attachment') }},
        <a :href="payment.attachment" target="_blank" class="link">{{ t('click here to view') }}</a>
      </div>

      <div class="mt-6 block sm:flex print:flex items-center gap-x-6 gap-y-2">
        <div class="text-left text-xl w-full flex items-start">
          <div class="w-24 md:w-28 print:w-28">{{ t('Amount') }}:</div>
          <div class="font-bold">{{ $currency(payment.amount) }}</div>
        </div>

        <div v-if="payment.method" class="text-left w-full">
          <div class="flex items-start">
            <div class="mr-2">{{ t('Method') }}:</div>
            <div class="capitalize">{{ payment.method }}</div>
          </div>
          <div v-if="payment.fees" class="flex items-start">
            <div class="mr-2">{{ t(payment.method + ' Gateway Fees') }}:</div>
            <div class="capitalize">{{ $currency(payment.fees) }}</div>
          </div>
          <div v-if="payment.fees" class="flex items-start">
            <div class="mr-2">{{ t('Total Charged') }}:</div>
            <div class="capitalize font-bold">{{ $currency(Number(payment.amount) + Number(payment.fees)) }}</div>
          </div>
        </div>
      </div>

      <div v-if="payment.note" class="mt-6 w-full whitespace-pre-wrap">
        {{ payment.note }}
      </div>
    </div>

    <div class="mt-auto pt-4 w-full text-center text-sm text-gray-500 hidden print:block">
      {{ t('This is computer generated document, no signature required.') }}
    </div>
  </div>
</template>
