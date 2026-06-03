<script setup>
// import JsBarcode from 'jsbarcode';
import { useI18n } from 'vue-i18n';
// import { onMounted, onUpdated } from 'vue';
import { hasValueWithZero } from '@/Core/helpers';
import Attachments from '@/Shared/Attachments.vue';

const { t } = useI18n({});
const props = defineProps({ quotation: Object, settings: Object });

// onMounted(() => {
//   //   JsBarcode('.barcode').init();
//   JsBarcode('#barcode', props.quotation.reference, {
//     width: 1,
//     margin: 0,
//     fontSize: 0,
//     height: '30',
//     format: 'CODE128',
//   });
// });

// onUpdated(() => {
//   JsBarcode('#barcode', props.quotation.reference, {
//     width: 1,
//     margin: 0,
//     fontSize: 0,
//     height: '30',
//     format: 'CODE128',
//   });
// });
</script>

<template>
  <Head
    :title="
      t('Quotation No. {x}', {
        x: ($page.props.settings?.use_company_number == 1 ? quotation.company_number : quotation.number) || quotation.id,
      })
    "
  />
  <div
    v-if="quotation"
    class="mt-auto max-w-4xl print:min-w-[700px] mx-auto h-full bg-gray-100 dark:bg-gray-800 -m-6 pt-3 pb-6 rounded-md leading-relaxed print:bg-white print:mt-0 print:pt-0 print:h-full print:overflow-visible"
  >
    <div
      class="bg-white dark:bg-gray-900 p-4 rounded-md overflow-x-auto border dark:border-gray-700 print:border-0 print:shadow-none print:pt-0"
    >
      <div class="flex justify-end print:flex">
        <div class="text-left sm:text-right max-w-md print:text-right">
          <div v-if="quotation.company.logo || $page.props.logo" class="h-20 mb-2 flex items-center justify-end dark:hidden print:flex">
            <img v-if="quotation.company.logo" :src="quotation.company.logo" alt="" class="h-full max-h-48" />
            <img v-else-if="$page.props.logo" :src="$page.props.logo" alt="" class="h-full max-h-48" />
          </div>
          <div
            v-if="quotation.company.logo_dark || $page.props.logo_dark"
            class="h-20 mb-2 items-center justify-end hidden dark:flex print:hidden"
          >
            <img v-if="quotation.company.logo_dark" :src="quotation.company.logo_dark" alt="" class="h-full max-h-48" />
            <img v-else-if="$page.props.logo" :src="$page.props.logo" alt="" class="h-full max-h-48" />
          </div>
          <h2 class="text-2xl font-extrabold" v-if="quotation.company.show_name == 1">{{ quotation.company.name }}</h2>
          <!-- <div class="font-bold" v-if="quotation.company.contact_person">
            ({{ quotation.company.contact_person }})
          </div> -->
          <div v-if="quotation.company.show_address">
            {{ quotation.company.address || '' }}
            {{ quotation.company.city || '' }}
            {{ quotation.company.postal_code || '' }}
            {{ quotation.company.state || '' }}
            {{ quotation.company.country || '' }}
          </div>
          <div v-if="quotation.company.phone">{{ quotation.company.phone }}</div>
          <div v-if="quotation.company.email">{{ quotation.company.email }}</div>
          <template v-if="quotation.company.extra_attributes">
            <div v-for="field of Object.keys(quotation.company.extra_attributes)" :key="field">
              <template v-if="hasValueWithZero(quotation.company.extra_attributes[field])">
                <span class="text-gray-500 dark:text-gray-400">{{ t(field) }}:</span>
                {{ quotation.company.extra_attributes[field] }}
              </template>
            </div>
          </template>
        </div>
      </div>

      <div class="mt-4 w-full">
        <h1 class="text-5xl uppercase font-extrabold">{{ t('Quotation') }}</h1>
        <!-- <div class="mt-4 flex items-center justify-center">
          <div class="inline-flex bg-white p-2 rounded-md mx-auto">
            <svg id="barcode" class="mt-px"></svg>
          </div>
        </div> -->
      </div>

      <div class="mt-6 block sm:flex print:flex gap-x-6 gap-y-2">
        <div class="w-full min-w-[250px]">
          <div class="flex gap-1">
            <div class="w-24 md:w-28 print:w-28">{{ t('Quotation No.') }}:</div>
            {{ ($page.props.settings?.use_company_number == 1 ? quotation.company_number : quotation.number) || quotation.id }}
          </div>
          <div class="flex gap-1">
            <div class="w-24 md:w-28 print:w-28">{{ t('Reference') }}:</div>
            {{ quotation.reference }}
          </div>
        </div>
        <div class="w-full min-w-[250px]">
          <div class="flex gap-1">
            <div class="w-24 md:w-28 print:w-28">{{ t('Date') }}:</div>
            {{ $date(quotation.date) }}
          </div>
          <div class="flex gap-1">
            <div class="w-24 md:w-28 print:w-28">{{ t('Created at') }}:</div>
            {{ $datetime(quotation.created_at) }}
          </div>
          <div v-if="quotation.expiry_date" class="flex gap-1">
            <div class="w-24 md:w-28 print:w-28">{{ t('Expiry Date') }}:</div>
            {{ $date(quotation.expiry_date) }}
          </div>
        </div>
      </div>
      <div v-if="quotation.extra_attributes" class="grid grid-cols-2 gap-x-6 gap-y-2">
        <div class="flex gap-1" v-for="field of Object.keys(quotation.extra_attributes)" :key="field">
          <template v-if="hasValueWithZero(quotation.extra_attributes[field])">
            <div class="w-24 md:w-28 print:w-28">{{ t(field) }}:</div>
            {{ quotation.extra_attributes[field] }}
          </template>
        </div>
      </div>

      <div class="mt-6 text-left w-full flex items-start">
        <div class="w-24 md:w-28 print:w-28">{{ t('Quote to') }}:</div>
        <div>
          <div>{{ quotation.customer.name }}</div>
          <div v-if="quotation.customer?.company" class="font-bold">{{ quotation.customer?.company }}</div>
          <div v-if="quotation.customer?.address">
            {{ quotation.customer?.address || '' }}
            {{ quotation.customer?.city || '' }}
            {{ quotation.customer?.postal_code || '' }}
            {{ quotation.customer?.state || '' }}
            {{ quotation.customer?.country || '' }}
          </div>
          <div v-if="quotation.customer.phone">{{ t('Tel') }}: {{ quotation.customer.phone }}</div>
          <div v-if="quotation.customer.email">{{ t('Email') }}: {{ quotation.customer.email }}</div>
          <template v-if="quotation.customer.extra_attributes">
            <div v-for="field of Object.keys(quotation.customer.extra_attributes)" :key="field">
              <template v-if="hasValueWithZero(quotation.customer.extra_attributes[field])">
                <span class="text-gray-500 dark:text-gray-400">{{ t(field) }}:</span>
                {{ quotation.customer.extra_attributes[field] }}
              </template>
            </div>
          </template>
        </div>
      </div>

      <div v-if="quotation.attachment" class="print:hidden mt-4 p-4 w-full border dark:border-gray-700 rounded-md">
        {{ t('This record has an attachment') }},
        <a :href="quotation.attachment" target="_blank" class="link">{{ t('click here to view') }}</a>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full mt-6 mb-4" style="min-width: 650px">
          <thead>
            <tr class="border-t-2 border-b-2 dark:border-gray-700">
              <th class="px-6 py-3 text-left">{{ t('Description') }}</th>
              <th class="px-6 py-3 w-28 text-center">{{ t('Quantity') }}</th>
              <th class="px-4 print:px-2 py-3 w-28 text-center">{{ t('Price') }}</th>
              <th v-if="$page.props.settings?.show_subtotal == 1" class="px-4 print:px-2 py-3 w-24 text-center">{{ t('Tax') }}</th>
              <th class="px-4 print:px-2 py-3 w-32 text-center">
                {{ $page.props.settings?.show_subtotal == 1 ? t('Subtotal') : t('Total') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(item, ii) in quotation.items" :key="'i_' + item.id">
              <!-- :class="{ 'bg-gray-50 dark:bg-gray-800': ii % 2 != 0 }" -->
              <tr class="group avoid">
                <td
                  class="align-top px-6"
                  :class="{
                    'pt-4 pb-2': ii == 0,
                    'pb-4 pt-2': ii == quotation.items.length - 1,
                    'py-2': ii > 0 && ii + i != quotation.items.length,
                  }"
                >
                  <span class="-ml-5 mr-1 text-xs">{{ ii + 1 }}.</span>
                  <span class="font-bold">{{ item.name }}</span>
                  <div v-if="item.details" class="whitespace-pre-wrap">{{ item.details }}</div>
                  <div v-if="item.extra_attributes" class="flex flex-wrap gap-x-6 gap-y-2">
                    <div v-for="field of Object.keys(item.extra_attributes)" :key="field">
                      <template v-if="hasValueWithZero(item.extra_attributes[field])">
                        <span class="text-gray-500 dark:text-gray-400">{{ t(field) }}:</span>
                        {{ item.extra_attributes[field] }}
                      </template>
                    </div>
                  </div>
                </td>
                <td
                  class="align-top px-6 w-32 text-right"
                  :class="{
                    'pt-4 pb-2': ii == 0,
                    'pb-4 pt-2': ii == quotation.items.length - 1,
                    'py-2': ii > 0 && ii + i != quotation.items.length,
                  }"
                >
                  {{ $number(item.quantity) }}
                </td>
                <td
                  class="align-top px-6 w-32 text-right"
                  :class="{
                    'pt-4 pb-2': ii == 0,
                    'pb-4 pt-2': ii == quotation.items.length - 1,
                    'py-2': ii > 0 && ii + i != quotation.items.length,
                  }"
                >
                  {{ $page.props.settings?.show_subtotal == 1 ? $currency(item.net_price) : $currency(item.unit_price) }}
                </td>
                <td
                  v-if="$page.props.settings?.show_subtotal == 1"
                  class="align-top px-4 print:px-2 w-24 text-right"
                  :class="{
                    'pt-4 pb-2': ii == 0,
                    'pb-4 pt-2': ii == quotation.items.length - 1,
                    'py-2': ii > 0 && ii + i != quotation.items.length,
                  }"
                >
                  {{ $currency(item.tax_amount) }}
                </td>
                <td
                  class="align-top px-6 w-32 text-right"
                  :class="{
                    'pt-4 pb-2': ii == 0,
                    'pb-4 pt-2': ii == quotation.items.length - 1,
                    'py-2': ii > 0 && ii + i != quotation.items.length,
                  }"
                >
                  <!-- {{ $number(item.total) }} -->
                  {{ $page.props.settings?.show_subtotal == 1 ? $currency(item.subtotal) : $currency(item.total) }}
                </td>
              </tr>
            </template>
          </tbody>
          <tfoot>
            <tr class="border-t-2 dark:border-gray-700">
              <th :colspan="$page.props.settings?.show_subtotal == 1 ? 4 : 3" class="px-6 pt-4 pb-0.5 text-right">
                {{ $page.props.settings?.show_subtotal == 1 ? t('Subtotal') : t('Total') }}
              </th>
              <th class="px-6 pt-4 pb-0.5 w-32 text-right">
                {{ $page.props.settings?.show_subtotal == 1 ? $currency(quotation.subtotal) : $currency(quotation.total) }}
              </th>
            </tr>
            <tr class="" v-if="quotation.product_discount_amount > 0">
              <th :colspan="$page.props.settings?.show_subtotal == 1 ? 4 : 3" class="px-6 py-0.5 text-right">
                {{ t('Product Discount') }}
              </th>
              <th class="px-6 py-0.5 w-32 text-right">{{ $currency(quotation.product_discount_amount) }}</th>
            </tr>
            <tr class="" v-if="quotation.product_tax_amount > 0">
              <th :colspan="$page.props.settings?.show_subtotal == 1 ? 4 : 3" class="px-6 py-0.5 text-right">{{ t('Product Tax') }}</th>
              <th class="px-6 py-0.5 w-32 text-right">{{ $currency(quotation.product_tax_amount) }}</th>
            </tr>
            <tr class="" v-if="quotation.order_discount_amount > 0">
              <th :colspan="$page.props.settings?.show_subtotal == 1 ? 4 : 3" class="px-6 py-0.5 text-right">{{ t('Order Discount') }}</th>
              <th class="px-6 py-0.5 w-32 text-right">{{ $currency(quotation.order_discount_amount) }}</th>
            </tr>
            <tr class="" v-if="quotation.order_tax_amount > 0">
              <th :colspan="$page.props.settings?.show_subtotal == 1 ? 4 : 3" class="px-6 py-0.5 text-right">{{ t('Order Tax') }}</th>
              <th class="px-6 py-0.5 w-32 text-right">{{ $currency(quotation.order_tax_amount) }}</th>
            </tr>
            <tr class="" v-if="settings?.show_tax == 1 && quotation.product_tax_amount == 0 && quotation.order_tax_amount == 0">
              <th :colspan="$page.props.settings?.show_subtotal == 1 ? 4 : 3" class="px-6 py-0.5 text-right">{{ t('Tax') }}</th>
              <th class="px-6 py-0.5 w-32 text-right">{{ $currency(quotation.total_tax_amount) }}</th>
            </tr>
            <tr class="" v-if="quotation.shipping > 0">
              <th :colspan="$page.props.settings?.show_subtotal == 1 ? 4 : 3" class="px-6 py-0.5 text-right">{{ t('Shipping') }}</th>
              <th class="px-6 py-0.5 w-32 text-right">{{ $currency(quotation.shipping) }}</th>
            </tr>
            <tr class="text-lg">
              <th :colspan="$page.props.settings?.show_subtotal == 1 ? 4 : 3" class="px-6 py-0.5 text-right">{{ t('Grand Total') }}</th>
              <th class="px-6 py-0.5 w-32 text-right">{{ $currency(quotation.grand_total) }}</th>
            </tr>
          </tfoot>
        </table>
      </div>

      <div v-if="quotation.attachments && quotation.attachments.length" class="mt-6 py-4 w-full">
        <Attachments :attachments="quotation.attachments" />
      </div>

      <div v-if="quotation.note" class="mt-6 w-full whitespace-pre-wrap">
        {{ quotation.note }}
      </div>
    </div>

    <div class="mt-auto pt-4 w-full text-center text-sm text-gray-500 hidden print:block">
      {{ t('This is computer generated document, no signature required.') }}
    </div>
  </div>
</template>
