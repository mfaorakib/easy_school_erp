@php
    $__toasts = [];
    if (session('status')) {
        $__toasts[] = ['type' => 'success', 'title' => __('ui.success'), 'msg' => session('status')];
    }
    if (session('error')) {
        $__toasts[] = ['type' => 'error', 'title' => __('ui.error'), 'msg' => session('error')];
    }
    if ($errors->any()) {
        foreach ($errors->all() as $__e) {
            $__toasts[] = ['type' => 'error', 'title' => __('ui.error'), 'msg' => $__e];
        }
    }
@endphp
@if(count($__toasts))
    <div class="toast-stack" id="esToastStack" aria-live="polite">
        @foreach($__toasts as $__t)
            <div class="toast toast-{{ $__t['type'] }}">
                <span class="toast-icon"><i class="fa-solid {{ $__t['type'] === 'success' ? 'fa-check' : 'fa-xmark' }}" aria-hidden="true"></i></span>
                <div class="toast-body">
                    <div class="toast-title">{{ $__t['title'] }}</div>
                    <div class="toast-msg">{{ $__t['msg'] }}</div>
                </div>
                <button type="button" class="toast-close" aria-label="{{ __('ui.close') }}" onclick="this.closest('.toast').remove()">&times;</button>
                <div class="toast-bar"><i></i></div>
            </div>
        @endforeach
    </div>
    <script>
        (function () {
            var stack = document.getElementById('esToastStack');
            if (!stack) return;
            stack.querySelectorAll('.toast').forEach(function (el, i) {
                setTimeout(function () { el.classList.add('show'); }, 30 + i * 120);
                var remove = function () {
                    el.classList.remove('show');
                    setTimeout(function () { el.remove(); }, 350);
                };
                setTimeout(remove, 5000 + i * 120);
            });
        })();
    </script>
@endif
