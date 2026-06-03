<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  href: String,
  active: Boolean,
});

const classes = computed(() => {
  return props.active
    ? 'inline-flex items-center px-1 border-b border-blue-400 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-hidden focus:border-blue-700 transition'
    : 'inline-flex items-center px-1 border-b border-transparent text-sm font-medium leading-5 text-gray-700 hover:text-gray-900 dark:hover:text-gray-100 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-500 focus:outline-hidden focus:text-gray-700 focus:border-gray-300 dark:focus:border-gray-500 transition';
});
</script>

<template>
  <Link :href="href" :class="classes">
    <slot />
  </Link>
</template>
