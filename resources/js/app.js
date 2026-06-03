import './bootstrap';
import '../css/app.css';

import mixin from '@/Core/mixin';
import Icons from '@/Shared/Icons.vue';
import i18n, { SUPPORT_LOCALES } from '@/Core/i18n';

import { createSSRApp, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { route, ZiggyVue } from 'ziggy-js';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
// import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';

const appName = import.meta.env.VITE_APP_NAME || 'SIM';

createInertiaApp({
  title: title => `${title} - ${window?.appName || appName}`,
  resolve: name => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  async setup({ el, App, props, plugin }) {
    for await (const lang of SUPPORT_LOCALES) {
      let messages = await import(`../../lang/${lang}.json`);
      messages = JSON.parse(JSON.stringify(messages));
      messages = { ...messages, ...messages?.default, default: 'default' };
      i18n.global.setLocaleMessage(lang, messages);

      //   (async () => {
      //     return await import(`../../lang/${lang}.json`);
      //   })().then(messages => {
      //     messages = JSON.parse(JSON.stringify(messages));
      //     messages = { ...messages, ...messages?.default, default: '' };
      //     i18n.global.setLocaleMessage(lang, messages);
      //   });
    }

    let app = createSSRApp({
      setup() {
        const { t } = useI18n();
        return { t };
      },
      render: () => h(App, props),
      mounted: () => {
        setTimeout(() => {
          document.getElementById('app-loading').style.display = 'none';
        }, 800);
      },
    })
      .use(i18n)
      .use(plugin)
      .mixin(mixin)
      .use(ZiggyVue)
      .component('Head', Head)
      .component('Link', Link)
      .component('Icons', Icons);

    app.config.globalProperties.$route = route;
    return app.mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});

// InertiaProgress.init({ color: '#4B5563' });
