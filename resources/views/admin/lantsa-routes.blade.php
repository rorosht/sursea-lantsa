@extends('layouts.panel')

@section('title', 'Lantsa & Routes - SURSEA')
@section('crumb', 'Lantsa & Routes')

@section('content')
@php
    $routeRequests = \App\Support\SampleData::routeRequests();
    $pendingRouteReqs = \App\Support\SampleData::pendingRouteRequestsCount();
@endphp

<div class="page-head">
    <div>
        <p class="eyebrow">ADMIN MANAGEMENT</p>
        <h1>Lantsa &amp; Routes</h1>
        <p class="subtitle">Manage registered vessels, lantsa service types (Passenger &amp; Cargo, Passenger Only, Cargo Only), assigned island routes, and operator route requests.</p>
    </div>

    <div class="head-filters">
        <button type="button" class="btn btn-blue" data-toggle-box="addLantsaBox">
            @include('partials.icon', ['name' => 'plus']) Register lantsa
        </button>
    </div>
</div>

<div class="stat-grid cols-4">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Registered lantsas</p>
            @include('partials.icon', ['name' => 'ship'])
        </div>
        <p class="stat-number">{{ max(32, count($lantsas)) }}</p>
        <p class="stat-note">Across all operators</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Active routes</p>
            @include('partials.icon', ['name' => 'pin'])
        </div>
        <p class="stat-number">18</p>
        <p class="stat-note">Open for reservations</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Route requests</p>
            @include('partials.icon', ['name' => 'clipboard'])
        </div>
        <p class="stat-number">{{ $pendingRouteReqs }}</p>
        <p class="stat-note">Pending operator route approvals</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Suspended vessels</p>
            @include('partials.icon', ['name' => 'shield'])
        </div>
        <p class="stat-number">{{ count(array_filter($lantsas, fn ($v) => $v['status'] === 'Suspended')) }}</p>
        <p class="stat-note">Not accepting trips</p>
    </div>
</div>

{{-- Register Lantsa & Route Box --}}
<section class="box hidden" id="addLantsaBox">
    <div class="box-head">
        <div>
            <h2>Register vessel &amp; route</h2>
            <p class="subtitle">Assign a verified lantsa, select its vessel type, and set its route and capacity.</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" data-toggle-box="addLantsaBox">Close</button>
    </div>

    <form method="POST" action="{{ route('admin.lantsa.store') }}">
        @csrf
        <div class="form-two">
            <div class="form-field">
                <label for="lantsa">Lantsa / vessel name</label>
                <input type="text" id="lantsa" name="lantsa" placeholder="e.g. MV Sea Breeze" required>
            </div>
            <div class="form-field">
                <label for="operator">Operator business</label>
                <input type="text" id="operator" name="operator" placeholder="e.g. Sea Breeze Transport" required>
            </div>
        </div>

        <div class="form-two">
            <div class="form-field">
                <label for="type">Lantsa type</label>
                <select id="type" name="type" class="input-select" style="width: 100%;" required>
                    <option value="Passenger & Cargo">Passenger &amp; Cargo</option>
                    <option value="Passenger Only">Passenger Only</option>
                    <option value="Cargo Only">Cargo Only</option>
                </select>
            </div>
            <div class="form-field">
                <label for="route">Assigned route</label>
                <input type="text" id="route" name="route" placeholder="e.g. Surigao City → Dinagat" required>
            </div>
        </div>

        <div class="form-two">
            <div class="form-field">
                <label for="seats">Passenger seat capacity (0 if Cargo Only)</label>
                <input type="number" id="seats" name="seats" min="0" max="200" value="40" required>
            </div>
            <div class="form-field">
                <label for="port">Primary port / pier</label>
                <input type="text" id="port" name="port" value="Surigao City Port" required>
            </div>
        </div>

        <button type="submit" class="btn btn-blue">Save vessel</button>
    </form>
</section>

<section class="box">
    <h2>Lantsa directory</h2>

    <div class="toolbar">
        <input type="text" id="tableSearch" class="input-search" placeholder="Search lantsa, operator, type, or route">
        <select id="tableStatus" class="input-select">
            <option value="">All types &amp; statuses</option>
            <option value="Passenger & Cargo">Passenger &amp; Cargo</option>
            <option value="Passenger Only">Passenger Only</option>
            <option value="Cargo Only">Cargo Only</option>
            <option value="Active">Active</option>
            <option value="Suspended">Suspended</option>
        </select>
        <a href="{{ route('admin.module', 'trip-schedule') }}" class="input-select export-link">View schedules</a>
    </div>

    <div class="table-wrap">
        <table class="data" id="filterTable">
            <thead>
                <tr>
                    <th>Lantsa</th>
                    <th>Operator</th>
                    <th>Lantsa Type</th>
                    <th>Route</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lantsas as $v)
                    <tr>
                        <td><strong>{{ $v['lantsa'] }}</strong></td>
                        <td>{{ $v['operator'] }}</td>
                        <td>
                            <span class="badge {{ \App\Support\SampleData::badge($v['type'] ?? 'Passenger & Cargo') }}">
                                {{ $v['type'] ?? 'Passenger & Cargo' }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ $v['route'] }}</strong>
                            @if(!empty($v['port']))
                                <br><small class="muted">Via {{ $v['port'] }}</small>
                            @endif
                        </td>
                        <td>
                            @if(($v['type'] ?? '') === 'Cargo Only')
                                {{ $v['cargo_limit'] ?? '120 items' }}
                            @elseif(($v['type'] ?? '') === 'Passenger Only')
                                {{ $v['seats'] }} seats
                            @else
                                {{ $v['seats'] }} seats · {{ $v['cargo_limit'] ?? '50 items' }}
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ \App\Support\SampleData::badge($v['status']) }}">{{ $v['status'] }}</span>
                        </td>
                        <td>
                            <div class="action-group">
                                <form method="POST" action="{{ route('admin.lantsa.status', $v['id']) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $v['status'] === 'Active' ? 'Suspended' : 'Active' }}">
                                    <button type="submit" class="btn {{ $v['status'] === 'Active' ? 'btn-outline-red' : 'btn-blue' }} btn-sm">
                                        {{ $v['status'] === 'Active' ? 'Suspend' : 'Activate' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.lantsa.delete', $v['id']) }}" onsubmit="return confirm('Remove this vessel from the directory?');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger-outline btn-sm" title="Delete vessel">
                                        @include('partials.icon', ['name' => 'trash']) Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="table-note">Showing {{ count($lantsas) }} lantsas · Example data</p>
</section>

{{-- Operator Route Approval Requests Section --}}
<section class="box" id="routeRequestsSection">
    <div class="box-head">
        <div>
            <h2>Operator route approval requests</h2>
            <p class="subtitle">Review and approve new route requests submitted by operators before they become active on the passenger schedule.</p>
        </div>
        <span class="badge amber">{{ $pendingRouteReqs }} pending</span>
    </div>

    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Operator &amp; Vessel</th>
                    <th>Requested Route</th>
                    <th>Lantsa Type</th>
                    <th>Fare &amp; Cargo Fee</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routeRequests as $req)
                    @php
                        $reqFare = is_numeric($req['fare']) ? '₱'.$req['fare'] : $req['fare'];
                        $reqCargo = is_numeric($req['cargo_fee']) ? '₱'.$req['cargo_fee'].' / item' : $req['cargo_fee'];
                    @endphp
                    <tr>
                        <td><strong>{{ $req['id'] }}</strong></td>
                        <td>
                            <strong>{{ $req['lantsa'] }}</strong>
                            <br>
                            <small class="muted">{{ $req['business'] ?? $req['operator'] }}</small>
                        </td>
                        <td>
                            <strong>{{ $req['route'] }}</strong>
                            <br>
                            <small class="muted">Via {{ $req['port'] }}</small>
                        </td>
                        <td>
                            <span class="badge {{ \App\Support\SampleData::badge($req['type'] ?? 'Passenger & Cargo') }}">{{ $req['type'] ?? 'Passenger & Cargo' }}</span>
                        </td>
                        <td>{{ $reqFare }} · {{ $reqCargo }}</td>
                        <td>{{ $req['submitted'] ?? 'Today' }}</td>
                        <td><span class="badge {{ \App\Support\SampleData::badge($req['status']) }}">{{ $req['status'] }}</span></td>
                        <td>
                            <div class="action-group">
                                @if($req['status'] !== 'Approved')
                                    <form method="POST" action="{{ route('admin.route-requests.decide', $req['id']) }}">
                                        @csrf
                                        <input type="hidden" name="decision" value="Approved">
                                        <button type="submit" class="btn btn-blue btn-sm">Approve</button>
                                    </form>
                                @endif
                                @if($req['status'] !== 'Rejected')
                                    <form method="POST" action="{{ route('admin.route-requests.decide', $req['id']) }}">
                                        @csrf
                                        <input type="hidden" name="decision" value="Rejected">
                                        <button type="submit" class="btn btn-outline-red btn-sm">Reject</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.route-requests.delete', $req['id']) }}" onsubmit="return confirm('Delete this route request?');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger-outline btn-sm" title="Delete request">
                                        @include('partials.icon', ['name' => 'trash']) Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="muted">No route approval requests found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
