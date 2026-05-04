<x-app-layout>
    <x-slot name="header">Tambah Mutasi Obat</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('faskes.distribusi.store') }}">
            @include('stock-distributions._form')
        </form>
    </section>
</x-app-layout>
