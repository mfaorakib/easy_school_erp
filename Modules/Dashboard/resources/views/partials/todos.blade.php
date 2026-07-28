<div class="widget">
  <div class="widget-title"><span>{{ __('ui.my_tasks') }}</span></div>
  <form method="POST" action="{{ route('dashboard.todos.store') }}" style="display:flex;gap:.5rem">
    @csrf
    <input type="text" name="title" maxlength="255" placeholder="{{ __('ui.add_task') }}" required>
    <button class="btn btn-sm" type="submit">+</button>
  </form>
  <ul class="todo-list">
    @forelse($todos as $todo)
      <li class="{{ $todo->is_done ? 'done' : '' }}">
        <form method="POST" action="{{ route('dashboard.todos.toggle', $todo) }}">
          @csrf
          <input type="checkbox" onchange="this.form.submit()" {{ $todo->is_done ? 'checked' : '' }}>
        </form>
        <label>{{ $todo->title }}</label>
        <form method="POST" action="{{ route('dashboard.todos.destroy', $todo) }}" onsubmit="return confirm('Delete?')">
          @csrf @method('DELETE')
          <button class="btn btn-sm btn-danger" type="submit">×</button>
        </form>
      </li>
    @empty
      <li class="dash-empty">{{ __('ui.no_tasks') }}</li>
    @endforelse
  </ul>
</div>
