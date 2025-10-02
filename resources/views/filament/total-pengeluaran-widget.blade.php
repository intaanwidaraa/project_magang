@php
    $stats = $this->getStats();
@endphp

<x-filament-widgets::widget class="fi-wi-stats-overview">
    <div
        @if ($this->isLazy())
            wire:init="loadStats"
        @endif
        class="fi-wi-stats-overview-stats-container grid gap-6 md:grid-cols-1" {{-- Dibuat 1 kolom saja --}}
    >
        @if ($this->isLazy() && ! $this->isStatsLoaded())
            {{-- Tampilan loading --}}
            @foreach (range(1, count($stats)) as $stat)
                <div class="h-16 w-full animate-pulse rounded-xl bg-gray-100 dark:bg-gray-800"></div>
            @endforeach
        @else
            {{-- Tampilan statistik --}}
            @foreach ($stats as $stat)
                {{-- Modifikasi ada di baris x-filament::card di bawah --}}
                <x-filament::card class="p-4"> {{-- [FIX] Padding dikecilkan dari p-6 ke p-4 --}}
                    <div class="flex items-center gap-x-3">
                        @if ($icon = $stat->getIcon())
                            <x-filament::icon
                                :icon="$icon"
                                class="fi-wi-stats-overview-stat-icon h-8 w-8"
                                :style="'color: ' . \Filament\Support\get_color_css_variable($stat->getColor(), 500)"
                            />
                        @endif

                        <div class="flex-1">
                            <div
                                class="fi-wi-stats-overview-stat-label text-sm font-medium text-gray-500 dark:text-gray-400"
                            >
                                {{ $stat->getLabel() }}
                            </div>

                            <div class="text-2xl font-semibold tracking-tight text-gray-950 dark:text-white"> {{-- [FIX] Font dikecilkan dari text-3xl --}}
                                {{ $stat->getValue() }}
                            </div>
                        </div>

                        @if ($chart = $stat->getChart())
                            <div class="fi-wi-stats-overview-stat-chart absolute bottom-0 inset-x-0 h-14">
                                @livewire($chart['type'], $chart['data'])
                            </div>
                        @endif
                    </div>
                </x-filament::card>
            @endforeach
        @endif
    </div>
</x-filament-widgets::widget>