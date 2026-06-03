<script setup>
import { useI18n } from 'vue-i18n';
import Modal from '@/Jetstream/Modal.vue';

const { t } = useI18n({});
const emits = defineEmits(['close']);
const props = defineProps({
  show: {
    default: false,
  },
  maxWidth: {
    default: '2xl',
  },
  closeable: {
    default: true,
  },
});
</script>

<template>
  <Modal :show="show" :max-width="maxWidth" :closeable="closeable" @close="$emit('close')">
    <div class="relative px-6 py-4">
      <h3 class="text-lg font-bold">
        {{ t('Application Alerts') }}
      </h3>
      <button
        @click="$emit('close')"
        class="fixed top-2 right-2 flex items-center justify-center h-8 w-8 rounded-full hover:text-gray-800 hover:bg-gray-300 focus:outline-hidden"
      >
        <icons name="cross" class="h-5 w-5" />
      </button>

      <div class="mt-4">
        <section class="py-2 mx-auto max-w-7xl">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <a href="#/" class="block p-4 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-gray-900">
              <span class="mb-2 text-xl font-semibold leading-none text-gray-900 dark:text-gray-100 truncate">6</span>
              <p class="text-sm leading-none">{{ t('Items reached alert quantity') }}</p>
            </a>
            <a href="#/" class="p-4 border dark:border-gray-700 rounded-md hover:bg-gray-100 dark:hover:bg-gray-900">
              <span class="mb-2 text-xl font-semibold leading-none text-gray-900 dark:text-gray-100 truncate">4</span>
              <p class="text-sm leading-none">{{ t('Items need to be reorder') }}</p>
            </a>
          </div>
        </section>
      </div>
    </div>
  </Modal>
</template>
