<x-app-layout>
    <x-slot name="header">Edit RKO Header</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('rko.header.update', $rkoHeader) }}">
            @method('PUT')
            @include('rko-headers._form')
        </form>
    </section>
</x-app-layout>
