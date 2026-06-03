<script setup>
defineProps({
  type: {
    type: String,
    default: 'submit',
  },
});
</script>

<template>
  <button
    :type="type"
    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-700 focus:outline-hidden focus:border-blue-900 focus:ring-3 focus:ring-blue-300 disabled:opacity-25 transition"
  >
    <slot />
  </button>
</template>
