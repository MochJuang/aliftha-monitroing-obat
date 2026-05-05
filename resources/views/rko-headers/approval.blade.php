<x-app-layout>
    <x-slot name="header">Persetujuan RKO</x-slot>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('rko.header.approval.update', $rkoHeader) }}">
            @method('PUT')
            @include('rko-headers._approval_form')
        </form>
    </section>
</x-app-layout>
