<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Edit Slip Gaji</h2>
        </div>
    </x-slot>
    <div class="app-page-shell">
        <div class="mb-4"><a href="{{ route('admin.gaji-guru.show', $gaji) }}" class="btn-secondary text-sm">← Detail Slip</a></div>
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('admin.gaji-guru.update', $gaji) }}" class="card p-6 space-y-4">
            @csrf @method('PUT')
            @include('admin.gaji-guru._form', ['gaji' => $gaji])
            <div class="flex flex-wrap gap-3 pt-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <a href="{{ route('admin.gaji-guru.show', $gaji) }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-app-layout>
