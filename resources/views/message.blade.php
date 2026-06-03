<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/icon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="/apple-touch-icon-180x180.png">

  <title inertia>{{ config('app.name', 'SIM') }}</title>

  <script>
    let mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

    function updateTheme(savedTheme) {
      let theme = 'system';
      try {
        if (!savedTheme) {
          savedTheme = window.localStorage.theme;
        }
        if (savedTheme == 'dark') {
          theme = 'dark';
          document.documentElement.classList.add('dark');
        } else if (savedTheme == 'light') {
          theme = 'light';
          document.documentElement.classList.remove('dark');
        } else if (mediaQuery.matches) {
          document.documentElement.classList.add('dark');
          localStorage.setItem('theme', 'dark');
        } else {
          document.documentElement.classList.remove('dark');
          localStorage.setItem('theme', 'light');
        }
      } catch {
        theme = 'light';
        document.documentElement.classList.remove('dark');
      }
      return theme;
    }

    document.documentElement.setAttribute('data-theme', updateTheme());

    new MutationObserver(([{
      oldValue
    }]) => {
      let newValue = document.documentElement.getAttribute('data-theme');
      if (newValue !== oldValue) {
        try {
          window.localStorage.setItem('theme', newValue);
        } catch {}
        updateTheme(newValue);
      }
    }).observe(document.documentElement, {
      attributeFilter: ['data-theme'],
      attributeOldValue: true
    });

    window.addEventListener('storage', updateTheme);
    mediaQuery.addEventListener('change', updateTheme);
    window.Locale = '{{ app()->getLocale() }}';
  </script>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;400">
  <style>
    .app {
      top: 0px;
      left: 0px;
      right: 0px;
      bottom: 0px;
      width: 100%;
      z-index: 40;
      display: flex;
      position: fixed;
      font-weight: 200;
      font-size: 1.2rem;
      min-height: 100vh;
      align-items: center;
      white-space: pre-wrap;
      flex-direction: column;
      justify-content: center;
      font-family: "Nunito", Arial, Helvetica, sans-serif;
    }

    .app.bg-gray-50 {
      --tw-bg-opacity: 1;
      background-color: rgb(249 250 251 / var(--tw-bg-opacity));
    }

    .dark .app.dark\:bg-gray-900 {
      --tw-bg-opacity: 1;
      background-color: rgb(17 24 39 / var(--tw-bg-opacity));
    }

    .text-gray-700 {
      color: rgb(17 24 39);
    }

    .dark\:text-gray-300 {
      color: rgb(209 213 219);
    }
  </style>
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
  <div id="app" class="app bg-gray-50 dark:bg-gray-900">{{ session('message') ?? 'Notification!' }}</div>
</body>

</html>
