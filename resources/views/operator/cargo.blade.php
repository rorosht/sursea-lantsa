@extends('layouts.panel')

@section('title', 'Cargo Management - Operator Portal')
@section('crumb', 'Cargo')

@section('content')
<div class="page-head">
    <div>
        <p class="eyebrow">OPERATOR OVERVIEW</p>
        <h1>Cargo</h1>
        <p class="subtitle">Record, price, and monitor cargo assigned to MV Isla Norte across each crossing.</p>
    </div>
    <button type="button" class="btn btn-blue" data-toggle-box="opAddCargoBox">
        @include('partials.icon', ['name' => 'plus']) Record cargo
    </button>
</div>

<div class="stat-grid cols-3">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Cargo pending</p>
            @include('partials.icon', ['name' => 'box'])
        </div>
        <p class="stat-number">14</p>
        <p class="stat-note">Items awaiting check-in</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">In transit</p>
            @include('partials.icon', ['name' => 'ship'])
        </div>
        <p class="stat-number">20</p>
        <p class="stat-note">Already on board</p>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Delivered</p>
            @include('partials.icon', ['name' => 'check-square'])
        </div>
        <p class="stat-number">52</p>
        <p class="stat-note">Already received</p>
    </div>
</div>

{{-- Manual Record Cargo Box --}}
<section class="box hidden" id="opAddCargoBox">
    <div class="box-head">
        <div>
            <h2>Record cargo for MV Isla Norte</h2>
            <p class="subtitle">Log cargo packages brought by senders or passengers at the port.</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" data-toggle-box="opAddCargoBox">Close</button>
    </div>

    <form method="POST" action="{{ route('operator.cargo.store') }}">
        @csrf
        <div class="form-two">
            <div class="form-field">
                <label for="sender">Sender name</label>
                <input type="text" id="sender" name="sender" placeholder="e.g. Robert Zabala" required>
            </div>
            <div class="form-field">
                <label for="sender_contact">Sender contact number</label>
                <input type="text" id="sender_contact" name="sender_contact" placeholder="e.g. 09123456789" required>
            </div>
        </div>
        <div class="form-two">
            <div class="form-field">
                <label for="receiver">Receiver name</label>
                <input type="text" id="receiver" name="receiver" placeholder="e.g. Earl Fermillan" required>
            </div>
            <div class="form-field">
                <label for="receiver_contact">Receiver contact number</label>
                <input type="text" id="receiver_contact" name="receiver_contact" placeholder="e.g. 09987654321" required>
            </div>
        </div>
        <div class="form-two">
            <div class="form-field">
                <label for="cargo_route">Route</label>
                <select id="cargo_route" name="route" class="input-select" style="width: 100%;" required>
                    <option value="Surigao City → Dinagat">Surigao City → Dinagat</option>
                    <option value="Surigao City → Cabungaan">Surigao City → Cabungaan</option>
                    <option value="Surigao City → Cagdianao">Surigao City → Cagdianao</option>
                    <option value="Surigao City → Del Carmen">Surigao City → Del Carmen</option>
                </select>
            </div>
            <div class="form-field">
                <label for="cargo_fee">Total cargo amount (₱) — optional auto-calculated at ₱25/item</label>
                <input type="number" id="cargo_fee" name="fee" min="0" placeholder="Leave blank for ₱25 × total qty">
            </div>
        </div>

        @include('partials.panel-cargo-rows', ['listId' => 'opCargoItemsList'])

        <div style="margin-top: 16px;">
            <button type="submit" class="btn btn-blue">Save cargo entry</button>
        </div>
    </form>
</section>

{{-- Set Amount Total Box --}}
<section class="box hidden" id="opSetAmountCargoBox">
    <div class="box-head">
        <div>
            <h2>Set cargo total amount</h2>
            <p class="subtitle">Set or adjust the total freight amount (₱) for the selected cargo shipment.</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" data-toggle-box="opSetAmountCargoBox">Close</button>
    </div>

    <form method="POST" action="{{ route('operator.cargo.amount') }}">
        @csrf
        <input type="hidden" name="ref" id="amount_cargo_ref">
        <div class="form-two">
            <div class="form-field">
                <label for="amount_cargo_display">Cargo reference &amp; sender</label>
                <input type="text" id="amount_cargo_display" readonly>
            </div>
            <div class="form-field">
                <label for="amount_cargo_fee">Total amount (₱)</label>
                <input type="number" id="amount_cargo_fee" name="fee" min="1" required>
            </div>
        </div>
        <button type="submit" class="btn btn-blue">Save total amount</button>
    </form>
</section>

{{-- Edit Cargo Box --}}
<section class="box hidden" id="opEditCargoBox">
    <div class="box-head">
        <div>
            <h2>Edit cargo entry</h2>
            <p class="subtitle">Update sender/receiver details, items, quantity, status, or total amount.</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" data-toggle-box="opEditCargoBox">Close</button>
    </div>

    <form method="POST" action="{{ route('operator.cargo.update') }}">
        @csrf
        <input type="hidden" name="ref" id="edit_cargo_ref">
        <div class="form-two">
            <div class="form-field">
                <label for="edit_cargo_sender">Sender name</label>
                <input type="text" id="edit_cargo_sender" name="sender" required>
            </div>
            <div class="form-field">
                <label for="edit_cargo_sender_contact">Sender contact number</label>
                <input type="text" id="edit_cargo_sender_contact" name="sender_contact" required>
            </div>
        </div>
        <div class="form-two">
            <div class="form-field">
                <label for="edit_cargo_receiver">Receiver name</label>
                <input type="text" id="edit_cargo_receiver" name="receiver" required>
            </div>
            <div class="form-field">
                <label for="edit_cargo_receiver_contact">Receiver contact number</label>
                <input type="text" id="edit_cargo_receiver_contact" name="receiver_contact" required>
            </div>
        </div>
        <div class="form-two">
            <div class="form-field">
                <label for="edit_cargo_items">Cargo items</label>
                <input type="text" id="edit_cargo_items" name="items" required>
            </div>
            <div class="form-field">
                <label for="edit_cargo_qty">Quantity</label>
                <input type="number" id="edit_cargo_qty" name="qty" min="1" max="99" required>
            </div>
        </div>
        <div class="form-two">
            <div class="form-field">
                <label for="edit_cargo_fee">Total amount (₱)</label>
                <input type="number" id="edit_cargo_fee" name="fee" min="0" required>
            </div>
            <div class="form-field">
                <label for="edit_cargo_status">Status</label>
                <select id="edit_cargo_status" name="status" class="input-select" style="width: 100%;" required>
                    @foreach(['Pending', 'In transit', 'Delivered', 'Failed'] as $st)
                        <option value="{{ $st }}">{{ $st }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-blue">Update cargo</button>
    </form>
</section>

<section class="box">
    <h2>Cargo list</h2>

    <div class="toolbar">
        <input type="text" id="tableSearch" class="input-search" placeholder="Search tracking ID, sender, receiver, or item">
        <select id="tableStatus" class="input-select">
            <option value="">All statuses</option>
            <option value="Pending">Pending</option>
            <option value="In transit">In transit</option>
            <option value="Delivered">Delivered</option>
            <option value="Failed">Failed</option>
        </select>
        <a href="{{ route('operator.reports.export') }}" class="input-select export-link">Export report</a>
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
                    <th>Total Amount</th>
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
                        <td>{{ $cargo['items'] }}</td>
                        <td><strong>{{ $cargo['qty'] }}</strong></td>
                        <td><strong>₱{{ number_format((int) ($cargo['fee'] ?? ($cargo['qty'] * 25))) }}</strong></td>
                        <td>
                            <span class="badge {{ \App\Support\SampleData::badge($cargo['status']) }}">{{ $cargo['status'] }}</span>
                        </td>
                        <td>
                            <div class="action-group">
                                @if($cargo['status'] === 'Pending')
                                    <form method="POST" action="{{ route('operator.cargo.status', $cargo['ref']) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="In transit">
                                        <button type="submit" class="btn btn-blue btn-sm">Load</button>
                                    </form>
                                @elseif($cargo['status'] === 'In transit')
                                    <form method="POST" action="{{ route('operator.cargo.status', $cargo['ref']) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="Delivered">
                                        <button type="submit" class="btn btn-blue btn-sm">Delivered</button>
                                    </form>
                                @endif

                                <button
                                    type="button"
                                    class="btn btn-outline btn-sm"
                                    data-set-cargo-amount
                                    data-ref="{{ $cargo['ref'] }}"
                                    data-sender="{{ $cargo['sender'] }}"
                                    data-fee="{{ $cargo['fee'] ?? ($cargo['qty'] * 25) }}"
                                >Set amount</button>

                                <button
                                    type="button"
                                    class="btn btn-outline btn-sm"
                                    data-edit-op-cargo
                                    data-ref="{{ $cargo['ref'] }}"
                                    data-sender="{{ $cargo['sender'] }}"
                                    data-sender-contact="{{ $cargo['sender_contact'] }}"
                                    data-receiver="{{ $cargo['receiver'] }}"
                                    data-receiver-contact="{{ $cargo['receiver_contact'] }}"
                                    data-items="{{ $cargo['items'] }}"
                                    data-qty="{{ $cargo['qty'] }}"
                                    data-fee="{{ $cargo['fee'] ?? ($cargo['qty'] * 25) }}"
                                    data-status="{{ $cargo['status'] }}"
                                >Edit</button>

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
                                    data-fee="₱{{ $cargo['fee'] ?? ($cargo['qty'] * 25) }}"
                                >Ticket</button>

                                <form method="POST" action="{{ route('operator.cargo.delete', $cargo['ref']) }}" onsubmit="return confirm('Delete this cargo record?');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger-outline btn-sm" title="Delete cargo">
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

@include('partials.panel-ticket-modal')
@endsection
