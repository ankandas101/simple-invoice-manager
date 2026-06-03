<template>
  <button
    :type="type"
    :disabled="loading"
    :class="{ 'opacity-25': loading }"
    class="flex items-center px-4 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-700 focus:outline-hidden transition ease-in-out duration-150"
  >
    <slot />
    <svg v-if="loading" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg" class="stroke-current h-4 w-4 ml-2" :class="loadingClass">
      <g fill="none" fill-rule="evenodd">
        <g transform="translate(1 1)" stroke-width="2">
          <circle stroke-opacity=".5" cx="18" cy="18" r="18" />
          <path d="M36 18c0-9.94-8.06-18-18-18">
            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite" />
          </path>
        </g>
      </g>
    </svg>
  </button>
</template>

<script>
export default {
  props: {
    loading: Boolean,
    loadingClass: String,
    type: {
      type: String,
      default: 'submit',
    },
  },
};
</script>
