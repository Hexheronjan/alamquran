@props(['label', 'value'])

<div class="rounded-xl border border-gray-100 bg-gray-50/70 px-4 py-3 transition-colors hover:border-teal-100 hover:bg-teal-50/50">
    <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">{{ $label }}</p>
    <p class="text-sm font-semibold leading-5 text-gray-800 break-words">{{ $value }}</p>
</div>
