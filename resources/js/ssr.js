import i18n from '@/Core/i18n';
import mixin from '@/Core/mixin';
import Icons from '@/Shared/Icons.vue';
import { route, ZiggyVue } from 'ziggy-js';

import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from '@vue/server-renderer';
import { createSSRApp, h } from 'vue';

createServer(page =>
  createInertiaApp({
    page,
    render: renderToString,
    resolve: name => {
      const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
      return pages[`./Pages/${name}.vue`];
    },
    setup({ App, props, plugin }) {
      return createSSRApp({
        render: () => h(App, props),
      })
        .use(i18n)
        .use(plugin)
        .mixin(mixin)
        .use(ZiggyVue)
        .component('Head', Head)
        .component('Link', Link)
        .component('Icons', Icons);
    },
  })
);
