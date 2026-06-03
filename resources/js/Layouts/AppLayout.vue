<script setup>
import { useI18n } from 'vue-i18n';
import { computed, onMounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

import { LANGUAGES } from '@/Core/i18n';
import composer from '@base/composer.json';
import Banner from '@/Jetstream/Banner.vue';
import NavLink from '@/Jetstream/NavLink.vue';
import Dropdown from '@/Jetstream/Dropdown.vue';
import AlertsModal from '@/Shared/AlertsModal.vue';
import FlashMessages from '@/Shared/FlashMessages.vue';
import DropdownLink from '@/Jetstream/DropdownLink.vue';
import ApplicationMark from '@/Jetstream/ApplicationMark.vue';
import ResponsiveNavLink from '@/Jetstream/ResponsiveNavLink.vue';

const theme = ref(null);
const alerts = ref(false);
const { t } = useI18n({});
defineProps({ title: String });
const showingNavigationDropdown = ref(false);
const user = computed(() => usePage().props.auth.user);
const version = composer?.version || '4.0.0-beta.0';

onMounted(() => {
  theme.value = localStorage.getItem('theme') || document.documentElement.getAttribute('data-theme');
  document.getElementById('app-loading').style.display = 'none';
});

const switchToTeam = team => {
  router.put(route('current-team.update'), { team_id: team.id }, { preserveState: false });
};

const logout = () => {
  router.post(route('logout'));
};

const toggleMode = () => {
  theme.value = theme.value == 'light' ? 'dark' : 'light';
  localStorage.setItem('theme', theme.value);
  document.documentElement.setAttribute('data-theme', theme.value);
};
</script>

<script>
export default {
  data() {
    return { currentLang: this.$page.props.language };
  },
  methods: {
    getFlag(lang) {
      return LANGUAGES.find(l => l.value == this.$root.$i18n.locale)?.flag || 'US';
    },
    changeLang(lang) {
      router.visit('/language/' + lang, {
        onFinish: v => {
          window.Locale = lang;
          this.currentLang = lang;
          document.querySelector('html').setAttribute('lang', lang);
          this.$root.$i18n.locale = lang;
        },
      });
    },
  },
};
</script>

<template>
  <div>
    <Head :title="title" />
    <Banner />

    <div class="page min-h-screen bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
      <nav class="header bg-white dark:bg-gray-900 border-b dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-14">
            <div class="flex">
              <div class="max-w-[180px] sm:max-w-[250px] truncate flex items-center mr-4">
                <Link :href="route('dashboard')">
                  <h2 class="font-extrabold text-2xl">{{ $settings?.name || 'SIM' }}</h2>
                  <!-- <ApplicationMark class="block h-8 w-auto dark:invert dark:grayscale" /> -->
                </Link>
              </div>

              <!-- Navigation Links -->
              <div class="hidden space-x-2 lg:-my-px sm:ml-2 lg:ml-4 lg:flex">
                <NavLink :href="route('dashboard')" :active="route().current('dashboard')" class="hidden xl:flex">
                  {{ t('Dashboard') }}
                </NavLink>

                <template
                  v-if="
                    $can([
                      'read-invoices',
                      'create-invoices',
                      'import-invoices',
                      'read-payments',
                      'read-quotations',
                      'create-quotations',
                      'import-quotations',
                    ])
                  "
                >
                  <Dropdown align="center" :width="$page.props.language == 'en' ? '48' : '56'">
                    <template #trigger>
                      <span class="inline-flex rounded-md">
                        <button
                          type="button"
                          id="orders-menu"
                          class="inline-flex items-center px-3 py-2 border-b text-sm leading-4 font-medium text-gray-600 hover:text-gray-800 focus:outline-hidden transition focus:ring-0 focus:rounded-none focus:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100 dark:focus:text-gray-100"
                          :class="
                            route().current('invoices.*') || route().current('payments.*') || route().current('quotations.*')
                              ? 'border-blue-700 hover:border-blue-300 focus:border-blue-400 dark:border-blue-700 dark:hover:border-blue-300 dark:focus:border-blue-400'
                              : 'border-gray-200 hover:border-gray-400 focus:border-gray-400 dark:border-gray-700 dark:hover:border-gray-500 dark:focus:border-gray-500'
                          "
                        >
                          {{ t('Orders') }}
                          <icons name="chevron-down" class="ml-2 -mr-0.5 h-4 w-4 text-gray-500" />
                        </button>
                      </span>
                    </template>

                    <template #content>
                      <template v-if="$can(['read-invoices', 'create-invoices'])">
                        <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Invoices') }}</div>
                        <DropdownLink v-if="$can('read-invoices')" :href="route('invoices.index')" id="list-invoices">
                          {{ t('List Invoices') }}
                        </DropdownLink>
                        <DropdownLink v-if="$can('create-invoices')" :href="route('invoices.create')" id="add-invoice">
                          {{ t('Add New Invoice') }}
                        </DropdownLink>
                      </template>

                      <template v-if="$can(['read-payments', 'create-payments'])">
                        <div class="border-t border-gray-100 dark:border-gray-700" />
                        <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Payments') }}</div>
                        <DropdownLink v-if="$can('read-payments')" :href="route('payments.index')" id="list-payments">
                          {{ t('List Payments') }}
                        </DropdownLink>
                        <!-- <DropdownLink v-if="$can('create-payments')" :href="route('payments.create')">
                          {{ t('Add New Payment') }}
                        </DropdownLink> -->
                      </template>

                      <template v-if="$can(['read-quotations', 'create-quotations'])">
                        <div class="border-t border-gray-100 dark:border-gray-700" />
                        <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Quotations') }}</div>
                        <DropdownLink v-if="$can('read-quotations')" :href="route('quotations.index')">
                          {{ t('List Quotations') }}
                        </DropdownLink>
                        <DropdownLink v-if="$can('create-quotations')" :href="route('quotations.create')">
                          {{ t('Add New Quotation') }}
                        </DropdownLink>
                      </template>
                    </template>
                  </Dropdown>
                </template>

                <template
                  v-if="
                    $can(['read-customers', 'create-customers', 'import-customers', 'read-products', 'create-products', 'import-products'])
                  "
                >
                  <Dropdown align="center" :width="$page.props.language == 'en' ? '48' : '56'">
                    <template #trigger>
                      <span class="inline-flex rounded-md">
                        <button
                          type="button"
                          id="data-menu"
                          class="inline-flex items-center px-3 py-2 border-b text-sm leading-4 font-medium text-gray-600 hover:text-gray-800 focus:outline-hidden transition focus:ring-0 focus:rounded-none focus:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100 dark:focus:text-gray-100"
                          :class="
                            route().current('customers.*') || route().current('products.*')
                              ? 'border-blue-700 hover:border-blue-300 focus:border-blue-400 dark:border-blue-700 dark:hover:border-blue-300 dark:focus:border-blue-400'
                              : 'border-gray-200 hover:border-gray-400 focus:border-gray-400 dark:border-gray-700 dark:hover:border-gray-500 dark:focus:border-gray-500'
                          "
                        >
                          {{ t('Data') }}
                          <icons name="chevron-down" class="ml-2 -mr-0.5 h-4 w-4 text-gray-500" />
                        </button>
                      </span>
                    </template>

                    <template #content>
                      <template v-if="$can(['read-customers', 'create-customers', 'import-customers'])">
                        <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Customers') }}</div>
                        <DropdownLink id="list-customers" v-if="$can('read-customers')" :href="route('customers.index')">{{
                          t('List Customers')
                        }}</DropdownLink>
                        <DropdownLink id="add-customer" v-if="$can('create-customers')" :href="route('customers.create')">{{
                          t('Add New Customer')
                        }}</DropdownLink>
                        <DropdownLink v-if="$can('import-customers')" :href="route('customers.import')">
                          {{ t('Import Customers') }}
                        </DropdownLink>
                      </template>

                      <template v-if="$can(['read-products', 'create-products', 'import-products'])">
                        <div class="border-t border-gray-100 dark:border-gray-700" />
                        <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Products') }}</div>
                        <DropdownLink v-if="$can('read-products')" :href="route('products.index')"> {{ t('List Products') }} </DropdownLink>
                        <DropdownLink v-if="$can('create-products')" :href="route('products.create')">
                          {{ t('Add New Product') }}
                        </DropdownLink>
                        <template v-if="$can('import-products')">
                          <!-- <div class="border-t border-gray-100 dark:border-gray-700" /> -->
                          <DropdownLink :href="route('products.import')">
                            {{ t('Import Products') }}
                          </DropdownLink>
                        </template>
                      </template>
                    </template>
                  </Dropdown>
                </template>

                <template
                  v-if="
                    $can([
                      'read-settings',
                      'create-settings',
                      'read-tax-rates',
                      'create-tax-rates',
                      'read-roles',
                      'create-roles',
                      'read-fields',
                      'create-fields',
                      'read-notes',
                      'create-notes',
                      'read-companies',
                      'create-companies',
                      'read-users',
                      'create-users',
                    ])
                  "
                >
                  <Dropdown align="center" :width="$page.props.language == 'en' ? '48' : '56'">
                    <template #trigger>
                      <span class="inline-flex rounded-md">
                        <button
                          type="button"
                          class="inline-flex items-center px-3 py-2 border-b text-sm leading-4 font-medium text-gray-600 hover:text-gray-800 focus:outline-hidden transition focus:ring-0 focus:rounded-none focus:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100 dark:focus:text-gray-100"
                          :class="
                            route().current('settings') ||
                            route().current('tax_rates.*') ||
                            route().current('roles.*') ||
                            route().current('companies.*') ||
                            route().current('fields.*') ||
                            route().current('notes.*') ||
                            route().current('users.*')
                              ? 'border-blue-700 hover:border-blue-300 focus:border-blue-400 dark:border-blue-700 dark:hover:border-blue-300 dark:focus:border-blue-400'
                              : 'border-gray-200 hover:border-gray-400 focus:border-gray-400 dark:border-gray-700 dark:hover:border-gray-500 dark:focus:border-gray-500'
                          "
                        >
                          {{ t('Settings') }}
                          <icons name="chevron-down" class="ml-2 -mr-0.5 h-4 w-4 text-gray-500" />
                        </button>
                      </span>
                    </template>

                    <template #content>
                      <!-- <template v-if="$can('read-activity')">
                        <DropdownLink :href="route('activity')"> {{ t('Activity Log') }} </DropdownLink>
                      </template> -->
                      <template v-if="$can(['read-settings', 'create-settings'])">
                        <DropdownLink :href="route('settings')"> {{ t('System Settings') }} </DropdownLink>
                      </template>
                      <template v-if="$can(['read-companies', 'create-companies'])">
                        <DropdownLink :href="route('companies.index')"> {{ t('Companies') }} </DropdownLink>
                      </template>
                      <template v-if="$can(['read-users', 'create-users'])">
                        <DropdownLink :href="route('users.index')"> {{ t('Users') }} </DropdownLink>
                      </template>
                      <template v-if="$can(['read-tax-rates', 'create-tax-rates', 'import-tax-rates'])">
                        <!-- <div class="border-t border-gray-100 dark:border-gray-700" /> -->
                        <!-- <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Tax Rates') }}</div> -->
                        <DropdownLink v-if="$can('read-tax-rates')" :href="route('tax_rates.index')">
                          {{ t('Tax Rates') }}
                        </DropdownLink>
                        <!-- <DropdownLink v-if="$can('create-tax-rates')" :href="route('tax_rates.create')">
                          {{ t('Add New Tax Rate') }}
                        </DropdownLink> -->
                      </template>
                      <template v-if="$can(['read-notes', 'create-notes'])">
                        <!-- <div class="border-t border-gray-100 dark:border-gray-700" /> -->
                        <!-- <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Notes') }}</div> -->
                        <DropdownLink v-if="$can('read-notes')" :href="route('notes.index')">
                          {{ t('Notes') }}
                        </DropdownLink>
                        <!-- <DropdownLink v-if="$can('create-notes')" :href="route('notes.create')">
                          {{ t('Add New Note') }}
                        </DropdownLink> -->
                      </template>
                      <template v-if="$can(['read-roles', 'create-roles'])">
                        <!-- <div class="border-t border-gray-100 dark:border-gray-700" /> -->
                        <!-- <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage User Roles') }}</div> -->
                        <DropdownLink v-if="$can('read-roles')" :href="route('roles.index')">
                          {{ t('User Roles') }}
                        </DropdownLink>
                        <!-- <DropdownLink v-if="$can('create-roles')" :href="route('roles.create')">
                          {{ t('Add New User Role') }}
                        </DropdownLink> -->
                      </template>
                      <template v-if="$can(['read-fields', 'create-fields'])">
                        <!-- <div class="border-t border-gray-100 dark:border-gray-700" /> -->
                        <!-- <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage User Roles') }}</div> -->
                        <DropdownLink v-if="$can('read-fields')" :href="route('fields.index')">
                          {{ t('Custom Fields') }}
                        </DropdownLink>
                        <!-- <DropdownLink v-if="$can('create-fields')" :href="route('fields.create')">
                          {{ t('Add New Custom Field') }}
                        </DropdownLink> -->
                      </template>
                    </template>
                  </Dropdown>
                </template>

                <template
                  v-if="
                    $can(['read-activity', 'reports-daily-invoices', 'reports-monthly-invoices', 'reports-invoices', 'reports-payments'])
                  "
                >
                  <Dropdown align="center" :width="$page.props.language == 'en' ? '48' : '56'">
                    <template #trigger>
                      <span class="inline-flex rounded-md">
                        <button
                          type="button"
                          class="inline-flex items-center px-3 py-2 border-b text-sm leading-4 font-medium text-gray-600 hover:text-gray-800 focus:outline-hidden transition focus:ring-0 focus:rounded-none focus:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100 dark:focus:text-gray-100"
                          :class="
                            route().current('reports') || route().current('activity') || route().current('reports.*')
                              ? 'border-blue-700 hover:border-blue-300 focus:border-blue-400 dark:border-blue-700 dark:hover:border-blue-300 dark:focus:border-blue-400'
                              : 'border-gray-200 hover:border-gray-400 focus:border-gray-400 dark:border-gray-700 dark:hover:border-gray-500 dark:focus:border-gray-500'
                          "
                        >
                          {{ t('Reports') }}
                          <icons name="chevron-down" class="ml-2 -mr-0.5 h-4 w-4 text-gray-500" />
                        </button>
                      </span>
                    </template>

                    <template #content>
                      <template v-if="$can('reports-daily-invoices')">
                        <DropdownLink :href="route('reports.daily_invoices')"> {{ t('Daily Invoices') }} </DropdownLink>
                      </template>
                      <template v-if="$can('reports-monthly-invoices')">
                        <DropdownLink :href="route('reports.monthly_invoices')"> {{ t('Monthly Invoices') }} </DropdownLink>
                      </template>
                      <template v-if="$can('reports-invoices')">
                        <DropdownLink :href="route('reports.invoices')"> {{ t('Invoices Report') }} </DropdownLink>
                      </template>
                      <template v-if="$can('reports-payments')">
                        <DropdownLink :href="route('reports.payments')"> {{ t('Payments Report') }} </DropdownLink>
                      </template>
                      <div class="border-t border-gray-100 dark:border-gray-700" />
                      <template v-if="$can('read-activity')">
                        <DropdownLink :href="route('activity')"> {{ t('Activity Log') }} </DropdownLink>
                      </template>
                    </template>
                  </Dropdown>
                </template>
              </div>
            </div>

            <div class="hidden lg:flex lg:items-center lg:ml-6">
              <div class="ml-3 relative">
                <!-- Teams Dropdown -->
                <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700 focus:outline-hidden focus:bg-gray-50 active:bg-gray-50 transition"
                      >
                        {{ user?.current_team.name }}

                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path
                            fill-rule="evenodd"
                            d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <div class="w-60">
                      <!-- Team Management -->
                      <template v-if="$page.props.jetstream.hasTeamFeatures">
                        <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Team') }}</div>

                        <!-- Team Settings -->
                        <DropdownLink :href="route('teams.show', user?.current_team)">
                          {{ t('Team Settings') }}
                        </DropdownLink>

                        <DropdownLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')">
                          Create New Team
                        </DropdownLink>

                        <div class="border-t border-gray-100 dark:border-gray-700" />

                        <!-- Team Switcher -->
                        <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Switch Teams') }}</div>

                        <template v-for="team in user?.all_teams" :key="team.id">
                          <form @submit.prevent="switchToTeam(team)">
                            <DropdownLink as="button">
                              <div class="flex items-center">
                                <svg
                                  v-if="team.id == user?.current_team_id"
                                  class="mr-2 h-5 w-5 text-green-400"
                                  fill="none"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  stroke="currentColor"
                                  viewBox="0 0 24 24"
                                >
                                  <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                  {{ team.name }}
                                </div>
                              </div>
                            </DropdownLink>
                          </form>
                        </template>
                      </template>
                    </div>
                  </template>
                </Dropdown>
              </div>

              <div class="relative mx-2">
                <Dropdown align="center" width="40">
                  <template #trigger>
                    <button
                      class="flex h-12 items-center p-0.5 m-1 rounded-full text-gray-400 hover:text-gray-500 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-hidden focus:ring-0 focus:border-transparent"
                    >
                      <span
                        class="mt-0.5 w-6 h-6"
                        v-html="getFlag(currentLang).replace(/./g, char => String.fromCodePoint(char.charCodeAt(0) + 127397))"
                      >
                      </span>
                    </button>
                  </template>

                  <template #content>
                    <template v-for="lang in LANGUAGES" :key="lang.value">
                      <DropdownLink as="button" @click="changeLang(lang.value)">
                        <span class="mr-2" v-html="lang.flag.replace(/./g, char => String.fromCodePoint(char.charCodeAt(0) + 127397))">
                        </span>
                        {{ lang.label }}
                      </DropdownLink>
                    </template>
                  </template>
                </Dropdown>
              </div>
              <button
                type="button"
                @click="toggleMode()"
                class="bg-gray-200 dark:bg-gray-700 relative inline-flex h-6 w-11 mr-3 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden focus:border-transparent focus:rounded-full focus:ring-0"
              >
                <span class="sr-only">{{ t('Theme toggle') }}</span>
                <span
                  class="translate-x-0 dark:translate-x-5 pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white dark:bg-black shadow-sm ring-0 transition duration-200 ease-in-out"
                >
                  <span
                    :class="theme == 'dark' ? 'opacity-0 ease-out duration-100' : 'opacity-100 ease-in duration-200'"
                    class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
                  >
                    <svg
                      fill="none"
                      stroke-width="1.5"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-3 w-3 text-yellow-500 dark:text-yellow-300"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"
                      />
                    </svg>
                  </span>

                  <span
                    :class="theme == 'dark' ? 'opacity-100 ease-in duration-200' : 'opacity-0 ease-out duration-100'"
                    class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
                  >
                    <svg
                      fill="none"
                      stroke-width="1.5"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-3 w-3 text-blue-500 dark:text-blue-300"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"
                      />
                    </svg>
                  </span>
                </span>
              </button>
              <!-- <Link
                :href="route('impersonate.stop')"
                v-if="$page.props.is_impersonating"
                class="relative rounded-full text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
              >
                <span class="sr-only">{{ t('Stop Impersonating') }}</span>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="w-6 h-6"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9v6m-4.5 0V9M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </Link> -->
              <!-- Settings Dropdown -->
              <div class="ml-3 relative">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <div
                      class="flex items-center border-0 border-gray-300 dark:border-gray-700 mt-px -mb-px focus-within:border-blue-400 focus-within:ring-0"
                    >
                      <button
                        type="button"
                        class="inline-flex items-center px-3 py-2.5 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 focus:outline-hidden focus:ring-0 transition"
                      >
                        <img class="h-8 w-8 rounded-full object-cover mr-2" :src="user?.profile_photo_url" :alt="user?.name" />
                        {{ user?.name }}

                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                          <path
                            fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </div>
                  </template>

                  <template #content>
                    <!-- <template v-if="$page.props.is_impersonating">
                      <DropdownLink :href="route('impersonate.stop')">
                        {{ t('Stop Impersonating') }}
                      </DropdownLink>
                      <div class="border-t border-gray-100 dark:border-gray-700" />
                    </template> -->

                    <!-- Account Management -->
                    <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Account') }}</div>

                    <DropdownLink :href="route('profile.show')"> {{ t('Profile') }} </DropdownLink>
                    <DropdownLink v-if="user?.roles.find(r => r.name == 'customer')" :href="route('customer.edit')">
                      {{ t('Update Billing Details') }}
                    </DropdownLink>

                    <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">
                      {{ t('API Tokens') }}
                    </DropdownLink>

                    <div class="border-t border-gray-100 dark:border-gray-700" />

                    <!-- Authentication -->
                    <form @submit.prevent="logout">
                      <DropdownLink as="button"> {{ t('Log Out') }} </DropdownLink>
                    </form>
                  </template>
                </Dropdown>
              </div>
            </div>

            <div class="flex items-center gap-2 lg:hidden">
              <Dropdown align="center" width="40">
                <template #trigger>
                  <button
                    class="flex h-12 items-center p-0.5 m-1 rounded-full text-gray-400 hover:text-gray-500 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-hidden focus:ring-0 focus:border-transparent"
                  >
                    <span
                      class="mt-0.5 w-6 h-6"
                      v-html="getFlag(currentLang).replace(/./g, char => String.fromCodePoint(char.charCodeAt(0) + 127397))"
                    >
                    </span>
                  </button>
                </template>

                <template #content>
                  <template v-for="lang in LANGUAGES" :key="lang.value">
                    <DropdownLink as="button" @click="changeLang(lang.value)">
                      <span class="mr-2" v-html="lang.flag.replace(/./g, char => String.fromCodePoint(char.charCodeAt(0) + 127397))">
                      </span>
                      {{ lang.label }}
                    </DropdownLink>
                  </template>
                </template>
              </Dropdown>

              <button
                type="button"
                @click="toggleMode()"
                class="bg-gray-200 dark:bg-gray-700 relative inline-flex h-6 w-11 mr-3 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden focus:border-transparent focus:rounded-full focus:ring-0"
              >
                <span class="sr-only">Theme toggle</span>
                <span
                  :class="theme == 'dark' ? 'translate-x-5' : 'translate-x-0'"
                  class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white dark:bg-black shadow-sm ring-0 transition duration-200 ease-in-out"
                >
                  <span
                    :class="theme == 'dark' ? 'opacity-0 ease-out duration-100' : 'opacity-100 ease-in duration-200'"
                    class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
                    aria-hidden="true"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke-width="1.5"
                      stroke="currentColor"
                      class="h-3 w-3 text-gray-700 dark:text-gray-100"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"
                      />
                    </svg>
                  </span>

                  <span
                    :class="theme == 'dark' ? 'opacity-100 ease-in duration-200' : 'opacity-0 ease-out duration-100'"
                    class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
                    aria-hidden="true"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke-width="1.5"
                      stroke="currentColor"
                      class="h-3 w-3 text-blue-600 dark:text-blue-400"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"
                      />
                    </svg>
                  </span>
                </span>
              </button>
              <!-- <Link
                :href="route('impersonate.stop')"
                v-if="$page.props.is_impersonating"
                class="relative rounded-full text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
              >
                <span class="sr-only">{{ t('Stop Impersonating') }}</span>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="w-6 h-6"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9v6m-4.5 0V9M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </Link> -->
              <!-- Hamburger -->
              <div class="-mr-2 flex items-center lg:hidden">
                <button
                  class="inline-flex items-center justify-center p-2 rounded-full text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-hidden focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 dark:focus:text-gray-400 transition"
                  @click="showingNavigationDropdown = !showingNavigationDropdown"
                >
                  <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path
                      :class="{
                        hidden: showingNavigationDropdown,
                        'inline-flex': !showingNavigationDropdown,
                      }"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M4 6h16M4 12h16M4 18h16"
                    />
                    <path
                      :class="{
                        hidden: !showingNavigationDropdown,
                        'inline-flex': showingNavigationDropdown,
                      }"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div
          :class="{
            block: showingNavigationDropdown,
            hidden: !showingNavigationDropdown,
          }"
          class="lg:hidden"
        >
          <div class="pt-2 pb-3 space-y-1">
            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
              {{ t('Dashboard') }}
            </ResponsiveNavLink>

            <template v-if="$can(['read-invoices', 'create-invoices', 'import-invoices'])">
              <div class="border-t border-gray-100 dark:border-gray-700" />
              <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Invoices') }}</div>
              <ResponsiveNavLink v-if="$can('read-invoices')" :href="route('invoices.index')" :active="route().current('invoices.index')">
                {{ t('List Invoices') }}
              </ResponsiveNavLink>
              <ResponsiveNavLink
                v-if="$can('create-invoices')"
                :href="route('invoices.create')"
                :active="route().current('invoices.create')"
              >
                {{ t('Add New Invoice') }}
              </ResponsiveNavLink>
            </template>

            <template v-if="$can(['read-payments', 'create-payments', 'import-payments'])">
              <div class="border-t border-gray-100 dark:border-gray-700" />
              <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Payments') }}</div>
              <ResponsiveNavLink v-if="$can('read-payments')" :href="route('payments.index')" :active="route().current('payments.index')">
                {{ t('List Payments') }}
              </ResponsiveNavLink>
              <!-- <ResponsiveNavLink
                v-if="$can('create-quotations')"
                :href="route('quotations.create')"
                :active="route().current('quotations.create')"
              >
                {{ t('Add New Quotation') }}
              </ResponsiveNavLink> -->
            </template>

            <template v-if="$can(['read-quotations', 'create-quotations', 'import-quotations'])">
              <div class="border-t border-gray-100 dark:border-gray-700" />
              <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Quotations') }}</div>
              <ResponsiveNavLink
                v-if="$can('read-quotations')"
                :href="route('quotations.index')"
                :active="route().current('quotations.index')"
              >
                {{ t('List Quotations') }}
              </ResponsiveNavLink>
              <ResponsiveNavLink
                v-if="$can('create-quotations')"
                :href="route('quotations.create')"
                :active="route().current('quotations.create')"
              >
                {{ t('Add New Quotation') }}
              </ResponsiveNavLink>
            </template>

            <template v-if="$can(['read-customers', 'create-customers', 'import-customers'])">
              <div class="border-t border-gray-100 dark:border-gray-700" />
              <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Customers') }}</div>
              <ResponsiveNavLink
                v-if="$can('read-customers')"
                :href="route('customers.index')"
                :active="route().current('customers.index')"
              >
                {{ t('List Customers') }}
              </ResponsiveNavLink>
              <ResponsiveNavLink
                v-if="$can('create-customers')"
                :href="route('customers.create')"
                :active="route().current('customers.create')"
              >
                {{ t('Add New Customer') }}
              </ResponsiveNavLink>
              <ResponsiveNavLink
                v-if="$can('import-customers')"
                :href="route('customers.import')"
                :active="route().current('customers.import')"
              >
                {{ t('Import Customers') }}
              </ResponsiveNavLink>
            </template>

            <template v-if="$can(['read-products', 'create-products', 'import-products', 'labels-products'])">
              <div class="border-t border-gray-100 dark:border-gray-700" />
              <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Products') }}</div>
              <ResponsiveNavLink v-if="$can('read-products')" :href="route('products.index')" :active="route().current('products.index')">
                {{ t('List Products') }}
              </ResponsiveNavLink>
              <ResponsiveNavLink
                v-if="$can('create-products')"
                :href="route('products.create')"
                :active="route().current('products.create')"
              >
                {{ t('Add New Product') }}
              </ResponsiveNavLink>
              <ResponsiveNavLink
                v-if="$can('import-products')"
                :href="route('products.import')"
                :active="route().current('products.import')"
              >
                {{ t('Import Products') }}
              </ResponsiveNavLink>
            </template>

            <template v-if="$can(['read-companies', 'create-companies'])">
              <div class="border-t border-gray-100 dark:border-gray-700" />
              <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Companies') }}</div>
              <ResponsiveNavLink
                v-if="$can('read-companies')"
                :href="route('companies.index')"
                :active="route().current('companies.index')"
              >
                {{ t('List Companies') }}
              </ResponsiveNavLink>
              <ResponsiveNavLink
                v-if="$can('create-companies')"
                :href="route('companies.create')"
                :active="route().current('companies.create')"
              >
                {{ t('Add New Company') }}
              </ResponsiveNavLink>
            </template>

            <template v-if="$can(['read-users', 'create-users'])">
              <div class="border-t border-gray-100 dark:border-gray-700" />
              <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Users') }}</div>
              <ResponsiveNavLink v-if="$can('read-users')" :href="route('users.index')" :active="route().current('users.index')">
                {{ t('List Users') }}
              </ResponsiveNavLink>
              <ResponsiveNavLink v-if="$can('create-users')" :href="route('users.create')" :active="route().current('users.create')">
                {{ t('Add New User') }}
              </ResponsiveNavLink>
            </template>

            <template v-if="$can(['read-tax-rates', 'create-tax-rates'])">
              <div class="border-t border-gray-100 dark:border-gray-700" />
              <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Tax Rates') }}</div>
              <ResponsiveNavLink
                v-if="$can('read-tax-rates')"
                :href="route('tax_rates.index')"
                :active="route().current('tax_rates.index')"
              >
                {{ t('List Tax Rates') }}
              </ResponsiveNavLink>
              <ResponsiveNavLink
                v-if="$can('create-tax-rates')"
                :href="route('tax_rates.create')"
                :active="route().current('tax_rates.create')"
              >
                {{ t('Add New Tax Rate') }}
              </ResponsiveNavLink>
            </template>

            <template v-if="$can(['read-note', 'create-note'])">
              <div class="border-t border-gray-100 dark:border-gray-700" />
              <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Notes') }}</div>
              <ResponsiveNavLink v-if="$can('read-note')" :href="route('notes.index')" :active="route().current('notes.index')">
                {{ t('List Notes') }}
              </ResponsiveNavLink>
              <ResponsiveNavLink v-if="$can('create-note')" :href="route('notes.create')" :active="route().current('notes.create')">
                {{ t('Add New Note') }}
              </ResponsiveNavLink>
            </template>

            <template v-if="$can(['read-roles', 'create-roles'])">
              <div class="border-t border-gray-100 dark:border-gray-700" />
              <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Roles') }}</div>
              <ResponsiveNavLink v-if="$can('read-roles')" :href="route('roles.index')" :active="route().current('roles.index')">
                {{ t('List Roles') }}
              </ResponsiveNavLink>
              <ResponsiveNavLink v-if="$can('create-roles')" :href="route('roles.create')" :active="route().current('roles.create')">
                {{ t('Add New Role') }}
              </ResponsiveNavLink>
            </template>

            <template v-if="$can(['read-fields', 'create-fields'])">
              <div class="border-t border-gray-100 dark:border-gray-700" />
              <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Custom Fields') }}</div>
              <ResponsiveNavLink v-if="$can('read-fields')" :href="route('fields.index')" :active="route().current('fields.index')">
                {{ t('List Custom Fields') }}
              </ResponsiveNavLink>
              <ResponsiveNavLink v-if="$can('create-fields')" :href="route('fields.create')" :active="route().current('fields.create')">
                {{ t('Add New Custom Field') }}
              </ResponsiveNavLink>
            </template>

            <template v-if="$can(['read-settings', 'create-settings'])">
              <div class="border-t border-gray-100 dark:border-gray-700" />
              <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Settings') }}</div>
              <ResponsiveNavLink :href="route('settings')" :active="route().current('settings')">
                {{ t('System Settings') }}
              </ResponsiveNavLink>
            </template>

            <template
              v-if="$can(['read-activity', 'reports-daily-invoices', 'reports-monthly-invoices', 'reports-invoices', 'reports-payments'])"
            >
              <div class="border-t border-gray-100 dark:border-gray-700" />
              <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Reports') }}</div>

              <template v-if="$can('reports-daily-invoices')">
                <ResponsiveNavLink :href="route('reports.daily_invoices')"> {{ t('Daily Invoices') }} </ResponsiveNavLink>
              </template>
              <template v-if="$can('reports-monthly-invoices')">
                <ResponsiveNavLink :href="route('reports.monthly_invoices')"> {{ t('Monthly Invoices') }} </ResponsiveNavLink>
              </template>
              <template v-if="$can('reports-invoices')">
                <ResponsiveNavLink :href="route('reports.invoices')"> {{ t('Invoices Report') }} </ResponsiveNavLink>
              </template>
              <template v-if="$can('reports-payments')">
                <ResponsiveNavLink :href="route('reports.payments')"> {{ t('Payments Report') }} </ResponsiveNavLink>
              </template>

              <template v-if="$can('read-activity')">
                <ResponsiveNavLink :href="route('activity')"> {{ t('Activity Log') }} </ResponsiveNavLink>
              </template>
            </template>
          </div>

          <!-- Responsive Settings Options -->
          <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center px-4">
              <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 mr-3">
                <img class="h-10 w-10 rounded-full object-cover" :src="user?.profile_photo_url" :alt="user?.name" />
              </div>

              <div>
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">
                  {{ user?.name }}
                </div>
                <div class="font-medium text-sm text-gray-500 dark:text-gray-400">
                  {{ user?.email }}
                </div>
              </div>
            </div>

            <div class="mt-3 space-y-1">
              <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                {{ t('Profile') }}
              </ResponsiveNavLink>
              <!-- <ResponsiveNavLink v-if="$page.props.is_impersonating" :href="route('impersonate.stop')">
                {{ t('Stop Impersonating') }}
              </ResponsiveNavLink> -->
              <ResponsiveNavLink
                v-if="$page.props.jetstream.hasApiFeatures"
                :href="route('api-tokens.index')"
                :active="route().current('api-tokens.index')"
              >
                {{ t('API Tokens') }}
              </ResponsiveNavLink>

              <!-- Authentication -->
              <form method="POST" @submit.prevent="logout">
                <ResponsiveNavLink as="button"> {{ t('Log Out') }} </ResponsiveNavLink>
              </form>

              <!-- Team Management -->
              <template v-if="$page.props.jetstream.hasTeamFeatures">
                <div class="border-t border-gray-200" />

                <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Manage Team') }}</div>

                <!-- Team Settings -->
                <ResponsiveNavLink :href="route('teams.show', user?.current_team)" :active="route().current('teams.show')">
                  {{ t('Team Settings') }}
                </ResponsiveNavLink>

                <ResponsiveNavLink
                  v-if="$page.props.jetstream.canCreateTeams"
                  :href="route('teams.create')"
                  :active="route().current('teams.create')"
                >
                  {{ t('Create New Team') }}
                </ResponsiveNavLink>

                <div class="border-t border-gray-200" />

                <!-- Team Switcher -->
                <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">{{ t('Switch Teams') }}</div>

                <template v-for="team in user?.all_teams" :key="team.id">
                  <form @submit.prevent="switchToTeam(team)">
                    <ResponsiveNavLink as="button">
                      <div class="flex items-center">
                        <svg
                          v-if="team.id == user?.current_team_id"
                          fill="none"
                          stroke-width="2"
                          viewBox="0 0 24 24"
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          class="mr-2 h-5 w-5 text-green-400"
                        >
                          <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>{{ team.name }}</div>
                      </div>
                    </ResponsiveNavLink>
                  </form>
                </template>
              </template>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Heading -->
      <template v-if="$page.props.is_impersonating">
        <!-- <div class="bg-white dark:bg-gray-900 border-b dark:border-gray-700"> -->
        <div class="bg-yellow-300 bg-opacity-30 border-b dark:border-gray-700">
          <div class="max-w-7xl mx-auto py-2 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-x-6 gap-y-2">
            {{ t('You are impersonating as {x}', { x: $page.props.user?.name }) }}
            <Link
              :href="route('impersonate.stop')"
              class="relative flex items-center gap-2 text-sm font-bold rounded-full text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
            >
              <span class="sr-only">{{ t('Stop Impersonating') }}</span>
              <svg
                fill="none"
                class="w-6 h-6"
                stroke-width="1.5"
                viewBox="0 0 24 24"
                stroke="currentColor"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9v6m-4.5 0V9M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="hidden sm:block">{{ t('Stop Impersonating') }}</span>
            </Link>
          </div>
        </div>
      </template>

      <!-- Page Content -->
      <main style="min-height: calc(100vh - 100px)">
        <slot />
      </main>
      <div class="mt-auto mb-6 text-center text-sm">
        &copy; {{ new Date().getFullYear() }} <strong>{{ $page.props.settings.name }}</strong> v{{ version }}
      </div>
      <AlertsModal :show="alerts" @close="alerts = false" />
      <flash-messages class="print:hidden"></flash-messages>
    </div>
  </div>
</template>
