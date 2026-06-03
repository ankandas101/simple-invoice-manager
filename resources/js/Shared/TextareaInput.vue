<template>
  <div class="col-span-6 sm:col-span-4">
    <Label :for="id" :value="label" />
    <!-- <span class="textarea" role="textbox" contenteditable></span> -->
    <textarea
      :id="id"
      ref="input"
      :type="type"
      @keyup="resize"
      v-model="textValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :class="error ? 'border-red-500' : 'border-gray-300'"
      class="mt-1 block w-full rounded-md shadow-xs border dark:border-gray-700 focus:border-blue-300 focus:ring-2 focus:ring-blue-100/50 dark:bg-gray-800 dark:focus:border-gray-600 placeholder:text-gray-400 dark:placeholder:text-gray-500"
    />
    <InputError v-if="error" :message="error" class="mt-1" />
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { v4 as uuidv4 } from 'uuid';
import Label from '@/Jetstream/Label.vue';
import Input from '@/Jetstream/Input.vue';
import InputError from '@/Jetstream/InputError.vue';

const input = ref(null);
const textValue = ref('');
defineEmits(['update:modelValue']);
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
});

onMounted(() => {
  textValue.value = props.modelValue;
  setTimeout(() => resize(textValue.value), 250);
});

const calcHeight = value => {
  let numberOfLineBreaks = (value?.match(/\n/g) || []).length;
  let newHeight = 70 + numberOfLineBreaks * 23;
  return newHeight;
};

const resize = () => {
  input.value.style.height = calcHeight(textValue.value) + 'px';
};

const focus = () => {
  input.value.focus();
};

const select = () => {
  input.value.select();
};

const setSelectionRange = (start, end) => {
  input.value.setSelectionRange(start, end);
};
</script>
