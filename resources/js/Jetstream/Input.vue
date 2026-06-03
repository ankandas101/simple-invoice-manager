<script setup>
import { onMounted, ref } from 'vue';

defineProps({
  type: String,
  title: String,
  pattern: String,
  modelValue: String,
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
  if (input.value.hasAttribute('autofocus')) {
    input.value.focus();
  }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
  <input
    ref="input"
    :type="type"
    :title="title"
    :pattern="pattern"
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
    class="appearance-none border-gray-300 dark:border-gray-700 focus:border-blue-300 focus:ring-3 focus:ring-blue-200/50 rounded-md shadow-xs dark:bg-gray-800 autofill:bg-white dark:autofill:bg-gray-800"
  />
</template>
