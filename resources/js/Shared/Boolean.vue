<script setup>
defineProps(['value']);
</script>

<template>
  <span>
    <Icons name="tick" size="w-4 h-4 text-green-500" v-if="value" />
    <Icons name="cross" size="w-4 h-4 text-red-500" v-else />
  </span>
</template>
