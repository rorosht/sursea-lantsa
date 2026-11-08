@extends('layouts.panel')

@section('title', 'Admin Profile & Modules - SURSEA')
@section('crumb', 'Profile')

@section('content')
@php
    $adminProfile = session('admin_profile', [
        'name' => 'Administrator',
        'title' => 'Port Operations & Maritime System Administrator',
        'email' => 'admin@sursea.example',
        'contact' => '+63 917 800 2026',
        'office' => 'Surigao City Port Authority Terminal, Surigao del Norte',
        'department' => 'SURSEA Maritime Development Office',
    ]);
@endphp

<div class="page-head">
    <div>
        <p class="eyebrow">ADMINISTRATOR ACCOUNT HUB</p>
        <h1>Admin Profile &amp; Modules</h1>
        <p class="subtitle">Manage your administrator profile, change your password, inspect your administrative audit log, and review system health.</p>
    </div>
    <a href="{{ route('admin.settings') }}" class="btn btn-outline">Workspace Settings</a>
</div>

{{-- Profile Module Tabs --}}
<div class="panel-module-tabs" role="tablist">
    <button type="button" class="panel-tab active" data-panel-tab="adm-tab-profile">Profile Details</button>
    <button type="button" class="panel-tab" data-panel-tab="adm-tab-password">Change Password &amp; Security</button>
    <button type="button" class="panel-tab" data-panel-tab="adm-tab-audit">Admin Activity &amp; Audit Log</button>
    <button type="button" class="panel-tab" data-panel-tab="adm-tab-overview">System &amp; Port Overview</button>
</div>

{{-- Module 1: Profile Details --}}
<div class="panel-tab-pane active" id="adm-tab-profile">
    <form method="POST" action="{{ route('admin.profile.save') }}">
        @csrf
        <div class="settings-grid">
            <section class="box">
                <div class="box-head">
                    <h2>Administrator information</h2>
                    <button type="submit" class="btn btn-blue btn-sm">Save profile</button>
                </div>

                <div class="form-two">
                    <div class="form-field">
                        <label for="adm_name">Full name</label>
                        <input type="text" id="adm_name" name="name" value="{{ old('name', $adminProfile['name']) }}" required>
                    </div>
                    <div class="form-field">
                        <label for="adm_title">Official role / title</label>
                        <input type="text" id="adm_title" name="title" value="{{ old('title', $adminProfile['title']) }}" required>
                    </div>
                </div>

                <div class="form-two">
                    <div class="form-field">
                        <label for="adm_email">Email address</label>
                        <input type="email" id="adm_email" name="email" value="{{ old('email', $adminProfile['email']) }}" required>
                    </div>
                    <div class="form-field">
                        <label for="adm_contact">Contact number</label>
                        <input type="text" id="adm_contact" name="contact" value="{{ old('contact', $adminProfile['contact']) }}" required>
                    </div>
                </div>

                <div class="form-two">
                    <div class="form-field">
                        <label for="adm_department">Department</label>
                        <input type="text" id="adm_department" name="department" value="{{ old('department', $adminProfile['department']) }}" required>
                    </div>
                    <div class="form-field">
                        <label for="adm_office">Port office location</label>
                        <input type="text" id="adm_office" name="office" value="{{ old('office', $adminProfile['office']) }}" required>
                    </div>
                </div>
            </section>

            <section class="box">
                <h2>Administrator privileges</h2>
                <div class="policy-list">
                    <div class="policy">
                        <strong>Operator &amp; route approvals</strong>
                        <span>Approve, reject, or remove operator registrations and route requests</span>
                    </div>
                    <div class="policy">
                        <strong>Lantsa &amp; manifest oversight</strong>
                        <span>Full access to all lantsas, QR ticket verification, and cargo logs</span>
                    </div>
                    <div class="policy">
                        <strong>Account &amp; security management</strong>
                        <span>Manage user roles and workspace policies</span>
                    </div>
                </div>
            </section>
        </div>
    </form>
</div>

{{-- Module 2: Change Password & Security --}}
<div class="panel-tab-pane" id="adm-tab-password">
    <div class="settings-grid">
        <section class="box">
            <h2>Change administrator password</h2>
            <p class="subtitle" style="margin-bottom: 18px;">Update the password used to sign in to the SURSEA Admin Portal.</p>

            <form method="POST" action="{{ route('admin.settings.password') }}">
                @csrf
                <div class="form-field">
                    <label for="prof_current_password">Current password</label>
                    <input type="password" id="prof_current_password" name="current_password" placeholder="Enter current password" required>
                </div>
                <div class="form-two">
                    <div class="form-field">
                        <label for="prof_new_password">New password</label>
                        <input type="password" id="prof_new_password" name="new_password" placeholder="At least 6 characters" minlength="6" required>
                    </div>
                    <div class="form-field">
                        <label for="prof_new_password_confirmation">Confirm new password</label>
                        <input type="password" id="prof_new_password_confirmation" name="new_password_confirmation" placeholder="Re-enter new password" minlength="6" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-blue">Update password</button>
            </form>
        </section>

        <section class="box">
            <h2>Active admin session</h2>
            <div class="policy-list">
                <div class="policy">
                    <strong>Surigao City Port Control Desk</strong>
                    <span>Logged in as {{ $adminProfile['email'] }} · Active now</span>
                </div>
                <div class="policy">
                    <strong>Two-step verification</strong>
                    <span>Enabled for operator approval decisions</span>
                </div>
            </div>
        </section>
    </div>
</div>

{{-- Module 3: Admin Activity & Audit Log --}}
<div class="panel-tab-pane" id="adm-tab-audit">
    <section class="box">
        <h2>Administrative audit log</h2>
        <p class="subtitle" style="margin-bottom: 16px;">Recent actions performed by the administrator across operator approvals, route requests, manifest, and cargo.</p>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Module</th>
                        <th>Action Performed</th>
                        <th>Target / Reference</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Today · 11:20 AM</td>
                        <td><span class="badge green">Manifest</span></td>
                        <td>Scanned &amp; verified passenger QR ticket</td>
                        <td>Ref 0000-00001 · MV Isla Norte</td>
                    </tr>
                    <tr>
                        <td>Today · 10:05 AM</td>
                        <td><span class="badge teal">Lantsa &amp; Routes</span></td>
                        <td>Reviewed operator route request</td>
                        <td>RR-2026-01 · Surigao City → Nonoc Island</td>
                    </tr>
                    <tr>
                        <td>Today · 09:10 AM</td>
                        <td><span class="badge amber">Approvals</span></td>
                        <td>Verified vessel registration documents</td>
                        <td>OP-2026-041 · Sea Breeze Transport</td>
                    </tr>
                    <tr>
                        <td>Yesterday · 04:45 PM</td>
                        <td><span class="badge red">Lantsa &amp; Routes</span></td>
                        <td>Suspended vessel due to weather advisory</td>
                        <td>MV Island Star · Surigao City → Libjo</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</div>

{{-- Module 4: System & Port Overview --}}
<div class="panel-tab-pane" id="adm-tab-overview">
    <div class="stat-grid cols-3">
        <div class="stat-card">
            <p class="stat-label">Pending operator approvals</p>
            <p class="stat-number">{{ \App\Support\SampleData::pendingCount() }}</p>
            <p class="stat-note"><a href="{{ route('admin.approvals') }}">Review applications →</a></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Pending route requests</p>
            <p class="stat-number">{{ \App\Support\SampleData::pendingRouteRequestsCount() }}</p>
            <p class="stat-note"><a href="{{ route('admin.module', 'lantsa-routes') }}#routeRequestsSection">Review route requests →</a></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Active vessels</p>
            <p class="stat-number">{{ count(\App\Support\SampleData::lantsaRecords()) }}</p>
            <p class="stat-note"><a href="{{ route('admin.module', 'lantsa-routes') }}">Open Lantsa directory →</a></p>
        </div>
    </div>
</div>
@endsection

