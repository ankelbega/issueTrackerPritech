{{-- Dismissible banners for session('success') / session('error') messages. --}}

{{-- session('success') reads the one-time flash value set by a controller via
     ->with('success', '...') on a redirect. It only exists for the single
     request immediately after the redirect, then disappears automatically. --}}
@if (session('success'))
    {{-- x-data="{ show: true }" gives this banner its own local Alpine state;
         x-show="show" keeps it visible until the dismiss button sets show to false. --}}
    <div x-data="{ show: true }" x-show="show" class="flash-banner flash-success">
        <span>{{ session('success') }}</span>
        {{-- Clicking the button flips this banner's `show` to false, which
             Alpine's x-show then uses to hide the element (no page reload needed). --}}
        <button type="button" @click="show = false" class="flash-dismiss" aria-label="Dismiss">&times;</button>
    </div>
@endif

{{-- Same pattern as above, but for session('error') — used when a controller
     flashes ->with('error', '...') instead of a success message. --}}
@if (session('error'))
    <div x-data="{ show: true }" x-show="show" class="flash-banner flash-error">
        <span>{{ session('error') }}</span>
        <button type="button" @click="show = false" class="flash-dismiss" aria-label="Dismiss">&times;</button>
    </div>
@endif
