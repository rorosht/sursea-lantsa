@extends('layouts.auth')

@section('title', 'Log In - SURSEA Lantsa Hub')

@section('content')
@php $activeRole = old('role', 'passenger'); @endphp

<aside class="auth-side">
    <p class="auth-brand">SURSEA LANTSA HUB</p>
    <h1>Your next crossing starts here.</h1>
    <p class="auth-text">Plan your island trip, reserve your seat, and keep your tickets together.</p>

    <ul class="auth-steps">
        <li><span>01</span> Explore routes and departures</li>
        <li><span>02</span> Reserve a seat or send cargo</li>
        <li><span>03</span> Keep your ticket ready to board</li>
    </ul>

    <p class="auth-foot">Connecting Surigao City and its island communities.</p>
</aside>

<section class="auth-card">
    <h2>Welcome back</h2>
    <p class="muted">Choose how you use SURSEA.</p>

    @if(session('status'))
        <div class="alert success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert error-box">
            @foreach($errors->all() as $message)
                <div>{{ $message }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login.submit') }}">
        @csrf
        <input type="hidden" name="role" id="roleInput" value="{{ $activeRole }}">

        <span class="label">Log in as</span>
        <div class="role-tabs">
            <button type="button" data-role-tab="passenger" class="{{ $activeRole === 'passenger' ? 'active' : '' }}">Passenger</button>
            <button type="button" data-role-tab="operator" class="{{ $activeRole === 'operator' ? 'active' : '' }}">Operator</button>
        </div>

        <div class="auth-field">
            <label for="username">Username or email</label>
            <input type="text" id="username" name="username" value="{{ old('username', 'Robert') }}" placeholder="Enter username or email" required>
        </div>

        <div class="auth-field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" value="password" placeholder="••••••••••••" required>
        </div>

        <a href="#" class="forgot">Forgot password?</a>

        <button
            type="submit"
            id="submitBtn"
            class="btn btn-blue btn-block"
            data-passenger-text="Log in as passenger"
            data-operator-text="Log in as operator"
        >{{ $activeRole === 'operator' ? 'Log in as operator' : 'Log in as passenger' }}</button>

        <p
            class="muted small-note"
            id="roleHint"
            data-passenger-text="Access your reservations, tickets, and trip updates."
            data-operator-text="Manage your lantsa trips, manifests, check-ins, and cargo."
        >{{ $activeRole === 'operator' ? 'Manage your lantsa trips, manifests, check-ins, and cargo.' : 'Access your reservations, tickets, and trip updates.' }}</p>
    </form>

    <p class="switch-link">Don’t have an account? <a href="{{ route('register') }}">Register</a></p>

</section>
@endsection

