@props(['value', 'date' => 'j F Y', 'time' => 'H:i', 'showTime' => true])

@if($value)
    @php
        $dt = $value instanceof \DateTimeInterface
            ? \Illuminate\Support\Carbon::instance($value)
            : \Illuminate\Support\Carbon::parse($value);
        $dt = $dt->timezone(config('app.timezone'))->locale('nl');
    @endphp
    <time datetime="{{ $dt->toIso8601String() }}">
        {{ $dt->translatedFormat($date) }}@if($showTime) · {{ $dt->translatedFormat($time) }}@endif
    </time>
@else
    —
@endif
