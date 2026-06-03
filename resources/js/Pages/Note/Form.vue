<script setup>
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

import Button from '@/Shared/Button.vue';
import CheckBox from '@/Shared/CheckBox.vue';
import TextInput from '@/Shared/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormSection from '@/Jetstream/FormSection.vue';
import TextareaInput from '@/Shared/TextareaInput.vue';
import ActionMessage from '@/Jetstream/ActionMessage.vue';

const form = useForm({ name: null, details: null, default_sale: false, default_quote: false });

const props = defineProps(['edit']);
if (props.edit) {
  form.name = props.edit.data.name;
  form.details = props.edit.data.details;
  form.default_sale = props.edit.data.default_sale == 1;
  form.default_quote = props.edit.data.default_quote == 1;
}
const { t } = useI18n({});

function submit() {
  let method = props.edit ? 'put' : 'post';
  let url = props.edit ? route('notes.update', props.edit.data.id) : route('notes.store');
  form[method](url, {
    preserveScroll: true,
    onSuccess: () => {
      props.edit ? '' : form.reset();
    },
    onError: () => {
      if (form.errors.details) {
        document.getElementById('details').focus();
      }
      if (form.errors.name) {
        document.getElementById('name').focus();
      }
    },
  });
}
</script>

<template>
  <AppLayout :title="props.edit ? t('Edit Note') : t('Add New Note')">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
      <FormSection @submitted="submit" autocomplete="off">
        <template #title>{{ props.edit ? t('Edit Note') : t('Add New Note') }}</template>
        <template #description>
          {{
            t('Please fill the form below to {x}.', {
              x: props.edit ? t('update {x}', { x: t('note') }) : t('add {x}', { x: t('note') }),
            })
          }}
          <div>
            <Button :href="route('notes.index')" class="mt-4">{{ t('List Notes') }}</Button>
          </div>
        </template>

        <template #form>
          <div class="col-span-full">
            <TextInput v-model="form.name" id="name" :error="form.errors.name" :label="t('Name')" />
          </div>
          <div class="col-span-full">
            <TextareaInput v-model="form.details" id="details" :error="form.errors.details" :label="t('Details')" />
          </div>
          <div class="col-span-full flex flex-col gap-4">
            <CheckBox
              id="default_sale"
              :label="t('Default note for invoices')"
              v-model:checked="form.default_sale"
              :error="form.errors.default_sale"
            />
            <CheckBox
              id="default_quote"
              :label="t('Default note for quotations')"
              v-model:checked="form.default_quote"
              :error="form.errors.default_quote"
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
