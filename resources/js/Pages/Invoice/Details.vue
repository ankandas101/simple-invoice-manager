<script setup>
// import JsBarcode from 'jsbarcode';
import { useI18n } from 'vue-i18n';
import { loadScript } from '@paypal/paypal-js';
import { loadStripe } from '@stripe/stripe-js';
import { hasValueWithZero } from '@/Core/helpers';
import Attachments from '@/Shared/Attachments.vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { StripeElements, StripeElement } from 'vue-stripe-js';
import { onMounted, ref, computed, onBeforeMount } from 'vue';

import Button from '@/Jetstream/Button.vue';
import JetLabel from '@/Jetstream/Label.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import JetDangerButton from '@/Jetstream/DangerButton.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';

const card = ref(null);
const elms = ref(null);
const error = ref(null);
// const PayPal = ref(null);
const { t } = useI18n({});
const loading = ref(true);
const stripe_fees = ref(0);
const paypal_fees = ref(0);
const stripePK = ref(null);
const cardOptions = ref(null);
const stripeLoaded = ref(false);
const emit = defineEmits(['done', 'close', 'payment']);
const user = computed(() => usePage().props.auth.user);
const form = useForm({ paypal: null, stripe: null, gateway: null });
const props = defineProps({ invoice: Object, settings: Object, accept_payment: Boolean, hash: String });
const impersonate = computed(() => (usePage().props.is_impersonating ? usePage().props.user : null));

onBeforeMount(() => {
  if (
    (impersonate.value || props.accept_payment || props.hash == props.invoice.hash || user.value?.roles?.find(r => r.name == 'customer')) &&
    props.invoice.status != 'paid' &&
    props.invoice.status != 'canceled'
  ) {
    if (props.settings.stripe?.enabled && props.settings.stripe?.publishable_key) {
      stripePK.value = props.settings.stripe.publishable_key;
      cardOptions.value = {
        style: {
          base: {
            fontFamily:
              'Nunito, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,Arial, "Noto Sans", sans-serif',
            fontSize: '18px',
          },
        },
        value: {
          postalCode: props.invoice.customer.postal_code || null,
        },
      };
      const stripePromise = loadStripe(props.settings.stripe.publishable_key);
      stripePromise
        .then(() => {
          stripe_fees.value = 0;
          stripeLoaded.value = true;
          let amount = Number(props.invoice.grand_total) - Number(props.invoice.paid);
          if (props.settings.stripe.fixed) {
            stripe_fees.value += Number(props.settings.stripe.fixed);
          }
          if (props.invoice.company.country == props.invoice.customer.country && props.settings.stripe.same_country) {
            stripe_fees.value += amount * (Number(props.settings.stripe.same_country) / 100);
          }
          if (props.invoice.company.country != props.invoice.customer.country && props.settings.stripe.other_countries) {
            stripe_fees.value += amount * (Number(props.settings.stripe.other_countries) / 100);
          }
          setTimeout(() => (loading.value = false), 1500);
        })
        .catch(err => {
          console.error('Failed to load the Stripe JS SDK script', err);
        });
    }
    if (props.settings.paypal?.enabled) {
      loadScript({ 'client-id': props.settings.paypal.client_id, currency: props.settings.currency_code || 'USD' })
        .then(paypal => {
          // PayPal.value = paypal;
          paypal_fees.value = 0;
          let amount = Number(props.invoice.grand_total) - Number(props.invoice.paid);
          if (props.settings.paypal.fixed) {
            paypal_fees.value += Number(props.settings.paypal.fixed);
          }
          if (props.invoice.company.country == props.invoice.customer.country && props.settings.paypal.same_country) {
            paypal_fees.value += amount * (Number(props.settings.paypal.same_country) / 100);
          }
          if (props.invoice.company.country != props.invoice.customer.country && props.settings.paypal.other_countries) {
            paypal_fees.value += amount * (Number(props.settings.paypal.other_countries) / 100);
          }
          paypal
            .Buttons({
              style: { layout: 'vertical', color: 'blue', shape: 'rect', label: 'paypal' },
              createOrder: (data, actions) => {
                // loading.value = true;
                amount = amount + Number(parseFloat(paypal_fees.value).toFixed(props.settings.fraction));
                return actions.order.create({
                  purchase_units: [
                    {
                      reference_id: props.invoice.reference,
                      amount: {
                        value: amount,
                        currency_code: props.settings.currency_code || 'USD',
                      },
                    },
                  ],
                });
              },
              onCancel: data => {
                loading.value = false;
              },
              onError: err => {
                console.log(err);
                error.value = t('PayPal request has been failed, the error is logged to the console.');
                loading.value = false;
              },
              onApprove: (data, actions) => {
                return actions.order.capture().then(details => {
                  form.paypal = {
                    invoice_id: props.invoice.id,
                    id: details.id,
                    fees: Number(parseFloat(paypal_fees.value).toFixed(props.settings.fraction)),
                    status: details.status,
                    email: details.payer.email_address,
                    payer_id: details.payer.payer_id,
                    amount: details.purchase_units[0].amount,
                    invoice_reference: details.purchase_units[0].reference_id,
                    payment: {
                      id: details.purchase_units[0].payments.captures[0].id,
                      status: details.purchase_units[0].payments.captures[0].status,
                      amount: details.purchase_units[0].payments.captures[0].amount,
                    },
                  };

                  form.gateway = 'PayPal';
                  form.submit('post', route('paypal.charge', { id: props.invoice.id, hash: props.invoice.hash }), {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: () => {
                      emit('done');
                    },
                    onError: errors => {
                      error.value = errors.paypal;
                      loading.value = false;
                    },
                  });
                });
              },
            })
            .render('#paypal-button-container');
          setTimeout(() => (loading.value = false), 1000);
        })
        .catch(err => {
          console.error('Failed to load the PayPal JS SDK script', err);
        });
    }
  }
});

onMounted(async () => {
  //   JsBarcode('.barcode').init();
  // JsBarcode('#barcode', props.invoice.reference, {
  //   width: 1,
  //   margin: 0,
  //   fontSize: 0,
  //   height: '30',
  //   format: 'CODE128',
  // });
  setTimeout(() => (loading.value = false), 1500);
});

function rowSpan() {
  let rowspan = 4;
  if (props.invoice.shipping > 0) {
    rowspan++;
  }
  if (props.invoice.product_discount_amount > 0) {
    rowspan++;
  }
  if (props.invoice.product_tax_amount > 0) {
    rowspan++;
  }
  if (props.invoice.order_discount_amount > 0) {
    rowspan++;
  }
  if (props.invoice.order_tax_amount > 0) {
    rowspan++;
  }
  if (props.settings.show_tax == 1 && props.invoice.product_tax_amount == 0 && props.invoice.order_tax_amount == 0) {
    rowspan++;
  }
  return rowspan;
}

const submit = () => {
  error.value = null;
  loading.value = true;
  const cardElement = card.value.stripeElement;
  elms.value.instance.createToken(cardElement).then(result => {
    if (result.error) {
      console.log(result.error);
      error.value = result.error;
      return false;
    }
    form.gateway = 'Stripe';
    form.stripe = result.token;
    form.stripe.fees = Number(parseFloat(stripe_fees.value).toFixed(props.settings.fraction));
    form.submit('post', route('paypal.charge', { id: props.invoice.id, hash: props.invoice.hash }), {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        emit('done');
      },
      onError: errors => {
        error.value = errors.stripe;
        loading.value = false;
      },
    });
  });
};

const pdf = ref(null);
const receiptInput = ref(null);
const receiptPreview = ref(null);
const receipt_form = useForm({ _method: 'post', receipt: null });
const selectNewReceipt = () => {
  receiptInput.value.click();
};
const updateReceiptPreview = () => {
  pdf.value = false;
  const photo = receiptInput.value.files[0];
  if (!photo) return;

  if (photo.type == 'application/pdf') {
    pdf.value = true;
    receiptPreview.value = URL.createObjectURL(photo);
  } else {
    const reader = new FileReader();
    reader.onload = e => {
      receiptPreview.value = e.target.result;
    };
    reader.readAsDataURL(photo);
  }
};

const viewReceipt = () => {
  let receipt = props.invoice.attachments.find(a => a.title == props.invoice.receipt);
  window.open(route('attachments.download', { attachment: receipt.uuid, view: 'yes' }), '_blank');
};

const addPayment = si => {
  emit('close');
  emit('payment', si);
};

const cancelReceipt = () => {
  router.post(
    route('invoices.receipt.cancel', props.invoice.id),
    {},
    {
      onSuccess: () => {
        emit('close');
      },
    }
  );
};

const uploadReceipt = () => {
  if (receiptInput.value) {
    receipt_form.receipt = receiptInput.value.files[0];
  }

  let url = route('invoices.receipt', { id: props.invoice.id, hash: props.invoice.hash });

  receipt_form.post(url, {
    preserveScroll: true,
    onSuccess: () => {
      emit('close');
    },
    onError: () => {},
  });
};
</script>

<template>
  <Head
    :title="
      t('Invoice No. {x}', { x: ($page.props.settings?.use_company_number == 1 ? invoice.company_number : invoice.number) || invoice.id })
    "
  />
  <div v-if="$can('update-invoices') && invoice.receipt && invoice.paid < invoice.grand_total" class="print:hidden">
    <div class="rounded-md bg-yellow-50 p-4 mt-4">
      <div class="flex">
        <div class="shrink-0">
          <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path
              fill-rule="evenodd"
              d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-yellow-800">{{ t('Bank transfer receipt uploaded.') }}</h3>
          <div class="mt-2 text-sm text-yellow-700">
            <p>{{ t('This invoice has an receipt uploaded, please check the attachment name "{name}".', { name: invoice.receipt }) }}</p>
            <div v-if="!user?.roles?.find(r => r.name == 'customer')" class="mt-3 flex items-center gap-x-4 gap-y-2">
              <JetSecondaryButton type="button" @click="viewReceipt">
                {{ t('View Receipt') }}
              </JetSecondaryButton>
              <Button type="button" @click="addPayment(invoice)">
                {{ t('Add Payment') }}
              </Button>
              <JetDangerButton type="button" @click="cancelReceipt">
                {{ t('Cancel uploaded receipt') }}
              </JetDangerButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div
    v-if="invoice"
    class="mt-auto max-w-4xl print:min-w-[700px] mx-auto h-full bg-gray-100 dark:bg-gray-800 -m-6 pt-3 pb-6 rounded-md leading-relaxed print:bg-white print:mt-0 print:pt-0 print:h-full print:overflow-visible"
  >
    <div
      class="relative bg-white dark:bg-gray-900 p-4 rounded-md overflow-x-auto border dark:border-gray-700 print:border-0 print:shadow-none print:pt-0"
    >
      <div v-if="settings?.invoice_status != 'text'" class="absolute left-6 top-6 w-24 h-24 text-center uppercase -rotate-45">
        <div
          class="-mx-12 print:border-t-2 print:border-b-2 capitalize"
          :class="{
            'bg-blue-200 text-blue-800 border-blue-800': invoice.status == 'pending',
            'bg-green-200 text-green-800 border-green-800': invoice.status == 'paid',
            'bg-yellow-200 text-yellow-800 border-yellow-800': invoice.status == 'overdue',
            'bg-red-200 text-red-800 border-red-800': invoice.status == 'canceled',
          }"
        >
          {{ t(invoice.status) }}
        </div>
      </div>
      <div class="flex justify-end print:flex">
        <div class="text-left sm:text-right max-w-md print:text-right">
          <div v-if="invoice.company.logo || $page.props.logo" class="h-20 mb-2 flex items-center justify-end dark:hidden print:flex">
            <img v-if="invoice.company.logo" :src="invoice.company.logo" alt="" class="h-full max-h-48" />
            <img v-else-if="$page.props.logo" :src="$page.props.logo" alt="" class="h-full max-h-48" />
          </div>
          <div
            v-if="invoice.company.logo_dark || $page.props.logo_dark"
            class="h-20 mb-2 items-center justify-end hidden dark:flex print:hidden"
          >
            <img v-if="invoice.company.logo_dark" :src="invoice.company.logo_dark" alt="" class="h-full max-h-48" />
            <img v-else-if="$page.props.logo" :src="$page.props.logo" alt="" class="h-full max-h-48" />
          </div>
          <h2 class="text-2xl font-extrabold" v-if="invoice.company.show_name == 1">{{ invoice.company.name }}</h2>
          <!-- <div class="font-bold" v-if="invoice.company.contact_person">
            ({{ invoice.company.contact_person }})
          </div> -->
          <div v-if="invoice.company.show_address">
            {{ invoice.company.address || '' }}
            {{ invoice.company.city || '' }}
            {{ invoice.company.postal_code || '' }}
            {{ invoice.company.state || '' }}
            {{ invoice.company.country || '' }}
          </div>
          <div v-if="invoice.company.phone">{{ invoice.company.phone }}</div>
          <div v-if="invoice.company.email">{{ invoice.company.email }}</div>
          <template v-if="invoice.company.extra_attributes">
            <div v-for="field of Object.keys(invoice.company.extra_attributes)" :key="field">
              <template v-if="hasValueWithZero(invoice.company.extra_attributes[field])">
                <span class="text-gray-500 dark:text-gray-400">{{ t(field) }}:</span>
                {{ invoice.company.extra_attributes[field] }}
              </template>
            </div>
          </template>
        </div>
      </div>

      <div class="mt-4 w-full">
        <h1 class="text-6xl uppercase font-extrabold">{{ t('Invoice') }}</h1>
        <!-- <div class="mt-4 flex items-center justify-center">
          <div class="inline-flex bg-white p-2 rounded-md mx-auto">
            <svg id="barcode" class="mt-px"></svg>
          </div>
        </div> -->
      </div>

      <div class="mt-6 block sm:flex print:flex gap-x-6 gap-y-2">
        <div class="w-full min-w-[250px]">
          <div class="flex gap-1">
            <div class="w-24 md:w-28 print:w-28">{{ t('Invoice No.') }}:</div>
            {{ ($page.props.settings?.use_company_number == 1 ? invoice.company_number : invoice.number) || invoice.id }}
          </div>
          <div class="flex gap-1">
            <div class="w-24 md:w-28 print:w-28">{{ t('Reference') }}:</div>
            {{ invoice.reference }}
          </div>
          <div v-if="settings?.invoice_status == 'text'" class="flex gap-1">
            <div class="w-24 md:w-28 print:w-28">{{ t('Status') }}:</div>
            <span class="capitalize">{{ t(invoice.status) }}</span>
          </div>
        </div>
        <div class="w-full min-w-[250px]">
          <div class="flex gap-1">
            <div class="w-24 md:w-28 print:w-28">{{ t('Date') }}:</div>
            {{ $date(invoice.date) }}
          </div>
          <div class="flex gap-1">
            <div class="w-24 md:w-28 print:w-28">{{ t('Created at') }}:</div>
            {{ $datetime(invoice.created_at) }}
          </div>
          <div v-if="invoice.due_date" class="flex gap-1">
            <div class="w-24 md:w-28 print:w-28">{{ t('Due Date') }}:</div>
            {{ $date(invoice.due_date) }}
          </div>
        </div>
      </div>
      <div v-if="invoice.extra_attributes" class="grid grid-cols-2 gap-x-6 gap-y-2">
        <div class="flex gap-1" v-for="field of Object.keys(invoice.extra_attributes)" :key="field">
          <template v-if="hasValueWithZero(invoice.extra_attributes[field])">
            <div class="w-24 md:w-28 print:w-28">{{ t(field) }}:</div>
            {{ invoice.extra_attributes[field] }}
          </template>
        </div>
      </div>

      <div v-if="invoice.recurring == 1 && user && !user.roles?.find(r => r.name == 'customer')" class="mt-6">
        <div class="rounded-md bg-yellow-50 p-4">
          <div class="flex">
            <div class="shrink-0">
              <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path
                  fill-rule="evenodd"
                  d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-bold text-yellow-800">{{ t('Recurring Invoice') }}</h3>
              <div class="mt-1 text-sm text-yellow-700">
                <p>
                  {{
                    t('System will automatically generate new invoice {repeat}.', {
                      repeat: {
                        daily: 'Every day (daily)',
                        weekly: 'Every 7 days (weekly)',
                        monthly: 'Every month (monthly)',
                        quarterly: 'Every 3 months (quarterly)',
                        semiannually: 'Every 6 months (semiannually)',
                        annually: 'Every year (annually)',
                        biennially: 'Every 2 years (biennially)',
                      }[invoice.repeat]?.toLowerCase(),
                    })
                  }}
                </p>
                <p class="mt-1 text-red-600">
                  <a href="https://laravel.com/docs/10.x/scheduling#running-the-scheduler" target="_blank" class="font-bold">Cron Job</a>
                  must be setup otherwise no invoice will be generated.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-6 text-left w-full flex items-start">
        <div class="w-24 md:w-28 print:w-28">{{ t('Bill to') }}:</div>
        <div>
          <div>{{ invoice.customer.name }}</div>
          <div v-if="invoice.customer?.company" class="font-bold">{{ invoice.customer?.company }}</div>
          <div v-if="invoice.customer?.address">
            {{ invoice.customer?.address || '' }}
            {{ invoice.customer?.city || '' }}
            {{ invoice.customer?.postal_code || '' }}
            {{ invoice.customer?.state || '' }}
            {{ invoice.customer?.country || '' }}
          </div>
          <div v-if="invoice.customer.phone">{{ t('Tel') }}: {{ invoice.customer.phone }}</div>
          <div v-if="invoice.customer.email">{{ t('Email') }}: {{ invoice.customer.email }}</div>
          <template v-if="invoice.customer.extra_attributes">
            <div v-for="field of Object.keys(invoice.customer.extra_attributes)" :key="field">
              <template v-if="hasValueWithZero(invoice.customer.extra_attributes[field])">
                <span class="text-gray-500 dark:text-gray-400">{{ t(field) }}:</span>
                {{ invoice.customer.extra_attributes[field] }}
              </template>
            </div>
          </template>
        </div>
      </div>

      <div v-if="invoice.attachment" class="print:hidden mt-4 p-4 w-full border dark:border-gray-700 rounded-md">
        {{ t('This record has an attachment') }},
        <a :href="invoice.attachment" target="_blank" class="link">{{ t('click here to view') }}</a>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full mt-6 mb-4" style="min-width: 650px">
          <thead>
            <tr class="border-t-2 border-b-2 dark:border-gray-700">
              <th class="px-4 print:px-2 py-3 text-left">{{ t('Description') }}</th>
              <th class="px-4 print:px-2 py-3 w-28 text-center">{{ t('Quantity') }}</th>
              <th class="px-4 print:px-2 py-3 w-28 text-center">{{ t('Price') }}</th>
              <th v-if="$page.props.settings?.show_subtotal == 1" class="px-4 print:px-2 py-3 w-24 text-center">{{ t('Tax') }}</th>
              <th class="px-4 print:px-2 py-3 w-32 text-center">
                {{ $page.props.settings?.show_subtotal == 1 ? t('Subtotal') : t('Total') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(item, ii) in invoice.items" :key="'i_' + item.id">
              <!-- :class="{ 'bg-gray-50 dark:bg-gray-800': ii % 2 != 0 }" -->
              <tr class="group avoid">
                <td
                  class="relative align-top px-4 print:px-2"
                  :class="{
                    'pt-4 pb-2': ii == 0,
                    'pb-4 pt-2': ii == invoice.items.length - 1,
                    'py-2': ii > 0 && ii + i != invoice.items.length,
                  }"
                >
                  <span :class="ii == 0 ? 'top-4' : 'top-2'" class="absolute mt-1.5 -ml-4 mr-1 text-xs print:hidden">{{ ii + 1 }}.</span>
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
                  class="align-top px-4 print:px-2 w-28 text-right"
                  :class="{
                    'pt-4 pb-2': ii == 0,
                    'pb-4 pt-2': ii == invoice.items.length - 1,
                    'py-2': ii > 0 && ii + i != invoice.items.length,
                  }"
                >
                  {{ $number(item.quantity) }}
                </td>
                <td
                  class="align-top px-4 print:px-2 w-28 text-right"
                  :class="{
                    'pt-4 pb-2': ii == 0,
                    'pb-4 pt-2': ii == invoice.items.length - 1,
                    'py-2': ii > 0 && ii + i != invoice.items.length,
                  }"
                >
                  <!-- {{ $number(item.unit_price) }} -->
                  {{ $page.props.settings?.show_subtotal == 1 ? $currency(item.net_price) : $currency(item.unit_price) }}
                </td>
                <td
                  v-if="$page.props.settings?.show_subtotal == 1"
                  class="align-top px-4 print:px-2 w-24 text-right"
                  :class="{
                    'pt-4 pb-2': ii == 0,
                    'pb-4 pt-2': ii == invoice.items.length - 1,
                    'py-2': ii > 0 && ii + i != invoice.items.length,
                  }"
                >
                  {{ $currency(item.tax_amount) }}
                </td>
                <td
                  class="align-top px-4 print:px-2 w-32 text-right"
                  :class="{
                    'pt-4 pb-2': ii == 0,
                    'pb-4 pt-2': ii == invoice.items.length - 1,
                    'py-2': ii > 0 && ii + i != invoice.items.length,
                  }"
                >
                  <!-- {{ $number(item.total) }} -->
                  {{ $page.props.settings?.show_subtotal == 1 ? $currency(item.subtotal) : $currency(item.total) }}
                </td>
              </tr>
              <!-- <tr v-if="item.details">
                <td colspan="100%" class="align-top px-4 print:px-2">
                  <div class="-mt-2" :class="ii == invoice.items.length - 1 ? 'pb-4' : 'pb-2'">
                    {{ item.details }}
                  </div>
                </td>
              </tr> -->
            </template>
          </tbody>
          <tfoot>
            <tr class="border-t-2 dark:border-gray-700">
              <th
                :rowspan="rowSpan()"
                class="px-4 print:px-2 pt-4 pb-0.5 text-left"
                :colspan="$page.props.settings?.show_subtotal == 1 ? 3 : 2"
              >
                <div
                  v-if="invoice.company.allow_transfer && invoice.grand_total > invoice.paid"
                  class="whitespace-pre-wrap font-normal leading-tight flex flex-wrap gap-4 items-center"
                >
                  <img v-if="invoice.company.qrcode" :src="invoice.company.qrcode" alt="" class="w-28 h-28" />
                  <div>{{ invoice.company.bank_account_details }}</div>
                </div>
              </th>
              <th class="px-4 print:px-2 pt-4 pb-0.5 text-right whitespace-nowrap">
                {{ $page.props.settings?.show_subtotal == 1 ? t('Subtotal') : t('Total') }}
              </th>
              <th class="px-4 print:px-2 pt-4 pb-0.5 w-32 text-right whitespace-nowrap">
                {{ $page.props.settings?.show_subtotal == 1 ? $currency(invoice.subtotal) : $currency(invoice.total) }}
              </th>
            </tr>
            <tr class="" v-if="invoice.product_discount_amount > 0">
              <th class="px-4 print:px-2 py-0.5 text-right whitespace-nowrap">{{ t('Product Discount') }}</th>
              <th class="px-4 print:px-2 py-0.5 w-32 text-right whitespace-nowrap">{{ $currency(invoice.product_discount_amount) }}</th>
            </tr>
            <tr class="" v-if="invoice.product_tax_amount > 0">
              <th class="px-4 print:px-2 py-0.5 text-right whitespace-nowrap">{{ t('Product Tax') }}</th>
              <th class="px-4 print:px-2 py-0.5 w-32 text-right whitespace-nowrap">{{ $currency(invoice.product_tax_amount) }}</th>
            </tr>
            <tr class="" v-if="invoice.order_discount_amount > 0">
              <th class="px-4 print:px-2 py-0.5 text-right whitespace-nowrap">{{ t('Order Discount') }}</th>
              <th class="px-4 print:px-2 py-0.5 w-32 text-right whitespace-nowrap">{{ $currency(invoice.order_discount_amount) }}</th>
            </tr>
            <tr class="" v-if="invoice.order_tax_amount > 0">
              <th class="px-4 print:px-2 py-0.5 text-right whitespace-nowrap">{{ t('Order Tax') }}</th>
              <th class="px-4 print:px-2 py-0.5 w-32 text-right whitespace-nowrap">{{ $currency(invoice.order_tax_amount) }}</th>
            </tr>
            <tr class="" v-if="settings.show_tax == 1 && invoice.product_tax_amount == 0 && invoice.order_tax_amount == 0">
              <th class="px-4 print:px-2 py-0.5 text-right whitespace-nowrap">{{ t('Tax') }}</th>
              <th class="px-4 print:px-2 py-0.5 w-32 text-right whitespace-nowrap">{{ $currency(invoice.total_tax_amount) }}</th>
            </tr>
            <tr class="" v-if="invoice.shipping > 0">
              <th class="px-4 print:px-2 py-0.5 text-right whitespace-nowrap">{{ t('Shipping') }}</th>
              <th class="px-4 print:px-2 py-0.5 w-32 text-right whitespace-nowrap">{{ $currency(invoice.shipping) }}</th>
            </tr>
            <tr class="text-lg">
              <th class="px-4 print:px-2 py-0.5 text-right whitespace-nowrap">{{ t('Grand Total') }}</th>
              <th class="px-4 print:px-2 py-0.5 w-32 text-right whitespace-nowrap">{{ $currency(invoice.grand_total) }}</th>
            </tr>
            <tr>
              <th class="px-4 print:px-2 py-0.5 text-right whitespace-nowrap">{{ t('paid') }}</th>
              <th class="px-4 print:px-2 py-0.5 w-32 text-right whitespace-nowrap">{{ $currency(invoice.paid) }}</th>
            </tr>
            <tr>
              <th class="px-4 print:px-2 py-0.5 text-right whitespace-nowrap">{{ t('Balance') }}</th>
              <th class="px-4 print:px-2 py-0.5 w-32 text-right whitespace-nowrap">{{ $currency(invoice.grand_total - invoice.paid) }}</th>
            </tr>
          </tfoot>
        </table>
      </div>

      <div v-if="invoice.attachments && invoice.attachments.length" class="mt-6 py-4 w-full">
        <Attachments :attachments="invoice.attachments" />
      </div>

      <div v-if="invoice.note" class="mt-6 w-full whitespace-pre-wrap">
        {{ invoice.note }}
      </div>
    </div>

    <div
      class="relative mt-12 print:hidden w-full max-w-2xl mx-auto flex flex-col justify-center"
      v-if="
        (impersonate || accept_payment || hash == invoice.hash || user?.roles?.find(r => r.name == 'customer')) &&
        invoice.status != 'paid' &&
        invoice.status != 'canceled' &&
        (settings.paypal?.enabled || settings.stripe?.enabled || invoice.company.allow_transfer)
      "
    >
      <div v-if="invoice.company.allow_transfer" class="flex flex-wrap gap-4 items-center">
        <div v-if="invoice.receipt" class="flex flex-wrap gap-2 items-center mb-8 font-bold">
          <p>{{ t('You have uploaded the receipt {receipt}', { receipt: invoice.receipt }) }}</p>
          <p>{{ t('Please allow us time to check your receipt and add payment to your invoice.') }}</p>
        </div>
        <div v-else>
          {{ t('If you have transferred the amount to our bank account then please upload the receipt below.') }}
          <!-- <div class="whitespace-pre-wrap leading-tight flex flex-wrap gap-4 items-center">
          <img v-if="invoice.company.qrcode" :src="invoice.company.qrcode" alt="" class="w-28 h-28" />
          <div>{{ invoice.company.bank_account_details }}</div>
        </div> -->
          <div class="mb-8 w-full">
            <!-- Receipt File Input -->
            <input ref="receiptInput" type="file" class="hidden" accept=".png,.jpg,.jpeg,.svg, .pdf" @change="updateReceiptPreview" />
            <JetLabel for="receipt" :value="t('Receipt')" />

            <!-- Current Receipt -->
            <div v-if="!receiptPreview && form.receipt" class="my-2">
              <img :src="form.receipt" :alt="form.name" class="block rounded-sm w-full h-96 mx-auto bg-contain" />
            </div>

            <!-- New Receipt Preview -->
            <embed v-if="pdf && receiptPreview" class="my-2 block w-full h-96" :src="receiptPreview" />
            <div v-else-if="receiptPreview" class="my-2">
              <span
                class="block rounded-sm w-full h-96 mx-auto bg-contain bg-no-repeat bg-center"
                :style="'background-image: url(\'' + receiptPreview + '\');'"
              />
            </div>

            <JetSecondaryButton class="mt-2 mr-2" type="button" @click.prevent="selectNewReceipt">
              {{ t('Select Receipt') }}
            </JetSecondaryButton>
            <Button v-if="receiptPreview" class="mt-2 mr-2" type="button" @click.prevent="uploadReceipt">
              {{ t('Upload') }}
            </Button>

            <JetInputError :message="form.errors.receipt" class="mt-2" />
          </div>
        </div>
      </div>

      <template v-if="!invoice.receipt">
        <template v-if="error">
          <div class="rounded-md bg-red-700 px-4 py-2 mb-4">
            <h3 class="text-sm font-medium text-red-100">{{ error?.message || error }}</h3>
          </div>
        </template>
        <template v-if="paypal_fees > 0 || stripe_fees > 0">
          <div class="rounded-md bg-blue-700 px-4 py-2 mb-4 flex gap-6 justify-between items-start">
            <div>
              <h3 v-if="stripe_fees > 0" class="text-sm font-medium text-red-100">
                {{ t('Stripe Gateway Fees') }}: {{ $number(stripe_fees) }}
              </h3>
              <h3 v-if="stripe_fees > 0" class="text-sm font-medium text-red-100">
                {{ t('Stripe Total Amount') }}:
                <strong>{{ $number(Number(invoice.grand_total) - Number(invoice.paid) + Number(stripe_fees)) }}</strong>
              </h3>
            </div>
            <div>
              <h3 v-if="paypal_fees > 0" class="text-sm font-medium text-red-100">
                {{ t('PayPal Gateway Fees') }}: {{ $number(paypal_fees) }}
              </h3>
              <h3 v-if="paypal_fees > 0" class="text-sm font-medium text-red-100">
                {{ t('PayPal Total Amount') }}:
                <strong>{{ $number(Number(invoice.grand_total) - Number(invoice.paid) + Number(paypal_fees)) }}</strong>
              </h3>
            </div>
          </div>
        </template>
        <template v-if="stripeLoaded">
          <div class="bg-white py-3 px-6 mb-4 rounded-md border" :class="stripeError ? 'border-red-700' : ''">
            <!-- <stripe-element-card ref="elementRef" :testMode="$page.props.demo" :pk="stripePK" @token="tokenCreated" /> -->
            <StripeElements v-if="stripeLoaded" v-slot="{ elements, instance }" ref="elms" :stripe-key="stripePK">
              <!-- :elements-options="elementsOptions" -->
              <!-- :instance-options="instanceOptions" -->
              <StripeElement ref="card" :elements="elements" :options="cardOptions" />
            </StripeElements>
          </div>
          <input type="hidden" name="gateway" value="Stripe" />
          <button
            type="button"
            @click="submit"
            :disabled="loading"
            class="relative mb-6 w-full flex flex-wrap items-center justify-center rounded-md text-base bg-blue-600 px-4 py-3 text-gray-200 hover:bg-blue-700 focus:z-10 focus:border-indigo-500 focus:outline-hidden focus:ring-1 focus:ring-indigo-500"
          >
            {{ t('Pay with Card') }}
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="ml-2 w-6 h-6"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"
              />
            </svg>
            <span class="ml-1 text-lg font-extrabold">{{ t('Stripe') }}</span>
          </button>
        </template>

        <div id="paypal-button-container" class="w-full z-10"></div>

        <div v-if="loading" class="absolute inset-0 -mx-6 -mt-6">
          <div class="relative z-20 bg-gray-500 bg-opacity-50 w-full h-full flex items-center justify-center text-white rounded-md">
            <svg viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg" class="stroke-current h-12 w-12">
              <g fill="none" fill-rule="evenodd">
                <g transform="translate(1 1)" stroke-width="2">
                  <circle stroke-opacity=".5" cx="18" cy="18" r="18" />
                  <path d="M36 18c0-9.94-8.06-18-18-18">
                    <animateTransform
                      attributeName="transform"
                      type="rotate"
                      from="0 18 18"
                      to="360 18 18"
                      dur="1s"
                      repeatCount="indefinite"
                    />
                  </path>
                </g>
              </g>
            </svg>
          </div>
        </div>
      </template>
    </div>

    <div class="mt-auto pt-4 w-full text-center text-sm text-gray-500 hidden print:block">
      {{ t('This is computer generated document, no signature required.') }}
    </div>
  </div>
</template>
