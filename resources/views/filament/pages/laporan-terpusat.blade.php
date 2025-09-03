<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    <div class="mt-4">
        {{ $this->table }}
    </div>
</x-filament-panels::page>