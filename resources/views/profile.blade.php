<x-app-layout>
    <div class="max-w-xl space-y-6">
        <h1 class="text-xl font-semibold text-zinc-900">Profil Saya</h1>

        <div class="p-6 bg-white border border-zinc-200 rounded-lg">
            <livewire:profile.update-profile-information-form />
        </div>

        <div class="p-6 bg-white border border-zinc-200 rounded-lg">
            <livewire:profile.update-password-form />
        </div>

        <div class="p-6 bg-white border border-zinc-200 rounded-lg">
            <livewire:profile.delete-user-form />
        </div>
    </div>
</x-app-layout>
