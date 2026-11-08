<section class="box qr-scanner-section" id="qrScannerBox">
    <div class="box-head">
        <div>
            <h2 class="with-icon">@include('partials.icon', ['name' => 'ticket']) QR Ticket Scanner &amp; Verification</h2>
            <p class="subtitle">Scan digital QR tickets for <strong>Passengers</strong>, <strong>Waiting List</strong>, and <strong>Cargo Deliveries</strong> using a camera or uploaded ticket screenshot.</p>
        </div>
        <span class="badge teal" id="qrScannerStatusBadge">Scanner Ready</span>
    </div>

    <div class="qr-scanner-grid">
        {{-- Left: Camera / Image QR Scanner Viewport --}}
        <div class="qr-viewport-card">
            <div class="qr-type-tabs" role="tablist">
                <button type="button" class="qr-tab active" data-qr-type="all">All Tickets</button>
                <button type="button" class="qr-tab" data-qr-type="passenger">Passenger</button>
                <button type="button" class="qr-tab" data-qr-type="waitlist">Waiting List</button>
                <button type="button" class="qr-tab" data-qr-type="cargo">Cargo</button>
            </div>

            <div class="qr-camera-frame" id="qrCameraFrame">
                <video id="qrVideo" playsinline muted class="hidden"></video>
                <div class="qr-placeholder" id="qrPlaceholder">
                    <div class="qr-reticle">
                        <span></span><span></span><span></span><span></span>
                        @include('partials.icon', ['name' => 'ticket'])
                    </div>
                    <strong>Position QR Ticket Inside Frame</strong>
                    <small class="muted">Supports Passenger Reservation, Waiting List, and Cargo Delivery QR codes</small>
                </div>
            </div>

            <div class="qr-controls">
                <button type="button" class="btn btn-blue btn-sm" id="startQrCamBtn">
                    Start Camera Scan
                </button>
                <button type="button" class="btn btn-outline-red btn-sm hidden" id="stopQrCamBtn">
                    Stop Camera
                </button>
                <label class="btn btn-outline btn-sm qr-upload-label">
                    Upload Ticket Screenshot
                    <input type="file" id="qrImageInput" accept="image/*" class="hidden">
                </label>
            </div>

            <div class="qr-demo-bar">
                <small class="muted">Quick simulate QR scan:</small>
                <div class="action-group">
                    <button
                        type="button"
                        class="btn btn-outline btn-sm"
                        data-simulate-qr="0000-00003"
                        data-qr-category="Passenger Ticket"
                        data-qr-holder="Juan Reyes (3 Passengers · 2 Cargo)"
                        data-qr-route="Surigao City → Dinagat · MV Isla Norte"
                    >Passenger QR (0000-00003)</button>

                    <button
                        type="button"
                        class="btn btn-outline btn-sm"
                        data-simulate-qr="0000-00005"
                        data-qr-category="Waiting List Ticket"
                        data-qr-holder="Pedro Lim (2 Passengers · Waitlist #1)"
                        data-qr-route="San Jose → Surigao City · MV Marajaw"
                    >Waitlist QR (0000-00005)</button>

                    <button
                        type="button"
                        class="btn btn-outline btn-sm"
                        data-simulate-qr="0000-00111"
                        data-qr-category="Cargo Delivery Ticket"
                        data-qr-holder="Robert Zabala → Earl Fermillan (9 Items)"
                        data-qr-route="Dinagat → Surigao City · MV Isla Norte"
                    >Cargo QR (0000-00111)</button>
                </div>
            </div>
        </div>

        {{-- Right: Decoded Ticket Result & Confirm Action --}}
        <div class="qr-result-card">
            <h3>Scanned Ticket Details</h3>
            <p class="subtitle">Scan a QR code on the left or select a ticket from the table below to verify.</p>

            <div class="white-card qr-detected-box" id="qrDetectedBox">
                <div class="box-head" style="margin-bottom: 6px;">
                    <strong id="qrResultCode">0000-00003</strong>
                    <span class="badge blue" id="qrResultCategory">Passenger Ticket</span>
                </div>
                <p id="qrResultHolder"><strong>Juan Reyes</strong> (3 Passengers · 2 Cargo)</p>
                <p class="muted" id="qrResultRoute">Surigao City → Dinagat · MV Isla Norte</p>
            </div>

            <form method="POST" action="{{ $verifyAction }}" id="qrVerifyForm">
                @csrf
                <div class="form-field">
                    <label for="qrScannedInput">Detected QR Reference / Tracking Code</label>
                    <input
                        type="text"
                        id="qrScannedInput"
                        name="ref"
                        value="0000-00003"
                        placeholder="Scan QR ticket or enter reference (e.g. 0000-00003 / 0000-00111)"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="qrTicketTypeSelect">Verification Action</label>
                    <select id="qrTicketTypeSelect" name="ticket_type" class="input-select" style="width: 100%;">
                        <option value="auto">Auto-detect (Passenger / Waiting List / Cargo)</option>
                        <option value="passenger">Passenger Boarding Check-in</option>
                        <option value="waitlist">Waiting List Seat Promotion &amp; Check-in</option>
                        <option value="cargo">Cargo Loading / Verification</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-blue btn-block">
                    @include('partials.icon', ['name' => 'check']) Confirm QR Verification &amp; Check In
                </button>
            </form>
        </div>
    </div>
</section>

