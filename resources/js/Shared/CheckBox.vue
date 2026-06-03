<template>
  <div>
    <label :for="id" class="inline-flex items-start">
      <input
        :id="id"
        :value="value"
        type="checkbox"
        v-model="proxyChecked"
        :class="{ 'border-red-500': error }"
        class="mt-1 rounded-sm border-gray-300 dark:border-gray-700 dark:bg-gray-700 text-blue-600 shadow-xs w-5 h-5 focus:rounded-sm focus:ring-offset-0 focus:ring-blue-200/50"
      />
      <span v-if="label" v-html="label" class="ml-2 mt-0.5 text-gray-600 dark:text-gray-400"></span>
    </label>
  </div>
</template>

<script>
import { v4 as uuidv4 } from 'uuid';
import TecCheckbox from '@/Jetstream/Checkbox.vue';
import TecInputError from '@/Jetstream/InputError.vue';

export default {
  emits: ['update:checked'],
  components: { TecCheckbox, TecInputError },
  props: {
    id: {
      type: String,
      default() {
        return uuidv4();
      },
    },
    checked: {
      type: [Array, Boolean],
      default: false,
    },
    value: {
      default: null,
    },
    label: String,
    error: String,
  },
  computed: {
    proxyChecked: {
      get() {
        return this.checked;
      },
      set(val) {
        this.$emit('update:checked', val);
      },
    },
  },
};
</script>
