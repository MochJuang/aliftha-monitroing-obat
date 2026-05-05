<x-app-layout>
    <x-slot name="header">Tambah Sumber Dana</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('rko.sumber-dana.store') }}">
            @include('funding-sources._form')
        </form>
    </section>
</x-app-layout>
