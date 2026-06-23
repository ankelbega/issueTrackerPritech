@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="card" style="margin-bottom: 1.5rem;">
        @include('profile.partials.update-profile-information-form')
    </div>

    <div class="card" style="margin-bottom: 1.5rem;">
        @include('profile.partials.update-password-form')
    </div>

    <div class="card">
        @include('profile.partials.delete-user-form')
    </div>
@endsection
