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
        </aside>

        <!-- Main content -->
        <main class="main-content">
            @include('layouts.partials.flash')

            <!-- Generic page title; individual views can still build their own
                 .page-header (title + action button) inside @section('content'). -->
            @hasSection('title')
                <h1>@yield('title')</h1>
            @endif

            @yield('content')
        </main>
    </body>
</html>
