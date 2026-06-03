<template>
  <div class="px-4 py-1 bg-yellow-300 rounded-sm flex items-center justify-between">
    <div class="flex items-center">
      <icons name="trash" class="shrink-0 w-4 h-4 fill-yellow-800 mr-2" />
      <div class="text-sm font-medium text-yellow-800">
        <slot />
      </div>
    </div>
    <button
      tabindex="-1"
      type="button"
      @click="$emit('restore')"
      class="text-sm px-4 py-2 text-yellow-800 rounded-sm border-2 border-transparent hover:border-yellow-800 focus:border-yellow-800"
    >
      {{ $t('Restore') }}
    </button>
  </div>
</template>

<script>
export default {
  emits: ['restore'],
};
</script>
