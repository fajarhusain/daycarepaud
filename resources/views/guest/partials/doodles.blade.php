@props(['variant' => 'hero'])
<div class="guest-doodles" aria-hidden="true">
    @if($variant === 'hero')
        @include('guest.partials.illustration', [
            'name' => 'deco.cloud',
            'class' => 'guest-illustration-deco top-6 right-[10%] w-20 h-auto',
            'eager' => true,
        ])
        @include('guest.partials.illustration', [
            'name' => 'deco.tree-left',
            'class' => 'guest-illustration-deco bottom-[15%] left-[3%] w-24 h-auto hidden sm:block',
            'eager' => true,
        ])
        @include('guest.partials.illustration', [
            'name' => 'deco.tree-right',
            'class' => 'guest-illustration-deco bottom-[25%] right-[8%] w-16 h-auto',
            'eager' => true,
        ])
    @elseif($variant === 'footer')
        @include('guest.partials.illustration', [
            'name' => 'deco.footer',
            'class' => 'guest-illustration-deco bottom-2 left-[8%] w-28 h-auto opacity-[0.12]',
        ])
        @include('guest.partials.illustration', [
            'name' => 'deco.tree-right',
            'class' => 'guest-illustration-deco top-6 right-[12%] w-20 h-auto opacity-[0.1]',
        ])
    @endif
</div>
