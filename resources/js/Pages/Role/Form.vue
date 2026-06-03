<script setup>
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

import Button from '@/Shared/Button.vue';
import CheckBox from '@/Shared/CheckBox.vue';
import TextInput from '@/Shared/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormSection from '@/Jetstream/FormSection.vue';
import ActionMessage from '@/Jetstream/ActionMessage.vue';

const form = useForm({ name: null, permissions: [] });

const props = defineProps(['edit', 'roles']);
if (props.edit) {
  form.name = props.edit.data.name;
  form.permissions = props.edit.data.permissions || [];
}
const { t } = useI18n({});

const updatePermissions = v => {
  if (form.permissions.includes(v)) {
    form.permissions = form.permissions.filter(r => r != v);
  } else {
    form.permissions = [...form.permissions, v];
  }
};

function submit() {
  let method = props.edit ? 'put' : 'post';
  let url = props.edit ? route('roles.update', props.edit.data.id) : route('roles.store');
  form[method](url, {
    preserveScroll: true,
    onSuccess: () => {
      props.edit ? '' : form.reset();
    },
    onError: () => {
      if (form.errors.name) {
        document.getElementById('name').focus();
      }
    },
  });
}
</script>

<template>
  <AppLayout :title="props.edit ? t('Edit Role') : t('Add New Role')">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
      <FormSection @submitted="submit" autocomplete="off">
        <template #title>{{ props.edit ? t('Edit Role') : t('Add New Role') }}</template>
        <template #description>
          {{
            t('Please fill the form below to {x}.', {
              x: props.edit ? t('update {x}', { x: t('role') }) : t('add {x}', { x: t('role') }),
            })
          }}
          <div>
            <Button :href="route('roles.index')" class="mt-4">{{ t('List Roles') }}</Button>
          </div>
        </template>

        <template #form>
          <div class="col-span-full">
            <TextInput v-model="form.name" id="name" :error="form.errors.name" :label="t('Name')" />
          </div>

          <template v-if="edit">
            <div class="col-span-full">
              <div class="mt-6">
                <label class="block w-full font-bold mb-2">{{ t('Invoices') }}</label>
                <div class="flex flex-wrap sm:flex-row items-start gap-x-6 gap-y-2 sm:gap-y-6">
                  <check-box
                    id="read-invoices"
                    value="read-invoices"
                    @input="updatePermissions('read-invoices')"
                    :label="t('View {x}', { x: t('Invoices') })"
                    :checked="form.permissions.includes('read-invoices')"
                  />

                  <check-box
                    id="create-invoices"
                    value="create-invoices"
                    @input="updatePermissions('create-invoices')"
                    :label="t('Create {x}', { x: t('Invoices') })"
                    :checked="form.permissions.includes('create-invoices')"
                  />

                  <check-box
                    id="update-invoices"
                    value="update-invoices"
                    @input="updatePermissions('update-invoices')"
                    :label="t('Update {x}', { x: t('Invoices') })"
                    :checked="form.permissions.includes('update-invoices')"
                  />

                  <check-box
                    id="delete-invoices"
                    value="delete-invoices"
                    @input="updatePermissions('delete-invoices')"
                    :label="t('Delete {x}', { x: t('Invoices') })"
                    :checked="form.permissions.includes('delete-invoices')"
                  />
                </div>
              </div>

              <div class="mt-6">
                <label class="block w-full font-bold mb-2">{{ t('Payments') }}</label>
                <div class="flex flex-wrap sm:flex-row items-start gap-x-6 gap-y-2 sm:gap-y-6">
                  <check-box
                    id="read-payments"
                    value="read-payments"
                    @input="updatePermissions('read-payments')"
                    :label="t('View {x}', { x: t('Payments') })"
                    :checked="form.permissions.includes('read-payments')"
                  />

                  <check-box
                    id="create-payments"
                    value="create-payments"
                    @input="updatePermissions('create-payments')"
                    :label="t('Create {x}', { x: t('Payments') })"
                    :checked="form.permissions.includes('create-payments')"
                  />

                  <check-box
                    id="update-payments"
                    value="update-payments"
                    @input="updatePermissions('update-payments')"
                    :label="t('Update {x}', { x: t('Payments') })"
                    :checked="form.permissions.includes('update-payments')"
                  />

                  <check-box
                    id="delete-payments"
                    value="delete-payments"
                    @input="updatePermissions('delete-payments')"
                    :label="t('Delete {x}', { x: t('Payments') })"
                    :checked="form.permissions.includes('delete-payments')"
                  />
                </div>
              </div>

              <div class="my-6">
                <label class="block w-full font-bold mb-2">{{ t('Quotations') }}</label>
                <div class="flex flex-wrap sm:flex-row items-start gap-x-6 gap-y-2 sm:gap-y-6">
                  <check-box
                    id="read-quotations"
                    value="read-quotations"
                    @input="updatePermissions('read-quotations')"
                    :label="t('View {x}', { x: t('Quotations') })"
                    :checked="form.permissions.includes('read-quotations')"
                  />

                  <check-box
                    id="create-quotations"
                    value="create-quotations"
                    @input="updatePermissions('create-quotations')"
                    :label="t('Create {x}', { x: t('Quotations') })"
                    :checked="form.permissions.includes('create-quotations')"
                  />

                  <check-box
                    id="update-quotations"
                    value="update-quotations"
                    @input="updatePermissions('update-quotations')"
                    :label="t('Update {x}', { x: t('Quotations') })"
                    :checked="form.permissions.includes('update-quotations')"
                  />

                  <check-box
                    id="delete-quotations"
                    value="delete-quotations"
                    @input="updatePermissions('delete-quotations')"
                    :label="t('Delete {x}', { x: t('Quotations') })"
                    :checked="form.permissions.includes('delete-quotations')"
                  />
                </div>
              </div>
              <div class="my-6">
                <label class="block w-full font-bold mb-2">{{ t('Products') }}</label>
                <div class="flex flex-wrap sm:flex-row items-start gap-x-6 gap-y-2 sm:gap-y-6">
                  <check-box
                    id="read-products"
                    value="read-products"
                    @input="updatePermissions('read-products')"
                    :label="t('View {x}', { x: t('Products') })"
                    :checked="form.permissions.includes('read-products')"
                  />

                  <check-box
                    id="create-products"
                    value="create-products"
                    @input="updatePermissions('create-products')"
                    :label="t('Create {x}', { x: t('Products') })"
                    :checked="form.permissions.includes('create-products')"
                  />

                  <check-box
                    id="update-products"
                    value="update-products"
                    @input="updatePermissions('update-products')"
                    :label="t('Update {x}', { x: t('Products') })"
                    :checked="form.permissions.includes('update-products')"
                  />

                  <check-box
                    id="import-products"
                    value="import-products"
                    @input="updatePermissions('import-products')"
                    :label="t('Import {x}', { x: t('Products') })"
                    :checked="form.permissions.includes('import-products')"
                  />

                  <check-box
                    id="delete-products"
                    value="delete-products"
                    @input="updatePermissions('delete-products')"
                    :label="t('Delete {x}', { x: t('Products') })"
                    :checked="form.permissions.includes('delete-products')"
                  />
                </div>
              </div>
              <div class="my-6">
                <label class="block w-full font-bold mb-2">{{ t('Customers') }}</label>
                <div class="flex flex-wrap sm:flex-row items-start gap-x-6 gap-y-2 sm:gap-y-6">
                  <check-box
                    id="read-customers"
                    value="read-customers"
                    @input="updatePermissions('read-customers')"
                    :label="t('View {x}', { x: t('Customers') })"
                    :checked="form.permissions.includes('read-customers')"
                  />

                  <check-box
                    id="create-customers"
                    value="create-customers"
                    @input="updatePermissions('create-customers')"
                    :label="t('Create {x}', { x: t('Customers') })"
                    :checked="form.permissions.includes('create-customers')"
                  />

                  <check-box
                    id="update-customers"
                    value="update-customers"
                    @input="updatePermissions('update-customers')"
                    :label="t('Update {x}', { x: t('Customers') })"
                    :checked="form.permissions.includes('update-customers')"
                  />

                  <check-box
                    id="import-customers"
                    value="import-customers"
                    @input="updatePermissions('import-customers')"
                    :label="t('Import {x}', { x: t('Customers') })"
                    :checked="form.permissions.includes('import-customers')"
                  />

                  <check-box
                    id="delete-customers"
                    value="delete-customers"
                    @input="updatePermissions('delete-customers')"
                    :label="t('Delete {x}', { x: t('Customers') })"
                    :checked="form.permissions.includes('delete-customers')"
                  />
                </div>
              </div>
              <div class="my-6">
                <label class="block w-full font-bold mb-2">{{ t('Companies') }}</label>
                <div class="flex flex-wrap sm:flex-row items-start gap-x-6 gap-y-2 sm:gap-y-6">
                  <check-box
                    id="read-companies"
                    value="read-companies"
                    @input="updatePermissions('read-companies')"
                    :label="t('View {x}', { x: t('Companies') })"
                    :checked="form.permissions.includes('read-companies')"
                  />

                  <check-box
                    id="create-companies"
                    value="create-companies"
                    @input="updatePermissions('create-companies')"
                    :label="t('Create {x}', { x: t('Companies') })"
                    :checked="form.permissions.includes('create-companies')"
                  />

                  <check-box
                    id="update-companies"
                    value="update-companies"
                    @input="updatePermissions('update-companies')"
                    :label="t('Update {x}', { x: t('Companies') })"
                    :checked="form.permissions.includes('update-companies')"
                  />

                  <check-box
                    id="delete-companies"
                    value="delete-companies"
                    @input="updatePermissions('delete-companies')"
                    :label="t('Delete {x}', { x: t('Companies') })"
                    :checked="form.permissions.includes('delete-companies')"
                  />
                </div>
              </div>
              <div class="my-6">
                <label class="block w-full font-bold mb-2">{{ t('Tax Rates') }}</label>
                <div class="flex flex-wrap sm:flex-row items-start gap-x-6 gap-y-2 sm:gap-y-6">
                  <check-box
                    id="read-tax-rates"
                    value="read-tax-rates"
                    @input="updatePermissions('read-tax-rates')"
                    :label="t('View {x}', { x: t('Tax Rates') })"
                    :checked="form.permissions.includes('read-tax-rates')"
                  />

                  <check-box
                    id="create-tax-rates"
                    value="create-tax-rates"
                    @input="updatePermissions('create-tax-rates')"
                    :label="t('Create {x}', { x: t('Tax Rates') })"
                    :checked="form.permissions.includes('create-tax-rates')"
                  />

                  <check-box
                    id="update-tax-rates"
                    value="update-tax-rates"
                    @input="updatePermissions('update-tax-rates')"
                    :label="t('Update {x}', { x: t('Tax Rates') })"
                    :checked="form.permissions.includes('update-tax-rates')"
                  />

                  <check-box
                    id="delete-tax-rates"
                    value="delete-tax-rates"
                    @input="updatePermissions('delete-tax-rates')"
                    :label="t('Delete {x}', { x: t('Tax Rates') })"
                    :checked="form.permissions.includes('delete-tax-rates')"
                  />
                </div>
              </div>
              <div class="my-6">
                <label class="block w-full font-bold mb-2">{{ t('Notes') }}</label>
                <div class="flex flex-wrap sm:flex-row items-start gap-x-6 gap-y-2 sm:gap-y-6">
                  <check-box
                    id="read-notes"
                    value="read-notes"
                    @input="updatePermissions('read-notes')"
                    :label="t('View {x}', { x: t('Notes') })"
                    :checked="form.permissions.includes('read-notes')"
                  />

                  <check-box
                    id="create-notes"
                    value="create-notes"
                    @input="updatePermissions('create-notes')"
                    :label="t('Create {x}', { x: t('Notes') })"
                    :checked="form.permissions.includes('create-notes')"
                  />

                  <check-box
                    id="update-notes"
                    value="update-notes"
                    @input="updatePermissions('update-notes')"
                    :label="t('Update {x}', { x: t('Notes') })"
                    :checked="form.permissions.includes('update-notes')"
                  />

                  <check-box
                    id="delete-notes"
                    value="delete-notes"
                    @input="updatePermissions('delete-notes')"
                    :label="t('Delete {x}', { x: t('Notes') })"
                    :checked="form.permissions.includes('delete-notes')"
                  />
                </div>
              </div>
              <div class="my-6">
                <label class="block w-full font-bold mb-2">{{ t('Custom Fields') }}</label>
                <div class="flex flex-wrap sm:flex-row items-start gap-x-6 gap-y-2 sm:gap-y-6">
                  <check-box
                    id="read-fields"
                    value="read-fields"
                    @input="updatePermissions('read-fields')"
                    :label="t('View {x}', { x: t('Custom Fields') })"
                    :checked="form.permissions.includes('read-fields')"
                  />

                  <check-box
                    id="create-fields"
                    value="create-fields"
                    @input="updatePermissions('create-fields')"
                    :label="t('Create {x}', { x: t('Custom Fields') })"
                    :checked="form.permissions.includes('create-fields')"
                  />

                  <check-box
                    id="update-fields"
                    value="update-fields"
                    @input="updatePermissions('update-fields')"
                    :label="t('Update {x}', { x: t('Custom Fields') })"
                    :checked="form.permissions.includes('update-fields')"
                  />

                  <check-box
                    id="delete-fields"
                    value="delete-fields"
                    @input="updatePermissions('delete-fields')"
                    :label="t('Delete {x}', { x: t('Custom Fields') })"
                    :checked="form.permissions.includes('delete-fields')"
                  />
                </div>
              </div>
              <div class="my-6">
                <label class="block w-full font-bold mb-2">{{ t('Users') }}</label>
                <div class="flex flex-wrap sm:flex-row items-start gap-x-6 gap-y-2 sm:gap-y-6">
                  <check-box
                    id="read-users"
                    value="read-users"
                    @input="updatePermissions('read-users')"
                    :label="t('View {x}', { x: t('Users') })"
                    :checked="form.permissions.includes('read-users')"
                  />

                  <check-box
                    id="create-users"
                    value="create-users"
                    @input="updatePermissions('create-users')"
                    :label="t('Create {x}', { x: t('Users') })"
                    :checked="form.permissions.includes('create-users')"
                  />

                  <check-box
                    id="update-users"
                    value="update-users"
                    @input="updatePermissions('update-users')"
                    :label="t('Update {x}', { x: t('Users') })"
                    :checked="form.permissions.includes('update-users')"
                  />

                  <check-box
                    id="delete-users"
                    value="delete-users"
                    @input="updatePermissions('delete-users')"
                    :label="t('Delete {x}', { x: t('Users') })"
                    :checked="form.permissions.includes('delete-users')"
                  />
                </div>
              </div>
              <div class="my-6">
                <label class="block w-full font-bold mb-2">{{ t('Roles') }}</label>
                <div class="flex flex-wrap sm:flex-row items-start gap-x-6 gap-y-2 sm:gap-y-6">
                  <check-box
                    id="read-roles"
                    value="read-roles"
                    @input="updatePermissions('read-roles')"
                    :label="t('View {x}', { x: t('Roles') })"
                    :checked="form.permissions.includes('read-roles')"
                  />

                  <check-box
                    id="create-roles"
                    value="create-roles"
                    @input="updatePermissions('create-roles')"
                    :label="t('Create {x}', { x: t('Roles') })"
                    :checked="form.permissions.includes('create-roles')"
                  />

                  <check-box
                    id="update-roles"
                    value="update-roles"
                    @input="updatePermissions('update-roles')"
                    :label="t('Update {x}', { x: t('Roles') })"
                    :checked="form.permissions.includes('update-roles')"
                  />

                  <check-box
                    id="delete-roles"
                    value="delete-roles"
                    @input="updatePermissions('delete-roles')"
                    :label="t('Delete {x}', { x: t('Roles') })"
                    :checked="form.permissions.includes('delete-roles')"
                  />
                </div>
              </div>
              <div class="mt-6">
                <label class="block w-full font-bold mb-2">{{ t('Attachments') }}</label>
                <div class="flex flex-wrap sm:flex-row items-start gap-x-6 gap-y-2 sm:gap-y-6">
                  <check-box
                    id="delete-attachments"
                    value="delete-attachments"
                    @input="updatePermissions('delete-attachments')"
                    :label="t('Delete {x}', { x: t('Attachments') })"
                    :checked="form.permissions.includes('delete-attachments')"
                  />
                </div>
              </div>
              <div class="mt-6">
                <label class="block w-full font-bold mb-2">{{ t('Activity Logs') }}</label>
                <div class="flex flex-wrap sm:flex-row items-start gap-x-6 gap-y-2 sm:gap-y-6">
                  <check-box
                    id="read-activity"
                    value="read-activity"
                    @input="updatePermissions('read-activity')"
                    :label="t('View {x}', { x: t('Activity Logs') })"
                    :checked="form.permissions.includes('read-activity')"
                  />
                </div>
              </div>
              <div class="mt-6">
                <label class="block w-full font-bold mb-2">{{ t('Settings') }}</label>
                <div class="flex flex-wrap sm:flex-row items-start gap-x-6 gap-y-2 sm:gap-y-6">
                  <check-box
                    id="read-settings"
                    value="read-settings"
                    @input="updatePermissions('read-settings')"
                    :label="t('View {x}', { x: t('Settings') })"
                    :checked="form.permissions.includes('read-settings')"
                  />
                  <check-box
                    id="create-settings"
                    value="create-settings"
                    @input="updatePermissions('create-settings')"
                    :label="t('Update {x}', { x: t('Settings') })"
                    :checked="form.permissions.includes('create-settings')"
                  />
                </div>
              </div>
            </div>
          </template>
        </template>

        <template #actions>
          <ActionMessage :on="form.recentlySuccessful" class="mr-3"> {{ t('Saved.') }} </ActionMessage>
          <Button type="submit" :loading="form.processing"> {{ t('Save') }} </Button>
        </template>
      </FormSection>
    </div>
  </AppLayout>
</template>
