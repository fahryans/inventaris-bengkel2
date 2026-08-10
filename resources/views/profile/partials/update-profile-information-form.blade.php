<section>

    <header>

        <h2 class="text-lg font-medium text-gray-900">
            Informasi Profil
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Perbarui informasi profil akun Anda.
        </p>

    </header>

    <form method="POST"
          action="{{ route('profile.update') }}"
          class="mt-6 space-y-6">

        @csrf
        @method('PATCH')

        <div>

            <x-input-label
                for="nama"
                value="Nama" />

            <x-text-input
                id="nama"
                name="nama"
                type="text"
                class="mt-1 block w-full"
                :value="old('nama', $user->nama)"
                required
                autofocus />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('nama')" />

        </div>

        <div>

            <x-input-label
                for="email"
                value="Email" />

            <x-text-input
                id="email"
                name="email"
                type="email"
                class="mt-1 block w-full"
                :value="old('email', $user->email)"
                required />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('email')" />

        </div>

        <div class="flex items-center gap-4">

            <x-primary-button>
                Simpan Perubahan
            </x-primary-button>

            @if(session('status') === 'profile-updated')

                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600">

                    Berhasil disimpan.

                </p>

            @endif

        </div>

    </form>

</section>