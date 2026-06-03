<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from '@/Jetstream/Button.vue';
import DialogModal from '@/Jetstream/DialogModal.vue';
import DangerButton from '@/Jetstream/DangerButton.vue';
import SecondaryButton from '@/Jetstream/SecondaryButton.vue';

const { t } = useI18n({});

const show = ref(false);
const restore = ref(false);

const props = defineProps({
  pm: String,
  editFn: String,
  editStr: String,
  property: String,
  deleteFn: Function,
  restoreFn: Function,
  row: { type: Object, required: true },
  classStr: { type: String, default: '' },
});

const deleteConfirmed = () => {
  props.deleteFn(props.row.id, !!props.row.deleted_at);
  closeModal();
};

const restoreConfirmed = () => {
  props.restoreFn(props.row.id);
  closeModal();
};

const confirmDelete = () => {
  show.value = true;
};

const confirmRestore = () => {
  restore.value = true;
};

const closeModal = () => {
  show.value = false;
};

const closeRestoreModal = () => {
  restore.value = false;
};
</script>

<template>
  <div class="flex items-center justify-end gap-3" :class="classStr">
    <template v-if="!row.deleted_at && $can('update-' + pm)">
      <Link v-if="editStr" :href="editStr" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-300">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"
          />
        </svg>
      </Link>
      <button v-if="editFn" @click="editFn(row)" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-300">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"
          />
        </svg>
      </button>
    </template>

    <template v-if="row.deleted_at && $can('update-' + pm) && restoreFn">
      <button @click="confirmRestore()" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-300">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7m0 0a3 3 0 01-3 3m0 3h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008zm-3 6h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008z"
          />
        </svg>
      </button>

      <DialogModal :show="restore" @close="closeRestoreModal" maxWidth="sm">
        <template #title>
          <span v-if="property"> {{ t('Restore {x}', { x: row[property] }) }}</span>
          <span v-else>{{ t('Confirm record restore') }}</span>
        </template>
        <template #content>
          <p>{{ t('Please confirm that you would like to restore the record?') }}</p>
        </template>
        <template #footer>
          <SecondaryButton @click="closeRestoreModal"> {{ t('Cancel') }} </SecondaryButton>
          <Button @click="restoreConfirmed()"> {{ t('Yes, restore') }} </Button>
        </template>
      </DialogModal>
    </template>

    <template v-if="$can('delete-' + pm) && deleteFn">
      <button @click="confirmDelete()" class="text-red-600 hover:text-red-900 dark:hover:text-red-300">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"
          />
        </svg>
      </button>

      <DialogModal :show="show" @close="closeModal" maxWidth="sm">
        <template #title>
          <span v-if="property"> {{ t('Delete {x}', { x: row[property] }) }}</span>
          <span v-else>{{ t('Confirm record delete') }}</span>
        </template>
        <template #content>
          <p>{{ t('Please confirm that you would like to delete the record?') }}</p>
          <p v-if="row.deleted_at" class="mt-2 text-red-500">{{ t('This action will delete the data permanently.') }}</p>
        </template>
        <template #footer>
          <SecondaryButton @click="closeModal"> {{ t('Cancel') }} </SecondaryButton>
          <DangerButton @click="deleteConfirmed()"> {{ t('Yes, delete') }} </DangerButton>
        </template>
      </DialogModal>
    </template>
  </div>
</template>
