@extends('layouts.panel')

@section('title', 'Notifications - SURSEA')
@section('crumb', 'Notifications')

@section('content')
@php
    $user = session('user');
    $isAdmin = ($user['role'] ?? '') === 'admin';
    $notifications = $isAdmin
        ? \App\Support\SampleData::adminNotifications()
        : \App\Support\SampleData::operatorNotifications();
    $unreadCount = count(array_filter($notifications, fn ($n) => $n['status'] === 'Unread'));
    $readCount = count($notifications) - $unreadCount;
    $markAllRoute = $isAdmin ? route('admin.notifications.readAll') : route('operator.notifications.readAll');
@endphp

<div class="page-head">
    <div>
        <p class="eyebrow">{{ $isAdmin ? 'ADMIN ALERTS' : 'OPERATOR ALERTS' }}</p>
        <h1>Notifications</h1>
        <p class="subtitle">
            {{ $isAdmin
                ? 'Operator applications, route approval requests, vessel status alerts, and daily reports. Click any notification to navigate to its module.'
                : 'Seat availability updates, waiting list alerts, and cargo registrations for MV Isla Norte. Click any notification to open its module.' }}
        </p>
    </div>

    <form method="POST" action="{{ $markAllRoute }}">
        @csrf
        <button type="submit" class="btn btn-blue" id="markAllRead">Mark all as read</button>
    </form>
</div>

<div class="stat-grid cols-3">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Unread</p>
            @include('partials.icon', ['name' => 'bell'])
        </div>
        <p class="stat-number" id="unreadStatCount">{{ $unreadCount }}</p>
        <p class="stat-note">Need your attention</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Read</p>
            @include('partials.icon', ['name' => 'check-square'])
        </div>
        <p class="stat-number">{{ $readCount }}</p>
        <p class="stat-note">Reviewed notifications</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total alerts</p>
            @include('partials.icon', ['name' => 'clipboard'])
        </div>
        <p class="stat-number">{{ count($notifications) }}</p>
        <p class="stat-note">All system notifications</p>
    </div>
</div>

<section class="box">
    <div class="box-head">
        <div>
            <h2>{{ $isAdmin ? 'Recent administrative notifications' : 'Operator notifications' }}</h2>
            <p class="subtitle">Click a notification row or its action button to jump directly to the relevant page.</p>
        </div>
    </div>

    <div class="toolbar">
        <input type="text" id="tableSearch" class="input-search" placeholder="Search notification message or category">
        <select id="tableStatus" class="input-select">
            <option value="">All statuses</option>
            <option value="Unread">Unread</option>
            <option value="Read">Read</option>
        </select>
    </div>

    <div class="table-wrap">
        <table class="data" id="filterTable">
            <thead>
                <tr>
                    <th>Received</th>
                    <th>Notification</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Action &amp; Navigation</th>
                </tr>
            </thead>
            <tbody>
                @foreach($notifications as $notif)
                    <tr class="clickable-notif-row {{ $notif['status'] === 'Unread' ? 'notif-row-unread' : '' }}" data-href="{{ $notif['url'] }}">
                        <td><strong>{{ $notif['received'] }}</strong></td>
                        <td>
                            <a href="{{ $notif['url'] }}" class="notif-row-link">
                                <strong>{{ $notif['notification'] }}</strong>
                            </a>
                        </td>
                        <td><span class="badge blue">{{ $notif['type'] }}</span></td>
                        <td>
                            <span class="badge {{ \App\Support\SampleData::badge($notif['status']) }}">{{ $notif['status'] }}</span>
                        </td>
                        <td>
                            <div class="action-group">
                                <a href="{{ $notif['url'] }}" class="btn btn-blue btn-sm">
                                    {{ $notif['action_label'] }} @include('partials.icon', ['name' => 'arrow-right'])
                                </a>
                                @if($notif['status'] === 'Unread')
                                    <form method="POST" action="{{ $isAdmin ? route('admin.notifications.read', $notif['id']) : route('operator.notifications.read', $notif['id']) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline btn-sm">Mark read</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="table-note">Showing {{ count($notifications) }} notifications · Click any item to navigate</p>
</section>
@endsection

