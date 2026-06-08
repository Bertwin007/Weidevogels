@can('delete', $observation)
<form
    action="{{ $action ?? route('annotate.destroy', $observation) }}"
    method="post"
    style="display:inline"
    onsubmit="return confirm('{{ $confirm ?? 'Weet je zeker dat je dit moment definitief wilt verwijderen?' }}');"
>
    @csrf
    @method('DELETE')
    <button type="submit" class="{{ $class ?? '' }}" style="{{ $style ?? 'background:none;border:none;color:#7f1d1d;cursor:pointer;text-decoration:underline;font:inherit' }}">
        {{ $label ?? 'Verwijderen' }}
    </button>
</form>
@endcan
