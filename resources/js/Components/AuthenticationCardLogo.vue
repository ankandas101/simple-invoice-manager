<script setup>
import Mark from './ApplicationMark.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <Link :href="'/'" class="h-20 w-80 block">
    <Mark />
  </Link>
</template>
