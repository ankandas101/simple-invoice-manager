<script setup>
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

import Button from '@/Shared/Button.vue';
import CheckBox from '@/Shared/CheckBox.vue';
import TextInput from '@/Shared/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Jetstream/InputError.vue';
import CustomFields from '@/Shared/CustomFields.vue';
import FormSection from '@/Jetstream/FormSection.vue';
import ActionMessage from '@/Jetstream/ActionMessage.vue';

const emit = defineEmits(['close', 'done']);
const props = defineProps(['edit', 'roles', 'fields', 'customer']);

const form = useForm({
  name: null,
  email: null,
  password: null,
  roles: ['customer'],
  extra_attributes: {},
  password_confirmation: null,
  customer_id: props.customer?.id,
});

if (props.edit) {
  if (props.edit.extra_attributes && props.fields) {
    Object.keys(props.edit.extra_attributes)
      .filter(k => !props.fields?.find(f => f.name == k))
      .map(k => delete props.edit.extra_attributes[k]);
  }

  form.name = props.edit.name;
  form.email = props.edit.email;
  form.password = props.edit.password;
  form.roles = props.edit.roles.map(r => r.name);
  form.password_confirmation = props.edit.password_confirmation;
  form.extra_attributes = props.edit.extra_attributes || {};
}
const { t } = useI18n({});

function submit() {
  let method = props.edit ? 'put' : 'post';
  let url = props.edit
    ? route('customer.users.update', { customer: props.customer.id, user: props.edit.id })
    : route('customer.users.store', props.customer.id);
  form
    .transform(data => ({ ...data, extra_attributes: { ...data.extra_attributes } }))
    [method](url, {
      preserveScroll: true,
      onSuccess: () => {
        props.edit ? '' : form.reset();
        emit('done');
      },
      onError: () => {
        if (form.errors.phone) {
          document.getElementById('phone').focus();
        }
        if (form.errors.email) {
          document.getElementById('email').focus();
        }
        if (form.errors.name) {
          document.getElementById('name').focus();
        }
      },
    });
}
</script>

<template>
  <div v-if="customer" class="px-6 pt-3 pb-6 print:px-0 bg-white dark:bg-gray-900">
    <div class="flex items-start justify-between print:hidden">
      <div>
        <div class="text-lg">{{ props.edit ? t('Edit User') : t('Add New User') }} ({{ customer.name }})</div>
        <div>
          {{
            t('Please fill the form below to {x}.', {
              x: props.edit ? t('update {x}', { x: t('user') }) : t('add {x}', { x: t('user') }),
            })
          }}
        </div>
      </div>
      <div class="-mr-2 flex items- gap-2">
        <button
          @click="$emit('close')"
          class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
        >
          <icons name="cross" class="h-5 w-5" />
        </button>
      </div>
    </div>

    <div class="mt-4">
      <div class="grid grid-cols-6 gap-6">
        <div class="col-span-6 sm:col-span-3">
          <TextInput v-model="form.name" id="name" :error="form.errors.name" :label="t('Name')" />
        </div>
        <div class="col-span-6 sm:col-span-3">
          <TextInput v-model="form.email" id="email" type="email" :error="form.errors.email" :label="t('Email')" />
        </div>
        <div class="col-span-6 sm:col-span-3">
          <TextInput v-model="form.password" id="password" type="password" :error="form.errors.password" :label="t('Password')" />
        </div>
        <div class="col-span-6 sm:col-span-3">
          <TextInput
            type="password"
            id="password_confirmation"
            :label="t('Confirm Password')"
            v-model="form.password_confirmation"
            :error="form.errors.password_confirmation"
          />
        </div>

        <template v-if="fields && fields.length">
          <CustomFields :fields="fields" :errors="form.errors" v-model="form.extra_attributes" className="col-span-6 sm:col-span-3" />
        </template>

        <div class="col-span-full mt-2">
          <ActionMessage :on="form.recentlySuccessful" class="mr-3"> {{ t('Saved.') }} </ActionMessage>
          <Button type="button" @click="submit" :loading="form.processing"> {{ t('Save') }} </Button>
        </div>
      </div>
    </div>
  </div>
</template>
