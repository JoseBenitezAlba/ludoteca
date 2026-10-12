@props(['value' => 0, 'size' => 'text-base'])
@php $pct = max(0, min(100, ($value / 5) * 100)); @endphp
<span {{ $attributes->merge(['class' => "relative inline-block align-middle $size"]) }} title="{{ $value ? number_format($value, 1, ',', '') : 'Sin nota' }}">
    <span class="stars-base">★★★★★</span>
    <span class="stars-fill" style="width: {{ $pct }}%">★★★★★</span>
</span>
