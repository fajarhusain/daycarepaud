<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            </div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Buat Slip Gaji</h2>
        </div>
    </x-slot>
    <div class="app-page-shell">
        <div class="mb-4"><a href="{{ route('admin.gaji-guru.index') }}" class="btn-secondary text-sm">← Gaji Guru</a></div>
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('admin.gaji-guru.store') }}" class="card p-6 space-y-4">
            @csrf
            @include('admin.gaji-guru._form')
            <div class="flex flex-wrap gap-3 pt-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <a href="{{ route('admin.gaji-guru.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan Draft</button>
                <button type="submit" name="approve" value="1" class="btn-primary" style="background:#0d5c5c;">Simpan &amp; Setujui</button>
            </div>
        </form>
    </div>
</x-app-layout>
