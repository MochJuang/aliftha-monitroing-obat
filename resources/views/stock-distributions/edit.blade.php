<x-app-layout>
    <x-slot name="header">Edit Distribusi Obat</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('stock-distributions.update', $distribution) }}">
            @method('PUT')
            @include('stock-distributions._form')
        </form>
    </section>
</x-app-layout>
