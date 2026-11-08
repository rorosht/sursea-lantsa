<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SURSEA Portal')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}?v={{ filemtime(public_path('css/base.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/panel.css') }}?v={{ filemtime(public_path('css/panel.css')) }}">
</head>
<body class="panel-body">
@php
    // This one layout is used by BOTH the admin portal and the operator portal.
    $user = session('user');
    $isAdmin = $user['role'] === 'admin';
    $menu = \App\Support\SampleData::menu($user['role']);
    $homeUrl = url($isAdmin ? 'admin/dashboard' : 'operator/dashboard');
    $notifs = $isAdmin ? \App\Support\SampleData::adminNotifications() : \App\Support\SampleData::operatorNotifications();
    $unreadCount = count(array_filter($notifs, fn ($n) => $n['status'] === 'Unread'));
@endphp

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="panel">

    {{-- ============ SIDEBAR ============ --}}
    <aside class="sidebar" id="panelSidebar">
        <div class="sidebar-top">
            <a href="{{ $homeUrl }}" class="brand">@include('partials.logo')</a>
            <button type="button" class="sidebar-close" id="sidebarClose" aria-label="Close navigation">
                @include('partials.icon', ['name' => 'x'])
            </button>
        </div>

        <div class="workspace-card">
            @if($isAdmin)
                <small>ADMIN WORKSPACE</small>
                <strong>SURSEA Administration</strong>
                <span>@include('partials.icon', ['name' => 'shield']) Surigao City</span>
            @else
                <small>YOUR BUSINESS</small>
                <strong>{{ $user['business'] }}</strong>
                <span>@include('partials.icon', ['name' => 'ship']) {{ $user['lantsa'] }}</span>
            @endif
        </div>

        <nav class="side-nav">
            @foreach($menu as $group)
                <p class="side-label">{{ $group['label'] }}</p>
                @foreach($group['items'] as $item)
                    <a href="{{ url($item['path']) }}" class="side-link {{ request()->is($item['path'].'*') ? 'active' : '' }}">
                        @include('partials.icon', ['name' => $item['icon']])
                        <span>{{ $item['label'] }}</span>
                        @if($item['badge'])
                            <em class="count">{{ $item['badge'] }}</em>
                        @endif
                    </a>
                @endforeach
            @endforeach
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button type="submit" class="side-link">@include('partials.icon', ['name' => 'logout']) <span>Log out</span></button>
        </form>
    </aside>

    {{-- ============ MAIN AREA ============ --}}
    <div class="panel-main">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Open navigation" aria-expanded="false">
                    @include('partials.icon', ['name' => 'menu'])
                </button>
                <div class="crumbs">
                    <strong>{{ $isAdmin ? 'Admin Portal' : 'Operator Portal' }}</strong>
                    <span>/</span>
                    <span>@yield('crumb')</span>
                </div>
            </div>

            <div class="topbar-right">
                <span class="top-text">{{ $isAdmin ? 'Surigao City' : $user['lantsa'] }}</span>

                {{-- Clickable Notifications Dropdown --}}
                <div class="dropdown">
                    <button type="button" class="bell-btn" data-dropdown-btn="panelNotifMenu" aria-label="Notifications">
                        @include('partials.icon', ['name' => 'bell'])
                        @if($unreadCount > 0)
                            <span class="bell-badge">{{ $unreadCount }}</span>
                        @endif
                    </button>
                    <div class="dropdown-menu notif-dropdown-menu" id="panelNotifMenu">
                        <div class="notif-dropdown-head">
                            <strong>Notifications</strong>
                            <span class="badge {{ $unreadCount > 0 ? 'amber' : 'grey' }}">{{ $unreadCount }} unread</span>
                        </div>
                        <div class="notif-dropdown-list">
                            @foreach(array_slice($notifs, 0, 4) as $nf)
                                <a href="{{ $nf['url'] }}" class="notif-dropdown-item {{ $nf['status'] === 'Unread' ? 'unread' : '' }}">
                                    <div class="notif-dropdown-top">
                                        <span class="badge {{ \App\Support\SampleData::badge($nf['status']) }}">{{ $nf['type'] }}</span>
                                        <small>{{ $nf['received'] }}</small>
                                    </div>
                                    <p>{{ $nf['notification'] }}</p>
                                    <span class="notif-dropdown-cta">{{ $nf['action_label'] }} →</span>
                                </a>
                            @endforeach
                        </div>
                        <a href="{{ url($isAdmin ? 'admin/notifications' : 'operator/notifications') }}" class="btn btn-blue btn-sm btn-block" style="margin-top: 8px; text-align: center;">
                            Open all notifications
                        </a>
                    </div>
                </div>

                {{-- User Profile Dropdown --}}
                <div class="dropdown">
                    <button type="button" class="user-btn" data-dropdown-btn="panelUserMenu">
                        @include('partials.icon', ['name' => 'user'])
                        <span>{{ $isAdmin ? 'Administrator' : 'Operator' }}</span>
                        @include('partials.icon', ['name' => 'chevron-down'])
                    </button>
                    <div class="dropdown-menu user-dropdown-menu" id="panelUserMenu">
                        <div class="user-dropdown-header">
                            <p class="menu-title">{{ $user['name'] }}</p>
                            <p class="menu-text">{{ $user['email'] }} · {{ $isAdmin ? 'Administrator' : 'Operator' }}</p>
                        </div>
                        <div class="user-dropdown-nav">
                            <a href="{{ url($isAdmin ? 'admin/profile' : 'operator/profile') }}" class="menu-item">
                                @include('partials.icon', ['name' => 'user'])
                                <span>Profile &amp; Modules</span>
                            </a>
                            @if($isAdmin)
                                <a href="{{ route('admin.settings') }}" class="menu-item">
                                    @include('partials.icon', ['name' => 'gear'])
                                    <span>Settings &amp; Accounts</span>
                                </a>
                            @endif
                            <a href="{{ url($isAdmin ? 'admin/notifications' : 'operator/notifications') }}" class="menu-item">
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
            </div>
        </header>

        <main class="panel-content">
            @if(session('status'))
                <div class="alert success">{{ session('status') }}</div>
            @endif

            @yield('content')
        </main>

        @if($isAdmin)
            <footer class="mini-footer">
                <span>© 2026 SURSEA · Surigao City Maritime Development · Example data</span>
                <span><a href="#">Privacy Policy</a> · <a href="#">Terms of Service</a></span>
            </footer>
        @else
            @include('partials.footer')
        @endif
    </div>
</div>

<script src="{{ asset('js/common.js') }}?v={{ filemtime(public_path('js/common.js')) }}"></script>
<script src="{{ asset('js/panel.js') }}?v={{ filemtime(public_path('js/panel.js')) }}"></script>
</body>
</html>
