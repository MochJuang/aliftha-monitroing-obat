<x-app-layout>
    <x-slot name="header">Tambah Obat</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('medicines.store') }}">
            @include('medicines._form')
        </form>
    </section>
</x-app-layout>
