@props(['active', 'icon', 'label', 'href'])

@php
$classes = ($active ?? false)
            ? 'bg-brand-primary/10 text-brand-primary border-brand-primary/20 shadow-sm'
            : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-gray-700 dark:hover:text-gray-200 border-transparent';
@endphp

<a {{ $attributes->merge(['href' => $href, 'class' => 'flex items-center space-x-3 px-4 py-3.5 rounded-2xl border transition-all duration-200 group ' . $classes]) }} wire:navigate>
    <div class="shrink-0 flex items-center justify-center">
        <svg class="w-6 h-6 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}" />
        </svg>
    </div>
    <span x-show="sidebarOpen" x-transition.opacity class="font-bold text-sm tracking-tight whitespace-nowrap">{{ $label }}</span>
    
    @if($active)
        <div x-show="sidebarOpen" class="ml-auto w-1.5 h-1.5 rounded-full bg-brand-primary shadow-lg shadow-brand-primary/50"></div>
    @endif
</a>
