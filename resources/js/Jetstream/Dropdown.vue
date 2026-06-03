<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
  align: {
    type: String,
    default: 'right',
  },
  width: {
    type: String,
    default: '48',
  },
  contentClasses: {
    type: Array,
    default: () => ['py-1', 'bg-white dark:bg-gray-900'],
  },
  closeOnClick: {
    default: true,
  },
});

let open = ref(false);

const closeOnEscape = e => {
  if (open.value && e.key === 'Escape') {
    open.value = false;
  }
};

const close = () => {
  if (props.closeOnClick) {
    open = false;
  }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const widthClass = computed(() => {
  return {
    40: 'w-40',
    48: 'w-48',
    56: 'w-56',
    64: 'w-64',
    80: 'w-80',
  }[props.width.toString()];
});

const alignmentClasses = computed(() => {
  if (props.align === 'center') {
    return 'origin-top left-1/2 -translate-x-1/2 -translate-y-1';
  }
  if (props.align === 'left') {
    return 'origin-top-left left-0';
  }

  if (props.align === 'right') {
    return 'origin-top-right right-0';
  }

  return 'origin-top';
});
</script>

<template>
  <div class="relative flex items-stretch">
    <div @click="open = !open" class="self-stretch flex items-stretch">
      <slot name="trigger" />
    </div>

    <!-- Full Screen Dropdown Overlay -->
    <div v-show="open" class="fixed inset-0 z-40" @click="open = false" />

    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-show="open"
        class="absolute z-50 top-full mt-2 rounded-md shadow-lg dark:border dark:border-gray-700"
        :class="[widthClass, alignmentClasses]"
        style="display: none"
        @click="close()"
      >
        <div class="rounded-md ring-1 ring-black/5" :class="contentClasses">
          <slot name="content" />
        </div>
      </div>
    </transition>
  </div>
</template>
