<template>
  <div>
    <tec-label :value="label" />
    <div class="mt-1">
      <slot />
    </div>
    <tec-input-error v-if="error" :message="error" class="mt-2" />
  </div>
</template>

<script>
import TecLabel from '@/Jetstream/Label';
import TecInputError from '@/Jetstream/InputError';

export default {
  emits: ['update:modelValue'],
  components: { TecLabel, TecInputError },
  props: {
    modelValue: [String, Number, Boolean],
    label: String,
    error: String,
  },
};
</script>
