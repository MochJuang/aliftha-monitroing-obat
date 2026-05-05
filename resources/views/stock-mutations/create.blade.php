<x-app-layout>
    <x-slot name="header">Tambah Mutasi Stok</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('transaksi.mutasi.store') }}" class="space-y-6">
            @csrf
            @include('stock-mutations._form')
        </form>
    </section>
</x-app-layout>
