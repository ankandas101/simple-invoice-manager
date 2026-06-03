<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

import Button from '@/Shared/Button.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormSection from '@/Jetstream/FormSection.vue';
import DropDownInput from '@/Shared/DropDownInput.vue';
import LoadingButton from '@/Shared/LoadingButton.vue';

const { t } = useI18n({});

const formRef = ref([]);
const selected = ref(null);
const form = useForm({ _method: 'POST', excel: null });

const updateFile = e => {
  selected.value = e.target.files[0].name;
};

const submit = () => {
  if (formRef.value.files) {
    form.excel = formRef.value.files[0];
  }

  // var data = new FormData();
  // data.append('excel', this.form.excel);
  // data.append('_method', this.form._method);
  // this.$router.post(route('products.import.save'), data);
  form.post(route('products.import.save'), { preserveScroll: true });
};
</script>

<template>
  <AppLayout :title="t('Import {x}', { x: t('Products') })">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
      <FormSection @submitted="submit">
        <template #title>{{ t('Import {x}', { x: t('Products') }) }}</template>
        <template #description>
          {{ t('Please upload the excel file to import records.') }}
          <div class="flex items-center gap-4 flex-wrap mt-4">
            <Button :href="route('products.index')">{{ t('List Products') }}</Button>
            <Button away :href="route('products.export')">{{ t('Export Products') }}</Button>
          </div>
        </template>

        <template #form>
          <div class="w-full col-span-full">
            <label for="file-upload" class="block font-medium">{{ t('Excel File') }}</label>
            <div
              :class="$page.props.errors.excel ? 'border-red-500' : 'border-gray-300 dark:border-gray-700'"
              class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-md"
            >
              <div class="space-y-1 text-center">
                <icons name="doc-text" size="mx-auto h-12 w-12 text-gray-400" />
                <div class="flex items-center justify-center py-2">
                  <label
                    for="file-upload"
                    class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-hidden focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-gray-300"
                  >
                    <span v-if="selected" class="font-semibold">{{ t('Change file') }}</span>
                    <span v-else class="font-semibold">{{ t('Select file') }}</span>
                    <input
                      type="file"
                      ref="formRef"
                      class="sr-only"
                      id="file-upload"
                      name="file-upload"
                      @change="updateFile"
                      accept=".xls,.xlsx,application/vnd.ms-excel"
                    />
                  </label>
                  <p class="pl-1">{{ t('or drag and drop') }}</p>
                </div>
                <div class="text-xs">
                  <div>
                    {{ t('Excel file should have name, price, details, taxes, tax_method and custom fields columns.') }}
                  </div>
                  <div>{{ t('The name and price columns are required.') }}</div>
                  <div>{{ t('taxes column could have multiple tax names separated bu comma.') }}</div>
                  <div class="mt-3">
                    {{ t('The custom fields can be any number of columns.') }}
                  </div>
                  <div>
                    {{ t('The column title must the custom filed name that you have already added in the settings.') }}
                  </div>
                </div>
                <div v-if="selected" class="inline-block pt-4">
                  <div class="px-3 py-1 rounded-md border font-bold text-lg">{{ t('Selected File') }}: {{ selected }}</div>
                </div>
                <div v-if="$page.props.errors.excel" class="mt-4 pt-2 text-red-600 rounded-md">
                  {{ $page.props.errors.excel }}
                </div>
              </div>
            </div>
          </div>
        </template>

        <template #actions>
          <LoadingButton :loading="form.processing" :disabled="form.processing">{{ t('Import') }}</LoadingButton>
        </template>
      </FormSection>
    </div>
  </AppLayout>
</template>
