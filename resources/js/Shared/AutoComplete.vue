<script setup>
import axios from 'axios';
import { useI18n } from 'vue-i18n';
import { v4 as uuidv4 } from 'uuid';
import throttle from 'lodash/throttle';
import Label from '@/Jetstream/Label.vue';
import InputError from '@/Jetstream/InputError.vue';
import useClickOutside from '@/Core/useClickOutside.js';
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';

const { t } = useI18n({});
const emit = defineEmits(['update:modelValue']);

const componentRef = ref(null);
const auto_complete = ref(null);
const props = defineProps({
  id: {
    type: String,
    default() {
      return uuidv4();
    },
  },
  json: Boolean,
  label: String,
  error: String,
  position: String,
  selected: String,
  defaultText: String,
  placeholder: String,
  action: Function,
  actionText: String,
  addResult: Array,
  disable: [String, Number],
  modelValue: [String, Number],
  suggestions: [String, Array],
  valueKey: { type: String, default: 'value' },
  labelKey: { type: String, default: 'label' },
  multiple: { type: Boolean, default: false },
  clearable: { type: Boolean, default: false },
  searchable: { type: Boolean, default: true },
  keepFocus: { type: Boolean, default: false },
  resetSearch: { type: Boolean, default: false },
  placement: { type: String, default: 'bottom' },
});

const search = ref('');
const result = ref([]);
const input = ref(null);
const open = ref(false);
const stop = ref(false);
const search0 = ref('');
const current = ref(null);
const loading = ref(false);
const selected = ref(null);
const multi_current = ref([]);
const multi_selected = ref([]);

useClickOutside(componentRef, () => hide(), auto_complete);
// useClickOutside(componentRef, () => (open.value = false), auto_complete);

watch(
  () => props.modelValue,
  async v => {
    // console.log(JSON.stringify(v));
    if (!v) {
      search.value = null;
      await hide();
    } else {
      let vl = multi_selected.value.map(s => (props.json ? s.value : s));
      if (v.toString() !== vl.toString()) {
        await initializeValue();
      }
    }
  }
);

watch(
  () => props.addResult,
  async v => {
    if (v && v.value) {
      result.value.push(v.value);
      current.value = v.value;
      selected.value = v.value;
      suggestionClick(current.value);
      setTimeout(() => {
        search.value = v.value.label;
        emit('update:modelValue', v.value);
      }, 100);
    }
  }
);

watch(search, s => {
  if (props.searchable) {
    open.value = true;
    if (Array.isArray(props.suggestions)) {
      if (props.json) {
        result.value = s ? props.suggestions.filter(r => cLabel(r, s)) : props.suggestions;
      } else {
        result.value = s ? props.suggestions.filter(r => r.includes(s)) : props.suggestions;
      }
    } else if (s) {
      loading.value = true;
      getSuggestions(s);
    }
  }
});

const currentLabel = computed(() => cLabel(selected.value));
const currentValue = computed(() => cValue(selected.value));

onBeforeUnmount(() => {
  if (props.multiple) {
    window.removeEventListener('click', clickListener);
  }
});

onMounted(async () => {
  if (props.multiple) {
    window.addEventListener('click', clickListener);
  }
  await initializeValue();
});

const initializeValue = async () => {
  if (Array.isArray(props.suggestions)) {
    result.value = props.suggestions;
  }
  if (props.modelValue) {
    multi_current.value = [];
    if (Array.isArray(props.suggestions)) {
      if (props.multiple) {
        if (props.selected) {
          multi_selected.value = props.selected;
        } else {
          multi_selected.value = props.suggestions.filter(
            s =>
              s == props.modelValue ||
              s[props.valueKey] == props.modelValue ||
              props.modelValue.filter(m => m == s[props.valueKey])[0] ||
              props.modelValue.filter(m => m[props.valueKey] == s[props.valueKey])[0] ||
              s.id == props.modelValue
          );
        }
        if (props.json) {
          props.suggestions.map((s, si) => {
            if (multi_selected.value.find(v => v.value == s.value)) {
              multi_current.value = [...multi_current.value, si];
            }
          });
        } else {
          //   multi_selected.value = props.modelValue;
          props.suggestions.map((s, si) => {
            if (multi_selected.value.includes(s)) {
              multi_current.value = [...multi_current.value, si];
            }
          });
        }
        search.value = multi_selected.value.map(v => v.label).join(', ');
      } else {
        if (props.json) {
          selected.value = props.suggestions.find(
            s => s == props.modelValue || s[props.valueKey] == props.modelValue || s.value == props.modelValue || s.id == props.modelValue
          );
          current.value = props.suggestions.findIndex(
            s => s == props.modelValue || s[props.valueKey] == props.modelValue || s.value == props.modelValue || s.id == props.modelValue
          );
          search.value = currentLabel.value;
          // search.value = props.searchable ? currentLabel.value : '';
        } else {
          selected.value = props.modelValue;
        }
      }
      await nextTick();
      await hide();
    } else {
      if (!stop.value) {
        axios
          .post(props.suggestions, { id: props.modelValue })
          .then(res => {
            current.value = 0;
            result.value = res.data;
            selected.value = res.data[0];
            search.value = cLabel(selected.value);
          })
          .finally(async () => {
            await hide();
            loading.value = false;
          });
      }
    }
  }
};

const clickListener = e => {
  if (e.target == auto_complete.value || e.composedPath().includes(auto_complete.value)) {
    return false;
  }
  hide(e, true);
};

const getSuggestions = throttle(s => {
  let nr = result.value;
  let nr0 = nr?.length ? nr[0] : {};
  if (!stop.value && s && s !== currentLabel.value) {
    search0.value = s;
    axios
      .post(props.suggestions, { search: s })
      .then(res => {
        result.value = res.data;
        if (result.value.length == 1) {
          suggestionClick(0);
          //   suggestionClick(0, true);
        }
      })
      .finally(() => (loading.value = false));
  } else {
    loading.value = false;
  }
  stop.value = false;
}, 300);

const cLabel = (r, s) => {
  if (r === null) {
    return null;
  } else if (typeof r !== 'object') {
    return r;
  }
  let key = '';
  if (props.labelKey) {
    key = props.labelKey;
  } else if (r.label !== undefined) {
    key = 'label';
  } else if (r.name !== undefined) {
    key = 'name';
  }
  if (s) {
    return key ? r[key].toLowerCase().includes(s.toString().toLowerCase()) : r.toLowerCase().includes(s.toLowerCase());
  }
  return key ? r[key] : r;
};
const cValue = r => {
  if (r === null) {
    return null;
  } else if (typeof r !== 'object') {
    return r;
  }
  let key = '';
  if (props.valueKey) {
    key = props.valueKey;
  } else if (r.value !== undefined) {
    key = 'value';
  } else if (r.id !== undefined) {
    key = 'id';
  }
  return key ? r[key] : r;
};

const show = () => {
  open.value = true;
  if (!props.multiple) {
    search.value = null;
    input.value.select();
    if (!selected.value) {
      current.value = 0;
    }
  }
};

const hide = async (event, force) => {
  if (!props.multiple || force) {
    if (selected.value) {
      search.value = currentLabel.value;
    }
    await nextTick();
    open.value = false;
    // setTimeout(() => (open.value = false), 100);
  }
};

const enter = async e => {
  e.preventDefault();
  e.stopImmediatePropagation();
  suggestionClick(current.value);
  await hide();
  e.stopPropagation();
  return false;
};

const up = () => {
  e.preventDefault();
  if (current.value > 0) current.value--;
  e.stopPropagation();
  return false;
};

const down = e => {
  e.preventDefault();
  if (current.value < result.value.length - 1) current.value++;
  e.stopPropagation();
  return false;
};

const isActive = index => {
  if (props.multiple) {
    return multi_current.value.includes(index);
  }
  return index == current.value;
};

const suggestionClick = async (index, reset = false) => {
  stop.value = true;
  if (index !== undefined) {
    if (props.multiple) {
      if (props.json && multi_selected.value.find(v => v.value == result.value[index].value)) {
        multi_selected.value = multi_selected.value.filter(v => v.value != result.value[index].value);
      } else {
        if (multi_selected.value.find(v => v == result.value[index])) {
          multi_selected.value = multi_selected.value.filter(v => v != result.value[index]);
        } else {
          multi_selected.value = [
            ...multi_selected.value,
            Array.isArray(index) ? result.value.find(v => v == index[0]) : result.value[index],
          ];
        }
      }

      multi_current.value = [];
      props.suggestions.map((s, si) => {
        if (props.json && multi_selected.value.find(v => v.value == s.value)) {
          multi_current.value = [...multi_current.value, si];
        } else if (multi_selected.value.includes(s)) {
          multi_current.value = [...multi_current.value, si];
        }
      });

      emit('update:modelValue', props.json ? multi_selected.value.map(v => v.value) : multi_selected.value);
      search.value = multi_selected.value.map(v => (props.json ? v.label : v)).join(', ');
      search.value = search.value || currentLabel.value;
    } else {
      current.value = index;
      selected.value = result.value[index];
      emit('update:modelValue', props.json ? currentValue.value : selected.value);
      search.value = props.resetSearch ? null : currentLabel.value;
      await hide();
    }
  }
  if (reset) {
    result.value = [];
  }
  if (props.resetSearch) {
    selected.value = null;
  }
  if (props.keepFocus) {
    input.value.focus();
  }
};

const reset = async () => {
  search.value = null;
  emit('update:modelValue', '');
  await hide();
};
</script>

<template>
  <div class="col-span-6 sm:col-span-4">
    <div class="flex items-center justify-between">
      <Label :for="id" :value="label" v-if="label" class="flex-1" />
      <button v-if="actionText && action" type="button" @click="action()" class="link text-sm focus:ring-0">{{ actionText }}</button>
    </div>
    <div class="relative mt-1 mb-2 flex items-center" ref="auto_complete">
      <label :for="id" class="cursor-pointer absolute top-0 right-0 border border-transparent p-3 text-gray-500 dark:text-gray-400">
        <icons name="chevron-down" class-name="w-5 h-6" />
      </label>
      <button
        type="button"
        @click="reset()"
        v-if="clearable && modelValue"
        class="cursor-pointer focus:outline-hidden absolute top-0.5 right-8 border border-transparent py-2.5 px-2 text-gray-500 hover:text-red-500"
      >
        <icons name="cross" class-name="w-5 h-6" />
      </button>
      <input
        :id="id"
        ref="input"
        @focus="show"
        @click="show"
        @keyup.up="up"
        v-model="search"
        autocomplete="off"
        @keyup.down="down"
        @keyup.right="enter"
        :readonly="!searchable"
        @keyup.enter.prevent="enter"
        :placeholder="placeholder || label"
        :class="{ 'border-red-500': error, 'pr-8': !clearable, 'pr-16': clearable }"
        class="w-full block text-base py-2 pl-4 placeholder-gray-400 border border-gray-300 dark:border-gray-700 focus:outline-hidden focus:border-blue-300 focus:ring-3 focus:ring-blue-200/50 rounded-md shadow-xs dark:bg-gray-800 placeholder:text-gray-400 dark:placeholder:text-gray-500"
      />
      <div
        v-if="open"
        ref="componentRef"
        class="absolute w-full rounded-md z-20"
        :class="placement == 'top' ? 'mb-2 bottom-full ' + position : 'mt-2 top-full ' + position"
      >
        <ul
          class="max-h-56 rounded-md bg-white dark:bg-gray-900 border dark:border-gray-700 text-base overflow-auto focus:outline-hidden sm:text-sm"
        >
          <template v-if="result && result.length">
            <template :key="ri" v-for="(r, ri) in result">
              <!-- v-if="disable && !disabledOptions.includes(cValue(r))" -->
              <template v-if="!(disable && disable == cValue())">
                <li>
                  <button
                    type="button"
                    :id="id + '-' + ri"
                    @click="suggestionClick(ri)"
                    :class="
                      isActive(ri)
                        ? 'bg-blue-600 text-white hover:bg-blue-200 hover:text-blue-800 '
                        : 'text-gray-900 dark:text-gray-100 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-100'
                    "
                    class="w-full text-left flex items-center select-none relative py-2 focus:outline-hidden focus:ring-0 focus:rounded-none"
                  >
                    <span class="ml-3 block font-normal truncate">
                      {{ cLabel(r) }}
                    </span>
                  </button>
                </li>
              </template>
            </template>
          </template>
          <li v-else class="bg-blue-600 text-white cursor-default select-none relative py-2">
            <div class="flex items-center">
              <span class="ml-3 block font-normal truncate">
                <span v-if="loading">{{ t('Searching for results') }}...</span>
                <span v-else-if="result == null">{{ t('Scan barcode or search items for next') }}</span>
                <span v-else-if="json">{{ defaultText || t('Please type to search') }}</span>
                <span v-else>{{ t('No result suggestions to list.') }}</span>
              </span>
            </div>
          </li>
        </ul>
      </div>
    </div>
    <InputError v-if="error" :message="error" class="mt-0" />
  </div>
</template>
