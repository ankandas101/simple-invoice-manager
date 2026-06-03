<script setup>
import axios from 'axios';
import dayjs from 'dayjs';
import { useI18n } from 'vue-i18n';
import debounce from 'lodash/debounce';
import { useForm, usePage } from '@inertiajs/vue3';
import { onMounted, ref, watch, computed } from 'vue';

import Button from '@/Shared/Button.vue';
import Modal from '@/Jetstream/Modal.vue';
import CheckBox from '@/Shared/CheckBox.vue';
import FileInput from '@/Shared/FileInput.vue';
import TextInput from '@/Shared/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Attachments from '@/Shared/Attachments.vue';
import CreateCustomer from '../Customer/Create.vue';
import CustomFields from '@/Shared/CustomFields.vue';
import AutoComplete from '@/Shared/AutoComplete.vue';
import FormSection from '@/Jetstream/FormSection.vue';
import TextareaInput from '@/Shared/TextareaInput.vue';
import ActionMessage from '@/Jetstream/ActionMessage.vue';
import { calculateDiscount, calculateTax, number_format } from '@/Core/helpers';

const show = ref(false);
const added = ref(false);
const initial_rows = ref(2);
const showNotes = ref(false);
const showModal = ref(false);
const searchIndex = ref(null);
const item = {
  name: '',
  quantity: 1,
  price: null,
  taxes: [],
  tax_method: 'exclusive',
  show_details: false,
  details: '',
  discount: null,
  discount_amount: null,
  tax_amount: null,
  total: null,
  discount_amount: 0,
  tax_amount: 0,
  net_price: 0,
  unit_price: 0,
  extra_attributes: {},
};
const notes = ref([]);
const results = ref([]);
const company = ref(null);
const customer = ref(null);
const addResult = ref(null);
const searching = ref(false);
const items = ref([]);
const form = useForm({
  company_id: null,
  customer_id: null,
  date: new Date().toISOString().slice(0, 10),
  reference: null,
  discount: null,
  taxes: [],
  expiry_date: null,
  shipping: null,
  status: 'pending',
  attachment: null,
  items: [],
  attachments: null,
  note: null,
  tax_method: 'exclusive',
  extra_attributes: {},
});

const props = defineProps(['edit', 'companies', 'tax_rates', 'note', 'fields', 'customer_fields', 'item_fields']);
if (props.edit) {
  if (props.edit.data.extra_attributes && props.fields) {
    Object.keys(props.edit.data.extra_attributes)
      .filter(k => !props.fields?.find(f => f.name == k))
      .map(k => delete props.edit.data.extra_attributes[k]);
  }

  company.value = { value: props.edit.data.company.id, label: props.edit.data.company.name };
  customer.value = { value: props.edit.data.customer.id, label: props.edit.data.customer.name };
  form.date = dayjs(props.edit.data.date).format('YYYY-MM-DD'); // props.edit.data.date.split('T')[0];
  form.expiry_date = props.edit.data.expiry_date ? props.edit.data.expiry_date.split('T')[0] : null;
  form.reference = props.edit.data.reference;
  form.shipping = props.edit.data.shipping;
  form.company_id = props.edit.data.company_id;
  form.customer_id = props.edit.data.customer_id;
  form.discount = props.edit.data.order_discount;
  form.taxes = props.edit.data.taxes.map(t => t.id);
  //   form.taxes = props.edit.data.taxes
  //     ? props.edit.data.taxes.map(t => ({ value: t.id, id: t.id, label: t.name, fixed: t.fixed, rate: t.rate }))
  //     : [];
  form.status = props.edit.data.status;
  form.items = props.edit.data.items.map(i => {
    i.price = Number(i.price);
    i.quantity = Number(i.quantity);
    i.taxes = i.taxes.map(t => t.id);
    i.show_details = i.details ? true : false;
    // i.taxes = i.taxes ? i.taxes.map(t => ({ value: t.id, id: t.id, label: t.name, fixed: t.fixed, rate: t.rate })) : [];
    return i;
  });
  items.value = form.items;
  form.note = props.edit.data.note;
  form.attachments = [];
  form.extra_attributes = props.edit.data.extra_attributes || {};
}
const { t } = useI18n({});

if (props.note) {
  form.note = props.note.details;
}
onMounted(() => {
  initial_rows.value = usePage().props.settings.initial_rows;
  if (initial_rows.value) {
    for (let index = items.value.length; index < initial_rows.value; index++) {
      items.value.push({ ...item });
    }
  }
});

const totals = computed(() => {
  let data = { total: 0, discount_amount: 0, tax_amount: 0, grand_total: 0 };
  for (const i of form.items) {
    data.total += Number(number_format(Number(i.quantity) * Number(i.unit_price)));
    data.discount_amount += Number(number_format(Number(i.quantity) * Number(i.discount_amount)));
    data.tax_amount += Number(number_format(Number(i.quantity) * Number(i.tax_amount)));
    data.grand_total += Number(number_format(Number(i.quantity) * Number(i.unit_price)));
  }
  form.discount_amount = 0;
  if (form.discount) {
    form.discount_amount = calculateDiscount(data.total, form.discount);
    data.grand_total -= form.discount_amount;
  }
  if (form.taxes && form.taxes.length) {
    // let itaxes = form.taxes.map(t => t.value);
    // let taxes = props.tax_rates.filter(t => itaxes.includes(t.id));
    let taxes = props.tax_rates.filter(t => form.taxes.includes(t.id));
    form.tax_amount = calculateTax(data.grand_total, taxes, form.tax_method);
    if (form.tax_method == 'exclusive') {
      data.grand_total += form.tax_amount;
    }
  }
  data.grand_total += form.shipping ? Number(form.shipping) : 0;
  return { ...data };
});

watch(
  items,
  debounce(newItems => {
    form.items = newItems.map(i => {
      if (i.quantity && i.price) {
        let price = i.price;
        if (i.discount) {
          i.discount_amount = calculateDiscount(price, i.discount);
          price = price - i.discount_amount;
        }
        if (i.taxes && i.taxes.length) {
          //   let itaxes = i.taxes.map(t => t.value);
          let taxes = props.tax_rates.filter(t => i.taxes.includes(t.id));
          i.tax_amount = calculateTax(price, taxes, i.tax_method);
        }
        if (i.tax_method == 'inclusive') {
          i.net_price = Number(price) - Number(i.tax_amount);
          i.unit_price = Number(price);
        } else {
          i.net_price = Number(price);
          i.unit_price = Number(number_format(Number(price) + Number(i.tax_amount)));
        }
      }
      i.total = Number(number_format(i.unit_price * i.quantity));
      return i;
    });
  }, 500),
  { deep: true }
);

const searchProduct = debounce((e, i) => {
  searchIndex.value = i;
  axios
    .post(route('search.products'), { search: e.target.value })
    .then(res => {
      show.value = true;
      results.value = res.data;
      if (results.value.length == 1) {
        selectItem(results.value[0], i);
      }
    })
    .finally(() => (searching.value = false));
}, 250);

const searchNotes = debounce(e => {
  showNotes.value = true;
  axios
    .post(route('search.notes'), { search: e.target.value })
    .then(res => {
      notes.value = res.data;
      if (notes.value.length == 1) {
        selectItem(notes.value[0], i);
      }
    })
    .finally(() => (searching.value = false));
}, 250);

const selectItem = (item, index) => {
  let items = form.items.map((i, ii) => {
    if (ii == index) {
      i.code = item.code;
      i.name = item.label;
      i.price = item.price;
      i.details = item.details;
      i.product_id = item.value;
      i.tax_method = item.tax_method;
      i.taxes = item.taxes.map(t => t.id);
      i.show_details = item.details ? true : false;
    }
    return i;
  });
  items.value = [...items];
  form.items = items.value;
  show.value = false;
};

const itemDiscountFocused = r => {
  if (r + 1 == items.value.length) {
    addOrderItem();
  }
};
const hideResult = () => {
  setTimeout(() => (show.value = false), 150);
};

const selectNote = async n => {
  form.note = '';
  showNotes.value = false;
  await nextTick();
  form.note = n.details;
  setTimeout(() => {
    document.getElementById('note').value = n.details;
  }, 30);
};

const addCustomer = () => {
  showModal.value = true;
};

const selectCustomer = nc => {
  addResult.value = { value: { value: nc.id, label: nc.name } };
  //   customer.value = { value: nc.id, label: nc.name };
  //   form.customer_id = nc.id;
  showModal.value = false;
};

const addOrderItem = () => {
  items.value.push({ ...item });
  added.value = true;
  setTimeout(() => (added.value = false), 1000);
};

const removeOrderItem = v => {
  if (initial_rows.value < items.value.length) {
    items.value = items.value.filter((i, ii) => ii != v);
  }
};

function submit() {
  form.errors = {};
  let method = props.edit ? 'put' : 'post';
  let url = props.edit ? route('quotations.update', props.edit.data.id) : route('quotations.store');
  form.transform(data => {
    data._method = method;
    data.items = [...items.value];
    // data.company_id = company.value?.value || company.value;
    data.customer_id = customer.value?.value || customer.value;
    data.extra_attributes = { ...data.extra_attributes };
    return data;
  });
  form.post(url, {
    preserveScroll: true,
    onSuccess: () => {
      props.edit ? '' : form.reset();
    },
    onError: () => {
      window.scroll(0, 0);
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
  <AppLayout :title="props.edit ? t('Edit Quotation') : t('Add New Quotation')">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
      <FormSection @submitted="submit" autocomplete="off">
        <template #title>{{ props.edit ? t('Edit Quotation') : t('Add New Quotation') }}</template>
        <template #description>
          {{
            t('Please fill the form below to {x}.', {
              x: props.edit ? t('update {x}', { x: t('quotation') }) : t('add {x}', { x: t('quotation') }),
            })
          }}
          <div>
            <Button :href="route('quotations.index')" class="mt-4">{{ t('List Quotations') }}</Button>
          </div>
        </template>

        <template #form>
          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              :json="true"
              id="company_id"
              :searchable="true"
              :label="t('Company')"
              :suggestions="companies"
              v-model="form.company_id"
              :error="form.errors.company_id"
            />
          </div>

          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              :json="true"
              id="customer_id"
              v-model="customer"
              :searchable="true"
              :label="t('Customer')"
              :addResult="addResult"
              :action-text="t('Create')"
              :selected="customer?.value"
              :action="() => addCustomer()"
              :error="form.errors.customer_id"
              :suggestions="route('search.customers')"
            />
          </div>

          <div class="col-span-6 sm:col-span-3">
            <TextInput type="date" v-model="form.date" id="date" :error="form.errors.date" :label="t('Date')" />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput v-model="form.reference" id="reference" :error="form.errors.reference" :label="t('Reference')" />
          </div>

          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              id="status"
              :json="true"
              :searchable="false"
              :label="t('Status')"
              v-model="form.status"
              :error="form.errors.status"
              :suggestions="[
                { value: 'pending', label: t('pending') },
                { value: 'sent', label: t('sent') },
                { value: 'confirmed', label: t('confirmed') },
                { value: 'canceled', label: t('canceled') },
              ]"
            />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <FileInput multiple v-model="form.attachments" id="attachments" :error="form.errors.attachments" :label="t('Attachments')" />
          </div>

          <div class="col-span-full -mx-6">
            <div v-if="items && items.length" class="flex flex-col">
              <div class="flex items-center justify-between mx-6 mb-3">
                <h2 class="text-lg font-extrabold">{{ t('Order Items') }}</h2>
                <div class="flex items-center">
                  <span v-if="added" class="text-gray-500 dark:text-gray-400 mr-2">{{ t('Added') }}</span>
                  <button
                    type="button"
                    @click="addOrderItem()"
                    class="py-1 px-2 border dark:border-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-800"
                  >
                    {{ t('Add Row') }}
                  </button>
                </div>
              </div>
              <div class="w-full flex flex-col divide-y dark:divide-gray-700 border-y dark:border-gray-700">
                <div
                  :key="r"
                  v-for="(i, r) of items"
                  class="odd:bg-gray-50 dark:odd:bg-black/20 even:bg-neutral-50 dark:even:bg-black/50 w-full flex flex-col items-start gap-2 p-6"
                >
                  <div class="flex items-start gap-2">
                    <div class="text-sm">{{ r + 1 }}.</div>
                    <div class="w-full flex flex-col gap-2">
                      <div class="text-sm font-medium flex gap-2 items-center justify-between">
                        <div class="flex-1 relative">
                          <div class="relative">
                            <TextInput
                              v-model="i.name"
                              @blur="hideResult"
                              :label="t('Item Description')"
                              @input="e => searchProduct(e, r)"
                              class="text-gray-700 dark:text-gray-300"
                            />

                            <ul
                              v-if="show && results && results.length && searchIndex == r"
                              class="absolute top-full w-full z-20 max-h-64 scroll-py-2 overflow-y-auto p-2 mt-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-md"
                            >
                              <li :key="si" v-for="(sitem, si) in results">
                                <button
                                  type="button"
                                  @click="selectItem(sitem, r)"
                                  class="block text-left w-full select-none rounded-md px-3 py-2 hover:bg-blue-500 hover:text-white"
                                >
                                  <span class="ml-3 flex-auto truncate">{{ sitem.label }}</span>
                                </button>
                              </li>
                            </ul>
                          </div>

                          <div v-if="$page.props.errors['items.' + r + '.name']" class="text-red-600 pt-1 rounded-md">
                            {{ $page.props.errors['items.' + r + '.name'].replace('items.' + r + '.', '') }}
                          </div>

                          <div class="absolute top-6 right-0 flex">
                            <button
                              type="button"
                              @click="() => (i.show_details = !i.show_details)"
                              :class="i.show_details ? 'text-blue-500 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400'"
                              class="inline-flex items-center rounded-sm mt-px p-2 font-sans text-sm font-medium focus:ring-0"
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
                                  d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"
                                />
                              </svg>
                            </button>
                          </div>
                          <template v-if="i.show_details">
                            <TextareaInput v-model="i.details" class="mt-2 text-gray-700 dark:text-gray-300" />
                          </template>

                          <button
                            type="button"
                            v-if="initial_rows < r + 1"
                            @click="removeOrderItem(r)"
                            class="absolute right-0 top-0 text-sm text-red-500 rounded-md flex items-center focus:ring-0"
                          >
                            {{ t('Remove') }}
                          </button>
                        </div>
                      </div>
                      <div class="grid grid-cols-2 xl:grid-cols-6 text-sm gap-x-4 gap-y-2">
                        <div>
                          <TextInput
                            type="number"
                            selectOnFocus
                            v-model="i.quantity"
                            :label="t('Quantity')"
                            class="text-gray-700 dark:text-gray-300"
                          />
                        </div>
                        <div>
                          <TextInput type="number" v-model="i.price" :label="t('Price')" class="text-gray-700 dark:text-gray-300" />
                        </div>
                        <div class="col-span-2">
                          <AutoComplete
                            :json="true"
                            id="tax_rates"
                            :multiple="true"
                            v-model="i.taxes"
                            :searchable="false"
                            :label="t('Tax Rates')"
                            :suggestions="tax_rates"
                          />
                        </div>
                        <div>
                          <AutoComplete
                            :json="true"
                            id="tax_method"
                            :searchable="false"
                            v-model="i.tax_method"
                            :label="t('Tax Method')"
                            :suggestions="[
                              { value: 'inclusive', label: t('inclusive') },
                              { value: 'exclusive', label: t('exclusive') },
                            ]"
                          />
                        </div>
                        <div>
                          <TextInput
                            v-model="i.discount"
                            :label="t('Discount')"
                            @focus="itemDiscountFocused(r)"
                            pattern="[0-9]+(\.[0-9]{1,2})?%?"
                            class="text-gray-700 dark:text-gray-300"
                            :title="t('please type number with or without percentage sign.')"
                          />
                        </div>
                      </div>
                      <div class="text-sm -mt-4">
                        <div v-if="$page.props.errors['items.' + r + '.quantity']" class="text-red-600 pt-1 rounded-md">
                          {{ $page.props.errors['items.' + r + '.quantity'].replace('items.' + r + '.', '') }}
                        </div>
                        <div v-if="$page.props.errors['items.' + r + '.price']" class="text-red-600 pt-1 rounded-md">
                          {{ $page.props.errors['items.' + r + '.price'].replace('items.' + r + '.', '') }}
                        </div>
                      </div>

                      <template v-if="item_fields && item_fields.length">
                        <div class="grid grid-cols-2 xl:grid-cols-3 text-sm gap-x-4 gap-y-2">
                          <CustomFields id="item" :fields="item_fields" v-model="i.extra_attributes" />
                          <!-- @updated="e => (i.extra_attributes = { ...e })" -->
                        </div>
                        <div class="-mt-1 text-red-600 text-sm">
                          <div v-for="field of item_fields" :key="field.slug">
                            {{ $page.props.errors['items.' + r + '.extra_attributes.' + field.name] }}
                          </div>
                        </div>
                      </template>
                    </div>
                  </div>
                </div>
                <div
                  class="odd:bg-gray-50 dark:odd:bg-black/20 even:bg-neutral-50 dark:even:bg-black/50 w-full flex items-start justify-end gap-y-2 gap-x-8 p-6"
                >
                  <div>
                    <div class="text-right h-6">{{ t('Total') }} :</div>
                    <div v-if="totals.discount_amount" class="text-right h-6">{{ t('Products Discount') }} :</div>
                    <div v-if="totals.tax_amount" class="text-right h-6">{{ t('Products Tax') }} :</div>
                    <div v-if="form.discount_amount" class="text-right h-6">{{ t('Order Discount') }} :</div>
                    <div v-if="form.tax_amount" class="text-right h-6">{{ t('Order Tax') }} :</div>
                    <div v-if="form.shipping" class="text-right h-6">{{ t('Shipping') }} :</div>
                    <div v-if="totals.grand_total && totals.grand_total != totals.total" class="text-right h-6">
                      {{ t('Grand Total') }} :
                    </div>
                  </div>
                  <div>
                    <div class="text-right h-6 font-bold">
                      {{ $number(totals.total) }}
                    </div>
                    <div v-if="totals.discount_amount" class="text-right h-6 font-bold">
                      {{ $number(totals.discount_amount) }}
                    </div>
                    <div v-if="totals.tax_amount" class="text-right h-6 font-bold">
                      {{ $number(totals.tax_amount) }}
                    </div>
                    <div v-if="form.discount_amount" class="text-right h-6 font-bold">
                      {{ $number(form.discount_amount) }}
                    </div>
                    <div v-if="form.tax_amount" class="text-right h-6 font-bold">
                      {{ $number(form.tax_amount) }}
                    </div>
                    <div v-if="form.shipping" class="text-right h-6">{{ $number(form.shipping) }}</div>
                    <div v-if="totals.grand_total && totals.grand_total != totals.total" class="text-right h-6 font-bold">
                      {{ $number(totals.grand_total) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-6 sm:col-span-3">
            <AutoComplete
              :json="true"
              :multiple="true"
              :searchable="false"
              id="order_tax_rates"
              v-model="form.taxes"
              :label="t('Tax Rates')"
              :suggestions="tax_rates"
              :error="form.errors.taxes"
            />
          </div>
          <div class="col-span-6 sm:col-span-3 flex items-start gap-6">
            <div class="w-1/2">
              <AutoComplete
                :json="true"
                :searchable="false"
                id="form_tax_method"
                :label="t('Tax Method')"
                v-model="form.tax_method"
                :suggestions="[
                  { value: 'inclusive', label: t('inclusive') },
                  { value: 'exclusive', label: t('exclusive') },
                ]"
              />
            </div>
            <div class="w-1/2">
              <TextInput
                id="discount"
                :label="t('Discount')"
                v-model="form.discount"
                :error="form.errors.discount"
                pattern="[0-9]+(\.[0-9]{1,2})?%?"
                :title="t('please type number with or without percentage sign.')"
              />
            </div>
          </div>

          <div class="col-span-6 sm:col-span-3">
            <TextInput type="date" v-model="form.expiry_date" id="expiry_date" :error="form.errors.expiry_date" :label="t('Expiry Date')" />
          </div>
          <div class="col-span-6 sm:col-span-3">
            <TextInput type="number" v-model="form.shipping" id="shipping" :error="form.errors.shipping" :label="t('Shipping')" />
          </div>

          <CustomFields :fields="fields" :errors="form.errors" v-model="form.extra_attributes" className="col-span-6 sm:col-span-3" />
          <!-- @updated="e => (form.extra_attributes = { ...e })" -->

          <div class="col-span-full">
            <div class="relative">
              <TextareaInput @input="searchNotes" v-model="form.note" id="note" :error="form.errors.note" :label="t('Note')" />
              <ul
                v-if="showNotes && notes && notes.length"
                class="absolute top-full w-full z-20 max-h-64 scroll-py-2 overflow-y-auto p-2 mt-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-md"
              >
                <li :key="ni" v-for="(n, ni) in notes">
                  <button
                    type="button"
                    @click="selectNote(n)"
                    class="block text-left w-full select-none rounded-md px-3 py-2 hover:bg-blue-500 hover:text-white"
                  >
                    <span class="ml-3 flex-auto truncate">{{ n.label }}</span>
                  </button>
                </li>
              </ul>
            </div>
          </div>
          <div class="col-span-full">
            <Attachments :attachments="edit ? edit.data?.attachments : []" />
          </div>
        </template>

        <template #actions>
          <ActionMessage :on="form.recentlySuccessful" class="mr-3"> {{ t('Saved.') }} </ActionMessage>
          <Button type="submit" :loading="form.processing"> {{ t('Save') }} </Button>
        </template>
      </FormSection>
    </div>

    <Modal :show="showModal" max-width="xl" :closeable="false" @close="() => (showModal = false)">
      <div class="px-6 pt-3 pb-6 print:px-0 bg-gray-50 dark:bg-gray-900">
        <div class="flex items-start justify-between print:hidden">
          <div class="px-4 sm:px-0 mb-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
              {{ t('Add New Customer') }}
            </h3>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
              {{ t('Please fill the form below to {x}.', { x: t('add {x}', { x: t('customer') }) }) }}
            </p>
          </div>
          <div class="-mr-2 flex items- gap-2">
            <button
              @click="showModal = false"
              class="flex items-center justify-center h-8 w-8 rounded-full text-gray-600 hover:text-gray-800 hover:bg-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 focus:outline-hidden"
            >
              <icons name="cross" class="h-5 w-5" />
            </button>
          </div>
        </div>

        <div class="">
          <CreateCustomer :fields="customer_fields" @close="() => (showModal = false)" @done="selectCustomer" />
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>
