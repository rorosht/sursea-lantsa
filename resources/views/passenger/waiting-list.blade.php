@extends('layouts.passenger')

@section('title', 'Waiting List - SURSEA')

@section('content')
<div class="container page">
    <div class="wl-top">
        <div>
            <h1 class="page-title big">Waiting List</h1>
            <p class="wl-lead">The Waiting List Module lets passengers queue for a full trip, tracks their position, and automatically moves them to a confirmed reservation, with a notification, when a seat becomes available.</p>
        </div>

        @if($waitlist)
            <div class="wl-banner">
                <div class="wl-banner-head">
                    <h2>{{ $waitlist['trip']['from'] }} → {{ $waitlist['trip']['to'] }}</h2>
                    <div class="wl-banner-badges">
                        <span class="badge green-dark">On Going</span>
                        <form method="POST" action="{{ route('passenger.reservation.delete') }}" class="banner-delete-form">
                            @csrf
                            <input type="hidden" name="target" value="waitlist">
                            <button type="submit" class="banner-delete-btn" title="Remove from waiting list" aria-label="Remove from waiting list">
                                @include('partials.icon', ['name' => 'trash'])
                            </button>
                        </form>
                    </div>
                </div>
                <div class="wl-banner-grid">
                    <div>
                        <p>Next Arrival Time</p>
                        <strong>@include('partials.icon', ['name' => 'clock']) {{ $waitlist['trip']['arrival'] }}</strong>
                    </div>
                    <div>
                        <p>Departure Time</p>
                        <strong>@include('partials.icon', ['name' => 'clock']) {{ $waitlist['trip']['departure'] }}</strong>
                    </div>
                </div>
                <div class="wl-banner-bottom">
                    <button type="button" class="btn banner-btn" data-open-modal="ticketModal">View Ticket</button>
                    <div class="capacity">
                        <p>Capacity</p>
                        <strong>21/45</strong>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <h2 class="section-title">On Going List</h2>

    <div class="wl-grid">
        @foreach($trips as $trip)
            <article class="wl-card">
                <div class="trip-top">
                    <h3>{{ $trip['from'] }} → {{ $trip['to'] }}</h3>
                    <span class="badge solid blue">On Going</span>
                </div>
                <p class="trip-sub">{{ $trip['lantsa'] }} · Today</p>

                <div class="trip-mid">
                    <div>
                        <span class="small-label">Next Arrival Time</span>
                        <strong class="time">@include('partials.icon', ['name' => 'clock']) {{ $trip['arrival'] }}</strong>
                    </div>
                    <div class="cap">
                        <span class="small-label">Capacity</span>
                        <span class="cap-value">{{ $trip['capacity_text'] ?? '25/45' }}</span>
                    </div>
                </div>

                <div class="trip-actions">
                    <a href="{{ route('waitlist.form', $trip['id']) }}" class="btn btn-dark btn-sm">Add to waiting list</a>
                </div>
            </article>
        @endforeach
    </div>
</div>

@if($waitlist)
    @include('partials.ticket', ['ticket' => $waitlist, 'open' => false])
@endif
@endsection
