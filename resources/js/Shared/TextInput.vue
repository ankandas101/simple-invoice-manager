<template>
  <div class="col-span-6 sm:col-span-4">
    <div class="flex items-center justify-between">
      <Label :for="id" :value="label" class="flex-1" />
      <button v-if="actionText && action" type="button" @click="action()" class="link text-sm">{{ actionText }}</button>
    </div>
    <Input
      :id="id"
      ref="input"
      :type="type"
      :title="title"
      @focus="focused"
      :pattern="pattern"
      :value="modelValue"
      @blur="$emit('blur-sm')"
      :class="{ error: error }"
      :step="type == 'number' ? '.01' : ''"
      :placeholder="placeholder || label || ''"
      @input="$emit('update:modelValue', $event.target.value)"
      class="mt-1 block w-full placeholder:text-gray-400 dark:placeholder:text-gray-500"
    />
    <InputError v-if="error" :message="error" class="mt-1" />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { v4 as uuidv4 } from 'uuid';
import Label from '@/Jetstream/Label.vue';
import Input from '@/Jetstream/Input.vue';
import InputError from '@/Jetstream/InputError.vue';

const input = ref(null);
const emit = defineEmits(['blur-sm', 'focus', 'update:modelValue']);
const props = defineProps({
  id: {
    type: String,
    default() {
      return uuidv4();
    },
  },
  type: {
    type: String,
    default: 'text',
  },
  modelValue: String,
  label: String,
  error: String,
  pattern: String,
  title: String,
  action: Function,
  actionText: String,
  selectOnFocus: Boolean,
});

const focus = () => {
  input.value.focus();
};
const focused = () => {
  if (props.selectOnFocus) {
    $event.target.select();
  }
  emit('focus');
};
const select = () => {
  input.value.select();
};
const setSelectionRange = (start, end) => {
  input.value.setSelectionRange(start, end);
};
</script>
