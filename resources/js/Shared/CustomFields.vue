<template>
  <template v-if="!loading && fields && fields.length">
    <div v-for="(field, fi) in fields" :key="fi" :class="field.type == 'textarea_field' ? 'col-span-full w-full' : className">
      <template v-if="field.type == 'text_field' || field.type == 'date_field' || field.type == 'time_field'">
        <TextInput
          :label="t(field.name)"
          :id="id + '_extra_attr_' + fi"
          :error="extra_errors[field.name]"
          v-model="extra_attributes[field.name]"
          :type="field.type.replace('_field', '')"
        />
      </template>
      <template v-else-if="field.type == 'select_field' || field.type == 'select_multiple_field'">
        <AutoComplete
          :searchable="false"
          :label="t(field.name)"
          :id="id + '_extra_attr_' + fi"
          :error="extra_errors[field.name]"
          v-model="extra_attributes[field.name]"
          :multiple="field.type == 'select_multiple_field'"
          :suggestions="field.options.split(',')?.map(item => item.trim())"
        />
      </template>
      <template v-else-if="field.type == 'textarea_field'">
        <TextareaInput
          :label="t(field.name)"
          :id="id + '_extra_attr_' + fi"
          :error="extra_errors[field.name]"
          v-model="extra_attributes[field.name]"
        />
      </template>
      <template v-else-if="field.type == 'checkbox_field'">
        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ t(field.name) }}</label>
        <div class="flex flex-col mt-2">
          <div class="relative flex items-center" v-for="(opt, oi) of field.options.split(',')?.map(item => item.trim())" :key="oi">
            <CheckBox :label="t(opt)" :id="id + '_extra_attr_' + fi + '_' + oi" v-model:checked="extra_attributes[field.name][opt]" />
          </div>
        </div>
        <span v-if="extra_errors[field.name]" class="text-red-600 text-sm">{{
          extra_errors[field.name].replace('extra_attributes.', '')
        }}</span>
      </template>
      <template v-else-if="field.type == 'radio_field'">
        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ t(field.name) }}</label>
        <div class="flex flex-col mt-2 gap-2">
          <div v-for="(opt, oi) of field.options.split(',')?.map(item => item.trim())" :key="oi" class="flex items-center">
            <div class="flex items-center gap-1">
              <input
                type="radio"
                :value="opt"
                :name="field.name"
                v-model="extra_attributes[field.name]"
                :id="id + '_extra_attr_' + fi + '_' + oi"
                class="rounded-full border-gray-300 dark:border-gray-700 dark:bg-gray-700 text-blue-600 shadow-xs w-5 h-5 focus:rounded-full focus:ring-offset-0 focus:ring-blue-200/50"
              />
              <label :for="id + '_extra_attr_' + fi + '_' + oi" class="ml-2 block">{{ opt }}</label>
            </div>
          </div>
          <span v-if="extra_errors[field.name]" class="text-red-600 text-sm">{{
            extra_errors[field.name].replace('extra_attributes.', '')
          }}</span>
        </div>
      </template>
    </div>
  </template>
</template>

<script setup>
import { useI18n } from 'vue-i18n';
import debounce from 'lodash/debounce';
import { onMounted, ref, watch } from 'vue';
import CheckBox from '@/Shared/CheckBox.vue';
import TextInput from '@/Shared/TextInput.vue';
import AutoComplete from '@/Shared/AutoComplete.vue';
import TextareaInput from '@/Shared/TextareaInput.vue';

const { t } = useI18n({});
const loading = ref(true);
const extra_errors = ref({});
const extra_attributes = ref({});
const emit = defineEmits(['updated', 'update:modelValue']);
const props = defineProps({
  id: String,
  fields: Array,
  className: String,
  errors: { type: [Boolean, Object] },
  modelValue: { type: [Array, Object] },
});

watch(
  () => props.errors,
  debounce(errors => {
    Object.keys(errors).map(e => {
      if (e.includes('extra_attributes.')) {
        extra_errors.value[e.split('.').pop()] = errors[e];
      }
    });
  }, 50),
  { deep: true }
);

watch(
  extra_attributes,
  debounce(e => {
    emit('updated', e);
    emit('update:modelValue', e);
  }, 500),
  { deep: true }
);

onMounted(() => {
  props.fields.map(f => {
    if (f.type == 'checkbox_field') {
      //   extra_attributes.value = { ...extra_attributes.value };
      extra_attributes.value[f.name] = {};
      f.options
        .split(',')
        ?.map(item => item.trim())
        .map(i => {
          //   console.log(i);
          extra_attributes.value[f.name][i] = props.modelValue[f.name] || false;
        });
      //   console.log(extra_attributes.value[f.name]);
    } else {
      extra_attributes.value[f.name] = props.modelValue[f.name] || null;
    }
  });
  loading.value = false;
});
</script>
