<template>
  <div
    class="mt-4 sm:mt-0 w-full flex items-stretch group bg-white dark:bg-gray-900 border dark:border-gray-700 rounded-md focus-within:border-blue-300 focus-within:ring-2 focus-within:ring-blue-200/50"
  >
    <div class="relative w-full flex items-stretch">
      <div class="flex items-center w-full rounded-md">
        <input
          type="text"
          name="search"
          autocomplete="off"
          :value="modelValue"
          :placeholder="t('Search')"
          :class="{ 'rounded-l-md': !dropdown }"
          @input="$emit('update:modelValue', $event.target.value)"
          class="flex-1 text-sm z-0 w-full pl-3 pr-2 py-2.5 rounded-l-md focus:outline-hidden focus:ring-0 border-0 dark:bg-gray-800 focus:rounded-r-none"
        />
      </div>
      <button
        type="button"
        @click="$emit('reset')"
        class="hidden group-hover:block absolute top-0.5 my-1 right-2 px-2 py-1 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:text-blue-500 rounded-md focus:outline-hidden focus:rounded-none dark:focus:rounded-sm"
      >
        {{ t('Reset') }}
      </button>
    </div>
    <div v-if="dropdown" class="flex items-stretch">
      <Dropdown align="right" width="56" :closeOnClick="close">
        <template #trigger>
          <button
            type="button"
            class="flex py-1 px-1 md:pl-2 md:pr-0 items-center justify-center rounded-r-md focus:outline-hidden relative transition duration-150 ease-in-out bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 border-0 border-l border-gray-200 dark:border-gray-700"
          >
            <span class="hidden md:inline">{{ t('Filter') }}</span>
            <icons name="chevron-down" size="h-4 w-4 text-gray-400 md:mx-1" />
          </button>
        </template>

        <template #content>
          <div class="p-4">
            <slot />
          </div>
        </template>
      </Dropdown>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';
import Dropdown from '@/Jetstream/Dropdown.vue';

const { t } = useI18n({});
defineEmits(['update:modelValue', 'reset']);
defineProps({ modelValue: String, close: { type: Boolean, default: false }, dropdown: { type: Boolean, default: true } });
</script>
