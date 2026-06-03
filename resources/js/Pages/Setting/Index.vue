<script setup>
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';
import { ref, version, onMounted } from 'vue';

import i18n from '@/Core/i18n';
import Button from '@/Shared/Button.vue';
import CheckBox from '@/Shared/CheckBox.vue';
import JetLabel from '@/Jetstream/Label.vue';
import TextInput from '@/Shared/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SelectInput from '@/Shared/SelectInput.vue';
import FormSection from '@/Jetstream/FormSection.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import AutoComplete from '@/Shared/AutoComplete.vue';
import SectionTitle from '@/Jetstream/SectionTitle.vue';
import ActionMessage from '@/Jetstream/ActionMessage.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';

const { t } = useI18n({});
const props = defineProps(['current', 'languages', 'timezones', 'laravel_version']);

const logoInput = ref(null);
const logoPreview = ref(null);
const app_version = ref(null);
const invoiceLogoInput = ref(null);
const invoiceLogoPreview = ref(null);

const form = useForm({
  name: props.current?.name,
  language: props.current?.language || 'en',
  per_page: props.current?.per_page || 10,
  fraction: props.current.fraction,
  hour12: props.current.hour12 || '1',
  timezone: props.current.timezone || 'UTC',
  reference: props.current?.reference || 'ulid',
  reference_prefix: props.current?.reference_prefix || '',
  initial_rows: props.current?.initial_rows,
  invoice_status: props.current?.invoice_status,
  currency_code: props.current?.currency_code || 'USD',
  default_locale: props.current?.default_locale || 'en-US',
  paypal: props.current?.paypal || { enabled: false },
  stripe: props.current?.stripe || { enabled: false },
  show_tax: props.current?.show_tax == 1,
  product_discount: props.current?.product_discount == 1,
  impersonation: props.current?.impersonation == 1,
  create_user: props.current?.create_user == 1,
  prefer_company_email: props.current?.prefer_company_email == 1,
  login_logo: props.current?.login_logo || null,
  invoice_logo: props.current?.invoice_logo || null,
  attachment_exts: props.current.attachment_exts || 'jpg,png,pdf,xlsx,docx,zip',
  mail_from_address: props.current['mail_from_address'] || 'noreply@domain.com',
  mail_from_name: props.current['mail_from_name'] || 'SIM Desk',
  mail_default: props.current['mail_default'] || 'smtp',
  mail_mailers_smtp_host: props.current['mail_mailers_smtp_host'] || '127.0.0.1',
  mail_mailers_smtp_port: props.current['mail_mailers_smtp_port'] || '2525',
  mail_mailers_smtp_username: props.current['mail_mailers_smtp_username'] || null,
  mail_mailers_smtp_password: props.current['mail_mailers_smtp_password'] || null,
  mail_mailers_smtp_encryption: props.current['mail_mailers_smtp_encryption'] || null,
  services_mailgun_domain: props.current['services_mailgun_domain'] || null,
  services_mailgun_secret: props.current['services_mailgun_secret'] || null,
  services_mailgun_endpoint: props.current['services_mailgun_endpoint'] || null,
  services_sparkpost_secret: props.current['services_sparkpost_secret'] || null,
  services_sparkpost_endpoint: props.current['services_sparkpost_endpoint'] || null,
  services_ses_key: props.current['services_ses_key'] || null,
  services_ses_secret: props.current['services_ses_secret'] || null,
  services_ses_region: props.current['services_ses_region'] || null,
  date_format: props.current?.date_format || 'js',
  php_date_format: props.current?.php_date_format || 'Y-m-d',
  number_format: props.current?.number_format || '',
  invoice_prefix: props.current?.invoice_prefix || '',
  payment_prefix: props.current?.payment_prefix || '',
  quotation_prefix: props.current?.quotation_prefix || '',
  use_company_number: props.current?.use_company_number == 1,
  show_subtotal: props.current?.show_subtotal == 1,
});

onMounted(async () => {
  let composer = await import('@r/../composer.json');
  app_version.value = composer.version;
});

const submit = () => {
  if (logoInput.value) {
    form.login_logo = logoInput.value.files[0];
  }
  if (invoiceLogoInput.value) {
    form.invoice_logo = invoiceLogoInput.value.files[0];
  }

  form.post(route('settings.store'), {
    onSuccess: p => {
      i18n.global.locale.value = p.props.settings.language;
      document.querySelector('html').setAttribute('lang', p.props.settings.language);
    },
  });
};

const selectNewLogo = () => {
  logoInput.value.click();
};

const notAllowed = () => {
  console.log('Not allowed!');
};

const selectNewInvoiceLogo = () => {
  invoiceLogoInput.value.click();
};

const updateLogoPreview = () => {
  const photo = logoInput.value.files[0];
  if (!photo) return;

  const reader = new FileReader();
  reader.onload = e => {
    logoPreview.value = e.target.result;
  };
  reader.readAsDataURL(photo);
};

const updateInvoiceLogoPreview = () => {
  const photo = invoiceLogoInput.value.files[0];
  if (!photo) return;

  const reader = new FileReader();
  reader.onload = e => {
    invoiceLogoPreview.value = e.target.result;
  };
  reader.readAsDataURL(photo);
};

// const deleteLogo = () => {
//   router.delete(route('current-user-photo.destroy'), {
//     preserveScroll: true,
//     onSuccess: () => {
//       logoPreview.value = null;
//       clearLogoFileInput();
//     },
//   });
// };

// const clearLogoFileInput = () => {
//   if (logoInput.value?.value) {
//     logoInput.value.value = null;
//   }
// };
</script>

<template>
  <AppLayout :title="t('Settings')">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
      <FormSection @submitted="submit" autocomplete="off">
        <template #title>{{ t('Settings') }}</template>
        <template #description>
          <div>
            <p>{{ t('Please fill the form below to update settings.') }}</p>
            <div class="my-4 flex flex-wrap gap-x-6 gap-y-3">
              <a target="_blank" href="/commands/generate_invoices" class="link">Generate Invoices</a>
              <a target="_blank" href="/commands/send_overdue_invoices" class="link">Send Overdue Invoices</a>
            </div>
          </div>
        </template>

        <template #form>
          <div class="col-span-6 sm:col-span-3">
            <!-- Logo File Input -->
            <input ref="logoInput" type="file" class="hidden" accept=".png,.jpg,.jpeg,.svg" @change="updateLogoPreview" />
            <JetLabel for="logo" :value="t('Login Logo')" />

            <!-- Current Logo -->
            <div v-show="!logoPreview && form.login_logo" class="my-2 rounded-md bg-white p-2">
              <img :src="form.login_logo" :alt="form.name" class="block rounded-sm w-80 h-20 mx-auto bg-contain" />
            </div>

            <!-- New Logo Preview -->
            <div v-show="logoPreview" class="my-2 rounded-md bg-white p-2">
              <span
                class="block rounded-sm w-80 h-20 mx-auto bg-contain bg-no-repeat bg-center"
                :style="'background-image: url(\'' + logoPreview + '\');'"
              />
            </div>

            <JetSecondaryButton class="mt-2 mr-2" type="button" @click.prevent="selectNewLogo">
              {{ t('Select Login Logo') }}
            </JetSecondaryButton>

            <!-- <JetSecondaryButton v-if="form.logo" type="button" class="mt-2" @click.prevent="deleteLogo">
              {{ t('Remove Logo') }}
            </JetSecondaryButton> -->
            <JetInputError :message="form.errors.login_logo" class="mt-2" />
          </div>

          <div class="col-span-6 sm:col-span-3">
            <!-- Invoice Logo File Input -->
            <input ref="invoiceLogoInput" type="file" class="hidden" accept=".png,.jpg,.jpeg,.svg" @change="updateInvoiceLogoPreview" />
            <JetLabel for="invoice_logo" :value="t('Invoice Logo')" />

            <!-- Current Invoice Logo -->
            <div v-show="!invoiceLogoPreview && form.invoice_logo" class="my-2 rounded-md bg-white p-2">
              <img :src="form.invoice_logo" :alt="form.name" class="block rounded-sm w-80 h-20 mx-auto bg-contain" />
            </div>

            <!-- New Invoice Logo Preview -->
            <div v-show="invoiceLogoPreview" class="my-2 rounded-md bg-white p-2">
              <span
                class="block rounded-sm w-80 h-20 mx-auto bg-contain bg-no-repeat bg-center"
                :style="'background-image: url(\'' + invoiceLogoPreview + '\');'"
              />
            </div>

            <JetSecondaryButton class="mt-2 mr-2" type="button" @click.prevent="selectNewInvoiceLogo">
              {{ t('Select Invoice Logo') }}
            </JetSecondaryButton>

            <!-- <JetSecondaryButton v-if="form.invoice_logo" type="button" class="mt-2" @click.prevent="deleteLogo">
              {{ t('Remove Logo') }}
            </JetSecondaryButton> -->
            <JetInputError :message="form.errors.invoice_logo" class="mt-2" />
          </div>

          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.name" :error="form.errors.name" :label="t('Site Name')" />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              :json="true"
              id="language"
              :searchable="false"
              v-model="form.language"
              :label="t('Language')"
              :error="form.errors.language"
              :suggestions="props.languages"
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              :json="true"
              id="reference"
              :searchable="false"
              v-model="form.reference"
              :label="t('Reference')"
              :error="form.errors.reference"
              :suggestions="[
                { value: 'ulid', label: 'ULID - Universally Unique Lexicographically Sortable Identifier' },
                { value: 'ai', label: 'Auto Increment (MYSQL only)' },
                { value: 'uniqid', label: 'Uniqid - PHP Generate a Unique ID' },
                { value: 'uuid', label: 'UUID - Universally Unique Identifier' },
              ]"
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              :json="true"
              :clearable="true"
              :searchable="false"
              id="reference_prefix"
              :label="t('Reference Prefix')"
              v-model="form.reference_prefix"
              :error="form.errors.reference_prefix"
              :suggestions="[
                { value: 'Y', label: t('Year') },
                { value: 'Y-', label: t('Year-') },
                { value: 'Ym', label: t('YearMonth') },
                { value: 'Y-m-', label: t('Year-Month-') },
              ]"
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.currency_code" :error="form.errors.currency_code" :label="t('Currency Code')" />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              :json="true"
              id="date_format"
              :searchable="false"
              v-model="form.date_format"
              :label="t('Date Format')"
              :error="form.errors.date_format"
              :suggestions="[
                { value: 'php', label: 'On Server Side (PHP)' },
                { value: 'js', label: 'In Browser (Javascript)' },
              ]"
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.default_locale" :error="form.errors.default_locale" :label="t('Date & Number Locale')" />
          </div>
          <!-- <div v-if="form.date_format == 'php'" class="col-span-6 sm:col-span-3">
            <AutoComplete
              :json="true"
              id="php_date_format"
              :searchable="false"
              v-model="form.php_date_format"
              :label="t('PHP Date Formate')"
              :error="form.errors.php_date_format"
              :suggestions="[
                { value: 'd/m/Y', label: 'DD/MM/YYYY' },
                { value: 'm/d/Y', label: 'MM/DD/YYYY' },
                { value: 'd.m.Y', label: 'DD.MM.YYYY' },
                { value: 'm.d.Y', label: 'MM.DD.YYYY' },
                { value: 'd-m-Y', label: 'DD-MM-YYYY' },
                { value: 'm-d-Y', label: 'MM-DD-YYYY' },
                { value: 'Y-m-d', label: 'YYYY-MM-DD' },
              ]"
            />
          </div> -->
          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              id="timezone"
              :searchable="true"
              v-model="form.timezone"
              :label="t('Timezone')"
              :suggestions="timezones"
              :error="form.errors.timezone"
            />
          </div>
          <!-- <div v-if="form.date_format == 'js'" class="col-span-6 sm:col-span-3">
            <AutoComplete
              id="hour12"
              :json="true"
              :searchable="false"
              v-model="form.hour12"
              :label="t('Time Format')"
              :error="form.errors.hour12"
              :suggestions="[
                { value: '0', label: '24 Hours (23:30)' },
                { value: '1', label: '12 Hours (11:30 PM)' },
              ]"
            />
          </div> -->
          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              id="fraction"
              :searchable="false"
              v-model="form.fraction"
              :suggestions="[0, 1, 2]"
              :error="form.errors.fraction"
              :label="t('Decimal Fractions')"
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              id="per_page"
              :searchable="false"
              v-model="form.per_page"
              :error="form.errors.per_page"
              :label="t('Table rows per page')"
              :suggestions="[10, 15, 25, 50, 100]"
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              id="initial_rows"
              :searchable="false"
              v-model="form.initial_rows"
              :suggestions="[2, 5, 10, 20]"
              :error="form.errors.initial_rows"
              :label="t('Initial rows on order form')"
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              :json="true"
              id="invoice_status"
              :searchable="false"
              :label="t('Invoice Status')"
              v-model="form.invoice_status"
              :suggestions="[
                { value: 'badge', label: t('As Badge') },
                { value: 'text', label: t('As Text') },
              ]"
              :error="form.errors.invoice_status"
            />
          </div>
          <div class="col-span-6">
            <TextInput v-model="form.attachment_exts" :error="form.errors.attachment_exts" :label="t('Allowed attachments')" />
          </div>
          <!-- <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              id="default_tax_rate"
              :searchable="false"
              v-model="form.default_tax_rate"
              :label="t('Default tax rate')"
              :error="form.errors.default_tax_rate"
              :suggestions="[
                { value: 2, label: 2 },
                { value: 5, label: 5 },
                { value: 10, label: 10 },
                { value: 20, label: 20 },
              ]"
            />
          </div> -->

          <div class="col-span-full bg-gray-50 dark:bg-gray-800 px-4 rounded-md">
            <div class="my-4 grid grid-cols-1 sm:grid-cols-6 gap-4">
              <div class="col-span-6 sm:col-span-3">
                <AutoComplete
                  :json="true"
                  id="number_format"
                  :searchable="false"
                  :label="t('Number Format')"
                  v-model="form.number_format"
                  :error="form.errors.number_format"
                  :suggestions="[
                    { value: '', label: $t('SequenceNumber') },
                    { value: 'Y', label: $t('YearSequenceNumber') },
                    { value: 'Y/', label: $t('Year/SequenceNumber') },
                    { value: 'Y-', label: $t('Year-SequenceNumber') },
                    { value: 'Ym', label: $t('YearMonthSequenceNumber') },
                    { value: 'Y/m/', label: $t('Year/Month/SequenceNumber') },
                    { value: 'Y-m-', label: $t('Year-Month-SequenceNumber') },
                  ]"
                />
              </div>

              <div class="col-span-6 sm:col-span-3">
                <TextInput v-model="form.invoice_prefix" :error="form.errors.invoice_prefix" :label="t('Invoice Number Prefix')" />
              </div>
              <div class="col-span-6 sm:col-span-3">
                <TextInput v-model="form.quotation_prefix" :error="form.errors.quotation_prefix" :label="t('Quotation Number Prefix')" />
              </div>
              <div class="col-span-6 sm:col-span-3">
                <TextInput v-model="form.payment_prefix" :error="form.errors.payment_prefix" :label="t('Payment Number Prefix')" />
              </div>
              <div class="col-span-full">
                <CheckBox
                  id="use_company_number"
                  :error="form.errors.use_company_number"
                  v-model:checked="form.use_company_number"
                  :label="t('Use separate number for each company')"
                />
              </div>
            </div>
          </div>

          <div class="col-span-full bg-gray-50 dark:bg-gray-800 px-4 rounded-md">
            <div class="my-4 grid grid-cols-1 sm:grid-cols-6 gap-4">
              <div class="col-span-6 sm:col-span-3">
                <TextInput v-model="form.mail_from_name" :error="form.errors.mail_from_name" :label="t('Mail From Name')" />
              </div>
              <div class="col-span-6 sm:col-span-3">
                <TextInput
                  type="email"
                  v-model="form.mail_from_address"
                  :error="form.errors.mail_from_address"
                  :label="t('Mail From Email Address')"
                />
              </div>

              <div class="col-span-full">
                <CheckBox
                  id="prefer_company_email"
                  :label="t('Please prefer company name and email')"
                  v-model:checked="form.prefer_company_email"
                  :error="form.errors.prefer_company_email"
                />
                <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-500">
                  This could result in email failure depending on your mail server and company email address.
                </p>
              </div>

              <div class="col-span-full">
                <AutoComplete
                  :json="true"
                  id="mail_default"
                  :searchable="false"
                  :label="t('Mail Driver')"
                  v-model="form.mail_default"
                  :error="form.errors.mail_default"
                  :suggestions="[
                    { value: 'smtp', label: 'SMTP' },
                    { value: 'ses', label: 'AWS SES' },
                    { value: 'mailgun', label: 'Mailgun' },
                    { value: 'postmark', label: 'Postmark' },
                  ]"
                />
              </div>

              <template v-if="form.mail_default == 'smtp'">
                <div class="col-span-6 sm:col-span-3">
                  <TextInput :label="t('SMTP Host')" v-model="form.mail_mailers_smtp_host" :error="form.errors.mail_mailers_smtp_host" />
                </div>
                <div class="col-span-6 sm:col-span-3">
                  <TextInput :label="t('SMTP Port')" v-model="form.mail_mailers_smtp_port" :error="form.errors.mail_mailers_smtp_port" />
                </div>
                <div class="col-span-6 sm:col-span-3">
                  <TextInput
                    :label="t('SMTP Username')"
                    v-model="form.mail_mailers_smtp_username"
                    :error="form.errors.mail_mailers_smtp_username"
                  />
                </div>
                <div class="col-span-6 sm:col-span-3">
                  <TextInput
                    :label="t('SMTP Password')"
                    v-model="form.mail_mailers_smtp_password"
                    :error="form.errors.mail_mailers_smtp_password"
                  />
                </div>
                <div class="col-span-6 sm:col-span-3">
                  <TextInput
                    :label="t('SMTP Encryption')"
                    v-model="form.mail_mailers_smtp_encryption"
                    :error="form.errors.mail_mailers_smtp_encryption"
                  />
                </div>
                <div class="col-span-6 sm:col-span-3"></div>
              </template>
              <template v-else-if="form.mail_default == 'ses'">
                <div class="col-span-6 sm:col-span-3">
                  <TextInput :label="t('AWS SES Key')" v-model="form.services_ses_key" :error="form.errors.services_ses_key" />
                </div>
                <div class="col-span-6 sm:col-span-3">
                  <TextInput :label="t('AWS SES Secret')" v-model="form.services_ses_secret" :error="form.errors.services_ses_secret" />
                </div>
                <div class="col-span-6 sm:col-span-3">
                  <TextInput :label="t('AWS SES Region')" v-model="form.services_ses_region" :error="form.errors.services_ses_region" />
                </div>
                <div class="col-span-6 sm:col-span-3"></div>
              </template>
              <template v-else-if="form.mail_default == 'mailgun'">
                <div class="col-span-6 sm:col-span-3">
                  <TextInput
                    :label="t('Mailgun Domain')"
                    v-model="form.services_mailgun_domain"
                    :error="form.errors.services_mailgun_domain"
                  />
                </div>
                <div class="col-span-6 sm:col-span-3">
                  <TextInput
                    :label="t('Mailgun Secret')"
                    v-model="form.services_mailgun_secret"
                    :error="form.errors.services_mailgun_secret"
                  />
                </div>
                <div class="col-span-6 sm:col-span-3">
                  <TextInput
                    :label="t('Mailgun Endpoint')"
                    v-model="form.services_mailgun_endpoint"
                    :error="form.errors.services_mailgun_endpoint"
                  />
                </div>
                <div class="col-span-6 sm:col-span-3"></div>
              </template>
              <template v-else-if="form.mail_default == 'postmark'">
                <div class="col-span-full">
                  <TextInput
                    :label="t('Postmark Token')"
                    v-model="form.services_postmark_token"
                    :error="form.errors.services_postmark_token"
                  />
                </div>
              </template>
            </div>
          </div>

          <div class="col-span-full">
            <CheckBox id="show_tax" :label="t('Show zero tax')" v-model:checked="form.show_tax" :error="form.errors.show_tax" />
          </div>
          <div class="col-span-full">
            <CheckBox
              id="show_subtotal"
              :error="form.errors.show_subtotal"
              v-model:checked="form.show_subtotal"
              :label="t('Show subtotal on detail view')"
            />
          </div>

          <div class="col-span-full">
            <CheckBox
              id="paypal-enabled"
              :label="t('Enable PayPal Payments')"
              v-model:checked="form.paypal.enabled"
              :error="form.errors.paypal?.enabled"
            />
            <div v-if="form.paypal.enabled" class="my-4 grid grid-cols-1 sm:grid-cols-6 gap-4">
              <div class="col-span-6">
                <TextInput v-model="form.paypal.client_id" :error="form.errors.paypal?.client_id" :label="t('PayPal Client Id')" />
              </div>
              <div class="col-span-6">
                <TextInput v-model="form.paypal.secret" :error="form.errors.paypal?.secret" :label="t('PayPal Secret')" />
              </div>
              <div class="col-span-2">
                <TextInput v-model="form.paypal.fixed" :error="form.errors.paypal?.fixed" :label="t('Fixed Fee Amount')" />
              </div>
              <div class="col-span-2">
                <TextInput
                  v-model="form.paypal.same_country"
                  :error="form.errors.paypal?.same_country"
                  :label="t('Same Country Percentage')"
                />
              </div>
              <div class="col-span-2">
                <TextInput
                  v-model="form.paypal.other_countries"
                  :error="form.errors.paypal?.other_countries"
                  :label="t('Other Countries Percentage')"
                />
              </div>
            </div>
          </div>

          <div class="col-span-full">
            <CheckBox
              id="stripe-enabled"
              :label="t('Enable Stripe Payments')"
              v-model:checked="form.stripe.enabled"
              :error="form.errors.stripe?.enabled"
            />
            <div v-if="form.stripe.enabled" class="my-4 grid grid-cols-1 sm:grid-cols-6 gap-4">
              <div class="col-span-6">
                <TextInput
                  v-model="form.stripe.publishable_key"
                  :error="form.errors.stripe?.publishable_key"
                  :label="t('Stripe Publishable Key')"
                />
              </div>
              <div class="col-span-6">
                <TextInput v-model="form.stripe.secret_key" :error="form.errors.stripe?.secret_key" :label="t('Stripe Secret Key')" />
              </div>
              <div class="col-span-2">
                <TextInput v-model="form.stripe.fixed" :error="form.errors.stripe?.fixed" :label="t('Fixed Fee Amount')" />
              </div>
              <div class="col-span-2">
                <TextInput
                  v-model="form.stripe.same_country"
                  :error="form.errors.stripe?.same_country"
                  :label="t('Same Country Percentage')"
                />
              </div>
              <div class="col-span-2">
                <TextInput
                  v-model="form.stripe.other_countries"
                  :error="form.errors.stripe?.other_countries"
                  :label="t('Other Countries Percentage')"
                />
              </div>
            </div>
          </div>

          <div class="col-span-full flex flex-col gap-4">
            <!-- <CheckBox
                id="product_discount"
                :label="t('Enable product discount')"
                :error="form.errors.product_discount"
                v-model:checked="form.product_discount"
                />
                <CheckBox
                id="create_user"
                :error="form.errors.create_user"
                v-model:checked="form.create_user"
                :label="t('Auto create user for new customer')"
                /> -->
            <CheckBox
              id="impersonation"
              :error="form.errors.impersonation"
              v-model:checked="form.impersonation"
              :label="t('Enable impersonation of customer user')"
            />
          </div>
        </template>

        <template #actions v-if="$can('create-settings')">
          <ActionMessage :on="form.recentlySuccessful" class="mr-3"> {{ t('Saved.') }} </ActionMessage>
          <Button :type="$can('create-settings') ? 'submit' : 'button'" :loading="form.processing">
            {{ t('Save') }}
          </Button>
        </template>
      </FormSection>

      <div v-if="form && form?.default_locale" class="mt-8">
        <FormSection>
          <template #title>{{ t('Preview') }}</template>
          <template #description>{{ t('Number and Date Format') }}</template>

          <template #form>
            <div class="col-span-full -m-6 rounded-md overflow-hidden">
              <dl>
                <div
                  class="odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-black/20 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                >
                  <dt class="font-medium text-gray-500 dark:text-gray-400">{{ t('Currency') }}</dt>
                  <dd class="mt-1 sm:mt-0 sm:col-span-2">
                    {{
                      $currency(20000000.5, form.default_locale, {
                        minimumFractionDigits: form.fraction,
                      })
                    }}
                  </dd>
                </div>
                <div
                  class="odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-black/20 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                >
                  <dt class="font-medium text-gray-500 dark:text-gray-400">{{ t('Number') }}</dt>
                  <dd class="mt-1 sm:mt-0 sm:col-span-2">
                    {{
                      $number(20000000.5, form.default_locale, {
                        minimumFractionDigits: form.fraction,
                      })
                    }}
                  </dd>
                </div>
                <template v-if="form.date_format == 'js'">
                  <div
                    class="odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-black/20 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                  >
                    <dt class="font-medium text-gray-500 dark:text-gray-400">{{ t('Date') }}</dt>
                    <dd class="mt-1 sm:mt-0 sm:col-span-2">
                      {{ $date(new Date().toDateString(), form.default_locale) }}
                    </dd>
                  </div>
                  <div
                    class="odd:bg-white dark:odd:bg-gray-900 even:bg-gray-50 dark:even:bg-black/20 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                  >
                    <dt class="font-medium text-gray-500 dark:text-gray-400">{{ t('Date Time') }}</dt>
                    <dd class="mt-1 sm:mt-0 sm:col-span-2">
                      {{ $datetime(new Date().toISOString(), form.default_locale) }}
                    </dd>
                  </div>
                </template>
              </dl>
            </div>
          </template>
        </FormSection>
      </div>
    </div>

    <div v-if="app_version" class="my-4 flex flex-wrap items-center justify-center gap-x-8 gap-y-2">
      <div>
        App: <span class="font-bold"> {{ app_version }} </span>
      </div>
      <div>
        Laravel: <span class="font-bold"> {{ laravel_version }} </span>
      </div>
      <div>
        Vue: <span class="font-bold"> {{ version }} </span>
      </div>
      <div class="flex items-center gap-2">
        Docs:
        <a target="_blank" href="/documentation.pdf" class="group relative mr-4 font-bold">
          PDF
          <Icon name="a-blank" class="absolute top-0.5 left-full ml-1 hidden h-4 w-4 group-hover:block" />
        </a>
        <!-- <a target="_blank" href="https://tecdiary.github.io/sim-guide" class="group relative text-sm font-bold">
          Online Version
          <Icon name="a-blank" class="absolute top-0 left-full ml-1 hidden h-4 w-4 group-hover:block" />
        </a> -->
      </div>
    </div>
  </AppLayout>
</template>
