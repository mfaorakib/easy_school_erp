@extends('layouts.admin')
@section('title', __('ui.designations'))

@section('content')
<div class="page-head"><h1>{{ __('ui.designations') }}</h1>
    <a href="{{ route('hr.designations.create') }}" class="btn">+ {{ __('ui.add') }}</a></div>

<div class="card">
    @if($designations->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($designations as $d)
            <tr>
                <td><strong>{{ $d->title }}</strong></td>
                <td class="actions">
                    <a href="{{ route('hr.designations.edit', $d) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('hr.designations.destroy', $d) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endsection
