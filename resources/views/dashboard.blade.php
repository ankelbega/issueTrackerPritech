@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <h1>Dashboard</h1>
    </div>

    <div class="card">
        {{ __("You're logged in!") }}
    </div>
@endsection
