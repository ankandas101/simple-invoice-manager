<template>
  <div>
    <div class="py-6">
      <FileInput v-model="excel" accept=".xlsx" id="file" :error="error" :label="t('Excel File')" />
    </div>
    <div class="flex items-center justify-end">
      <LoadingButton
        type="button"
        @click="submit"
        :loading="loading"
        class="inline-flex items-center px-4 py-3 bg-blue-600 border-0 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-700 disabled:opacity-25 transition"
      >
        {{ t('Import') }}
      </LoadingButton>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import FileInput from '@/Shared/FileInput.vue';
import LoadingButton from './LoadingButton.vue';

const excel = ref(null);
const error = ref(null);
const { t } = useI18n({});
const loading = ref(false);
const emits = defineEmits(['done']);

function submit() {
  loading.value = true;
  let form = new FormData();
  form.append('excel', excel.value);
  axios
    .post(route('read.excel'), form)
    .then(res => {
      emits('done', res.data);
    })
    .catch(err => {
      error.value = err.response.data.message;
    })
    .finally(() => (loading.value = false));
}
</script>
