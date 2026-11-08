@extends('layouts.passenger')

@section('title', 'Notifications - SURSEA')

@section('content')
@php
    $unreadCount = count(array_filter($notifications, fn ($n) => $n['status'] === 'Unread'));
@endphp

<div class="container page">
    <div class="schedule-top">
        <div>
            <p class="eyebrow">PASSENGER ALERTS &amp; UPDATES</p>
            <h1 class="page-title">Notifications</h1>
            <p class="lead small">Stay updated on your seat reservations, waiting list promotions, cargo deliveries, and port boarding schedules.</p>
        </div>

        <div class="passenger-head-actions">
            <form method="POST" action="{{ route('passenger.notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn-dark btn-sm">Mark all as read</button>
            </form>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="passenger-stat-row">
        <div class="passenger-stat-card">
            <span>Unread Alerts</span>
            <strong>{{ $unreadCount }}</strong>
            <small>Need your attention</small>
        </div>
        <div class="passenger-stat-card">
            <span>Total Notifications</span>
            <strong>{{ count($notifications) }}</strong>
            <small>Across all trips &amp; cargo</small>
        </div>
        <div class="passenger-stat-card">
            <span>Quick Navigation</span>
            <strong>Live</strong>
            <small>Click any alert to open its module</small>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="notif-filter-tabs" role="tablist">
        <button type="button" class="notif-tab active" data-notif-filter="all">All ({{ count($notifications) }})</button>
        <button type="button" class="notif-tab" data-notif-filter="Unread">Unread ({{ $unreadCount }})</button>
        <button type="button" class="notif-tab" data-notif-filter="Reservation">Reservations</button>
        <button type="button" class="notif-tab" data-notif-filter="Waiting list">Waiting List</button>
        <button type="button" class="notif-tab" data-notif-filter="Cargo">Cargo</button>
    </div>

    {{-- Notification list --}}
    <div class="notif-card-list" id="passengerNotifList">
        @foreach($notifications as $notif)
            <article
                class="notif-card {{ $notif['status'] === 'Unread' ? 'is-unread' : '' }}"
                data-notif-status="{{ $notif['status'] }}"
                data-notif-type="{{ $notif['type'] }}"
            >
                <div class="notif-card-icon">
                    @if($notif['type'] === 'Cargo')
                        @include('partials.icon', ['name' => 'box'])
                    @elseif($notif['type'] === 'Waiting list')
                        @include('partials.icon', ['name' => 'waiting'])
                    @else
                        @include('partials.icon', ['name' => 'ticket'])
                    @endif
                </div>

                <div class="notif-card-body">
                    <div class="notif-card-top">
                        <h3>{{ $notif['title'] }}</h3>
                        <div class="badges">
                            <span class="badge plain grey">{{ $notif['type'] }}</span>
                            <span class="badge {{ \App\Support\SampleData::badge($notif['status']) }}">{{ $notif['status'] }}</span>
                        </div>
                    </div>
                    <p>{{ $notif['detail'] }}</p>
                    <small class="muted">{{ $notif['time'] }}</small>
                </div>

                <div class="notif-card-actions">
                    <a href="{{ $notif['url'] }}" class="btn btn-dark btn-sm">
                        {{ $notif['action_label'] }} @include('partials.icon', ['name' => 'arrow-right'])
                    </a>
                </div>
            </article>
        @endforeach
    </div>
</div>

@include('partials.footer')
@endsection

