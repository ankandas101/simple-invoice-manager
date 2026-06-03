const mixin = {
  computed: {
    $isAdmin() {
      return this.$page.props.auth.user && this.$page.props.auth.user.roles.find(r => r.name == 'admin');
    },
    $company() {
      return this.$page.props.company;
    },
    $settings() {
      return this.$page.props.settings;
    },
  },

  methods: {
    $capitalize(string) {
      return string.charAt(0).toUpperCase() + string.slice(1);
    },
    $extra_attributes(extras) {
      return Object.keys(extras)
        .map(k => {
          return '<div><span class="text-gray-500 dark:text-gray-400">' + k + ':</span> ' + extras[k] + '</div>';
        })
        .join('');
    },
    $number(amount, locale, options) {
      if (!amount) {
        amount = 0;
      }
      let formatted = parseFloat(amount);
      if (!locale || (locale.length != 2 && locale.length != 5)) {
        locale = this.$page.props.settings.default_locale;
      }
      if (!options) {
        options = {
          minimumFractionDigits: this.$page.props.settings.fraction || 0,
          maximumFractionDigits: this.$page.props.settings.fraction || 0,
        };
      }
      try {
        return new Intl.NumberFormat(locale, options).format(formatted);
      } catch (err) {
        return new Intl.NumberFormat('en-US', options).format(formatted);
      }
    },
    $currency(amount, locale, options) {
      if (!amount) {
        amount = 0;
      }
      let formatted = parseFloat(amount);
      if (!locale || (locale.length != 2 && locale.length != 5)) {
        locale = this.$page.props.settings.default_locale;
      }
      if (options?.currency && options.currency.length != 3) {
        options.currency = this.$page.props.settings.currency_code;
      }
      if (!options) {
        options = {
          style: 'currency',
          //   signDisplay: 'always',
          currencyDisplay: 'symbol',
          // currencySign: 'accounting',
          currency: this.$page.props.settings.currency_code,
          minimumFractionDigits: this.$page.props.settings.fraction || 0,
        };
      }
      try {
        return new Intl.NumberFormat(locale, options).format(formatted);
      } catch (err) {
        return new Intl.NumberFormat('en-US', options).format(formatted);
      }
    },
    $parseNumber(amount, locale) {
      if (!locale || (locale.length != 2 && locale.length != 5)) {
        locale = this.$page.props.settings.default_locale;
      }
      var thousandSeparator = Intl.NumberFormat(locale)
        .format(11111)
        .replace(/\p{Number}/gu, '');
      var decimalSeparator = Intl.NumberFormat(locale)
        .format(1.1)
        .replace(/\p{Number}/gu, '');
      return parseFloat(amount.replace(new RegExp('\\' + thousandSeparator, 'g'), '').replace(new RegExp('\\' + decimalSeparator), '.'));
    },
    $date(date, locale, style) {
      if (this.$page.props.settings?.date_format == 'php') {
        return date.split('T')[0];
      }

      let formatted = new Date(Date.parse(date));

      try {
        let od = date.split('T')[0].split('-');
        formatted = new Date(od[0], od[1] - 1, od[2], 0, 0, 0, 0);
      } catch (err) {}

      if (!locale || (locale.length != 2 && locale.length != 5)) {
        locale = this.$page.props.settings.default_locale;
      }
      try {
        return formatted.toLocaleString(locale, {
          dateStyle: style ? style : 'medium',
          //   timeZone: this.$page.props.settings.timezone || 'UTC',
        });
      } catch (err) {
        return formatted.toLocaleString('en-US', {
          dateStyle: style ? style : 'medium',
          //   timeZone: this.$page.props.settings.timezone || 'UTC',
        });
      }
    },
    $formatJSDate(date) {
      var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();
      if (month.length < 2) month = '0' + month;
      if (day.length < 2) day = '0' + day;
      return [year, month, day].join('-');
    },
    $dateDay(date, locale) {
      let formatted = new Date(Date.parse(date));
      if (!locale || (locale.length != 2 && locale.length != 5)) {
        locale = this.$page.props.settings.default_locale;
      }
      try {
        return formatted.toLocaleString(locale, { day: 'numeric', weekday: 'short' });
      } catch (err) {
        return formatted.toLocaleString('en-US', { day: 'numeric', weekday: 'short' });
      }
    },
    $month(month, locale, style = 'short') {
      let formatted = new Date(Date.parse(month));
      if (!locale || (locale.length != 2 && locale.length != 5)) {
        locale = this.$page.props.settings.default_locale;
      }
      try {
        return formatted.toLocaleString(locale, { month: style, year: '2-digit' });
      } catch (err) {
        return formatted.toLocaleString('en-US', { month: style, year: '2-digit' });
      }
    },
    $monthName(month, locale, style = 'long') {
      let formatted = new Date(Date.parse(month));
      if (!locale || (locale.length != 2 && locale.length != 5)) {
        locale = this.$page.props.settings.default_locale;
      }
      try {
        return formatted.toLocaleString(locale, { month: style });
      } catch (err) {
        return formatted.toLocaleString('en-US', { month: style });
      }
    },
    $time(date, locale, style) {
      let formatted = new Date(Date.parse(date));
      if (!locale || (locale.length != 2 && locale.length != 5)) {
        locale = this.$page.props.settings.default_locale;
      }
      try {
        return formatted.toLocaleString(locale, {
          timeStyle: 'short',
          hour12: true, // this.$page.props.settings.hour12 == 1
        });
      } catch (err) {
        return formatted.toLocaleString('en-US', {
          timeStyle: 'short',
          hour12: true, // this.$page.props.settings.hour12 == 1
        });
      }
    },
    $datetime(datetime, locale, style) {
      if (!datetime) {
        return '';
      }
      if (this.$page.props.settings?.date_format == 'php') {
        return datetime;
        let dt = datetime.split('T');
        let dt_time = dt[1].split(':');
        return dt[0] + ' ' + dt_time[0] + ':' + dt_time[1];
      }

      let formatted = new Date(Date.parse(datetime));
      try {
        let od = datetime.split('T')[0].split('-');
        let ot = datetime.split('T')[1].split(':');
        formatted = new Date(Number(od[0]), Number(od[1]) - 1, Number(od[2]), Number(ot[0]), Number(ot[1]), 0, 0);
      } catch (err) {
        console.error('Failed to parse date time.');
      }

      if (!locale || (locale.length != 2 && locale.length != 5)) {
        locale = this.$page.props.settings.default_locale;
      }
      try {
        return formatted.toLocaleString(locale, {
          timeStyle: 'short',
          dateStyle: style ? style : 'medium',
          hour12: true, // this.$page.props.settings.hour12 == 1,
          // timeZone: this.$page.props.settings.timezone || 'UTC',
        });
      } catch (err) {
        return formatted.toLocaleString('en-US', {
          timeStyle: 'short',
          dateStyle: style ? style : 'medium',
          hour12: true, // this.$page.props.settings.hour12 == 1,
          //   timeZone: this.$page.props.settings.timezone || 'UTC',
        });
      }
    },
    $can(permissions) {
      let user = this.$page.props.is_impersonating ? this.$page.props.user : this.$page.props.auth.user;
      if (user && user.roles.find(r => r.name == 'admin')) {
        return true;
      }
      let allow = false;
      if (!Array.isArray(permissions)) {
        permissions = [permissions];
      }
      if (permissions && permissions.length > 0) {
        if (permissions.includes('all')) {
          allow = true;
        } else {
          permissions.map(p => {
            if (user && user.all_permissions && user.all_permissions.includes(p)) {
              allow = true;
            }
          });
        }
      }
      return allow;
    },
  },
};

export default mixin;
