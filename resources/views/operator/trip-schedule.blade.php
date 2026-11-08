@extends('layouts.panel')

@section('title', 'Trip Schedule - Operator Portal')
@section('crumb', 'Trip Schedule')

@section('content')
<div class="page-head">
    <div>
        <p class="eyebrow">OPERATOR OVERVIEW</p>
        <h1>Trip schedule</h1>
        <p class="subtitle">Monitor and update today’s crossings, seat capacity, and trip availability for MV Isla Norte.</p>
    </div>

    <button type="button" class="btn btn-blue" data-toggle-box="opAddTripBox">
        @include('partials.icon', ['name' => 'plus']) Add trip
    </button>
</div>

<div class="stat-grid cols-3">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Trips today</p>
            @include('partials.icon', ['name' => 'calendar'])
        </div>
        <p class="stat-number">{{ count($trips) }}</p>
        <p class="stat-note">Scheduled crossings</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Departed / active</p>
            @include('partials.icon', ['name' => 'ship'])
        </div>
        <p class="stat-number">2</p>
        <p class="stat-note">Already left the port</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Remaining</p>
            @include('partials.icon', ['name' => 'clock'])
        </div>
        <p class="stat-number">{{ max(1, count($trips) - 2) }}</p>
        <p class="stat-note">Still to depart</p>
    </div>
</div>

{{-- Add Trip Form (with capacity and status) --}}
<section class="box hidden" id="opAddTripBox">
    <div class="box-head">
        <div>
            <h2>Add crossing for MV Isla Norte</h2>
            <p class="subtitle">Schedule a departure on one of your approved routes with seat capacity and status.</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" data-toggle-box="opAddTripBox">Close</button>
    </div>

    <form method="POST" action="{{ route('operator.trips.store') }}">
        @csrf
        <div class="form-two">
            <div class="form-field">
                <label for="route">Route</label>
                <select id="route" name="route" class="input-select" style="width: 100%;" required>
                    <option value="Surigao City → Dinagat|Surigao City Port">Surigao City → Dinagat (Surigao City Port)</option>
                    <option value="Dinagat → Surigao City|Dinagat Port">Dinagat → Surigao City (Dinagat Port)</option>
                    <option value="Surigao City → Cabungaan|San Jose Wharf">Surigao City → Cabungaan (San Jose Wharf)</option>
                    <option value="Surigao City → Cagdianao|Cagdianao Pier">Surigao City → Cagdianao (Cagdianao Pier)</option>
                    <option value="Surigao City → Del Carmen|Del Carmen Port">Surigao City → Del Carmen (Del Carmen Port)</option>
                </select>
            </div>
            <div class="form-field">
                <label for="departure">Departure time</label>
                <input type="text" id="departure" name="departure" value="04:30 PM" required>
            </div>
        </div>
        <div class="form-two">
            <div class="form-field">
                <label for="capacity">Seat capacity</label>
                <input type="number" id="capacity" name="capacity" min="1" max="200" value="40" required>
            </div>
            <div class="form-field">
                <label for="status">Trip status</label>
                <select id="status" name="status" class="input-select" style="width: 100%;" required>
                    <option value="Available">Available</option>
                    <option value="Almost Full">Almost Full</option>
                    <option value="Boarding">Boarding</option>
                    <option value="On Going">On Going</option>
                    <option value="Scheduled">Scheduled</option>
                    <option value="Completed">Completed</option>
                    <option value="Not Available">Not Available</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-blue">Save crossing</button>
    </form>
</section>

{{-- Edit Trip Form --}}
<section class="box hidden" id="opEditTripBox">
    <div class="box-head">
        <div>
            <h2>Edit trip schedule</h2>
            <p class="subtitle">Update departure time, route, seat capacity, or status for this crossing.</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" data-toggle-box="opEditTripBox">Close</button>
    </div>

    <form method="POST" action="{{ route('operator.trips.update') }}">
        @csrf
        <input type="hidden" name="id" id="edit_trip_id">
        <div class="form-two">
            <div class="form-field">
                <label for="edit_trip_route">Route</label>
                <input type="text" id="edit_trip_route" name="route" required>
            </div>
            <div class="form-field">
                <label for="edit_trip_port">Port / pier</label>
                <input type="text" id="edit_trip_port" name="port" required>
            </div>
        </div>
        <div class="form-two">
            <div class="form-field">
                <label for="edit_trip_departure">Departure time</label>
                <input type="text" id="edit_trip_departure" name="departure" required>
            </div>
            <div class="form-field">
                <label for="edit_trip_capacity">Seat capacity</label>
                <input type="number" id="edit_trip_capacity" name="capacity" min="1" max="200" required>
            </div>
        </div>
        <div class="form-two">
            <div class="form-field">
                <label for="edit_trip_reserved">Reserved seats</label>
                <input type="number" id="edit_trip_reserved" name="reserved" min="0" max="200" required>
            </div>
            <div class="form-field">
                <label for="edit_trip_status">Trip status</label>
                <select id="edit_trip_status" name="status" class="input-select" style="width: 100%;" required>
                    @foreach(['Available', 'Almost Full', 'Boarding', 'On Going', 'Scheduled', 'Completed', 'Not Available'] as $st)
                        <option value="{{ $st }}">{{ $st }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-blue">Update trip</button>
    </form>
</section>

<section class="box">
    <h2>Today’s crossings</h2>

    <div class="toolbar">
        <input type="text" id="tableSearch" class="input-search" placeholder="Search route or departure time">
        <select id="tableStatus" class="input-select">
            <option value="">All statuses</option>
            <option value="Available">Available</option>
            <option value="Almost Full">Almost Full</option>
            <option value="Boarding">Boarding</option>
            <option value="On Going">On Going</option>
            <option value="Scheduled">Scheduled</option>
            <option value="Completed">Completed</option>
            <option value="Not Available">Not Available</option>
        </select>
        <a href="{{ route('operator.module', 'manifest') }}" class="input-select export-link">View manifest</a>
    </div>

    <div class="table-wrap">
        <table class="data" id="filterTable">
            <thead>
                <tr>
                    <th>Departure</th>
                    <th>Route</th>
                    <th>Seats reserved</th>
                    <th>Seats left</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trips as $trip)
                    <tr>
                        <td><strong>{{ $trip['departure'] }}</strong></td>
                        <td>
                            <strong>{{ $trip['route'] }}</strong>
                            <br>
                            <small class="muted">{{ $trip['port'] }}</small>
                        </td>
                        <td>{{ $trip['reserved'] }} / {{ $trip['capacity'] }}</td>
                        <td class="{{ in_array($trip['seats_left'], ['Full', '2 left', 'Not Available']) ? 'warn-text' : '' }}">
                            <strong>{{ $trip['seats_left'] }}</strong>
                        </td>
                        <td>
                            <span class="badge {{ \App\Support\SampleData::badge($trip['op_status']) }}">{{ $trip['op_status'] }}</span>
                        </td>
                        <td>
                            <div class="action-group">
                                <form method="POST" action="{{ route('operator.trips.status', $trip['id']) }}" class="inline-action-form">
                                    @csrf
                                    <select name="status" class="input-select compact-select" aria-label="Change status" onchange="this.form.submit()">
                                        @foreach(['Available', 'Almost Full', 'Boarding', 'On Going', 'Scheduled', 'Completed', 'Not Available'] as $st)
                                            <option value="{{ $st }}" @selected($trip['op_status'] === $st)>{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </form>

                                <button
                                    type="button"
                                    class="btn btn-outline btn-sm"
                                    data-edit-op-trip
                                    data-id="{{ $trip['id'] }}"
                                    data-route="{{ $trip['route'] }}"
                                    data-port="{{ $trip['port'] }}"
                                    data-departure="{{ $trip['departure'] }}"
                                    data-capacity="{{ $trip['capacity'] }}"
                                    data-reserved="{{ $trip['reserved'] }}"
                                    data-status="{{ $trip['op_status'] }}"
                                >Edit</button>

                                <form method="POST" action="{{ route('operator.trips.delete', $trip['id']) }}" onsubmit="return confirm('Delete this trip schedule?');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger-outline btn-sm" title="Delete trip">
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

    <p class="table-note">Showing {{ count($trips) }} trips · Example data</p>
</section>
@endsection
