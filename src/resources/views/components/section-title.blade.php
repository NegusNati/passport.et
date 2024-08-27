@props(['color' => 'white'])


@php

    $classes = " bg-white";
    if ($color == "blue") {
        $classes = " bg-blue-800";
    }

@endphp


<div class="inline-flex items-center gap-2">
    <span

    class="w-2 h-2 inline-block {{ $classes }} "></span>
    <h1 class="font-bold text-lg">{{ $slot }}</h1>
</div>
