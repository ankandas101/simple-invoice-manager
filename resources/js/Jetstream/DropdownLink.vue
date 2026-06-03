<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  href: String,
  as: String,
});
</script>

<template>
  <div>
    <button
      type="submit"
      v-if="as == 'button'"
      class="block w-full px-4 py-2 text-sm leading-5 text-gray-900 dark:text-gray-100 text-left hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-hidden focus:bg-gray-100 dark:focus:bg-gray-800 transition"
    >
      <slot />
    </button>

    <a
      :href="href"
      v-else-if="as == 'a'"
      class="block px-4 py-2 text-sm leading-5 text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-hidden focus:bg-gray-100 dark:focus:bg-gray-800 transition"
    >
      <slot />
    </a>

    <Link
      v-else
      :href="href"
      class="block px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-hidden focus:bg-gray-100 dark:focus:bg-gray-800 transition"
    >
      <slot />
    </Link>
  </div>
</template>
