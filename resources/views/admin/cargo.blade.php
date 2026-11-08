@extends('layouts.panel')

@section('title', 'Cargo Management - SURSEA')
@section('crumb', 'Cargo')

@section('content')
@php
    $pendingCount = count(array_filter($cargos, fn ($c) => $c['status'] === 'Pending'));
    $transitCount = count(array_filter($cargos, fn ($c) => $c['status'] === 'In transit'));
    $deliveredCount = count(array_filter($cargos, fn ($c) => $c['status'] === 'Delivered'));
    $failedCount = count(array_filter($cargos, fn ($c) => $c['status'] === 'Failed'));
@endphp

<div class="page-head">
    <div>
        <p class="eyebrow">ADMIN MANAGEMENT</p>
        <h1>Cargo</h1>
        <p class="subtitle">Monitor cargo registered by passengers, track loading status, and manage or remove failed cargo processes across all operators.</p>
    </div>

    <div class="head-filters">
        <button type="button" class="btn btn-blue" data-toggle-box="addCargoBox">
            @include('partials.icon', ['name' => 'plus']) Log cargo delivery
        </button>
        <a href="{{ route('admin.cargo.export') }}" class="btn btn-outline">
            Export cargo list @include('partials.icon', ['name' => 'download'])
        </a>
    </div>
</div>

<div class="stat-grid cols-4">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Cargo registered</p>
            @include('partials.icon', ['name' => 'box'])
        </div>
        <p class="stat-number">{{ max(86, array_sum(array_column($cargos, 'qty'))) }}</p>
        <p class="stat-note">Items across the network</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Pending check-in</p>
            @include('partials.icon', ['name' => 'clock'])
        </div>
        <p class="stat-number">{{ max(14, $pendingCount) }}</p>
        <p class="stat-note">Items awaiting check-in</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">In transit</p>
            @include('partials.icon', ['name' => 'ship'])
        </div>
        <p class="stat-number">{{ max(20, $transitCount) }}</p>
        <p class="stat-note">Loaded on active vessels</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Delivered / Failed</p>
            @include('partials.icon', ['name' => 'check-square'])
        </div>
        <p class="stat-number">{{ max(52, $deliveredCount) }}</p>
        <p class="stat-note">{{ $failedCount }} failed process(es)</p>
    </div>
</div>

{{-- Log new cargo delivery form box with dynamic multi-row cargo items + quantity + aligned delete icon --}}
<section class="box hidden" id="addCargoBox">
    <div class="box-head">
        <div>
            <h2>Log a cargo delivery</h2>
            <p class="subtitle">Record cargo assigned to a lantsa crossing with multiple cargo items and quantities.</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" data-toggle-box="addCargoBox">Close</button>
    </div>

    <form method="POST" action="{{ route('admin.cargo.store') }}">
        @csrf
        <div class="form-two">
            <div class="form-field">
                <label for="sender">Sender name</label>
                <input type="text" id="sender" name="sender" placeholder="e.g. Robert Zabala" required>
            </div>
            <div class="form-field">
                <label for="sender_contact">Sender contact number</label>
                <input type="text" id="sender_contact" name="sender_contact" placeholder="e.g. 09123123123" required>
            </div>
        </div>

        <div class="form-two">
            <div class="form-field">
                <label for="receiver">Receiver name</label>
                <input type="text" id="receiver" name="receiver" placeholder="e.g. Earl Fermillan" required>
            </div>
            <div class="form-field">
                <label for="receiver_contact">Receiver contact number</label>
                <input type="text" id="receiver_contact" name="receiver_contact" placeholder="e.g. 09123123123" required>
            </div>
        </div>

        <div class="form-two">
            <div class="form-field">
                <label for="lantsa">Assigned lantsa</label>
                <select id="lantsa" name="lantsa" class="input-select" style="width: 100%;" required>
                    <option value="MV Isla Norte">MV Isla Norte</option>
                    <option value="MV Marajaw">MV Marajaw</option>
                    <option value="M/B Nonoc Express">M/B Nonoc Express</option>
                    <option value="M/B Alona Waves">M/B Alona Waves</option>
                    <option value="M/B Island Voyager">M/B Island Voyager</option>
                </select>
            </div>
            <div class="form-field">
                <label for="route">Crossing route</label>
                <select id="route" name="route" class="input-select" style="width: 100%;" required>
                    <option value="Dinagat → Surigao City">Dinagat → Surigao City</option>
                    <option value="Surigao City → Dinagat">Surigao City → Dinagat</option>
                    <option value="Surigao City → Cabungaan">Surigao City → Cabungaan</option>
                    <option value="Surigao City → Cagdianao">Surigao City → Cagdianao</option>
                    <option value="Libjo → Surigao City">Libjo → Surigao City</option>
                </select>
            </div>
        </div>

        @include('partials.panel-cargo-rows', ['listId' => 'adminCargoItemsList'])

        <div style="margin-top: 16px;">
            <button type="submit" class="btn btn-blue">Register cargo</button>
        </div>
    </form>
</section>

<section class="box">
    <div class="box-head">
        <div>
            <h2>Cargo deliveries</h2>
            <p class="subtitle">Track declared cargo items from port check-in to destination delivery, or delete failed cargo processes.</p>
        </div>
    </div>

    <div class="toolbar">
        <input type="text" id="tableSearch" class="input-search" placeholder="Search tracking ID, sender, receiver, item, or lantsa">
        <select id="tableStatus" class="input-select">
            <option value="">All statuses</option>
            <option value="Pending">Pending</option>
            <option value="In transit">In transit</option>
            <option value="Delivered">Delivered</option>
            <option value="Failed">Failed</option>
        </select>
        <a href="{{ route('admin.cargo.export') }}" class="input-select export-link">Export list</a>
    </div>

    <div class="table-wrap">
        <table class="data" id="filterTable">
            <thead>
                <tr>
                    <th>Tracking ID</th>
                    <th>Sender</th>
                    <th>Receiver</th>
                    <th>Items</th>
                    <th>Qty</th>
                    <th>Lantsa</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cargos as $cargo)
                    <tr>
                        <td><strong>{{ $cargo['ref'] }}</strong></td>
                        <td>
                            <strong>{{ $cargo['sender'] }}</strong>
                            <br>
                            <small class="muted">{{ $cargo['sender_contact'] }}</small>
                        </td>
                        <td>
                            <strong>{{ $cargo['receiver'] }}</strong>
                            <br>
                            <small class="muted">{{ $cargo['receiver_contact'] }}</small>
                        </td>
                        <td>
                            <strong>{{ $cargo['items'] }}</strong>
                            <br>
                            <small class="muted">{{ $cargo['route'] }} · ₱{{ $cargo['fee'] }}</small>
                        </td>
                        <td><strong>{{ $cargo['qty'] }}</strong></td>
                        <td>{{ $cargo['lantsa'] }}</td>
                        <td>
                            <span class="badge {{ \App\Support\SampleData::badge($cargo['status']) }}">{{ $cargo['status'] }}</span>
                        </td>
                        <td>
                            <div class="action-group">
                                @if($cargo['status'] === 'Pending')
                                    <form method="POST" action="{{ route('admin.cargo.status', $cargo['ref']) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="In transit">
                                        <button type="submit" class="btn btn-blue btn-sm">Load cargo</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.cargo.status', $cargo['ref']) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="Failed">
                                        <button type="submit" class="btn btn-outline-red btn-sm">Mark failed</button>
                                    </form>
                                @elseif($cargo['status'] === 'In transit')
                                    <form method="POST" action="{{ route('admin.cargo.status', $cargo['ref']) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="Delivered">
                                        <button type="submit" class="btn btn-blue btn-sm">Mark delivered</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.cargo.status', $cargo['ref']) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="Failed">
                                        <button type="submit" class="btn btn-outline-red btn-sm">Mark failed</button>
                                    </form>
                                @endif

                                <button
                                    type="button"
                                    class="btn btn-outline btn-sm"
                                    data-preview-ticket="cargo"
                                    data-ref="{{ $cargo['ref'] }}"
                                    data-sender="{{ $cargo['sender'] }}"
                                    data-sender-contact="{{ $cargo['sender_contact'] }}"
                                    data-receiver="{{ $cargo['receiver'] }}"
                                    data-receiver-contact="{{ $cargo['receiver_contact'] }}"
                                    data-items="{{ $cargo['items'] }}"
                                    data-qty="{{ $cargo['qty'] }}"
                                    data-lantsa="{{ $cargo['lantsa'] }}"
                                    data-fee="₱{{ $cargo['fee'] }}"
                                >Ticket</button>

                                <form method="POST" action="{{ route('admin.cargo.delete', $cargo['ref']) }}" onsubmit="return confirm('Delete this cargo process record?');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger-outline btn-sm" title="Delete failed or cancelled cargo process">
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

    <p class="table-note">Showing {{ count($cargos) }} deliveries · Example data</p>
</section>

<section class="box cream">
    <h2>Cargo weight &amp; vessel safety monitoring</h2>
    <p class="subtitle big-text">Agricultural produce, market bags, and commercial boxes must be declared prior to departure so operators and port officers can keep each outrigger within safe load limits.</p>
</section>

@include('partials.panel-ticket-modal')
@endsection
