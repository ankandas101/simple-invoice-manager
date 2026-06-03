<template>
  <template v-if="away && href">
    <a
      :href="href"
      class="inline-flex items-center px-4 py-3 bg-blue-600 border-0 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-700 disabled:opacity-25 transition"
    >
      <slot></slot>
    </a>
  </template>
  <template v-else-if="href">
    <Link
      :href="href"
      class="inline-flex items-center px-4 py-3 bg-blue-600 border-0 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-700 disabled:opacity-25 transition"
    >
      <slot></slot>
    </Link>
  </template>
  <template v-else>
    <LoadingButton
      :type="type"
      :loading="loading"
      class="inline-flex items-center px-4 py-3 bg-blue-600 border-0 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-700 disabled:opacity-25 transition"
    >
      <slot></slot>
    </LoadingButton>
  </template>
</template>

<script setup>
import LoadingButton from './LoadingButton.vue';

defineProps({
  loading: Boolean,
  href: {
    type: String,
    default: '',
  },
  away: {
    type: Boolean,
    default: false,
  },
  type: {
    type: String,
    default: 'submit',
  },
});
</script>
