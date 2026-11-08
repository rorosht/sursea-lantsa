@extends('layouts.panel')

@section('title', 'My Vessel & Routes - SURSEA')
@section('crumb', 'My Vessel & Routes')

@section('content')
@php
    $routes = \App\Support\SampleData::operatorRoutes();
    $routeRequests = array_values(array_filter(
        \App\Support\SampleData::routeRequests(),
        fn ($r) => ($r['lantsa'] ?? '') === 'MV Isla Norte' || ($r['operator'] ?? '') === 'Isla Norte Transport'
    ));
@endphp

<div class="page-head">
    <div>
        <p class="eyebrow">OPERATOR OVERVIEW</p>
        <h1>My Vessel &amp; Routes</h1>
        <p class="subtitle">Your registered lantsa, passenger capacity, and the island routes approved by the administrator.</p>
    </div>
    <div class="action-group">
        <button type="button" class="btn btn-outline" data-toggle-box="requestRouteBox">
            @include('partials.icon', ['name' => 'plus']) Request route approval
        </button>
        <a href="{{ route('operator.module', 'trip-schedule') }}" class="btn btn-blue">Manage trip schedule</a>
    </div>
</div>

<div class="stat-grid cols-3">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Vessel</p>
            @include('partials.icon', ['name' => 'ship'])
        </div>
        <p class="stat-number small">MV Isla Norte</p>
        <p class="stat-note">Passenger &amp;Cargo · 40 seats</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Approved routes</p>
            @include('partials.icon', ['name' => 'pin'])
        </div>
        <p class="stat-number">{{ count($routes) }}</p>
        <p class="stat-note">Active island connections</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Route requests</p>
            @include('partials.icon', ['name' => 'clipboard'])
        </div>
        <p class="stat-number">{{ count($routeRequests) }}</p>
        <p class="stat-note">Submitted for admin approval</p>
    </div>
</div>

{{-- Request Route Approval Box --}}
<section class="box hidden" id="requestRouteBox">
    <div class="box-head">
        <div>
            <h2>Request new route approval</h2>
            <p class="subtitle">Submit a new island route for MV Isla Norte to the administrator for review and approval.</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" data-toggle-box="requestRouteBox">Close</button>
    </div>

    <form method="POST" action="{{ route('operator.routes.request') }}">
        @csrf
        <div class="form-two">
            <div class="form-field">
                <label for="req_route">Proposed route (Origin → Destination)</label>
                <input type="text" id="req_route" name="route" placeholder="e.g. Surigao City → Basilisa" required>
            </div>
            <div class="form-field">
                <label for="req_port">Via port / pier</label>
                <input type="text" id="req_port" name="port" placeholder="e.g. Basilisa Port" required>
            </div>
        </div>
        <div class="form-two">
            <div class="form-field">
                <label for="req_fare">Proposed passenger fare</label>
                <input type="text" id="req_fare" name="fare" value="₱150" required>
            </div>
            <div class="form-field">
                <label for="req_cargo_fee">Proposed cargo fee</label>
                <input type="text" id="req_cargo_fee" name="cargo_fee" value="₱25 / item" required>
            </div>
        </div>
        <div class="form-two">
            <div class="form-field">
                <label for="req_type">Lantsa service type for this route</label>
                <select id="req_type" name="type" class="input-select" style="width: 100%;" required>
                    <option value="Passenger & Cargo">Passenger &amp; Cargo</option>
                    <option value="Passenger Only">Passenger Only</option>
                    <option value="Cargo Only">Cargo Only</option>
                </select>
            </div>
            <div class="form-field">
                <label for="req_notes">Operational notes / schedule frequency</label>
                <input type="text" id="req_notes" name="notes" placeholder="e.g. Daily morning & afternoon trips">
            </div>
        </div>
        <button type="submit" class="btn btn-blue">Submit route request</button>
    </form>
</section>

{{-- Edit Route Box --}}
<section class="box hidden" id="editOperatorRouteBox">
    <div class="box-head">
        <div>
            <h2>Edit route details</h2>
            <p class="subtitle">Update port assignment, fare, cargo fee, or route status.</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" data-toggle-box="editOperatorRouteBox">Close</button>
    </div>

    <form method="POST" action="{{ route('operator.routes.update') }}">
        @csrf
        <input type="hidden" name="id" id="edit_route_id">
        <div class="form-two">
            <div class="form-field">
                <label for="edit_route_name">Route</label>
                <input type="text" id="edit_route_name" name="route" required>
            </div>
            <div class="form-field">
                <label for="edit_route_port">Via port / pier</label>
                <input type="text" id="edit_route_port" name="port" required>
            </div>
        </div>
        <div class="form-two">
            <div class="form-field">
                <label for="edit_route_fare">Passenger fare</label>
                <input type="text" id="edit_route_fare" name="fare" required>
            </div>
            <div class="form-field">
                <label for="edit_route_cargo_fee">Cargo fee</label>
                <input type="text" id="edit_route_cargo_fee" name="cargo_fee" required>
            </div>
        </div>
        <div class="form-two">
            <div class="form-field">
                <label for="edit_route_type">Service type</label>
                <select id="edit_route_type" name="type" class="input-select" style="width: 100%;">
                    <option value="Passenger & Cargo">Passenger &amp; Cargo</option>
                    <option value="Passenger Only">Passenger Only</option>
                    <option value="Cargo Only">Cargo Only</option>
                </select>
            </div>
            <div class="form-field">
                <label for="edit_route_status">Status</label>
                <select id="edit_route_status" name="status" class="input-select" style="width: 100%;">
                    <option value="Active">Active</option>
                    <option value="Not Available">Not Available</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-blue">Save route changes</button>
    </form>
</section>

<section class="box">
    <div class="box-head">
        <div>
            <h2>My approved routes</h2>
            <p class="subtitle">Manage active island routes or edit port/fare assignments for MV Isla Norte.</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" data-toggle-box="requestRouteBox">
            @include('partials.icon', ['name' => 'plus']) Add request route
        </button>
    </div>

    <div class="toolbar">
        <input type="text" id="tableSearch" class="input-search" placeholder="Search route or port">
        <select id="tableStatus" class="input-select">
            <option value="">All statuses</option>
            <option value="Active">Active</option>
            <option value="Not Available">Not Available</option>
        </select>
        <a href="{{ route('operator.module', 'trip-schedule') }}" class="input-select export-link">View trips</a>
    </div>

    <div class="table-wrap">
        <table class="data" id="filterTable">
            <thead>
                <tr>
                    <th>Route</th>
                    <th>Via port / pier</th>
                    <th>Service type</th>
                    <th>Passenger fare</th>
                    <th>Cargo fee</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($routes as $route)
                    @php
                        $fareDisplay = is_numeric($route['fare']) ? '₱'.$route['fare'] : $route['fare'];
                        $cargoFeeDisplay = is_numeric($route['cargo_fee']) ? '₱'.$route['cargo_fee'].' / item' : $route['cargo_fee'];
                    @endphp
                    <tr>
                        <td><strong>{{ $route['route'] }}</strong></td>
                        <td>{{ $route['port'] }}</td>
                        <td><span class="badge {{ \App\Support\SampleData::badge($route['type'] ?? 'Passenger & Cargo') }}">{{ $route['type'] ?? 'Passenger & Cargo' }}</span></td>
                        <td><strong>{{ $fareDisplay }}</strong></td>
                        <td>{{ $cargoFeeDisplay }}</td>
                        <td><span class="badge {{ \App\Support\SampleData::badge($route['status']) }}">{{ $route['status'] }}</span></td>
                        <td>
                            <div class="action-group">
                                <a href="{{ route('operator.module', 'trip-schedule') }}" class="btn btn-outline btn-sm">Schedule</a>
                                <button
                                    type="button"
                                    class="btn btn-outline btn-sm"
                                    data-edit-op-route
                                    data-id="{{ $route['id'] }}"
                                    data-route="{{ $route['route'] }}"
                                    data-port="{{ $route['port'] }}"
                                    data-fare="{{ $fareDisplay }}"
                                    data-cargo-fee="{{ $cargoFeeDisplay }}"
                                    data-type="{{ $route['type'] ?? 'Passenger & Cargo' }}"
                                    data-status="{{ $route['status'] }}"
                                >Edit</button>
                                <form method="POST" action="{{ route('operator.routes.delete', $route['id']) }}" onsubmit="return confirm('Remove this route from your active vessel list?');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger-outline btn-sm" title="Delete route">
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

    <p class="table-note">Showing {{ count($routes) }} routes · Example data</p>
</section>

<section class="box">
    <div class="box-head">
        <div>
            <h2>Route approval requests</h2>
            <p class="subtitle">Track route requests submitted to the system administrator.</p>
        </div>
    </div>

    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Proposed route</th>
                    <th>Via port / pier</th>
                    <th>Service type</th>
                    <th>Proposed fare</th>
                    <th>Requested at</th>
                    <th>Status</th>
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
                        <td><strong>{{ $req['route'] }}</strong></td>
                        <td>{{ $req['port'] }}</td>
                        <td><span class="badge {{ \App\Support\SampleData::badge($req['type'] ?? 'Passenger & Cargo') }}">{{ $req['type'] ?? 'Passenger & Cargo' }}</span></td>
                        <td>{{ $reqFare }} · {{ $reqCargo }}</td>
                        <td>{{ $req['submitted'] ?? $req['requested_at'] ?? 'Today' }}</td>
                        <td><span class="badge {{ \App\Support\SampleData::badge($req['status']) }}">{{ $req['status'] }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="muted">No route requests submitted yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
