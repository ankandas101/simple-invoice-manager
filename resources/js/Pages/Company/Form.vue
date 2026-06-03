<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

import Button from '@/Shared/Button.vue';
import JetLabel from '@/Jetstream/Label.vue';
import CheckBox from '@/Shared/CheckBox.vue';
import TextInput from '@/Shared/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import CustomFields from '@/Shared/CustomFields.vue';
import FormSection from '@/Jetstream/FormSection.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import TextareaInput from '@/Shared/TextareaInput.vue';
import ActionMessage from '@/Jetstream/ActionMessage.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';

const { t } = useI18n({});
const logoInput = ref(null);
const qrcodeInput = ref(null);
const logoPreview = ref(null);
const logoDarkInput = ref(null);
const qrcodePreview = ref(null);
const logoDarkPreview = ref(null);
const form = useForm({
  _method: 'post',
  name: null,
  contact_person: null,
  email: null,
  phone: null,
  address: null,
  city: null,
  postal_code: null,
  state: null,
  country: null,
  logo: null,
  logo_dark: null,
  ss_image: null,
  show_name: true,
  show_address: true,
  allow_transfer: true,
  qrcode: null,
  bank_account_details: null,
  extra_attributes: {},
});

const props = defineProps(['edit', 'fields']);
if (props.edit) {
  if (props.edit.data.extra_attributes && props.fields) {
    Object.keys(props.edit.data.extra_attributes)
      .filter(k => !props.fields?.find(f => f.name == k))
      .map(k => delete props.edit.data.extra_attributes[k]);
  }

  form.name = props.edit.data.name;
  form.contact_person = props.edit.data.contact_person;
  form.email = props.edit.data.email;
  form.phone = props.edit.data.phone;
  form.address = props.edit.data.address;
  form.city = props.edit.data.city;
  form.postal_code = props.edit.data.postal_code;
  form.state = props.edit.data.state;
  form.country = props.edit.data.country;
  form.logo = props.edit.data.logo;
  form.logo_dark = props.edit.data.logo_dark;
  form.show_name = props.edit.data.show_name == 1 ? true : false;
  form.ss_image = props.edit.data.ss_image;
  form.extra_attributes = props.edit.data.extra_attributes || {};
  form.show_address = props.edit.data.show_address == 1 ? true : false;
  form.allow_transfer = props.edit.data.allow_transfer == 1 ? true : false;
  form.bank_account_details = props.edit.data.bank_account_details || '';
  form.qrcode = props.edit.data.qrcode || '';
}

const selectNewQRCode = () => {
  qrcodeInput.value.click();
};

const selectNewLogo = () => {
  logoInput.value.click();
};

const selectNewLogoDark = () => {
  logoDarkInput.value.click();
};

const updateQRCodePreview = () => {
  const photo = qrcodeInput.value.files[0];
  if (!photo) return;

  const reader = new FileReader();
  reader.onload = e => {
    qrcodePreview.value = e.target.result;
  };
  reader.readAsDataURL(photo);
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

const updateLogoDarkPreview = () => {
  const photo = logoDarkInput.value.files[0];
  if (!photo) return;

  const reader = new FileReader();
  reader.onload = e => {
    logoDarkPreview.value = e.target.result;
  };
  reader.readAsDataURL(photo);
};

function deleteQRCode() {}

function submit() {
  if (logoInput.value) {
    form.logo = logoInput.value.files[0];
  }
  if (logoDarkInput.value) {
    form.logo_dark = logoDarkInput.value.files[0];
  }
  if (qrcodeInput.value) {
    form.qrcode = qrcodeInput.value.files[0];
  }

  let url = route('companies.store');
  if (props.edit) {
    form._method = 'put';
    url = route('companies.update', props.edit.data.id);
  }

  form
    .transform(data => ({ ...data, extra_attributes: { ...data.extra_attributes } }))
    .post(url, {
      preserveScroll: true,
      onSuccess: () => {
        props.edit ? '' : form.reset();
      },
      onError: () => {
        if (form.errors.company_id) {
          document.getElementById('company_id').focus();
        }
        if (form.errors.customer_id) {
          document.getElementById('customer_id').focus();
        }
        if (form.errors.email) {
          document.getElementById('email').focus();
        }
        if (form.errors.phone) {
          document.getElementById('phone').focus();
        }
      },
    });
}
</script>

<template>
  <AppLayout :title="props.edit ? t('Edit Company') : t('Add New Company')">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
      <FormSection @submitted="submit" autocomplete="off">
        <template #title>{{ props.edit ? t('Edit Company') : t('Add New Company') }}</template>
        <template #description>
          {{
            t('Please fill the form below to {x}.', {
              x: props.edit ? t('update {x}', { x: t('company') }) : t('add {x}', { x: t('company') }),
            })
          }}
          <div>
            <Button :href="route('companies.index')" class="mt-4">{{ t('List Companies') }}</Button>
          </div>
        </template>

        <template #form>
          <div class="col-span-6 sm:col-span-3">
            <!-- Logo File Input -->
            <input ref="logoInput" type="file" class="hidden" accept=".png,.jpg,.jpeg,.svg" @change="updateLogoPreview" />
            <JetLabel for="logo" :value="t('Logo')" />

            <!-- Current Logo -->
            <div v-if="!logoPreview && form.logo" class="my-2 rounded-md bg-white p-2">
              <img :src="form.logo" :alt="form.name" class="block rounded-sm w-80 h-20 mx-auto bg-contain" />
            </div>

            <!-- New Logo Preview -->
            <div v-if="logoPreview" class="my-2 rounded-md bg-white p-2">
              <span
                class="block rounded-sm w-80 h-20 mx-auto bg-contain bg-no-repeat bg-center"
                :style="'background-image: url(\'' + logoPreview + '\');'"
              />
            </div>

            <JetSecondaryButton class="mt-2 mr-2" type="button" @click.prevent="selectNewLogo">
              {{ t('Select Logo') }}
            </JetSecondaryButton>

            <!-- <JetSecondaryButton v-if="form.logo" type="button" class="mt-2" @click.prevent="deleteLogo">
              {{ t('Remove Logo') }}
            </JetSecondaryButton> -->
            <JetInputError :message="form.errors.logo" class="mt-2" />
          </div>

          <div class="col-span-6 sm:col-span-3">
            <!-- Logo Dark File Input -->
            <input ref="logoDarkInput" type="file" class="hidden" accept=".png,.jpg,.jpeg,.svg" @change="updateLogoDarkPreview" />
            <JetLabel for="logoDark" :value="t('Dark Logo')" />

            <!-- Current Logo Dark -->
            <div v-if="!logoDarkPreview && form.logo_dark" class="my-2 rounded-md bg-white p-2">
              <img :src="form.logo_dark" :alt="form.name" class="block rounded-sm w-80 h-20 mx-auto bg-contain" />
            </div>

            <!-- New Logo Dark Preview -->
            <div v-if="logoDarkPreview" class="my-2 rounded-md bg-white p-2">
              <span
                class="block rounded-sm w-80 h-20 mx-auto bg-contain bg-no-repeat bg-center"
                :style="'background-image: url(\'' + logoDarkPreview + '\');'"
              />
            </div>

            <JetSecondaryButton class="mt-2 mr-2" type="button" @click.prevent="selectNewLogoDark">
              {{ t('Select Dark Logo') }}
            </JetSecondaryButton>

            <!-- <JetSecondaryButton v-if="form.logoDark" type="button" class="mt-2" @click.prevent="deleteLogoDark">
              {{ t('Remove Dark Logo') }}
            </JetSecondaryButton> -->
            <JetInputError :message="form.errors.logo_dark" class="mt-2" />
          </div>

          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.name" id="name" :error="form.errors.name" :label="t('Name')" />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.contact_person" id="contact_person" :error="form.errors.contact_person" :label="t('Contact Person')" />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.email" id="email" :error="form.errors.email" :label="t('Email')" />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.phone" id="phone" :error="form.errors.phone" :label="t('Phone')" />
          </div>
          <div class="col-span-full">
            <TextInput v-model="form.address" id="address" :error="form.errors.address" :label="t('Address')" />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.city" id="city" :error="form.errors.city" :label="t('City')" />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.postal_code" id="postal_code" :error="form.errors.postal_code" :label="t('Postal Code')" />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.state" id="state" :error="form.errors.state" :label="t('State')" />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.country" id="country" :error="form.errors.country" :label="t('Country')" />
          </div>

          <div class="col-span-full">
            <CheckBox
              id="show_name"
              :error="form.errors.show_name"
              v-model:checked="form.show_name"
              :label="t('Show company name on invoices and quotations')"
            />
            <CheckBox
              id="show_address"
              :error="form.errors.show_address"
              v-model:checked="form.show_address"
              :label="t('Show company full address on invoices and quotations')"
            />
            <CheckBox
              id="allow_transfer"
              :error="form.errors.allow_transfer"
              v-model:checked="form.allow_transfer"
              :label="t('Allow transfer of invoice amount to company bank account')"
            />
            <div v-if="form.allow_transfer" class="mt-2">
              <TextareaInput
                v-model="form.bank_account_details"
                id="bank_account_details"
                :error="form.errors.bank_account_details"
                :label="t('Bank Transfer Details (Bank Name & Account Number etc)')"
              />
              <div class="mt-6">
                <!-- QRCode File Input -->
                <input ref="qrcodeInput" type="file" class="hidden" accept=".png,.jpg,.jpeg,.svg" @change="updateQRCodePreview" />
                <JetLabel for="qrcode" :value="t('QRCode')" />

                <!-- Current QRCode -->
                <div v-if="!qrcodePreview && form.qrcode" class="my-2 rounded-md bg-white p-2">
                  <img :src="form.qrcode" :alt="form.name" class="block rounded-sm w-28 h-28 bg-contain" />
                </div>

                <!-- New QRCode Preview -->
                <div v-if="qrcodePreview" class="my-2 rounded-md bg-white p-2">
                  <span
                    class="block rounded-sm w-28 h-28 bg-contain bg-no-repeat bg-center"
                    :style="'background-image: url(\'' + qrcodePreview + '\');'"
                  />
                </div>

                <JetSecondaryButton class="mt-2 mr-2" type="button" @click.prevent="selectNewQRCode">
                  {{ t('Select QRCode') }}
                </JetSecondaryButton>

                <JetSecondaryButton v-if="form.qrcode" type="button" class="mt-2" @click.prevent="deleteQRCode">
                  {{ t('Remove QRCode') }}
                </JetSecondaryButton>
                <JetInputError :message="form.errors.qrcode" class="mt-2" />
              </div>
            </div>
          </div>

          <CustomFields :fields="fields" :errors="form.errors" v-model="form.extra_attributes" className="col-span-6 sm:col-span-3" />
        </template>

        <template #actions>
          <ActionMessage :on="form.recentlySuccessful" class="mr-3"> {{ t('Saved.') }} </ActionMessage>
          <Button type="submit" :loading="form.processing"> {{ t('Save') }} </Button>
        </template>
      </FormSection>
    </div>
  </AppLayout>
</template>
