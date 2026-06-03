<script setup>
import axios from 'axios';
import dayjs from 'dayjs';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

import Button from '@/Shared/Button.vue';
import CheckBox from '@/Shared/CheckBox.vue';
import TextInput from '@/Shared/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import CustomFields from '@/Shared/CustomFields.vue';
import FormSection from '@/Jetstream/FormSection.vue';
import TextareaInput from '@/Shared/TextareaInput.vue';
import ActionMessage from '@/Jetstream/ActionMessage.vue';
import SecondaryButton from '@/Jetstream/SecondaryButton.vue';

const form = ref({
  date: new Date().toISOString().slice(0, 10),
  reference: null,
  amount: null,
  customer_id: null,
  company_id: null,
  invoice_id: null,
  method: null,
  note: null,
  attachment: null,
  send_receipt: false,
  extra_attributes: {},
  errors: {},
});

const { t } = useI18n({});
const emit = defineEmits(['close', 'done']);
const props = defineProps(['edit', 'fields', 'invoice']);

if (props.invoice) {
  form.value.invoice_id = props.invoice.id;
  form.value.company_id = props.invoice.company_id;
  form.value.customer_id = props.invoice.customer_id;
  form.value.amount = Number(props.invoice.grand_total) - Number(props.invoice.paid);
}

if (props.edit) {
  if (props.edit.data.extra_attributes && props.fields) {
    Object.keys(props.edit.data.extra_attributes)
      .filter(k => !props.fields?.find(f => f.name == k))
      .map(k => delete props.edit.data.extra_attributes[k]);
  }

  form.value.date = dayjs(props.edit.data.date).format('YYYY-MM-DD'); // props.edit.data.date.split('T')[0];
  form.value.reference = props.edit.data.reference;
  form.value.due_date = props.edit.data.due_date;
  form.value.company_id = props.edit.data.company_id;
  form.value.customer_id = props.edit.data.customer_id;
  form.value.invoice_id = props.edit.data.invoice_id;
  form.value.amount = Number(props.edit.data.amount);
  form.value.method = props.edit.data.method;
  form.value.note = props.edit.data.note;
  form.value.attachment = null;
  form.value.extra_attributes = props.edit.data.extra_attributes || {};
}

function submit() {
  axios[props.edit?.data?.id ? 'put' : 'post'](
    (props.edit?.data?.id ? route('payments.update', props.edit.data.id) : route('payments.store')) + '?is_ajax=yes',
    form.value
  )
    .then(res => {
      form.value.errors = {};
      emit('done', res.data);
    })
    .catch(err => {
      //   console.log(err.response);
      form.value.errors = err.response?.data?.errors;
    });
}
</script>

<template>
  <form @submit.prevent="submit" autocomplete="off">
    <div class="bg-white dark:bg-gray-900">
      <div class="flex items-start justify-between print:hidden">
        <div class="p-6">
          <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
            {{ edit ? t('Edit Payment') : t('Add New Payment') }}
          </h3>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{
              t('Please fill the form below to {x}.', {
                x: edit ? t('update {x}', { x: t('payment') }) : t('add {x}', { x: t('payment') }),
              })
            }}
          </p>
        </div>
        <div class="m-2 gap-2">
          <button
            type="button"
            @click="$emit('close')"
            class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
          >
            <icons name="cross" class="h-5 w-5" />
          </button>
        </div>
      </div>
      <div class="px-6 grid grid-cols-1 sm:grid-cols-6 gap-6">
        <div class="col-span-6 sm:col-span-3">
          <TextInput
            id="date"
            type="date"
            :label="t('Date')"
            v-model="form.date"
            :error="form.errors.date ? form.errors.date.join() : null"
          />
        </div>
        <div class="col-span-6 sm:col-span-3">
          <TextInput
            v-model="form.reference"
            id="reference"
            :error="form.errors.reference ? form.errors.reference.join() : null"
            :label="t('Reference')"
          />
        </div>

        <div class="col-span-6 sm:col-span-3">
          <TextInput
            id="amount"
            type="number"
            v-model="form.amount"
            :error="form.errors.amount ? form.errors.amount.join() : null"
            :label="t('Amount')"
          />
        </div>
        <div class="col-span-6 sm:col-span-3">
          <TextInput
            v-model="form.method"
            id="method"
            :error="form.errors.method ? form.errors.method.join() : null"
            :label="t('Method')"
          />
        </div>

        <CustomFields :fields="fields" :errors="form.errors" v-model="form.extra_attributes" className="col-span-6 sm:col-span-3" />

        <div class="col-span-full">
          <TextareaInput v-model="form.note" id="note" :error="form.errors.note" :label="t('Note')" />
        </div>
        <div v-if="!edit" class="col-span-full">
          <CheckBox id="send_receipt" :error="form.errors.send_receipt" v-model:checked="form.send_receipt" :label="t('Send Receipt')" />
        </div>
      </div>

      <div class="p-6 pb-6 w-full col-span-full flex items-center justify-between">
        <div>
          <SecondaryButton type="button" @click="emit('close')"> {{ t('Cancel') }} </SecondaryButton>
        </div>
        <ActionMessage :on="form.recentlySuccessful" class="mr-3"> {{ t('Saved.') }} </ActionMessage>
        <Button type="submit" :loading="form.processing"> {{ t('Save') }} </Button>
      </div>
    </div>
  </form>
</template>
