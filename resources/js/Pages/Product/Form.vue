<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

import Button from '@/Shared/Button.vue';
import FileInput from '@/Shared/FileInput.vue';
import TextInput from '@/Shared/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SelectInput from '@/Shared/SelectInput.vue';
import CustomFields from '@/Shared/CustomFields.vue';
import AutoComplete from '@/Shared/AutoComplete.vue';
import FormSection from '@/Jetstream/FormSection.vue';
import TextareaInput from '@/Shared/TextareaInput.vue';
import ActionMessage from '@/Jetstream/ActionMessage.vue';

const form = useForm({
  name: null,
  price: null,
  details: null,
  taxes: [],
  tax_method: 'exclusive',
  extra_attributes: {},
});
const props = defineProps(['edit', 'fields', 'tax_rates']);
// if (props.tax_rates) {
//   props.tax_rates = props.tax_rates.map(t => ({ ...t, value: t.id, label: t.name }));
// }
if (props.edit) {
  if (props.edit.data.extra_attributes && props.fields) {
    Object.keys(props.edit.data.extra_attributes)
      .filter(k => !props.fields?.find(f => f.name == k))
      .map(k => delete props.edit.data.extra_attributes[k]);
  }
  form.name = props.edit.data.name;
  form.price = Number(props.edit.data.price);
  form.details = props.edit.data.details;
  form.taxes = props.edit.data.taxes.map(t => t.id);
  //   form.taxes = props.tax_rates.filter(t => props.edit.data.taxes?.find(pt => pt.id == t.value)) || [];
  form.tax_method = props.edit.data.tax_method;
  form.extra_attributes = props.edit.data.extra_attributes || {};
}
const { t } = useI18n({});

function submit() {
  let method = props.edit ? 'put' : 'post';
  let url = props.edit ? route('products.update', props.edit.data.id) : route('products.store');
  form
    .transform(data => ({ ...data, _method: method, extra_attributes: { ...data.extra_attributes } }))
    .post(url, {
      preserveScroll: true,
      onSuccess: () => {
        props.edit ? '' : form.reset();
      },
      onError: () => {
        if (form.errors.price) {
          document.getElementById('price').focus();
        }
        if (form.errors.name) {
          document.getElementById('name').focus();
        }
      },
    });
}
</script>

<template>
  <AppLayout :title="props.edit ? t('Edit Product') : t('Add New Product')">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
      <FormSection @submitted="submit" autocomplete="off">
        <template #title>{{ props.edit ? t('Edit Product') : t('Add New Product') }}</template>
        <template #description>
          {{
            t('Please fill the form below to {x}.', {
              x: props.edit ? t('update {x}', { x: t('product') }) : t('add {x}', { x: t('product') }),
            })
          }}
          <div>
            <Button :href="route('products.index')" class="mt-4">{{ t('List Products') }}</Button>
          </div>
        </template>

        <template #form>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.name" id="name" :error="form.errors.name" :label="t('Name')" />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput type="number" v-model="form.price" id="price" :error="form.errors.price" :label="t('Price')" />
          </div>

          <CustomFields :fields="fields" :errors="form.errors" v-model="form.extra_attributes" className="col-span-6 sm:col-span-3" />

          <div class="col-span-full">
            <TextareaInput v-model="form.details" id="details" :error="form.errors.details" :label="t('Details')" />
          </div>
          <div class="col-span-6 sm:col-span-4">
            <AutoComplete
              :json="true"
              id="tax_rates"
              :multiple="true"
              :searchable="false"
              v-model="form.taxes"
              :label="t('Tax Rates')"
              :suggestions="tax_rates"
              :error="form.errors.taxes"
            />
          </div>
          <div class="col-span-6 sm:col-span-2">
            <AutoComplete
              :json="true"
              id="tax_method"
              :searchable="false"
              :label="t('Tax Method')"
              v-model="form.tax_method"
              :error="form.errors.tax_method"
              :suggestions="[
                { value: 'inclusive', label: t('inclusive') },
                { value: 'exclusive', label: t('exclusive') },
              ]"
            />
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
