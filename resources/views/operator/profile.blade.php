@extends('layouts.panel')

@section('title', 'Operator Profile & Modules - SURSEA')
@section('crumb', 'Profile')

@section('content')
@php
    $profile = session('operator_profile', [
        'business' => 'Isla Norte Transport',
        'lantsa' => 'MV Isla Norte',
        'service' => 'Passenger & Cargo',
        'seats' => '40',
        'email' => 'islanorte@sursea.example',
        'contact' => '09123123123',
        'address' => 'Surigao City, Surigao del Norte',
    ]);
    $prefs = session('operator_prefs', [
        'auto_waitlist_promote' => true,
        'qr_sound_alert' => true,
        'cargo_sms_receipt' => true,
    ]);
@endphp

<div class="page-head">
    <div>
        <p class="eyebrow">OPERATOR ACCOUNT HUB</p>
        <h1>Business profile &amp; account modules</h1>
        <p class="subtitle">Manage your business &amp; vessel information, change your password, configure operational preferences, and review your activity log.</p>
    </div>
</div>

{{-- Profile Module Switcher Tabs --}}
<div class="panel-module-tabs" role="tablist">
    <button type="button" class="panel-tab active" data-panel-tab="op-tab-business">Business &amp; Vessel Profile</button>
    <button type="button" class="panel-tab" data-panel-tab="op-tab-security">Change Password &amp; Security</button>
    <button type="button" class="panel-tab" data-panel-tab="op-tab-preferences">Operations &amp; Notification Settings</button>
    <button type="button" class="panel-tab" data-panel-tab="op-tab-activity">Operator Activity Log</button>
</div>

{{-- Module 1: Business & Vessel Profile --}}
<div class="panel-tab-pane active" id="op-tab-business">
    <form method="POST" action="{{ route('operator.profile.save') }}">
        @csrf
        <div class="settings-grid">
            <section class="box">
                <div class="box-head">
                    <h2>Operator &amp; vessel information</h2>
                    <button type="submit" class="btn btn-blue btn-sm">Save profile changes</button>
                </div>

                <div class="form-two">
                    <div class="form-field">
                        <label for="business">Business name</label>
                        <input type="text" id="business" name="business" value="{{ old('business', $profile['business']) }}" required>
                    </div>
                    <div class="form-field">
                        <label for="lantsa">Assigned lantsa</label>
                        <input type="text" id="lantsa" name="lantsa" value="{{ old('lantsa', $profile['lantsa']) }}" required>
                    </div>
                </div>

                <div class="form-two">
                    <div class="form-field">
                        <label for="service">Vessel service type</label>
                        <select id="service" name="service" class="input-select" style="width: 100%;">
                            @foreach(['Passenger & Cargo', 'Passenger Only', 'Cargo Only'] as $sType)
                                <option value="{{ $sType }}" @selected(($profile['service'] ?? 'Passenger & Cargo') === $sType)>{{ $sType }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="seats">Passenger seat capacity</label>
                        <input type="number" id="seats" name="seats" value="{{ old('seats', $profile['seats'] ?? 40) }}" min="0" max="200" required>
                    </div>
                </div>

                <div class="form-two">
                    <div class="form-field">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $profile['email']) }}" required>
                    </div>
                    <div class="form-field">
                        <label for="contact">Contact number</label>
                        <input type="text" id="contact" name="contact" value="{{ old('contact', $profile['contact']) }}" required>
                    </div>
                </div>

                <div class="form-field">
                    <label for="address">Business address</label>
                    <input type="text" id="address" name="address" value="{{ old('address', $profile['address']) }}" required>
                </div>

                <p class="table-note">Route additions can be requested from the <a href="{{ route('operator.module', 'vessel-routes') }}">My Vessel &amp; Routes</a> page.</p>
            </section>

            <section class="box">
                <h2>Permit &amp; verification status</h2>
                <div class="policy-list">
                    <div class="policy">
                        <strong>Approval status</strong>
                        <span>Approved by administrator · Active operator account</span>
                    </div>
                    <div class="policy">
                        <strong>Service type &amp; capacity</strong>
                        <span>{{ $profile['service'] }} · {{ $profile['seats'] }} passenger seats · 120 cargo items</span>
                    </div>
                    <div class="policy">
                        <strong>Submitted documents</strong>
                        <span>Business permit, Government ID, MARINA Vessel registration (3 of 3 verified)</span>
                    </div>
                    <div class="policy">
                        <strong>Quick shortcuts</strong>
                        <span>
                            <a href="{{ route('operator.module', 'vessel-routes') }}">Request new route</a> ·
                            <a href="{{ route('operator.module', 'reports') }}">View revenue report</a>
                        </span>
                    </div>
                </div>
            </section>
        </div>
    </form>
</div>

{{-- Module 2: Change Password & Security --}}
<div class="panel-tab-pane" id="op-tab-security">
    <div class="settings-grid">
        <section class="box">
            <h2>Change operator password</h2>
            <p class="subtitle" style="margin-bottom: 18px;">Keep your operator portal credentials secure when managing check-ins and manifests at the port.</p>

            <form method="POST" action="{{ route('operator.profile.password') }}">
                @csrf
                <div class="form-field">
                    <label for="current_password">Current password</label>
                    <input type="password" id="current_password" name="current_password" placeholder="Enter current password" required>
                </div>
                <div class="form-two">
                    <div class="form-field">
                        <label for="new_password">New password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="At least 6 characters" minlength="6" required>
                    </div>
                    <div class="form-field">
                        <label for="new_password_confirmation">Confirm new password</label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" placeholder="Re-enter new password" minlength="6" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-blue">Update password</button>
            </form>
        </section>

        <section class="box">
            <h2>Active operator sessions</h2>
            <div class="policy-list">
                <div class="policy">
                    <strong>Surigao City Port Terminal · Windows Browser</strong>
                    <span>Current active session · Logged in as {{ $profile['email'] }}</span>
                </div>
                <div class="policy">
                    <strong>Port Gate QR Scanner Tablet</strong>
                    <span>Authorized check-in scanner for MV Isla Norte</span>
                </div>
            </div>
        </section>
    </div>
</div>

{{-- Module 3: Operations & Notification Preferences --}}
<div class="panel-tab-pane" id="op-tab-preferences">
    <section class="box">
        <form method="POST" action="{{ route('operator.profile.preferences') }}">
            @csrf
            <div class="box-head">
                <div>
                    <h2>Operations &amp; notification preferences</h2>
                    <p class="subtitle">Customize how check-in alerts, waiting list promotions, and cargo receipts behave.</p>
                </div>
                <button type="submit" class="btn btn-blue btn-sm">Save preferences</button>
            </div>

            <div class="policy-list">
                <label class="policy policy-check">
                    <input type="checkbox" name="auto_waitlist_promote" value="1" @checked($prefs['auto_waitlist_promote'] ?? true)>
                    <div>
                        <strong>Notify waiting list automatically when seats open</strong>
                        <span>Alert queued passengers when a reserved seat is cancelled or capacity increases.</span>
                    </div>
                </label>
                <label class="policy policy-check">
                    <input type="checkbox" name="qr_sound_alert" value="1" @checked($prefs['qr_sound_alert'] ?? true)>
                    <div>
                        <strong>Confirmation banner on QR ticket scan</strong>
                        <span>Display immediate passenger/cargo verification details when scanning QR tickets.</span>
                    </div>
                </label>
                <label class="policy policy-check">
                    <input type="checkbox" name="cargo_sms_receipt" value="1" @checked($prefs['cargo_sms_receipt'] ?? true)>
                    <div>
                        <strong>Send receiver notification when cargo is loaded</strong>
                        <span>Notify the receiver contact number when cargo status changes to In transit or Delivered.</span>
                    </div>
                </label>
            </div>
        </form>
    </section>
</div>

{{-- Module 4: Operator Activity Log --}}
<div class="panel-tab-pane" id="op-tab-activity">
    <section class="box">
        <h2>Recent operator activity log</h2>
        <p class="subtitle" style="margin-bottom: 16px;">Audit trail of trip schedule updates, QR ticket check-ins, cargo pricing, and route requests.</p>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>Reference / Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Today · 11:42 AM</td>
                        <td><span class="badge green">Check-in</span></td>
                        <td>Verified passenger QR ticket</td>
                        <td>Ref 0000-00001 · Robert Zabala (2 pax)</td>
                    </tr>
                    <tr>
                        <td>Today · 11:15 AM</td>
                        <td><span class="badge amber">Cargo</span></td>
                        <td>Updated cargo freight amount &amp; loaded package</td>
                        <td>Ref 0000-00112 · ₱75 (3 items)</td>
                    </tr>
                    <tr>
                        <td>Today · 10:30 AM</td>
                        <td><span class="badge blue">Trip Schedule</span></td>
                        <td>Updated crossing status to Boarding</td>
                        <td>12:00 PM · Surigao City → Cagdianao</td>
                    </tr>
                    <tr>
                        <td>Today · 09:20 AM</td>
                        <td><span class="badge blue">Vessel &amp; Routes</span></td>
                        <td>Submitted new route approval request</td>
                        <td>REQ-101 · Surigao City → Socorros (Bucas Grande)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
