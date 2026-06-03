<script setup>
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

import CreateForm from './Create.vue';
import Button from '@/Shared/Button.vue';
import TextInput from '@/Shared/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import CustomFields from '@/Shared/CustomFields.vue';
import FormSection from '@/Jetstream/FormSection.vue';
import ActionMessage from '@/Jetstream/ActionMessage.vue';

const form = useForm({
  company: null,
  name: null,
  email: null,
  phone: null,
  address: null,
  city: null,
  postal_code: null,
  state: null,
  country: null,
  extra_attributes: {},
});

const props = defineProps(['edit', 'fields']);
if (props.edit) {
  if (props.edit.data.extra_attributes && props.fields) {
    Object.keys(props.edit.data.extra_attributes)
      .filter(k => !props.fields?.find(f => f.name == k))
      .map(k => delete props.edit.data.extra_attributes[k]);
  }

  form.company = props.edit.data.company;
  form.name = props.edit.data.name;
  form.email = props.edit.data.email;
  form.phone = props.edit.data.phone;
  form.address = props.edit.data.address;
  form.city = props.edit.data.city;
  form.postal_code = props.edit.data.postal_code;
  form.state = props.edit.data.state;
  form.country = props.edit.data.country;
  form.extra_attributes = props.edit.data.extra_attributes || {};
}
const { t } = useI18n({});

function submit() {
  let method = props.edit ? 'put' : 'post';
  let url = props.edit ? route('customers.update', props.edit.data.id) : route('customers.store');
  form
    .transform(data => ({ ...data, extra_attributes: { ...data.extra_attributes } }))
    [method](url, {
      preserveScroll: true,
      onSuccess: () => {
        props.edit ? '' : form.reset();
      },
      onError: () => {
        if (form.errors.company) {
          document.getElementById('company').focus();
        }
        if (form.errors.name) {
          document.getElementById('name').focus();
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
  <AppLayout :title="props.edit ? t('Edit Customer') : t('Add New Customer')">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
      <FormSection @submitted="submit" autocomplete="off">
        <template #title>{{ props.edit ? t('Edit Customer') : t('Add New Customer') }}</template>
        <template #description>
          {{
            t('Please fill the form below to {x}.', {
              x: props.edit ? t('update {x}', { x: t('customer') }) : t('add {x}', { x: t('customer') }),
            })
          }}
          <div>
            <Button :href="route('customers.index')" class="mt-4">{{ t('List Customers') }}</Button>
          </div>
        </template>

        <template #form>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.name" id="name" :error="form.errors.name" :label="t('Name')" />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.company" id="company" :error="form.errors.company" :label="t('Company')" />
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
