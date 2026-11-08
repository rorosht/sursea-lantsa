@extends('layouts.panel')

@section('title', 'Review Operator - SURSEA')
@section('crumb', 'Operator Approvals')

@section('content')
<a href="{{ route('admin.approvals') }}" class="back-link">
    @include('partials.icon', ['name' => 'arrow-left']) Operator approvals
</a>

<div class="review-title">
    <h1>Review operator</h1>
    <span class="badge {{ \App\Support\SampleData::badge($app['status']) }}">
        {{ $app['status'] === 'Pending' ? 'Pending approval' : $app['status'] }}
    </span>
</div>
<p class="subtitle">{{ $app['id'] }} · Submitted {{ $app['submitted_long'] }}</p>

<div class="review-grid">
    <div>
        <section class="box">
            <h2>Business &amp; contact</h2>
            <div class="white-card">
                <h3>{{ $app['business'] }}</h3>
                <p class="muted">Passenger and cargo operator · Surigao City</p>
            </div>
            <div class="info-grid">
                <div>
                    <small>Registered owner</small>
                    <strong>{{ $app['owner'] }}</strong>
                </div>
                <div>
                    <small>Email address</small>
                    <strong>{{ $app['email'] }}</strong>
                </div>
                <div>
                    <small>Contact number</small>
                    <strong>{{ $app['contact'] }}</strong>
                </div>
                <div>
                    <small>Business address</small>
                    <strong>{{ $app['address'] }}</strong>
                </div>
            </div>
        </section>

        <section class="box">
            <h2 class="with-icon">@include('partials.icon', ['name' => 'ship']) Lantsa &amp; requested route</h2>
            <div class="white-card" style="margin-bottom: 0;">
                <h3>{{ $app['lantsa'] }}</h3>
                <p class="muted">{{ $app['seats'] }} passenger seats · {{ $app['service'] }}</p>
                <strong>{{ $app['route'] }}</strong>
            </div>
        </section>

        <section class="box">
            <div class="box-head">
                <h2>Submitted documents</h2>
                <span class="badge green">3 of 3 verified</span>
            </div>
            <div class="doc-list">
                @foreach($documents as $doc)
                    <div class="doc-item">
                        <span class="doc-icon">@include('partials.icon', ['name' => 'file'])</span>
                        <div>
                            <strong>{{ $doc[0] }}</strong>
                            <small>{{ $doc[1] }}</small>
                        </div>
                        <a href="#docPreviewModal" class="doc-view" data-doc-preview="{{ $doc[0] }} ({{ $doc[1] }}) - {{ $app['business'] }}">
                            View @include('partials.icon', ['name' => 'external'])
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="review-right">
        <form method="POST" action="{{ route('admin.approvals.decide', $app['id']) }}" class="box">
            @csrf
            <h2>Review checklist</h2>
            <p class="subtitle">Confirm the required checks before making a decision.</p>

            <label class="check-line">
                <input type="checkbox" name="checks[]" value="business" checked>
                Business details confirmed
            </label>
            <label class="check-line">
                <input type="checkbox" name="checks[]" value="identity" checked>
                Operator identity verified
            </label>
            <label class="check-line">
                <input type="checkbox" name="checks[]" value="vessel" checked>
                Vessel documents verified
            </label>

            <label class="note-label" for="note">Internal note (optional)</label>
            <textarea id="note" name="note" rows="3" placeholder="Add a note for the admin team...">{{ session('decision_notes.'.$app['id']) }}</textarea>

            <button type="submit" name="decision" value="Approved" id="approveBtn" class="btn btn-blue btn-block">
                @include('partials.icon', ['name' => 'check']) Approve operator
            </button>
            <button type="submit" name="decision" value="Needs info" class="btn btn-outline btn-block">
                @include('partials.icon', ['name' => 'message']) Request information
            </button>
            <button type="submit" name="decision" value="Rejected" class="btn btn-outline-red btn-block">
                @include('partials.icon', ['name' => 'x']) Reject application
            </button>

            <p class="small-note">Your decision will notify the operator.</p>
        </form>

        <form method="POST" action="{{ route('admin.approvals.delete', $app['id']) }}" class="box" onsubmit="return confirm('Delete this operator application permanently?');">
            @csrf
            <h2>Remove application</h2>
            <p class="subtitle" style="margin-bottom: 12px;">Delete rejected, duplicate, or invalid operator applications from the queue.</p>
            <button type="submit" class="btn btn-danger-outline btn-block">
                @include('partials.icon', ['name' => 'trash']) Delete application
            </button>
        </form>

        <section class="box">
            <h2>Application activity</h2>
            <div class="timeline">
                <div>
                    <strong>Application received</strong>
                    <small>{{ $app['submitted'] }}</small>
                </div>
                <div>
                    <strong>Documents verified</strong>
                    <small>Today, 09:05</small>
                </div>
                <div>
                    <strong>{{ $app['status'] === 'Pending' ? 'Awaiting admin decision' : 'Decision: '.$app['status'] }}</strong>
                    <small>Now</small>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
