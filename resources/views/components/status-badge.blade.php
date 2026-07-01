@props(['status'])

@php
    $styles = [
        'baru' => 'bg-zinc-100 text-zinc-600',
        'diproses' => 'bg-blue-50 text-blue-700',
        'selesai' => 'bg-emerald-50 text-emerald-700',
        'ditolak' => 'bg-red-50 text-red-700',
    ];

    $labels = [
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium '.($styles[$status] ?? 'bg-zinc-100 text-zinc-600')]) }}>
    {{ $labels[$status] ?? $status }}
</span>
