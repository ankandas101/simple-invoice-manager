<script setup>
import { route } from 'ziggy-js';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

import Button from '@/Shared/Button.vue';
import Modal from '@/Jetstream/Modal.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Shared/Pagination.vue';
import RowActions from '@/Shared/RowActions.vue';
import SelectInput from '@/Shared/SelectInput.vue';
import UserForm from '@/Pages/Customer/UserForm.vue';
import SearchFilter from '@/Shared/SearchFilter.vue';
import DialogModal from '@/Jetstream/DialogModal.vue';
import DangerButton from '@/Jetstream/DangerButton.vue';
import SecondaryButton from '@/Jetstream/SecondaryButton.vue';

const { t } = useI18n({});

const userModal = ref(false);
const list_users = ref(false);
const bulkDelete = ref(false);
const selectedUser = ref(null);
const selectedCustomer = ref(null);
const props = defineProps(['customers']);
const form = useForm({ selection: [], force: false });
const search = useForm({ search: '', trashed: null });
const indeterminate = computed(() => form.selection.length > 0 && form.selection.length < props.customers.data.length);

onMounted(() => {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  search.trashed = urlParams.has('trashed') ? urlParams.get('trashed') : null;
});

const deleteRow = (id, force = false) => {
  router.delete(route('customers.destroy' + (force ? '.permanently' : ''), id));
};

const restoreRow = id => {
  router.put(route('customers.restore', id));
};

const closeModal = () => {
  bulkDelete.value = false;
};

const addUser = selected => {
  selectedCustomer.value = selected;
  userModal.value = true;
};
const editUser = selected => {
  selectedUser.value = selected;
  list_users.value = false;
  userModal.value = true;
};
const viewUsers = selected => {
  selectedCustomer.value = selected;
  list_users.value = true;
};
const updatedUser = selected => {
  router.reload({ only: ['customers'] });
  userModal.value = false;
};
const closeUserModal = () => {
  userModal.value = false;
  selectedUser.value = null;
  selectedCustomer.value = null;
};

const confirmDelete = force => {
  form.force = force || false;
  bulkDelete.value = true;
};

const deleteSelected = force => {
  form.delete(route('customers.destroy.many'), {
    onSuccess: () => {
      form.reset();
    },
    onFinish: () => {
      bulkDelete.value = false;
    },
  });
};

const searchNow = () => {
  search
    .transform(data => {
      let obj = {
        ...data,
        remember: data.remember ? 'on' : '',
      };
      return Object.entries(obj).reduce((a, [k, v]) => (v ? ((a[k] = v), a) : a), {});
    })
    .get(route('customers.index'), { preserveState: true });
};

const resetSearch = () => {
  search.search = '';
  search.trashed = null;
  searchNow();
};
</script>

<template>
  <AppLayout :title="t('List Customers')">
    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-900 shadow-sm sm:rounded-lg">
          <div class="sm:rounded-lg">
            <div class="p-6 sm:rounded-t-lg border-b border-gray-200 dark:border-gray-700 block md:flex md:items-center md:justify-between">
              <div>
                <div class="text-2xl">
                  {{ t('Customers') }}
                  <span class="text-base">
                    {{ { with: '(' + t('With Trashed') + ')', only: '(' + t('Only Trashed') + ')' }[search.trashed] }}
                  </span>
                </div>
                <div class="mt-1 text-gray-500 dark:text-gray-400">{{ t('Please review the result below') }}</div>
              </div>
              <div class="mt-4 sm:mt-0 block sm:flex sm:items-center sm:gap-4">
                <Button v-if="$can('create-customers')" :href="route('customers.create')">{{ t('Add New Customer') }}</Button>
                <form @submit.prevent="searchNow">
                  <SearchFilter @reset="resetSearch()" v-model="search.search">
                    <label class="block">{{ t('Trashed') }}:</label>
                    <SelectInput v-model="search.trashed" @change="searchNow()" class="mt-1 w-full">
                      <option :value="null">{{ t('Not Trashed') }}</option>
                      <option value="with">{{ t('With Trashed') }}</option>
                      <option value="only">{{ t('Only Trashed') }}</option>
                    </SelectInput>
                  </SearchFilter>
                </form>
              </div>
            </div>

            <div class="bg-gray-50 dark:bg-black/50 border-b dark:border-gray-700 sm:rounded-b-lg">
              <div v-if="customers && customers.data && customers.data.length">
                <div class="flex flex-col">
                  <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                      <div class="relative overflow-hidden ring-1 ring-black/5">
                        <div
                          v-if="form.selection && form.selection.length"
                          class="absolute top-0 left-12 flex h-12 items-center space-x-3 sm:left-16"
                        >
                          <button
                            type="button"
                            @click="confirmDelete()"
                            class="inline-flex items-center rounded-sm border text-white border-red-500 bg-red-500 px-2.5 py-1.5 text-xs font-medium shadow-xs hover:bg-red-600 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-30"
                          >
                            {{ t('Move to Trash') }}
                          </button>
                          <button
                            type="button"
                            @click="confirmDelete(true)"
                            class="inline-flex items-center rounded-sm border text-white border-red-500 bg-red-500 px-2.5 py-1.5 text-xs font-medium shadow-xs hover:bg-red-600 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-30"
                          >
                            {{ t('Delete Permanently') }}
                          </button>

                          <DialogModal :show="bulkDelete" @close="closeModal" maxWidth="sm">
                            <template #title> {{ t('Delete bulk records') }} </template>
                            <template #content>
                              <p>{{ t('Please confirm that you would like to delete the records?') }}</p>
                              <p v-if="form.force" class="mt-2 text-red-500">{{ t('This action will delete the data permanently.') }}</p>
                            </template>
                            <template #footer>
                              <SecondaryButton @click="closeModal"> {{ t('Cancel') }} </SecondaryButton>
                              <DangerButton @click="deleteSelected()"> {{ t('Yes, delete') }} </DangerButton>
                            </template>
                          </DialogModal>
                        </div>

                        <table class="min-w-full table-fixed divide-y divide-gray-300 dark:divide-gray-700">
                          <thead class="bg-gray-50 dark:bg-black/50">
                            <tr>
                              <th scope="col" class="relative w-12 px-6 sm:w-16 sm:px-8">
                                <input
                                  type="checkbox"
                                  :indeterminate="indeterminate"
                                  :checked="indeterminate || form.selection.length === customers.data.length"
                                  @change="form.selection = $event.target.checked ? customers.data.map(p => p.id) : []"
                                  class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded-sm border-gray-300 text-blue-600 focus:ring-blue-500 sm:left-6 dark:bg-gray-700 dark:border-gray-700"
                                />
                              </th>
                              <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ t('Customer') }}
                              </th>
                              <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ t('Contact') }}
                              </th>
                              <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ t('Balance') }}
                              </th>
                              <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ t('Address') }}
                              </th>
                              <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ t('Custom Fields') }}
                              </th>
                              <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">{{ t('Actions') }}</span>
                              </th>
                            </tr>
                          </thead>
                          <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                            <tr
                              :key="customer.id"
                              v-for="(customer, ci) in customers.data"
                              :class="[
                                form.selection.includes(customer.id) && 'bg-gray-50 dark:bg-gray-800/50',
                                customer.deleted_at && 'bg-red-100 dark:bg-red-900',
                              ]"
                            >
                              <td class="relative w-12 px-6 sm:w-16 sm:px-8">
                                <div v-if="form.selection.includes(customer.id)" class="absolute inset-y-0 left-0 w-0.5 bg-blue-600"></div>
                                <input
                                  type="checkbox"
                                  :value="customer.id"
                                  v-model="form.selection"
                                  class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded-sm border-gray-300 text-blue-600 focus:ring-blue-500 sm:left-6 dark:bg-gray-700 dark:border-gray-700"
                                />
                              </td>
                              <td class="whitespace-nowrap px-3 py-4 text-sm font-medium">
                                <div>{{ customer.name }}</div>
                                <div v-if="customer.company">{{ customer.company }}</div>
                              </td>
                              <td class="whitespace-nowrap px-3 py-4 text-sm font-medium">
                                <div v-if="customer.phone">{{ customer.phone }}</div>
                                <div v-if="customer.email">{{ customer.email }}</div>
                              </td>
                              <td class="whitespace-nowrap px-3 py-4 text-sm font-bold text-right">
                                <Link :href="route('customer.transactions', { customer: customer.id })" :class="'balance-' + ci">
                                  {{ $currency(customer.balance) }}
                                </Link>
                              </td>
                              <td class="px-3 py-4 text-sm min-w-[200px] max-w-xs">
                                {{ customer.address || '' }} {{ customer.city || '' }} {{ customer.postal_code || '' }}
                                {{ customer.state || '' }} {{ customer.country || '' }}
                              </td>
                              <td class="px-3 py-4 text-sm">
                                <div
                                  class="flex items-center flex-wrap gap-x-4 gap-y-2 min-w-[200px] max-w-xs"
                                  v-html="$extra_attributes(customer.extra_attributes)"
                                ></div>
                              </td>
                              <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 w-20">
                                <div class="flex flex-col items-center justify-end gap-3">
                                  <div class="flex items-center justify-end gap-3">
                                    <button
                                      type="button"
                                      @click="addUser(customer)"
                                      class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                                    >
                                      <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                        class="w-6 h-6"
                                      >
                                        <path
                                          stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"
                                        />
                                      </svg>
                                    </button>
                                    <button
                                      type="button"
                                      @click="viewUsers(customer)"
                                      class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                                    >
                                      <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                        class="w-6 h-6"
                                      >
                                        <path
                                          stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"
                                        />
                                      </svg>
                                    </button>
                                  </div>
                                  <row-actions
                                    pm="customers"
                                    property="name"
                                    :row="customer"
                                    :deleteFn="deleteRow"
                                    :restoreFn="restoreRow"
                                    :editStr="route('customers.edit', customer.id)"
                                  />
                                </div>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <pagination class="m-4" :meta="customers.meta" :links="customers.links" />
                </div>
              </div>
              <div v-else class="py-4 px-6">{{ t('There is no data to display.') }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Modal
      :show="list_users"
      :closeable="true"
      @close="() => (list_users = false)"
      :max-width="selectedCustomer && selectedCustomer.users && selectedCustomer.users.length ? '2xl' : 'xl'"
    >
      <div class="p-4 flex items-start justify-between print:hidden">
        <div>
          <div class="text-lg">{{ t('Users') }} ({{ selectedCustomer.name }})</div>
          <div>{{ t('Please review the result below') }}</div>
        </div>
        <div class="-mr-2 flex items- gap-2">
          <button
            @click="() => (list_users = false)"
            class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
          >
            <icons name="cross" class="h-5 w-5" />
          </button>
        </div>
      </div>
      <div class="">
        <template v-if="selectedCustomer && selectedCustomer.users && selectedCustomer.users.length">
          <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
            <li v-for="user in selectedCustomer.users" :key="user.id">
              <div class="w-full block text-left hover:bg-gray-50 dark:hover:bg-gray-900">
                <div class="flex items-center px-4 py-3 sm:px-6">
                  <div class="flex min-w-0 flex-1 items-center">
                    <div class="shrink-0">
                      <img class="h-8 w-8 rounded-full" :src="user.profile_photo_url" :alt="user.name" />
                    </div>
                    <div class="min-w-0 flex-1 px-4 md:grid md:grid-cols-2 md:gap-4">
                      <div>
                        <p class="truncate text-sm font-bold text-gray-600 dark:text-gray-400">{{ user.name }}</p>
                        <Link
                          :href="route('impersonate', user.id)"
                          v-if="$page.props.settings?.impersonation == 1 && $page.props.can_impersonate"
                          class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200"
                        >
                          {{ t('Impersonate') }}
                        </Link>
                      </div>
                      <p class="hidden md:flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg
                          class="mr-1.5 h-4 w-4 shrink-0 text-gray-500"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                          aria-hidden="true"
                        >
                          <path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z" />
                          <path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z" />
                        </svg>
                        <span class="truncate">{{ user.email }}</span>
                      </p>
                    </div>
                  </div>
                  <button type="button" @click="editUser(user)">
                    <svg
                      class="h-5 w-5 text-gray-400"
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                      aria-hidden="true"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </button>
                </div>
              </div>
            </li>
          </ul>
        </template>
        <div v-else class="p-4 text-lg font-light">{{ t('Customer do not have any user yet.') }}</div>
      </div>
    </Modal>

    <Modal :show="userModal" :closeable="true" @close="closeUserModal">
      <UserForm v-if="userModal" @done="updatedUser" :edit="selectedUser" :customer="selectedCustomer" @close="closeUserModal" />
    </Modal>
  </AppLayout>
</template>
