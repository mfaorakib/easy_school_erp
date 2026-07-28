@extends('layouts.admin')
@section('title', __('ui.staff'))

@section('content')
<div class="page-head">
    <h1>{{ __('ui.staff') }}</h1>
    <a href="{{ route('hr.staff.create') }}" class="btn">+ {{ __('ui.add') }} {{ __('ui.staff') }}</a>
</div>

<div class="card">
    @if($staff->isEmpty())
        <div class="empty">{{ __('ui.no_records') }}</div>
    @else
    <div class="overflow-x-auto">
    <table>
        <thead><tr><th>{{ __('ui.name') }}</th><th>Role</th><th>Designation</th><th>Department</th><th>Mobile</th><th></th></tr></thead>
        <tbody>
        @foreach($staff as $member)
            <tr>
                <td><strong>{{ $member->displayName() }}</strong></td>
                <td><span class="badge">{{ optional($member->user)->getRoleNames()->first() ?? '—' }}</span></td>
                <td>{{ optional($member->designation)->title ?? '—' }}</td>
                <td>{{ optional($member->department)->name ?? '—' }}</td>
                <td>{{ $member->mobile ?? '—' }}</td>
                <td class="actions">
                    <a href="{{ route('hr.staff.show', $member->id) }}" class="btn btn-sm btn-ghost">{{ __('ui.view') }}</a>
                    <a href="{{ route('hr.staff.edit', $member) }}" class="btn btn-sm btn-ghost">{{ __('ui.edit') }}</a>
                    <form method="POST" action="{{ route('hr.staff.destroy', $member) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    <div style="margin-top:1rem">{{ $staff->links() }}</div>
    @endif
</div>
@endsection
