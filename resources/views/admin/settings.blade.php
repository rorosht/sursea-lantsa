@extends('layouts.panel')

@section('title', 'Workspace Settings & Account Management - SURSEA')
@section('crumb', 'Settings')

@section('content')
@php
    $managedAccounts = session('admin_managed_accounts', [
        ['id' => 'ACC-1', 'name' => 'Administrator', 'email' => 'admin@sursea.example', 'role' => 'Admin', 'status' => 'Active'],
        ['id' => 'ACC-2', 'name' => 'Isla Norte Operator', 'email' => 'islanorte@sursea.example', 'role' => 'Operator', 'status' => 'Active'],
        ['id' => 'ACC-3', 'name' => 'Marajaw Operator', 'email' => 'marajaw@sursea.example', 'role' => 'Operator', 'status' => 'Active'],
        ['id' => 'ACC-4', 'name' => 'Robert Zabala', 'email' => 'passenger@sursea.example', 'role' => 'Passenger', 'status' => 'Active'],
    ]);
@endphp

<div class="page-head">
    <div>
        <p class="eyebrow">ADMIN CONFIGURATION</p>
        <h1>Workspace Settings &amp; Accounts</h1>
        <p class="subtitle">Configure workspace information, manage user &amp; operator accounts, update passwords, and control system policies.</p>
    </div>
    <a href="{{ route('admin.profile') }}" class="btn btn-outline">Open Admin Profile</a>
</div>

<div class="stat-grid cols-3">
    <div class="stat-card">
        <p class="stat-label">Administrator</p>
        <p class="stat-number small">Full oversight</p>
        <p class="stat-note">Accounts, approvals, and network</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Operator</p>
        <p class="stat-number small">Own business only</p>
        <p class="stat-note">Trips, manifests, and cargo</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Passenger</p>
        <p class="stat-number small">Personal access</p>
        <p class="stat-note">Reservations and tickets</p>
    </div>
</div>

{{-- Module Tabs for Settings --}}
<div class="panel-module-tabs" role="tablist">
    <button type="button" class="panel-tab active" data-panel-tab="settings-tab-workspace">Workspace &amp; Policies</button>
    <button type="button" class="panel-tab" data-panel-tab="settings-tab-accounts">Account Management</button>
    <button type="button" class="panel-tab" data-panel-tab="settings-tab-password">Password &amp; Security</button>
</div>

{{-- Tab 1: Workspace & Policies --}}
<div class="panel-tab-pane active" id="settings-tab-workspace">
    <form method="POST" action="{{ route('admin.settings.save') }}">
        @csrf
        <div class="settings-grid">
            <section class="box">
                <div class="box-head">
                    <h2>Workspace information</h2>
                    <button type="submit" class="btn btn-blue btn-sm">Save changes</button>
                </div>

                <div class="form-field">
                    <label for="workspace_name">Workspace name</label>
                    <input type="text" id="workspace_name" name="workspace_name" value="{{ old('workspace_name', $settings['workspace_name']) }}" required>
                </div>

                <div class="form-two">
                    <div class="form-field">
                        <label for="service_area">Service area</label>
                        <input type="text" id="service_area" name="service_area" value="{{ old('service_area', $settings['service_area']) }}" required>
                    </div>
                    <div class="form-field">
                        <label for="timezone">Timezone</label>
                        <input type="text" id="timezone" name="timezone" value="{{ old('timezone', $settings['timezone']) }}" required>
                    </div>
                </div>

                <div class="form-field">
                    <label for="support_contact">Support contact</label>
                    <input type="email" id="support_contact" name="support_contact" value="{{ old('support_contact', $settings['support_contact']) }}" required>
                </div>

                <p class="table-note">These details appear in administrative communications and digital tickets.</p>
            </section>

            <section class="box">
                <h2>Registration &amp; route policies</h2>
                <div class="policy-list">
                    <div class="policy">
                        <strong>Operator registration</strong>
                        <span>Admin approval required</span>
                    </div>
                    <div class="policy">
                        <strong>Required documents</strong>
                        <span>Permit, ID, vessel registration</span>
                    </div>
                    <div class="policy">
                        <strong>Passenger registration</strong>
                        <span>Self-service account creation</span>
                    </div>
                    <div class="policy">
                        <strong>Operator route requests</strong>
                        <span>Administrator review &amp; approval required</span>
                    </div>
                </div>
            </section>
        </div>
    </form>
</div>

{{-- Tab 2: Account Management --}}
<div class="panel-tab-pane" id="settings-tab-accounts">
    <section class="box">
        <div class="box-head">
            <div>
                <h2>Account management</h2>
                <p class="subtitle">Create or manage administrator, operator, and passenger portal accounts.</p>
            </div>
            <button type="button" class="btn btn-blue btn-sm" data-toggle-box="addAccountBox">
                @include('partials.icon', ['name' => 'plus']) Add account
            </button>
        </div>

        <div class="hidden" id="addAccountBox" style="margin-bottom: 20px; padding-bottom: 18px; border-bottom: 1px solid var(--line);">
            <form method="POST" action="{{ route('admin.settings.account.store') }}">
                @csrf
                <div class="form-two">
                    <div class="form-field">
                        <label for="acc_name">Full name / Business name</label>
                        <input type="text" id="acc_name" name="name" placeholder="e.g. Port Officer Santos" required>
                    </div>
                    <div class="form-field">
                        <label for="acc_email">Email address</label>
                        <input type="email" id="acc_email" name="email" placeholder="e.g. officer@sursea.example" required>
                    </div>
                </div>
                <div class="form-two">
                    <div class="form-field">
                        <label for="acc_role">Portal role</label>
                        <select id="acc_role" name="role" class="input-select" style="width: 100%;" required>
                            <option value="Admin">Admin</option>
                            <option value="Operator">Operator</option>
                            <option value="Passenger">Passenger</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="acc_password">Temporary password</label>
                        <input type="password" id="acc_password" name="password" value="password123" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-blue btn-sm">Create account</button>
            </form>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Account ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($managedAccounts as $acc)
                        <tr>
                            <td><strong>{{ $acc['id'] }}</strong></td>
                            <td><strong>{{ $acc['name'] }}</strong></td>
                            <td>{{ $acc['email'] }}</td>
                            <td><span class="badge blue">{{ $acc['role'] }}</span></td>
                            <td><span class="badge {{ \App\Support\SampleData::badge($acc['status']) }}">{{ $acc['status'] }}</span></td>
                            <td>
                                @if($acc['email'] !== 'admin@sursea.example')
                                    <form method="POST" action="{{ route('admin.settings.account.delete', $acc['id']) }}" onsubmit="return confirm('Remove this account?');">
                                        @csrf
                                        <button type="submit" class="btn btn-danger-outline btn-sm">
                                            @include('partials.icon', ['name' => 'trash']) Delete
                                        </button>
                                    </form>
                                @else
                                    <span class="muted">Primary Admin</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>

{{-- Tab 3: Password & Security --}}
<div class="panel-tab-pane" id="settings-tab-password">
    <div class="settings-grid">
        <section class="box">
            <h2>Change administrator password</h2>
            <p class="subtitle" style="margin-bottom: 18px;">Update your administrator login password and security credentials.</p>

            <form method="POST" action="{{ route('admin.settings.password') }}">
                @csrf
                <div class="form-field">
                    <label for="admin_current_password">Current password</label>
                    <input type="password" id="admin_current_password" name="current_password" placeholder="Enter current password" required>
                </div>
                <div class="form-two">
                    <div class="form-field">
                        <label for="admin_new_password">New password</label>
                        <input type="password" id="admin_new_password" name="new_password" placeholder="At least 6 characters" minlength="6" required>
                    </div>
                    <div class="form-field">
                        <label for="admin_new_password_confirmation">Confirm new password</label>
                        <input type="password" id="admin_new_password_confirmation" name="new_password_confirmation" placeholder="Confirm new password" minlength="6" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-blue">Update password</button>
            </form>
        </section>

        <section class="box">
            <h2>Security &amp; session controls</h2>
            <div class="policy-list">
                <div class="policy">
                    <strong>Role-based access control</strong>
                    <span>Strict separation of Admin, Operator, and Passenger routes</span>
                </div>
                <div class="policy">
                    <strong>QR ticket verification</strong>
                    <span>Enabled on Admin Manifest &amp; Operator Check-in</span>
                </div>
                <div class="policy">
                    <strong>Audit logging</strong>
                    <span>All operator approvals &amp; route decisions are recorded</span>
                </div>
            </div>
        </section>
    </div>
</div>

<section class="box cream">
    <h2>Keep access appropriate to each role</h2>
    <p class="subtitle big-text">Administrators manage accounts, operator approvals, and route requests. Operators manage only their assigned vessel and crossings. Passengers can view and manage only their own reservations and tickets.</p>
</section>
@endsection
