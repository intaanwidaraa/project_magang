<x-filament-panels::page.simple>

    {{-- [UBAH DI SINI] Gunakan slot "heading" untuk mengganti judul "Sign in" --}}
    <x-slot name="heading">
        <h1 class="text-2xl font-bold tracking-tight text-center">
            Sistem Gudang dan Persediaan Sparepart
        </h1>
    </x-slot>

    {{-- [OPSIONAL] Anda juga bisa menambahkan sub-judul di bawahnya --}}
    <x-slot name="subheading">
        <p class="text-sm text-gray-500 text-center">
            Silakan Login untuk melanjutkan
        </p>
    </x-slot>
    
    {{-- Bagian di bawah ini biarkan saja, ini yang menampilkan form dan tombol --}}
    
    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}

            {{ $this->registerAction }}
        </x-slot>
    @endif

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

</x-filament-panels::page.simple>