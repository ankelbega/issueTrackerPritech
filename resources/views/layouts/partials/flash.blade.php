{{-- Dismissible banners for session('success') / session('error') messages. --}}
@if (session('success'))
    <div x-data="{ show: true }" x-show="show" class="flash-banner flash-success">
        <span>{{ session('success') }}</span>
        <button type="button" @click="show = false" class="flash-dismiss" aria-label="Dismiss">&times;</button>
    </div>
@endif

@if (session('error'))
    <div x-data="{ show: true }" x-show="show" class="flash-banner flash-error">
        <span>{{ session('error') }}</span>
        <button type="button" @click="show = false" class="flash-dismiss" aria-label="Dismiss">&times;</button>
    </div>
@endif
