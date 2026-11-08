@extends('layouts.panel')

@section('title', 'Passenger Manifest - Operator Portal')
@section('crumb', 'Passenger Manifest')

@section('content')
<div class="page-head">
    <div>
        <p class="eyebrow">OPERATOR OVERVIEW</p>
        <h1>Passenger manifest</h1>
        <p class="subtitle">The official passenger and baggage/cargo manifest of MV Isla Norte across today’s crossings.</p>
    </div>
    <a href="{{ route('operator.manifest.export') }}" class="btn btn-blue">
        Export manifest @include('partials.icon', ['name' => 'download'])
    </a>
</div>

<div class="stat-grid cols-3">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Passengers</p>
            @include('partials.icon', ['name' => 'users'])
        </div>
        <p class="stat-number">140</p>
        <p class="stat-note">Across today’s trips</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Checked in</p>
            @include('partials.icon', ['name' => 'check-square'])
        </div>
        <p class="stat-number">96</p>
        <p class="stat-note">Ready to board</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Reserved</p>
            @include('partials.icon', ['name' => 'ticket'])
        </div>
        <p class="stat-number">44</p>
        <p class="stat-note">Not yet checked in</p>
    </div>
</div>

<section class="box">
    <h2>Passenger list</h2>

    <div class="toolbar">
        <input type="text" id="tableSearch" class="input-search" placeholder="Search passenger, contact, cargo, or reference code">
        <select id="tableStatus" class="input-select">
            <option value="">All statuses</option>
            <option value="Checked in">Checked in</option>
            <option value="Reserved">Reserved</option>
            <option value="Waiting">Waiting</option>
            <option value="Moved to seat">Moved to seat</option>
        </select>
        <a href="{{ route('operator.manifest.export') }}" class="input-select export-link">Export list</a>
    </div>

    <div class="table-wrap">
        <table class="data" id="filterTable">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Passenger</th>
                    <th>Contact</th>
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
                        <td><strong>{{ $row['name'] }}</strong></td>
                        <td>{{ $row['contact'] }}</td>
                        <td>{{ $row['route'] }}</td>
                        <td><strong>{{ $row['passengers'] }}</strong></td>
                        <td>
                            @if(($row['cargo_qty'] ?? 0) > 0)
                                <span class="badge blue">{{ $row['cargo_desc'] ?? ($row['cargo_qty'] . ' items') }}</span>
                            @else
                                <span class="muted">None</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ \App\Support\SampleData::badge($row['status']) }}">{{ $row['status'] }}</span>
                        </td>
                        <td>
                            <div class="action-group">
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
                                >View ticket</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="table-note">Showing {{ count($manifests) }} passengers · Example data</p>
</section>

@include('partials.panel-ticket-modal')
@endsection
