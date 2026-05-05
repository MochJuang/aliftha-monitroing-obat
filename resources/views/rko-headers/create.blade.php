<x-app-layout>
    <x-slot name="header">Pengajuan RKO</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('rko.header.store') }}">
            @include('rko-headers._form')
        </form>
    </section>
</x-app-layout>
