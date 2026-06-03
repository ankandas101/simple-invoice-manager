@props(['url'])
<tr>
  <td class="header">
    <a href="{{ $url }}" style="display: inline-block;">
      @php
        $logo = session('mail_logo') ?? get_settings('invoice_logo') ?: null;
        $name = session('mail_name') ?? get_settings('name') ?: config('app.name');
      @endphp
      @if ($logo)
        <img src="{{ asset($logo) }}" class="logo" alt="{{ $name }}">
      @else
        {{ $name ?? $slot }}
      @endif
    </a>
  </td>
</tr>
