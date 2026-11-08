@extends('layouts.panel')

@section('title', 'Waiting List - Operator Portal')
@section('crumb', 'Waiting List')

@section('content')
@php
    $waitingCount = count(array_filter($waitlist, fn ($w) => $w['status'] === 'Waiting'));
    $movedCount = count(array_filter($waitlist, fn ($w) => $w['status'] === 'Moved to seat'));
@endphp

<div class="page-head">
    <div>
        <p class="eyebrow">OPERATOR OVERVIEW</p>
        <h1>Waiting list</h1>
        <p class="subtitle">Passengers waiting for a seat on a full trip. Promote queued passengers when a seat opens or remove cancelled entries.</p>
    </div>
</div>

<div class="stat-grid cols-3">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">In queue</p>
            @include('partials.icon', ['name' => 'waiting'])
        </div>
        <p class="stat-number">{{ max(9, $waitingCount) }}</p>
        <p class="stat-note">Passengers waiting</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Moved to seat</p>
            @include('partials.icon', ['name' => 'check-square'])
        </div>
        <p class="stat-number">{{ max(3, $movedCount) }}</p>
        <p class="stat-note">Seat became available</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Full trips</p>
            @include('partials.icon', ['name' => 'ship'])
        </div>
        <p class="stat-number">2</p>
        <p class="stat-note">With an active waiting list</p>
    </div>
</div>

<section class="box">
    <h2>Waiting passengers</h2>

    <div class="toolbar">
        <input type="text" id="tableSearch" class="input-search" placeholder="Search passenger, reference code, or route">
        <select id="tableStatus" class="input-select">
            <option value="">All statuses</option>
            <option value="Waiting">Waiting</option>
            <option value="Moved to seat">Moved to seat</option>
        </select>
        <a href="{{ route('operator.module', 'manifest') }}" class="input-select export-link">Open manifest</a>
    </div>

    <div class="table-wrap">
        <table class="data" id="filterTable">
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Passenger</th>
                    <th>Route</th>
                    <th>Passengers</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($waitlist as $row)
                    <tr>
                        <td><strong>#{{ $row['position'] }}</strong></td>
                        <td>
                            <strong>{{ $row['name'] }}</strong>
                            <br>
                            <small class="muted">Ref {{ $row['ref'] }} · {{ $row['contact'] }}</small>
                        </td>
                        <td>{{ $row['route'] }}</td>
                        <td><strong>{{ $row['passengers'] }}</strong></td>
                        <td>
                            <span class="badge {{ \App\Support\SampleData::badge($row['status']) }}">{{ $row['status'] }}</span>
                        </td>
                        <td>
                            <div class="action-group">
                                @if($row['status'] === 'Waiting')
                                    <form method="POST" action="{{ route('operator.waitlist.promote', $row['ref']) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-blue btn-sm">Move to seat</button>
                                    </form>
                                @else
                                    <span class="muted">Seat assigned</span>
                                @endif

                                <form method="POST" action="{{ route('operator.waitlist.delete', $row['ref']) }}" onsubmit="return confirm('Remove this waiting list entry?');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger-outline btn-sm" title="Delete waiting list entry">
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

    <p class="table-note">Showing {{ count($waitlist) }} passengers · Example data</p>
</section>
@endsection
