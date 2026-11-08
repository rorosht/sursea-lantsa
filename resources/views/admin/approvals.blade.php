@extends('layouts.panel')

@section('title', 'Operator & Route Approvals - SURSEA')
@section('crumb', 'Operator Approvals')

@section('content')
@php
    $routeRequests = \App\Support\SampleData::routeRequests();
    $pendingRouteReqs = \App\Support\SampleData::pendingRouteRequestsCount();
@endphp

<a href="{{ route('admin.dashboard') }}" class="back-link">
    @include('partials.icon', ['name' => 'arrow-left']) Back to dashboard
</a>

<div class="review-title">
    <h1>Operator &amp; Route Approvals</h1>
    <span class="badge amber">{{ $pending }} operator · {{ $pendingRouteReqs }} route pending</span>
</div>
<p class="subtitle">Review new operator business registrations and approve or reject route requests submitted by active operators.</p>

<section class="box" style="margin-top: 22px;">
    <div class="box-head">
        <div>
            <h2>Operator business applications</h2>
            <p class="subtitle">Review new businesses, verify permits, or reject/delete invalid applications before they go live.</p>
        </div>
    </div>

    @include('admin.approval-table', ['applications' => $applications])
</section>

<section class="box" id="routeRequestsBox">
    <div class="box-head">
        <div>
            <h2>Operator route approval requests</h2>
            <p class="subtitle">Approve or reject additional island routes requested by registered operators.</p>
        </div>
        <a href="{{ route('admin.module', 'lantsa-routes') }}" class="link-arrow">
            Open Lantsa &amp; Routes @include('partials.icon', ['name' => 'arrow-right'])
        </a>
    </div>

    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Operator &amp; Lantsa</th>
                    <th>Requested Route &amp; Port</th>
                    <th>Lantsa Type</th>
                    <th>Proposed Fare</th>
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
                        <td class="muted">{{ $req['submitted'] ?? 'Today' }}</td>
                        <td>
                            <span class="badge {{ \App\Support\SampleData::badge($req['status']) }}">{{ $req['status'] }}</span>
                        </td>
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
                        <td colspan="8" class="muted">No operator route requests pending.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
