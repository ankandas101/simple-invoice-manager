<script setup>
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

import Button from '@/Shared/Button.vue';
import CheckBox from '@/Shared/CheckBox.vue';
import TextInput from '@/Shared/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Jetstream/InputError.vue';
import CustomFields from '@/Shared/CustomFields.vue';
import FormSection from '@/Jetstream/FormSection.vue';
import ActionMessage from '@/Jetstream/ActionMessage.vue';

const form = useForm({
  name: null,
  email: null,
  password: null,
  password_confirmation: null,
  roles: [],
  view_all: null,
  edit_all: null,
  extra_attributes: {},
});

const props = defineProps(['edit', 'roles', 'fields']);
if (props.edit) {
  if (props.edit.data.extra_attributes && props.fields) {
    Object.keys(props.edit.data.extra_attributes)
      .filter(k => !props.fields?.find(f => f.name == k))
      .map(k => delete props.edit.data.extra_attributes[k]);
  }

  form.name = props.edit.data.name;
  form.email = props.edit.data.email;
  form.password = props.edit.data.password;
  form.password_confirmation = props.edit.data.password_confirmation;
  form.roles = props.edit.data.roles.map(r => r.name);
  form.view_all = props.edit.view_all;
  form.edit_all = props.edit.edit_all;
  form.extra_attributes = props.edit.data.extra_attributes || {};
}
const { t } = useI18n({});

function submit() {
  let method = props.edit ? 'put' : 'post';
  let url = props.edit ? route('users.update', props.edit.data.id) : route('users.store');
  form
    .transform(data => ({ ...data, extra_attributes: { ...data.extra_attributes } }))
    [method](url, {
      preserveScroll: true,
      onSuccess: () => {
        props.edit ? '' : form.reset();
      },
      onError: () => {
        if (form.errors.name) {
          document.getElementById('name').focus();
        }
        if (form.errors.company) {
          document.getElementById('company').focus();
        }
        if (form.errors.email) {
          document.getElementById('email').focus();
        }
        if (form.errors.phone) {
          document.getElementById('phone').focus();
        }
      },
    });
}
</script>

<template>
  <AppLayout :title="props.edit ? t('Edit User') : t('Add New User')">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
      <FormSection @submitted="submit" autocomplete="off">
        <template #title>{{ props.edit ? t('Edit User') : t('Add New User') }}</template>
        <template #description>
          {{
            t('Please fill the form below to {x}.', {
              x: props.edit ? t('update {x}', { x: t('user') }) : t('add {x}', { x: t('user') }),
            })
          }}
          <div>
            <Button :href="route('users.index')" class="mt-4">{{ t('List Users') }}</Button>
          </div>
        </template>

        <template #form>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.name" id="name" :error="form.errors.name" :label="t('Name')" />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.email" id="email" type="email" :error="form.errors.email" :label="t('Email')" />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.password" id="password" type="password" :error="form.errors.password" :label="t('Password')" />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput
              type="password"
              id="password_confirmation"
              :label="t('Confirm Password')"
              v-model="form.password_confirmation"
              :error="form.errors.password_confirmation"
            />
          </div>

          <template v-if="fields && fields.length">
            <CustomFields :fields="fields" :errors="form.errors" v-model="form.extra_attributes" className="col-span-6 sm:col-span-3" />
          </template>

          <div class="col-span-full">
            <div v-if="roles && roles.length" class="flex flex-wrap items-center gap-x-6 gap-y-3 mb-4">
              <label class="block w-full font-medium">{{ t('Roles') }}</label>
              <template v-for="role in roles" :key="role.name">
                <CheckBox
                  :id="role.name"
                  :value="role.name"
                  :label="t(role.name)"
                  :error="form.errors.roles"
                  v-model:checked="form.roles"
                />
              </template>
              <InputError v-if="form.errors.roles" :message="form.errors.roles" class="w-full -mt-1" />
            </div>

            <h1 class="font-bold mb-2">{{ t('Permissions') }}</h1>
            <div class="flex flex-col lg:flex-row gap-6">
              <CheckBox id="view_all" v-model:checked="form.view_all" :label="t('Can view all records')" />
              <CheckBox id="edit_all" v-model:checked="form.edit_all" :label="t('Can edit all records')" />
            </div>
          </div>
        </template>

        <template #actions>
          <ActionMessage :on="form.recentlySuccessful" class="mr-3"> {{ t('Saved.') }} </ActionMessage>
          <Button type="submit" :loading="form.processing"> {{ t('Save') }} </Button>
        </template>
      </FormSection>
    </div>
  </AppLayout>
</template>
