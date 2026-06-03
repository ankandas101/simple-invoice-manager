<template>
  <div>
    <div class="hidden lg:flex items-center justify-between">
      <div class="mr-4">
        {{
          t('Showing from {from} to {to} of total {total} records', { from: props.meta.from, to: props.meta.to, total: props.meta.total })
        }}
      </div>

      <div class="flex flex-wrap -mb-1">
        <template v-for="(link, i) in props.meta.links" :key="'link_' + i">
          <template v-if="link.url === null">
            <div
              class="mr-1 mb-1 px-4 py-2 text-sm leading-4 text-gray-400 dark:text-gray-600 shadow-sm opacity-50 bg-white dark:bg-gray-800 rounded-md cursor-default"
              v-html="link.label"
            />
          </template>
          <template v-else>
            <Link
              :href="link.url"
              v-html="link.label"
              :class="link.active ? 'bg-blue-600 border-blue-700 text-white' : 'bg-white dark:bg-gray-800'"
              class="inline-block whitespace-nowrap mr-1 mb-1 px-4 py-2 text-sm leading-4 shadow-sm rounded-md hover:bg-blue-700 hover:text-white hover:border-blue-700 focus:border-blue-600 focus:text-white"
            ></Link>
          </template>
        </template>
      </div>
    </div>
    <div class="block sm:flex items-center justify-between lg:hidden">
      <div class="mx-3 flex-1 text-center sm:text-left">
        {{
          t('Showing from {from} to {to} of total {total} records', { from: props.meta.from, to: props.meta.to, total: props.meta.total })
        }}
      </div>
      <div class="flex items-center justify-between gap-3 mt-3 sm:mt-0">
        <Link
          v-if="props.links.prev"
          :href="props.links.prev"
          class="inline-block whitespace-nowrap mr-1 mb-1 px-4 py-2 text-sm leading-4 shadow-sm rounded-md bg-white dark:bg-gray-800 hover:bg-blue-700 hover:text-white hover:border-blue-700 focus:border-blue-600 focus:text-white"
        >
          &laquo; {{ t('Previous') }}
        </Link>
        <span
          v-else
          class="inline-block whitespace-nowrap mr-1 mb-1 px-4 py-2 text-sm leading-4 shadow-sm rounded-md text-gray-600 bg-gray-100 dark:bg-gray-800 cursor-default"
        >
          &laquo; {{ t('Previous') }}
        </span>
        <Link
          v-if="props.links.next"
          :href="props.links.next"
          class="inline-block whitespace-nowrap mr-1 mb-1 px-4 py-2 text-sm leading-4 shadow-sm rounded-md bg-white dark:bg-gray-800 hover:bg-blue-700 hover:text-white hover:border-blue-700 focus:border-blue-600 focus:text-white"
        >
          {{ t('Next') }} &raquo;
        </Link>
        <span
          v-else
          class="inline-block whitespace-nowrap mr-1 mb-1 px-4 py-2 text-sm leading-4 shadow-sm rounded-md text-gray-600 bg-gray-100 dark:bg-gray-800 cursor-default"
        >
          {{ t('Next') }} &raquo;
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';

const props = defineProps(['meta', 'links']);
const { t } = useI18n({});
</script>
