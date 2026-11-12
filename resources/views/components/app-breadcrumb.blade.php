@props(['items' => []])

@if(count($items) > 0)
    <nav aria-label="Breadcrumb" {{ $attributes->class(['min-w-0 flex-1']) }} data-tour="page-breadcrumb">
        <ol class="flex flex-wrap items-center gap-1 text-sm leading-snug">
            @foreach($items as $crumb)
                <li class="flex items-center gap-1 min-w-0">
                    @if(!$loop->first)
                        <span class="text-[#9E9790] select-none" aria-hidden="true">/</span>
                    @endif
                    @if(!empty($crumb['url']))
                        <a href="{{ $crumb['url'] }}" class="font-medium text-[#1A6B6B] hover:underline truncate max-w-[10rem] sm:max-w-none">{{ $crumb['label'] }}</a>
                    @else
                        <span class="font-semibold text-[#2C2C2C] truncate" @if($loop->last) aria-current="page" @endif>{{ $crumb['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
        @php $last = $items[array_key_last($items)] ?? null; @endphp
        @if(!empty($last['subtitle']))
            <p class="text-xs mt-0.5 text-[#6B6560] line-clamp-2">{{ $last['subtitle'] }}</p>
        @endif
    </nav>
@endif
