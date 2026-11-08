@extends('layouts.passenger')

@section('title', 'My Profile & Account - SURSEA')

@section('content')
@php
    $activeTab = request('tab', 'settings');
@endphp

<div class="container page">
    <div class="schedule-top">
        <div>
            <p class="eyebrow">PASSENGER ACCOUNT HUB</p>
            <h1 class="page-title">My Profile &amp; Account</h1>
            <p class="lead small">Manage your profile settings, view reservation and cargo transactions, inspect activity logs, and update account security.</p>
        </div>

        <div class="passenger-profile-badge">
            <span class="circle">@include('partials.icon', ['name' => 'user'])</span>
            <div>
                <strong>{{ $user['name'] }}</strong>
                <small class="muted">{{ $user['email'] }} · Verified Passenger</small>
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="passenger-stat-row cols-4">
        <div class="passenger-stat-card">
            <span>Total Transactions</span>
            <strong>{{ count($transactions) }}</strong>
            <small>Seat, waitlist &amp; cargo records</small>
        </div>
        <div class="passenger-stat-card">
            <span>Active Reservations</span>
            <strong>{{ count(array_filter($transactions, fn ($t) => $t['type'] === 'passenger')) }}</strong>
            <small>Confirmed &amp; waitlisted trips</small>
        </div>
        <div class="passenger-stat-card">
            <span>Cargo Shipments</span>
            <strong>{{ count(array_filter($transactions, fn ($t) => $t['type'] === 'cargo')) }}</strong>
            <small>Tracked deliveries</small>
        </div>
        <div class="passenger-stat-card">
            <span>Account Status</span>
            <strong>Active</strong>
            <small>Surigao City Port Priority</small>
        </div>
    </div>

    {{-- Module Navigation Tabs --}}
    <div class="profile-module-tabs" role="tablist">
        <button type="button" class="profile-tab-btn {{ $activeTab === 'settings' ? 'active' : '' }}" data-profile-tab="settings">
            @include('partials.icon', ['name' => 'user']) Profile Settings
        </button>
        <button type="button" class="profile-tab-btn {{ $activeTab === 'transactions' ? 'active' : '' }}" data-profile-tab="transactions">
            @include('partials.icon', ['name' => 'ticket']) Transactions &amp; Tickets
        </button>
        <button type="button" class="profile-tab-btn {{ $activeTab === 'activity' ? 'active' : '' }}" data-profile-tab="activity">
            @include('partials.icon', ['name' => 'clipboard']) Activity Log
        </button>
        <button type="button" class="profile-tab-btn {{ $activeTab === 'security' ? 'active' : '' }}" data-profile-tab="security">
            @include('partials.icon', ['name' => 'shield']) Password &amp; Security
        </button>
    </div>

    {{-- MODULE 1: PROFILE SETTINGS --}}
    <section class="profile-module-panel {{ $activeTab === 'settings' ? '' : 'hidden' }}" id="profileTab-settings">
        <div class="profile-card-box">
            <h2>Personal &amp; Travel Profile Settings</h2>
            <p class="muted" style="margin-bottom: 20px;">Keep your passenger name and contact number accurate for Philippine Coast Guard manifest verification and SMS/port alerts.</p>

            <form method="POST" action="{{ route('passenger.profile.save') }}">
                @csrf
                <div class="profile-form-grid">
                    <div class="field">
                        <label for="prof_name">Full Name</label>
                        <input type="text" id="prof_name" name="name" value="{{ old('name', $user['name']) }}" required>
                    </div>
                    <div class="field">
                        <label for="prof_username">Username</label>
                        <input type="text" id="prof_username" name="username" value="{{ old('username', $user['username']) }}" required>
                    </div>
                    <div class="field">
                        <label for="prof_email">Email Address</label>
                        <input type="email" id="prof_email" name="email" value="{{ old('email', $user['email']) }}" required>
                    </div>
                    <div class="field">
                        <label for="prof_contact">Mobile Contact Number</label>
                        <input type="text" id="prof_contact" name="contact" value="{{ old('contact', $user['contact']) }}" required>
                    </div>
                    <div class="field">
                        <label for="prof_address">Home Municipality / Island</label>
                        <input type="text" id="prof_address" name="address" value="{{ old('address', $user['address'] ?? 'Surigao City, Surigao del Norte') }}" required>
                    </div>
                    <div class="field">
                        <label for="prof_emergency">Emergency Contact Number</label>
                        <input type="text" id="prof_emergency" name="emergency_contact" value="{{ old('emergency_contact', $user['emergency_contact'] ?? '09179998877') }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-dark">Save Profile Settings</button>
            </form>
        </div>
    </section>

    {{-- MODULE 2: TRANSACTIONS & TICKETS --}}
    <section class="profile-module-panel {{ $activeTab === 'transactions' ? '' : 'hidden' }}" id="profileTab-transactions">
        <div class="profile-card-box">
            <div class="board-head" style="margin-bottom: 16px;">
                <div>
                    <h2 style="margin: 0;">My Transactions, Tickets &amp; Cargo</h2>
                    <p>View your seat reservations, waiting list entries, and cargo deliveries. Click <strong>View Ticket</strong> to open your QR code.</p>
                </div>
                <a href="{{ route('schedules') }}" class="btn btn-dark btn-sm">Book New Trip</a>
            </div>

            <div class="board" style="margin-top: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>REF / TRACKING ID</th>
                            <th>TYPE</th>
                            <th>ROUTE &amp; SCHEDULE</th>
                            <th>LANTSA</th>
                            <th>DETAILS</th>
                            <th>TOTAL</th>
                            <th>STATUS</th>
                            <th class="right">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr>
                                <td><strong>{{ $tx['ref'] }}</strong></td>
                                <td>{{ $tx['category'] }}</td>
                                <td>
                                    <strong>{{ $tx['route'] }}</strong>
                                    <br>
                                    <small class="muted">{{ $tx['date'] }}</small>
                                </td>
                                <td>{{ $tx['lantsa'] }}</td>
                                <td>
                                    @if($tx['type'] === 'cargo')
                                        {{ $tx['qty'] }} items ({{ $tx['items'] }})
                                    @else
                                        {{ $tx['passengers'] }} pax · {{ $tx['cargo_qty'] }} cargo
                                    @endif
                                </td>
                                <td><strong>₱{{ $tx['total'] }}</strong></td>
                                <td>
                                    <span class="badge {{ \App\Support\SampleData::badge($tx['status']) }}">{{ $tx['status'] }}</span>
                                </td>
                                <td class="right">
                                    <div class="tx-action-row">
                                        <button
                                            type="button"
                                            class="btn btn-dark btn-sm"
                                            data-open-tx-ticket="{{ $tx['type'] }}"
                                            data-ref="{{ $tx['ref'] }}"
                                            data-name="{{ $tx['name'] ?? $user['name'] }}"
                                            data-passengers="{{ $tx['passengers'] ?? 1 }}"
                                            data-cargo="{{ $tx['cargo_qty'] ?? 0 }}"
                                            data-route="{{ $tx['route'] }}, {{ $tx['date'] }}"
                                            data-lantsa="{{ $tx['lantsa'] }}"
                                            data-contact="{{ $tx['contact'] ?? $user['contact'] }}"
                                            data-total="{{ $tx['total'] }}"
                                            data-sender="{{ $tx['sender'] ?? $user['name'] }}"
                                            data-sender-contact="{{ $tx['sender_contact'] ?? $user['contact'] }}"
                                            data-receiver="{{ $tx['receiver'] ?? 'Earl Fermillan' }}"
                                            data-receiver-contact="{{ $tx['receiver_contact'] ?? '09123123123' }}"
                                            data-items="{{ $tx['items'] ?? '' }}"
                                            data-qty="{{ $tx['qty'] ?? 1 }}"
                                        >View Ticket</button>

                                        <form method="POST" action="{{ route('passenger.transaction.delete', $tx['ref']) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-red btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty">No transactions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- MODULE 3: ACTIVITY LOG --}}
    <section class="profile-module-panel {{ $activeTab === 'activity' ? '' : 'hidden' }}" id="profileTab-activity">
        <div class="profile-card-box">
            <h2>Account Activity Log</h2>
            <p class="muted" style="margin-bottom: 16px;">Recent actions performed on your passenger account.</p>

            <div class="board" style="margin-top: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>DATE &amp; TIME</th>
                            <th>ACTIVITY DESCRIPTION</th>
                            <th>MODULE</th>
                            <th class="right">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $act)
                            <tr>
                                <td><strong>{{ $act[0] }}</strong></td>
                                <td>{{ $act[1] }}</td>
                                <td><span class="badge plain grey">{{ $act[2] }}</span></td>
                                <td class="right">
                                    <span class="badge {{ \App\Support\SampleData::badge($act[3]) }}">{{ $act[3] }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- MODULE 4: PASSWORD & SECURITY --}}
    <section class="profile-module-panel {{ $activeTab === 'security' ? '' : 'hidden' }}" id="profileTab-security">
        <div class="profile-card-box">
            <h2>Password &amp; Account Security</h2>
            <p class="muted" style="margin-bottom: 20px;">Update your password and notification preferences to keep your SURSEA account secure.</p>

            <form method="POST" action="{{ route('passenger.password.save') }}">
                @csrf
                <div class="profile-form-grid">
                    <div class="field">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" placeholder="••••••••••••" required>
                    </div>
                    <div class="field">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="password" placeholder="Minimum 6 characters" required>
                    </div>
                    <div class="field">
                        <label for="new_password_confirmation">Confirm New Password</label>
                        <input type="password" id="new_password_confirmation" name="password_confirmation" placeholder="Re-enter new password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-dark">Update Password</button>
            </form>
        </div>
    </section>
</div>

{{-- Dynamic Ticket Modal for Transactions --}}
<div class="modal" id="txTicketModal">
    <div class="ticket">
        <div class="ticket-head">
            <h2 id="txModalTitle">Passenger Reservation Ticket</h2>
            <button type="button" class="modal-close" data-close-modal aria-label="Close ticket">
                @include('partials.icon', ['name' => 'close'])
            </button>
        </div>
        <dl class="ticket-body" id="txModalBody"></dl>
        <div class="ticket-foot">
            <div>
                <div class="total" id="txModalTotalWrap">
                    <strong>Total Payment:</strong>
                    <span id="txModalTotal">200</span>
                </div>
                <p id="txModalNote">Please take a screenshot on this ticket for verification before boarding</p>
            </div>
            <div class="qr" id="txModalQr" data-code="0000-00001"></div>
        </div>
    </div>
</div>

@include('partials.footer')
@endsection

