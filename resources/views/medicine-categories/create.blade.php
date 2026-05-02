<x-app-layout>
    <x-slot name="header">
        Tambah Kategori Obat
    </x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('master-obat.kategori.store') }}">
            @include('medicine-categories._form')
        </form>
    </section>
</x-app-layout>
