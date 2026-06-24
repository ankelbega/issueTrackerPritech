<!DOCTYPE html>
{{-- str_replace converts Laravel's locale format (e.g. "en_US") into the
     hyphenated format the HTML lang attribute expects ("en-US"). --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {{-- This token is read by JavaScript (see issues/show.blade.php) and sent
             back as the X-CSRF-TOKEN header on every AJAX request, so Laravel's
             CSRF protection middleware accepts POST/DELETE fetch() calls. --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- @hasSection checks whether the current view defined a @section('title').
             If it did, show "<page title> - <app name>"; otherwise just the app name. --}}
        <title>@hasSection('title')@yield('title') - @endif{{ config('app.name', 'Issue Tracker') }}</title>

        <!-- Inter font -->
        {{-- preconnect hints let the browser start the connection to Google's font
             servers early, before it even parses the <link rel="stylesheet"> below. --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        {{-- Loads the Inter typeface at the three weights the design system uses:
             400 (body text), 500 (labels/buttons), 600 (headings). --}}
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

        <!-- Compiled Tailwind (still used by the untouched Breeze auth/profile partials) -->
        {{-- @vite compiles/links resources/css/app.css (Tailwind) via Laravel's asset
             bundler. Kept so the Breeze-generated login/register/profile partials,
             which use Tailwind utility classes, still render with their intended styling. --}}
        @vite(['resources/css/app.css'])

        <!-- Design system -->
        {{-- The hand-written design system stylesheet (sidebar, cards, buttons,
             badges, forms, flash banners) used by every issue-tracker page. Served
             directly from /public, so no build step is needed to see changes. --}}
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- Alpine.js for lightweight interactivity (dropdowns, dismissible flashes, etc.) -->
        {{-- defer ensures this script only executes after the HTML has finished
             parsing, so Alpine's x-data/x-show directives are guaranteed to find
             their target elements already in the DOM. --}}
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body>
        <!-- Sidebar navigation -->
        <aside class="sidebar">
            <div class="sidebar-logo">Issue Tracker</div>

            <nav class="sidebar-nav">
                {{-- request()->routeIs('projects.*') matches any route name starting
                     with "projects." (index, show, create, etc.), so the link stays
                     highlighted while browsing anywhere in the Projects section. --}}
                <a href="{{ route('projects.index') }}"
                   class="sidebar-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                    Projects
                </a>
                <a href="{{ route('issues.index') }}"
                   class="sidebar-link {{ request()->routeIs('issues.*') ? 'active' : '' }}">
                    Issues
                </a>
                <a href="{{ route('tags.index') }}"
                   class="sidebar-link {{ request()->routeIs('tags.*') ? 'active' : '' }}">
                    Tags
                </a>
            </nav>

            {{-- Pinned to the bottom of the sidebar via margin-top: auto, separated
                 from the nav links above. --}}
            <div style="margin-top: auto; padding: 0.75rem; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                {{-- auth()->user() is always present here since every page using this
                     layout sits behind the 'auth' middleware (see routes/web.php). --}}
                <p style="color: rgba(255, 255, 255, 0.5); font-size: 0.8125rem; margin-bottom: 0.5rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ auth()->user()->email }}
                </p>

                {{-- Standard Breeze logout: POST to the named 'logout' route. @csrf
                     is required since this is a state-changing POST request. --}}
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" style="background: none; border: none; padding: 0; cursor: pointer; color: rgba(255, 255, 255, 0.5); font-size: 0.8125rem; font-family: inherit;">
                        Log out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main content -->
        <main class="main-content">
            {{-- Renders the dismissible success/error banners, if a flash message
                 was set on the current request's session (e.g. after a redirect). --}}
            @include('layouts.partials.flash')

            {{-- Every page builds its own <h1> inside @section('content'), usually
                 paired with an action button via .page-header. @yield('title') only
                 feeds the <title> tag above — rendering it again here would show
                 every page's heading twice. --}}
            @yield('content')
        </main>
    </body>
</html>
