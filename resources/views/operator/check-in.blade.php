@extends('layouts.panel')

@section('title', 'Passenger Check-in - Operator Portal')
@section('crumb', 'Check-in')

@section('content')
<div class="page-head">
    <div>
        <p class="eyebrow">OPERATOR OVERVIEW</p>
        <h1>Passenger &amp; ticket check-in</h1>
        <p class="subtitle">Scan QR tickets for Passengers, Waiting List Passengers, and Cargo before boarding MV Isla Norte.</p>
    </div>
</div>

<div class="stat-grid cols-3">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Checked in</p>
            @include('partials.icon', ['name' => 'check-square'])
        </div>
        <p class="stat-number">96</p>
        <p class="stat-note">Passengers today</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Waiting to check in</p>
            @include('partials.icon', ['name' => 'ticket'])
        </div>
        <p class="stat-number">44</p>
        <p class="stat-note">Reserved &amp; waitlist tickets</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Next departure</p>
            @include('partials.icon', ['name' => 'clock'])
        </div>
        <p class="stat-number small">12:00 PM</p>
        <p class="stat-note">Surigao City → Cagdianao</p>
    </div>
</div>

@include('partials.qr-scanner-box', [
    'verifyAction' => route('operator.checkin.verify'),
    'scannerTitle' => 'QR Ticket Scanner (Passengers, Waiting List & Cargo)',
    'scannerSubtitle' => 'Scan or upload a QR ticket for reserved passengers, waiting list passengers, or cargo shipments to verify and check in.'
])

<section class="box">
    <h2>Reservations to verify</h2>

    <div class="toolbar">
        <input type="text" id="tableSearch" class="input-search" placeholder="Search reference code or passenger">
        <select id="tableStatus" class="input-select">
            <option value="">All statuses</option>
            <option value="Checked in">Checked in</option>
            <option value="Reserved">Reserved</option>
            <option value="Waiting">Waiting</option>
            <option value="Moved to seat">Moved to seat</option>
        </select>
        <a href="{{ route('operator.module', 'manifest') }}" class="input-select export-link">Full manifest</a>
    </div>

    <div class="table-wrap">
        <table class="data" id="filterTable">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Passenger</th>
                    <th>Route</th>
                    <th>Passengers</th>
                    <th>Cargo</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($manifests as $row)
                    <tr>
                        <td><strong>{{ $row['ref'] }}</strong></td>
                        <td>
                            <strong>{{ $row['name'] }}</strong>
                            <br>
                            <small class="muted">{{ $row['contact'] }}</small>
                        </td>
                        <td>{{ $row['route'] }}</td>
                        <td><strong>{{ $row['passengers'] }}</strong></td>
                        <td>{{ $row['cargo_desc'] ?? ($row['cargo_qty'] > 0 ? $row['cargo_qty'] . ' items' : 'None') }}</td>
                        <td>
                            <span class="badge {{ \App\Support\SampleData::badge($row['status']) }}">{{ $row['status'] }}</span>
                        </td>
                        <td>
                            <div class="action-group">
                                @if($row['status'] !== 'Checked in')
                                    <form method="POST" action="{{ route('operator.checkin.verify') }}">
                                        @csrf
                                        <input type="hidden" name="ref" value="{{ $row['ref'] }}">
                                        <input type="hidden" name="ticket_type" value="passenger">
                                        <button type="submit" class="btn btn-blue btn-sm">Check in</button>
                                    </form>
                                @endif

                                <button
                                    type="button"
                                    class="btn btn-outline btn-sm"
                                    data-preview-ticket="passenger"
                                    data-ref="{{ $row['ref'] }}"
                                    data-name="{{ $row['name'] }}"
                                    data-passengers="{{ $row['passengers'] }}"
                                    data-cargo="{{ $row['cargo_qty'] }}"
                                    data-route="{{ $row['route'] }}, {{ $row['date'] }}, {{ $row['departure'] }}"
                                    data-lantsa="{{ $row['lantsa'] }}"
                                    data-contact="{{ $row['contact'] }}"
                                    data-total="{{ $row['total'] }}"
                                >Ticket</button>

                                <form method="POST" action="{{ route('operator.checkin.delete', $row['ref']) }}" onsubmit="return confirm('Delete this reservation check-in record?');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger-outline btn-sm" title="Delete reservation">
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

    <p class="table-note">Showing {{ count($manifests) }} reservations · Example data</p>
</section>

@include('partials.panel-ticket-modal')
@endsection
