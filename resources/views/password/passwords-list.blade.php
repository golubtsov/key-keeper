@php
    /** @var array $passwords */
@endphp
<div class="py-1 ml-2 ml-2">
    @foreach($passwords as $password)
        <div class="px-1 text-white">
            {{$password['login']}}
        </div>
    @endforeach
</div>
