<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SURSEA Lantsa Hub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}?v={{ filemtime(public_path('css/base.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/passenger.css') }}?v={{ filemtime(public_path('css/passenger.css')) }}">
</head>
<body>
@php
    $user = session('user');
    $isPassenger = $user && $user['role'] === 'passenger';
    $notifications = $isPassenger ? \App\Support\SampleData::passengerNotifications() : [];
    $unreadCount = count(array_filter($notifications, fn ($n) => $n['status'] === 'Unread'));
@endphp

<header class="site-header">
    <div class="header-inner">
        <a href="{{ route('home') }}" class="brand">@include('partials.logo')</a>

        <nav class="main-nav">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
            <a href="{{ route('schedules') }}" class="{{ request()->routeIs('schedules', 'seat.*', 'cargo.*') ? 'active' : '' }}">Schedules</a>
            <a href="{{ route('waiting-list') }}" class="{{ request()->routeIs('waiting-list', 'waitlist.*') ? 'active' : '' }}">Waiting List</a>
            <a href="{{ route('routes') }}" class="{{ request()->routeIs('routes') ? 'active' : '' }}">Routes</a>
        </nav>

        <div class="header-actions">
            {{-- Bell Notification Dropdown --}}
            <div class="dropdown">
                <button type="button" class="icon-btn" data-dropdown-btn="bellMenu" aria-label="Notifications">
                    @include('partials.icon', ['name' => 'bell'])
                    @if(!$isPassenger || $unreadCount > 0)
                        <span class="bell-dot"></span>
                    @endif
                </button>
                <div class="dropdown-menu notif-dropdown" id="bellMenu">
                    <div class="notif-drop-head">
                        <div>
                            <p class="menu-title">Notifications</p>
                            @if($isPassenger)
                                <span class="badge amber">{{ $unreadCount }} unread</span>
                            @endif
                        </div>
                        @if($isPassenger)
                            <a href="{{ route('passenger.notifications') }}" class="notif-drop-all">View all →</a>
                        @endif
                    </div>

                    @if($isPassenger)
                        <div class="notif-drop-list">
                            @foreach(array_slice($notifications, 0, 4) as $notif)
                                <a href="{{ $notif['url'] }}" class="notif-drop-item {{ $notif['status'] === 'Unread' ? 'unread' : '' }}">
                                    <div class="notif-drop-top">
                                        <strong>{{ $notif['title'] }}</strong>
                                        <span class="badge {{ \App\Support\SampleData::badge($notif['status']) }}">{{ $notif['status'] }}</span>
                                    </div>
                                    <p>{{ $notif['detail'] }}</p>
                                    <small>{{ $notif['time'] }} · {{ $notif['action_label'] }} →</small>
                                </a>
                            @endforeach
                        </div>
                        <a href="{{ route('passenger.notifications') }}" class="menu-item notif-center-btn">Open Notifications Center</a>
                    @else
                        <p class="menu-text">Log in to view your trip, waiting list, and cargo notifications.</p>
                        <a href="{{ route('login') }}" class="menu-item notif-center-btn">Log in as passenger</a>
                    @endif
                </div>
            </div>

            {{-- Profile (logged in) or Login button (guest) --}}
            @if($isPassenger)
                <div class="dropdown">
                    <button type="button" class="icon-btn" data-dropdown-btn="userMenu" aria-label="Account">
                        @include('partials.icon', ['name' => 'user-circle'])
                    </button>
                    <div class="dropdown-menu user-dropdown-menu profile-dropdown" id="userMenu">
                        <div class="user-dropdown-header">
                            <p class="menu-title">{{ $user['name'] }}</p>
                            <p class="menu-text">{{ $user['email'] }} · Passenger</p>
                        </div>
                        <div class="user-dropdown-nav">
                            <a href="{{ route('passenger.profile', ['tab' => 'settings']) }}" class="menu-item">
                                @include('partials.icon', ['name' => 'user'])
                                <span>Profile Settings</span>
                            </a>
                            <a href="{{ route('passenger.profile', ['tab' => 'transactions']) }}" class="menu-item">
                                @include('partials.icon', ['name' => 'ticket'])
                                <span>Transactions &amp; Tickets</span>
                            </a>
                            <a href="{{ route('passenger.profile', ['tab' => 'activity']) }}" class="menu-item">
                                @include('partials.icon', ['name' => 'clipboard'])
                                <span>Activity Log</span>
                            </a>
                            <a href="{{ route('passenger.profile', ['tab' => 'security']) }}" class="menu-item">
                                @include('partials.icon', ['name' => 'shield'])
                                <span>Password &amp; Security</span>
                            </a>
                            <a href="{{ route('passenger.notifications') }}" class="menu-item">
                                @include('partials.icon', ['name' => 'bell'])
                                <span>Notifications</span>
                                @if($unreadCount > 0)
                                    <span class="menu-badge">{{ $unreadCount }}</span>
                                @endif
                            </a>
                        </div>
                        <div class="user-dropdown-footer">
                            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                                @csrf
                                <button type="submit" class="menu-item logout-item">
                                    @include('partials.icon', ['name' => 'logout'])
                                    <span>Log out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @elseif($user)
                {{-- An operator or admin is looking at the public pages --}}
                <a href="{{ url(\App\Support\SampleData::homeFor($user['role'])) }}" class="btn btn-dark">Go to portal</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-dark login-btn">login</a>
            @endif
        </div>
    </div>
</header>

<main>
    @if(session('status'))
        <div class="container" style="padding-top: 18px;"><div class="alert success">{{ session('status') }}</div></div>
    @endif

    @yield('content')
</main>

<script src="{{ asset('js/common.js') }}?v={{ filemtime(public_path('js/common.js')) }}"></script>
<script src="{{ asset('js/passenger.js') }}?v={{ filemtime(public_path('js/passenger.js')) }}"></script>
</body>
</html>
