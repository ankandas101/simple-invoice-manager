<script setup>
import { onUpdated } from 'vue';
import JsBarcode from 'jsbarcode';

const props = defineProps({
  value: {
    type: String,
    default: undefined,
  },

  options: {
    type: Object,
    default: undefined,
  },

  tag: {
    type: String,
    default: 'canvas',
  },
});

onMounted(generate);
onUpdated(generate);

const generate = () => {
  JsBarcode('#barcode', String(props.value), props.options);
};
</script>

<template>
  <svg id="barcode"></svg>
</template>
