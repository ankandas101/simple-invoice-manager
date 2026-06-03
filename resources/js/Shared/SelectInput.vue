<template>
  <div>
    <button class="float-right text-blue-500 hover:text-blue-700" type="button" v-if="actionText && action" @click="action">
      {{ actionText }}
    </button>
    <tec-label :for="id" :value="label" />
    <select
      :id="id"
      ref="input"
      v-bind="$attrs"
      v-model="selected"
      :class="{ 'border-red-500': error }"
      class="w-full mt-1 border-gray-300 dark:border-gray-700 focus:border-blue-300 focus:ring-3 focus:ring-blue-200/50 rounded-md shadow-xs dark:bg-gray-800"
    >
      <slot />
    </select>
    <!-- <div class="relative mt-1">
      <select
        class="appearance-none w-full border-gray-300 focus:border-blue-300 focus:ring-3 focus:ring-blue-200/50 rounded-md shadow-xs"
      >
        <slot />
      </select>
      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
        <icons name="chevron-down" />
      </div>
    </div> -->
    <tec-input-error v-if="error" :message="error" class="mt-2" />
  </div>
</template>

<script>
import { v4 as uuidv4 } from 'uuid';
import TecLabel from '@/Jetstream/Label.vue';
import TecInputError from '@/Jetstream/InputError.vue';

export default {
  emits: ['update:modelValue'],
  components: { TecLabel, TecInputError },
  props: {
    id: {
      type: String,
      default() {
        return uuidv4();
      },
    },
    modelValue: [String, Number, Boolean],
    label: String,
    error: String,
    actionText: String,
    action: Function,
  },
  data() {
    return {
      selected: this.modelValue,
    };
  },
  watch: {
    selected(selected) {
      this.$emit('update:modelValue', selected);
    },
    modelValue(v) {
      this.selected = this.modelValue;
    },
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    },
    select() {
      this.$refs.input.select();
    },
  },
};
</script>
