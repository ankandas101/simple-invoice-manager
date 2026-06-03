<script setup>
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

import Button from '@/Shared/Button.vue';
import CheckBox from '@/Shared/CheckBox.vue';
import TextInput from '@/Shared/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormSection from '@/Jetstream/FormSection.vue';
import ActionMessage from '@/Jetstream/ActionMessage.vue';

const form = useForm({ name: null, rate: null, fixed: false });

const props = defineProps(['edit']);
if (props.edit) {
  form.name = props.edit.data.name;
  form.rate = Number(props.edit.data.rate);
  form.fixed = props.edit.data.fixed == 1 ? true : false;
}
const { t } = useI18n({});

onMounted(() => {
  document.getElementById('name').focus();
});

function submit() {
  let method = props.edit ? 'put' : 'post';
  let url = props.edit ? route('tax_rates.update', props.edit.data.id) : route('tax_rates.store');
  form[method](url, {
    preserveScroll: true,
    onSuccess: () => {
      props.edit ? '' : form.reset();
    },
    onError: () => {
      if (form.errors.name) {
        document.getElementById('rate').focus();
      }
      if (form.errors.code) {
        document.getElementById('name').focus();
      }
    },
  });
}
</script>

<template>
  <AppLayout :title="props.edit ? t('Edit Tax Rate') : t('Add New Tax Rate')">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
      <FormSection @submitted="submit" autocomplete="off">
        <template #title>{{ props.edit ? t('Edit Tax Rate') : t('Add New Tax Rate') }}</template>
        <template #description>
          {{
            t('Please fill the form below to {x}.', {
              x: props.edit ? t('update {x}', { x: t('tax rate') }) : t('add {x}', { x: t('tax rate') }),
            })
          }}
          <div>
            <Button :href="route('tax_rates.index')" class="mt-4">{{ t('List Tax Rates') }}</Button>
          </div>
        </template>

        <template #form>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.name" id="name" :error="form.errors.name" :label="t('Name')" />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput type="number" step=".01" v-model="form.rate" id="rate" :error="form.errors.rate" :label="t('Rate')" />
          </div>
          <div class="col-span-full mb-4">
            <CheckBox
              id="fixed"
              :error="form.errors.fixed"
              v-model:checked="form.fixed"
              :label="t('This is fixed amount tax rate, not percentage')"
            />
          </div>
        </template>

        <template #actions>
          <ActionMessage :on="form.recentlySuccessful" class="mr-3"> Saved. </ActionMessage>
          <Button type="submit" :loading="form.processing"> Save </Button>
        </template>
      </FormSection>
    </div>
  </AppLayout>
</template>
