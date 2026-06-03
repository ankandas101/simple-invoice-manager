<template>
  <div>
    <Label :for="id" :value="label" />
    <input :id="id" :multiple="multiple" type="file" name="photo" :accept="accept" class="invisible absolute" @change="handleFileChange" />
    <label
      :for="id"
      :class="{
        'border-red-500': error,
        'text-gray-700 dark:text-gray-300': photo,
        'text-gray-400 dark:text-gray-600': !photo,
        'border-gray-300 dark:border-gray-700': !error,
      }"
      class="cursor-pointer mt-1 py-2 px-4 border text-left block w-full focus:border-blue-300 focus:ring-3 focus:ring-blue-200/50 rounded-md shadow-xs dark:bg-gray-800"
    >
      {{ photo ? photo : t('Select') }}
    </label>
    <InputError v-if="error" :message="error" class="mt-1" />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { v4 as uuidv4 } from 'uuid';
import Label from '@/Jetstream/Label.vue';
import { usePage } from '@inertiajs/vue3';
import InputError from '@/Jetstream/InputError.vue';

const photo = ref(null);
const { t } = useI18n({});
const emits = defineEmits(['input', 'update:modelValue']);
const props = defineProps({
  id: {
    type: String,
    default() {
      return uuidv4();
    },
  },
  type: {
    type: String,
    default: 'text',
  },
  multiple: {
    type: Boolean,
    default: false,
  },
  accept: {
    type: String,
    default: usePage().settings?.attachment_exts || '.jpg,.png,.pdf,.xlsx,.docx,.zip',
  },
  modelValue: String,
  label: String,
  error: String,
});

const handleFileChange = e => {
  //   photo.value = e.target.value.split(/[\\/]/).pop();
  photo.value = '';
  if (e.target.files.length == 1) {
    photo.value = '1 ' + t('file selected');
  } else if (e.target.files.length > 1) {
    photo.value = e.target.files.length + ' ' + t('files selected');
  }
  emits('input', props.multiple ? e.target.files : e.target.files[0]);
  emits('update:modelValue', props.multiple ? e.target.files : e.target.files[0]);
};
</script>
