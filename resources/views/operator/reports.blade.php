@extends('layouts.panel')

@section('title', 'Operations Reports & Revenue - SURSEA')
@section('crumb', 'Operations Reports & Revenue')

@section('content')
@php
    $trips = \App\Support\SampleData::tripScheduleRecords();
    $manifests = \App\Support\SampleData::manifestRecords();
    $cargos = \App\Support\SampleData::cargoRecords();

    $totalPassengers = 140;
    $passengerRevenue = 21000;
    $totalCargoItems = 29;
    $totalCargoCapacity = 120;
    $cargoRevenue = 725;
    $grossRevenue = $passengerRevenue + $cargoRevenue;
@endphp

<div class="page-head">
    <div>
        <p class="eyebrow">OPERATOR OVERVIEW</p>
        <h1>Operations Reports &amp; Revenue</h1>
        <p class="subtitle">Daily gross revenue, passenger fare collections, cargo freight capacity, trips, and transaction logs for MV Isla Norte.</p>
    </div>
    <a href="{{ route('operator.reports.export') }}" class="btn btn-blue">
        Export operations &amp; revenue CSV @include('partials.icon', ['name' => 'download'])
    </a>
</div>

<div class="stat-grid cols-3">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total gross revenue</p>
            @include('partials.icon', ['name' => 'report'])
        </div>
        <p class="stat-number">₱{{ number_format($grossRevenue) }}</p>
        <p class="stat-note">Passenger fares + cargo freight today</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Passengers revenue</p>
            @include('partials.icon', ['name' => 'users'])
        </div>
        <p class="stat-number">₱{{ number_format($passengerRevenue) }}</p>
        <p class="stat-note"><strong>{{ $totalPassengers }} total passengers</strong> · ₱150 / seat</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total cargo freight</p>
            @include('partials.icon', ['name' => 'box'])
        </div>
        <p class="stat-number">₱{{ number_format($cargoRevenue) }}</p>
        <p class="stat-note"><strong>{{ $totalCargoItems }} / {{ $totalCargoCapacity }} items total capacity</strong></p>
    </div>
</div>

{{-- Trips & Revenue Table --}}
<section class="box">
    <div class="box-head">
        <div>
            <h2>Trips &amp; route revenue</h2>
            <p class="subtitle">Scheduled crossings, passenger occupancy, cargo volume, and gross revenue per trip.</p>
        </div>
        <select class="input-select small-select" aria-label="Filter period">
            <option>Today · MV Isla Norte</option>
            <option>This week · MV Isla Norte</option>
            <option>This month · MV Isla Norte</option>
        </select>
    </div>

    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>Departure</th>
                    <th>Route</th>
                    <th>Passengers (Reserved / Cap)</th>
                    <th>Passenger revenue</th>
                    <th>Cargo items / Cap</th>
                    <th>Cargo freight</th>
                    <th>Trip gross</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $tripRevenueRows = [
                        ['06:00 AM', 'Surigao City → Dinagat', '40 / 40', 6000, '8 / 20', 200, 'Completed'],
                        ['08:00 AM', 'Surigao City → Cabungaan', '38 / 40', 5700, '7 / 20', 175, 'Completed'],
                        ['10:00 AM', 'Surigao City → Dinagat', '35 / 40', 5250, '6 / 20', 150, 'On Going'],
                        ['12:00 PM', 'Surigao City → Cagdianao', '12 / 40', 1800, '4 / 20', 100, 'Boarding'],
                        ['01:30 PM', 'Surigao City → Del Carmen', '10 / 40', 1500, '2 / 20', 50, 'Available'],
                        ['03:00 PM', 'Dinagat → Surigao City', '5 / 40', 750, '2 / 20', 50, 'Scheduled'],
                    ];
                @endphp
                @foreach($tripRevenueRows as $tr)
                    <tr>
                        <td><strong>{{ $tr[0] }}</strong></td>
                        <td><strong>{{ $tr[1] }}</strong></td>
                        <td>{{ $tr[2] }}</td>
                        <td>₱{{ number_format($tr[3]) }}</td>
                        <td>{{ $tr[4] }}</td>
                        <td>₱{{ number_format($tr[5]) }}</td>
                        <td><strong>₱{{ number_format($tr[3] + $tr[5]) }}</strong></td>
                        <td><span class="badge {{ \App\Support\SampleData::badge($tr[6]) }}">{{ $tr[6] }}</span></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">TOTAL (6 TRIPS)</td>
                    <td>{{ $totalPassengers }} / 240 seats</td>
                    <td>₱{{ number_format($passengerRevenue) }}</td>
                    <td>{{ $totalCargoItems }} / {{ $totalCargoCapacity }} items</td>
                    <td>₱{{ number_format($cargoRevenue) }}</td>
                    <td>₱{{ number_format($grossRevenue) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</section>

{{-- Transactions List Table --}}
<section class="box">
    <div class="box-head">
        <div>
            <h2>Transactions list</h2>
            <p class="subtitle">Recorded passenger seat bookings and cargo freight collections for MV Isla Norte.</p>
        </div>
    </div>

    <div class="toolbar">
        <input type="text" id="tableSearch" class="input-search" placeholder="Search reference, customer name, or route">
        <select id="tableStatus" class="input-select">
            <option value="">All types &amp; statuses</option>
            <option value="Passenger Seat">Passenger Seat</option>
            <option value="Cargo Freight">Cargo Freight</option>
            <option value="Checked in">Checked in</option>
            <option value="Reserved">Reserved</option>
            <option value="Delivered">Delivered</option>
        </select>
        <a href="{{ route('operator.reports.export') }}" class="input-select export-link">Export transactions</a>
    </div>

    <div class="table-wrap">
        <table class="data" id="filterTable">
            <thead>
                <tr>
                    <th>Ref ID</th>
                    <th>Type</th>
                    <th>Customer / Sender</th>
                    <th>Route</th>
                    <th>Details</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($manifests as $m)
                    <tr>
                        <td><strong>{{ $m['ref'] }}</strong></td>
                        <td><span class="badge blue">Passenger Seat</span></td>
                        <td>
                            <strong>{{ $m['name'] }}</strong>
                            <br>
                            <small class="muted">{{ $m['contact'] }}</small>
                        </td>
                        <td>{{ $m['route'] }}</td>
                        <td>{{ $m['passengers'] }} pax · {{ $m['cargo_desc'] ?? 'No cargo' }}</td>
                        <td><strong>₱{{ $m['total'] }}</strong></td>
                        <td><span class="badge {{ \App\Support\SampleData::badge($m['status']) }}">{{ $m['status'] }}</span></td>
                    </tr>
                @endforeach
                @foreach($cargos as $c)
                    <tr>
                        <td><strong>{{ $c['ref'] }}</strong></td>
                        <td><span class="badge amber">Cargo Freight</span></td>
                        <td>
                            <strong>{{ $c['sender'] }}</strong> → {{ $c['receiver'] }}
                            <br>
                            <small class="muted">{{ $c['sender_contact'] }}</small>
                        </td>
                        <td>{{ $c['route'] }}</td>
                        <td>{{ $c['qty'] }} items ({{ $c['items'] }})</td>
                        <td><strong>₱{{ $c['fee'] ?? ($c['qty'] * 25) }}</strong></td>
                        <td><span class="badge {{ \App\Support\SampleData::badge($c['status']) }}">{{ $c['status'] }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="box cream">
    <h2>Trip completion &amp; capacity utilization today</h2>
    <p class="subtitle">2 completed of 6 scheduled crossings · {{ $totalPassengers }} passengers &amp; {{ $totalCargoItems }} cargo items recorded</p>
    <div class="progress">
        <span style="width: 58.3%"></span>
    </div>
    <div class="progress-text">
        <strong>58.3% seat capacity utilized</strong>
        <span>₱{{ number_format($grossRevenue) }} total gross revenue · Example data</span>
    </div>
</section>
@endsection
