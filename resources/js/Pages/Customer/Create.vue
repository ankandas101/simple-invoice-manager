<script setup>
import axios from 'axios';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

import Button from '@/Shared/Button.vue';
import TextInput from '@/Shared/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import CustomFields from '@/Shared/CustomFields.vue';
import FormSection from '@/Jetstream/FormSection.vue';
import ActionMessage from '@/Jetstream/ActionMessage.vue';
import SecondaryButton from '@/Jetstream/SecondaryButton.vue';

const form = ref({
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
  errors: {},
});

const { t } = useI18n({});
const emit = defineEmits(['close', 'done']);
const props = defineProps(['fields']);

function submit() {
  axios
    .post(route('customers.store') + '?is_ajax=yes', form.value)
    .then(res => {
      form.value.errors = {};
      emit('done', res.data);
    })
    .catch(err => {
      console.log(err.response);
      form.value.errors = err.response?.data?.errors;
    });
}
</script>

<template>
  <form @submit.prevent="submit" autocomplete="off">
    <div class="grid grid-cols-1 sm:grid-cols-6 gap-6">
      <div class="col-span-6 sm:col-span-3">
        <TextInput v-model="form.name" id="name" :error="form.errors.name ? form.errors.name.join() : null" :label="t('Name')" />
      </div>
      <div class="col-span-6 sm:col-span-3">
        <TextInput
          v-model="form.company"
          id="company"
          :error="form.errors.company ? form.errors.company.join() : null"
          :label="t('Company')"
        />
      </div>
      <div class="col-span-6 sm:col-span-3">
        <TextInput v-model="form.email" id="email" :error="form.errors.email ? form.errors.email.join() : null" :label="t('Email')" />
      </div>
      <div class="col-span-6 sm:col-span-3">
        <TextInput v-model="form.phone" id="phone" :error="form.errors.phone ? form.errors.phone.join() : null" :label="t('Phone')" />
      </div>
      <div class="col-span-full">
        <TextInput
          v-model="form.address"
          id="address"
          :error="form.errors.address ? form.errors.address.join() : null"
          :label="t('Address')"
        />
      </div>
      <div class="col-span-6 sm:col-span-3">
        <TextInput v-model="form.city" id="city" :error="form.errors.city ? form.errors.city.join() : null" :label="t('City')" />
      </div>
      <div class="col-span-6 sm:col-span-3">
        <TextInput
          v-model="form.postal_code"
          id="postal_code"
          :error="form.errors.postal_code ? form.errors.postal_code.join() : null"
          :label="t('Postal Code')"
        />
      </div>
      <div class="col-span-6 sm:col-span-3">
        <TextInput v-model="form.state" id="state" :error="form.errors.state ? form.errors.state.join() : null" :label="t('State')" />
      </div>
      <div class="col-span-6 sm:col-span-3">
        <TextInput
          v-model="form.country"
          id="country"
          :error="form.errors.country ? form.errors.country.join() : null"
          :label="t('Country')"
        />
      </div>

      <CustomFields :fields="fields" :errors="form.errors" v-model="form.extra_attributes" className="col-span-6 sm:col-span-3" />

      <div class="w-full col-span-full flex items-center justify-between">
        <div>
          <SecondaryButton type="button" @click="emit('close')"> Cancel </SecondaryButton>
        </div>
        <ActionMessage :on="form.recentlySuccessful" class="mr-3"> Saved. </ActionMessage>
        <Button type="submit" :loading="form.processing"> Save </Button>
      </div>
    </div>
  </form>
</template>
