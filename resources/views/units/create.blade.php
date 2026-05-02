<x-app-layout>
    <x-slot name="header">Tambah Satuan</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('master-obat.satuan.store') }}">
            @include('units._form')
        </form>
    </section>
</x-app-layout>
