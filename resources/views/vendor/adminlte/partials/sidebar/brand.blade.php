@php use Illuminate\Support\Str; @endphp
<a href="{{ url('/') }}" class="brand-link">
    @if ($hotel && $hotel->logo)
        @if (Str::startsWith($hotel->logo, ['http://', 'https://']))
            <img src="{{ $hotel->logo }}?v={{ Str::random(6) }}" alt="Logo Hotel"
                class="brand-image img-circle elevation-3" style="opacity:.8;object-fit:cover;width:40px;height:40px;">
        @elseif (Str::startsWith($hotel->logo, ['logos/']))
            <img src="{{ asset('storage/' . $hotel->logo) }}?v={{ Str::random(6) }}" alt="Logo Hotel"
                class="brand-image img-circle elevation-3" style="opacity:.8;object-fit:cover;width:40px;height:40px;">
        @else
            <img src="{{ asset('storage/logos/' . $hotel->logo) }}?v={{ Str::random(6) }}" alt="Logo Hotel"
                class="brand-image img-circle elevation-3" style="opacity:.8;object-fit:cover;width:40px;height:40px;">
        @endif
    @else
        <img src="{{ asset('img/logo-default.png') }}" alt="Logo Hotel" class="brand-image img-circle elevation-3"
            style="opacity:.8;object-fit:cover;width:40px;height:40px;">
    @endif
    <span class="brand-text font-weight-light">{{ $hotel->nombre ?? 'Hotel' }}</span>
</a>
