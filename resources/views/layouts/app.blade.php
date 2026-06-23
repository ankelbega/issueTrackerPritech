<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@hasSection('title')@yield('title') - @endif{{ config('app.name', 'Issue Tracker') }}</title>

        <!-- Inter font -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

        <!-- Compiled Tailwind (still used by the untouched Breeze auth/profile partials) -->
        @vite(['resources/css/app.css'])

        <!-- Design system -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- Alpine.js for lightweight interactivity (dropdowns, dismissible flashes, etc.) -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body>
        <!-- Sidebar navigation -->
        <aside class="sidebar">
            <div class="sidebar-logo">Issue Tracker</div>

            <nav class="sidebar-nav">
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
                <p style="color: rgba(255, 255, 255, 0.5); font-size: 0.8125rem; margin-bottom: 0.5rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ auth()->user()->email }}
                </p>

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
            @include('layouts.partials.flash')

            {{-- Every page builds its own <h1> inside @section('content'), usually
                 paired with an action button via .page-header. @yield('title') only
                 feeds the <title> tag above — rendering it again here would show
                 every page's heading twice. --}}
            @yield('content')
        </main>
    </body>
</html>
