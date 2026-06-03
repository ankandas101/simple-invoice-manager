<script setup>
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

import Button from '@/Shared/Button.vue';
import CheckBox from '@/Shared/CheckBox.vue';
import TextInput from '@/Shared/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import AutoComplete from '@/Shared/AutoComplete.vue';
import FormSection from '@/Jetstream/FormSection.vue';
import ActionMessage from '@/Jetstream/ActionMessage.vue';

const form = useForm({
  name: null,
  slug: null,
  order: null,
  type: 'text',
  options: null,
  models: [],
  show: false,
  required: false,
  description: null,
});

let selected = [];
const props = defineProps(['edit']);
if (props.edit) {
  selected = props.edit.data.models;
  form.name = props.edit.data.name;
  form.slug = props.edit.data.slug;
  form.order = props.edit.data.order;
  form.type = props.edit.data.type;
  form.options = props.edit.data.options;
  form.models = props.edit.data.models.map(m => m.value || m);
  form.show = props.edit.data.show == 1;
  form.required = props.edit.data.required == 1;
  form.description = props.edit.data.description;
}
const { t } = useI18n({});
const models = [
  { value: 'customer', label: t('Customer') },
  { value: 'company', label: t('Company') },
  { value: 'maininvoice', label: t('Invoice') },
  { value: 'invoiceitem', label: t('InvoiceItem') },
  { value: 'payment', label: t('Payment') },
  { value: 'product', label: t('Product') },
  { value: 'mainquotation', label: t('Quotation') },
  { value: 'quotationitem', label: t('QuotationItem') },
  { value: 'user', label: t('User') },
];

function submit() {
  let method = props.edit ? 'put' : 'post';
  let url = props.edit ? route('fields.update', props.edit.data.id) : route('fields.store');
  form.models = form.models.map(m => m.value || m);
  form.models = models.filter(m => form.models.includes(m.value));
  form[method](url, {
    preserveScroll: true,
    onSuccess: () => {
      props.edit ? '' : form.reset();
    },
    onError: () => {
      if (form.errors.name) {
        document.getElementById('name').focus();
      }
    },
  });
}
</script>

<template>
  <AppLayout :title="props.edit ? t('Edit Custom Field') : t('Add New Custom Field')">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
      <FormSection @submitted="submit" autocomplete="off">
        <template #title>{{ props.edit ? t('Edit Custom Field') : t('Add New Custom Field') }}</template>
        <template #description>
          {{
            t('Please fill the form below to {x}.', {
              x: props.edit ? t('update {x}', { x: t('field') }) : t('add {x}', { x: t('field') }),
            })
          }}
          <div>
            <Button :href="route('fields.index')" class="mt-4">{{ t('List Custom Fields') }}</Button>
          </div>
        </template>

        <template #form>
          <div class="col-span-full">
            <TextInput v-model="form.name" id="name" :error="form.errors.name" :label="t('Name')" />
          </div>
          <!-- <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.slug" id="slug" :error="form.errors.slug" :label="t('Slug')" />
          </div> -->
          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              :json="true"
              id="field_type"
              :label="t('Type')"
              :searchable="false"
              v-model="form.type"
              :error="form.errors.type"
              :suggestions="[
                { value: 'checkbox_field', label: t('checkbox_field') },
                { value: 'date_field', label: t('date_field') },
                { value: 'time_field', label: t('time_field') },
                { value: 'radio_field', label: t('radio_field') },
                { value: 'select_field', label: t('select_field') },
                { value: 'select_multiple_field', label: t('select_multiple_field') },
                { value: 'text_field', label: t('text_field') },
                { value: 'textarea_field', label: t('textarea_field') },
              ]"
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.order" id="order" :error="form.errors.order" :label="t('Order')" />
          </div>
          <!-- <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.type" id="type" :error="form.errors.type" :label="t('Type')" />
          </div> -->
          <div
            v-if="
              form.type == 'select_field' ||
              form.type == 'select_multiple_field' ||
              form.type == 'checkbox_field' ||
              form.type == 'radio_field'
            "
            class="col-span-full"
          >
            <TextInput v-model="form.options" id="options" :error="form.errors.options" :label="t('Options, separated by comma')" />
          </div>
          <div class="col-span-full">
            <AutoComplete
              id="models"
              :json="true"
              :multiple="true"
              :searchable="false"
              :label="t('Models')"
              :selected="selected"
              v-model="form.models"
              :suggestions="models"
              :error="form.errors.models"
            />
          </div>
          <div class="col-span-full">
            <TextInput v-model="form.description" id="description" :error="form.errors.description" :label="t('Description')" />
          </div>
          <div class="col-span-full">
            <CheckBox id="required" :label="t('This is required field')" v-model:checked="form.required" :error="form.errors.required" />
          </div>
          <div class="col-span-full">
            <CheckBox id="show" :label="t('Show this field on order')" v-model:checked="form.show" :error="form.errors.show" />
          </div>
        </template>

        <template #actions>
          <ActionMessage :on="form.recentlySuccessful" class="mr-3"> {{ t('Saved.') }} </ActionMessage>
          <Button type="submit" :loading="form.processing"> {{ t('Save') }} </Button>
        </template>
      </FormSection>
    </div>
  </AppLayout>
</template>
