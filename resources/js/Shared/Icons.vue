<template>
  <svg v-if="name == 'link'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path
      d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z"
    />
  </svg>
  <svg v-else-if="name == 'home'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path
      d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"
    />
  </svg>
  <svg v-else-if="name == 'copy'" :class="size || 'h-4 w-4'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
    <path
      fill-rule="evenodd"
      d="M10.5 3A1.501 1.501 0 0 0 9 4.5h6A1.5 1.5 0 0 0 13.5 3h-3Zm-2.693.178A3 3 0 0 1 10.5 1.5h3a3 3 0 0 1 2.694 1.678c.497.042.992.092 1.486.15 1.497.173 2.57 1.46 2.57 2.929V19.5a3 3 0 0 1-3 3H6.75a3 3 0 0 1-3-3V6.257c0-1.47 1.073-2.756 2.57-2.93.493-.057.989-.107 1.487-.15Z"
      clip-rule="evenodd"
    />
  </svg>
  <svg
    v-else-if="name == 'copied'"
    :class="size || 'h-4 w-4'"
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    stroke-width="1.5"
    stroke="currentColor"
  >
    <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75"
    />
  </svg>
  <svg
    v-else-if="name == 'cog'"
    :class="size || 'h-4 w-4'"
    class="fill-current"
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 20 20"
    fill="currentColor"
  >
    <path
      fill-rule="evenodd"
      d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"
      clip-rule="evenodd"
    />
  </svg>
  <svg v-else-if="name == 'plus'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" />
  </svg>
  <svg v-else-if="name == 'cart'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path
      d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"
    />
  </svg>
  <svg v-else-if="name == 'bag'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path
      d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z"
    />
  </svg>
  <svg v-else-if="name == 'user'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
  </svg>
  <svg v-else-if="name == 'add-user'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
    <path
      d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"
    />
  </svg>
  <svg v-else-if="name == 'users'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path
      d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"
    />
  </svg>
  <svg v-else-if="name == 'group'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
    <path
      d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"
    />
  </svg>
  <svg v-else-if="name == 'view'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
    <path
      d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
    />
  </svg>
  <svg v-else-if="name == 'edit'" :class="size || 'h-4 w-4'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
    <path
      d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z"
    />
    <path
      d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z"
    />
  </svg>
  <svg
    v-else-if="name == 'rate'"
    :class="size || 'h-4 w-4'"
    class="fill-current"
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 20 20"
    fill="currentColor"
  >
    <path
      fill-rule="evenodd"
      d="M5 2a2 2 0 00-2 2v14l3.5-2 3.5 2 3.5-2 3.5 2V4a2 2 0 00-2-2H5zm2.5 3a1.5 1.5 0 100 3 1.5 1.5 0 000-3zm6.207.293a1 1 0 00-1.414 0l-6 6a1 1 0 101.414 1.414l6-6a1 1 0 000-1.414zM12.5 10a1.5 1.5 0 100 3 1.5 1.5 0 000-3z"
      clip-rule="evenodd"
    />
  </svg>
  <svg v-else-if="name == 'cross'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path
      d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
    />
  </svg>
  <svg v-else-if="name == 'search'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" />
  </svg>
  <svg v-else-if="name == 'printer'" :class="size || 'h-4 w-4'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
    <path
      fill-rule="evenodd"
      d="M7.875 1.5C6.839 1.5 6 2.34 6 3.375v2.99c-.426.053-.851.11-1.274.174-1.454.218-2.476 1.483-2.476 2.917v6.294a3 3 0 0 0 3 3h.27l-.155 1.705A1.875 1.875 0 0 0 7.232 22.5h9.536a1.875 1.875 0 0 0 1.867-2.045l-.155-1.705h.27a3 3 0 0 0 3-3V9.456c0-1.434-1.022-2.7-2.476-2.917A48.716 48.716 0 0 0 18 6.366V3.375c0-1.036-.84-1.875-1.875-1.875h-8.25ZM16.5 6.205v-2.83A.375.375 0 0 0 16.125 3h-8.25a.375.375 0 0 0-.375.375v2.83a49.353 49.353 0 0 1 9 0Zm-.217 8.265c.178.018.317.16.333.337l.526 5.784a.375.375 0 0 1-.374.409H7.232a.375.375 0 0 1-.374-.409l.526-5.784a.373.373 0 0 1 .333-.337 41.741 41.741 0 0 1 8.566 0Zm.967-3.97a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H18a.75.75 0 0 1-.75-.75V10.5ZM15 9.75a.75.75 0 0 0-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 0 0 .75-.75V10.5a.75.75 0 0 0-.75-.75H15Z"
      clip-rule="evenodd"
    />
  </svg>
  <svg v-else-if="name == 'menu'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path
      d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
    />
  </svg>
  <svg v-else-if="name == 'adj'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
    <path
      d="M5 4a1 1 0 00-2 0v7.268a2 2 0 000 3.464V16a1 1 0 102 0v-1.268a2 2 0 000-3.464V4zM11 4a1 1 0 10-2 0v1.268a2 2 0 000 3.464V16a1 1 0 102 0V8.732a2 2 0 000-3.464V4zM16 3a1 1 0 011 1v7.268a2 2 0 010 3.464V16a1 1 0 11-2 0v-1.268a2 2 0 010-3.464V4a1 1 0 011-1z"
    />
  </svg>
  <svg v-else-if="name == 'email'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
  </svg>
  <svg v-else-if="name == 'info'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path
      d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
    />
  </svg>
  <svg v-else-if="name == 'folder'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
  </svg>
  <svg
    v-else-if="name == 'save'"
    xmlns="http://www.w3.org/2000/svg"
    class="h-4 w-4"
    fill="none"
    viewBox="0 0 24 24"
    stroke="currentColor"
    stroke-width="2"
  >
    <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"
    />
  </svg>
  <svg
    v-else-if="name == 'col'"
    xmlns="http://www.w3.org/2000/svg"
    class="h-4 w-4"
    fill="none"
    viewBox="0 0 24 24"
    stroke="currentColor"
    stroke-width="2"
  >
    <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
    />
  </svg>
  <svg
    v-else-if="name == 'open-folder'"
    :class="size || 'h-4 w-4'"
    class="fill-current"
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 20 20"
  >
    <path d="M2 6a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1H8a3 3 0 00-3 3v1.5a1.5 1.5 0 01-3 0V6z" />
    <path d="M6 12a2 2 0 012-2h8a2 2 0 012 2v2a2 2 0 01-2 2H2h2a2 2 0 002-2v-2z" />
  </svg>
  <svg
    v-else-if="name == 'chevron-down'"
    :class="size || 'h-4 w-4'"
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 20 20"
    fill="currentColor"
  >
    <path
      fill-rule="evenodd"
      d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
      clip-rule="evenodd"
    />
  </svg>
  <svg
    v-else-if="name == 'down'"
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
      d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181"
    />
  </svg>
  <svg
    v-else-if="name == 'up'"
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
      d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"
    />
  </svg>

  <svg
    v-else-if="name == 'chart-bar'"
    :class="size || 'h-4 w-4'"
    class="fill-current"
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 20 20"
  >
    <path
      d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"
    />
  </svg>
  <svg
    v-else-if="name == 'chart-pie'"
    :class="size || 'h-4 w-4'"
    class="fill-current"
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 20 20"
  >
    <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
    <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
  </svg>
  <svg
    v-else-if="name == 'bell'"
    xmlns="http://www.w3.org/2000/svg"
    class="h-5 w-5"
    fill="none"
    viewBox="0 0 24 24"
    stroke="currentColor"
    stroke-width="2"
  >
    <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
    />
  </svg>
  <svg v-else-if="name == 'in'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path
      d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z"
    />
  </svg>
  <svg v-else-if="name == 'out'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path
      d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z"
    />
  </svg>
  <svg v-else-if="name == 'plane'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path
      d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"
    />
  </svg>
  <svg v-else-if="name == 'upload'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path
      d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z"
    />
  </svg>
  <svg v-else-if="name == 'clip'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path
      d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z"
    />
  </svg>
  <svg v-else-if="name == 'doc'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" />
  </svg>
  <svg
    v-else-if="name == 'doc-text'"
    :class="size || 'h-4 w-4'"
    class="fill-current"
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 20 20"
  >
    <path
      fill-rule="evenodd"
      d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
    />
  </svg>
  <svg v-else-if="name == 'tick'" :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <polygon points="0 11 2 9 7 14 18 3 20 5 7 18" />
  </svg>
  <svg v-else-if="name == 'vdots'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
  </svg>
  <svg
    class="fill-current"
    v-else-if="name == 'xlsx'"
    :class="size || 'h-4 w-4'"
    viewBox="0 0 548.29 548.291"
    xmlns="http://www.w3.org/2000/svg"
  >
    <g>
      <g>
        <path
          d="M472.929,131.399c-0.031-2.518-0.823-5-2.55-6.961L364.655,3.677c-0.021-0.031-0.052-0.042-0.083-0.073
			c-0.631-0.707-1.355-1.287-2.132-1.795c-0.231-0.152-0.462-0.286-0.704-0.421c-0.683-0.365-1.386-0.666-2.121-0.887
			c-0.198-0.06-0.377-0.139-0.577-0.194C358.23,0.121,357.41,0,356.571,0H96.757C84.904,0,75.255,9.651,75.255,21.502V526.79
			c0,11.858,9.649,21.501,21.502,21.501h354.776c11.862,0,21.502-9.643,21.502-21.501V132.6
			C473.035,132.199,472.973,131.795,472.929,131.399z M165.545,508.217l-9.352-18.703c-3.83-7.209-6.278-12.574-9.192-18.547h-0.307
			c-2.145,5.973-4.749,11.338-7.966,18.547l-8.585,18.703h-26.662l29.888-52.273l-28.812-51.029h26.822l9.042,18.861
			c3.063,6.276,5.364,11.327,7.821,17.155h0.3c2.457-6.594,4.446-11.188,7.047-17.155l8.741-18.861h26.662l-29.118,50.414
			l30.657,52.889H165.545z M269.591,508.217h-64.526V404.915h23.452v83.67h41.074V508.217z M308.66,509.739
			c-11.795,0-23.439-3.064-29.263-6.282l4.743-19.308c6.287,3.212,15.947,6.43,25.899,6.43c10.735,0,16.399-4.436,16.399-11.192
			c0-6.425-4.903-10.109-17.316-14.551c-17.161-5.973-28.354-15.475-28.354-30.498c0-17.623,14.709-31.108,39.087-31.108
			c11.639,0,20.232,2.446,26.353,5.212l-5.207,18.852c-4.142-1.99-11.496-4.908-21.613-4.908c-10.115,0-15.013,4.594-15.013,9.969
			c0,6.587,5.822,9.5,19.155,14.551c18.237,6.752,26.814,16.246,26.814,30.805C350.346,495.03,337.012,509.739,308.66,509.739z
			 M420.53,508.217l-9.354-18.703c-3.833-7.209-6.279-12.574-9.198-18.547h-0.294c-2.142,5.973-4.755,11.338-7.975,18.547
			l-8.583,18.703h-26.665l29.885-52.273l-28.814-51.029h26.823l9.051,18.861c3.061,6.276,5.354,11.327,7.806,17.155h0.31
			c2.446-6.594,4.44-11.188,7.05-17.155l8.736-18.861h26.666l-29.117,50.414l30.65,52.889H420.53z M96.757,365.085V21.507H345.82
			v110.015c0,5.935,4.809,10.75,10.751,10.75h94.962l0.01,222.813H96.757z"
        />
        <path
          d="M310.933,138.004h-83.765h-94.516v40.316v5.375v34.94v5.375v34.939v5.375v40.319h89.141h5.375h83.765h5.376h89.14v-45.694
			v-34.939v-40.315v-5.375v-40.316H310.933z M310.933,218.636h-83.765v-34.94h83.765V218.636z M138.028,143.38h83.765v34.94h-83.765
			V143.38z M138.028,183.696h83.765v34.94h-83.765V183.696z M138.028,224.011h83.765v34.939h-83.765V224.011z M138.028,299.269
			v-34.943h83.765v34.943H138.028z M227.168,299.269v-34.943h83.765v34.943H227.168z M400.078,299.269h-83.77v-34.943h83.764v34.943
			H400.078z M400.078,218.636h-83.77v-34.94h83.764v34.94H400.078z M316.309,178.32v-34.94h83.764v34.94H316.309z"
        />
      </g>
    </g>
  </svg>
  <svg v-else :class="size || 'h-4 w-4'" class="fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
    <path
      d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 00-1-1h-.5a1.5 1.5 0 010-3H4a1 1 0 001-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z"
    />
  </svg>
</template>

<script>
export default {
  props: ['name', 'size'],
};
</script>
