@extends('layouts.passenger')

@section('title', 'Schedules - SURSEA')

@section('content')
<div class="container page">

    <div class="schedule-top">
        <div>
            <p class="eyebrow">FIND A LANTSA</p>
            <h1 class="page-title big">Where are you headed?</h1>
            <p class="lead">Browse practical schedules and reserve a seat for your next crossing.</p>
        </div>

        {{-- Shows after the passenger reserved a seat --}}
        @if($reservation)
            <div class="reserve-banner">
                <div class="banner-head-row">
                    <p>Reservation Schedule</p>
                    <form method="POST" action="{{ route('passenger.reservation.delete') }}">
                        @csrf
                        <input type="hidden" name="target" value="reservation">
                        <button type="submit" class="banner-delete-btn" title="Delete reservation" aria-label="Delete reservation">
                            @include('partials.icon', ['name' => 'trash'])
                        </button>
                    </form>
                </div>
                <h2>{{ $reservation['trip']['from'] }} → {{ $reservation['trip']['to'] }}</h2>
                <p class="banner-label">Departure Time</p>
                <div class="banner-bottom">
                    <strong>@include('partials.icon', ['name' => 'clock']) {{ $reservation['trip']['departure'] }}</strong>
                    <button type="button" class="btn banner-btn" data-open-modal="ticketModal">View Ticket</button>
                </div>
            </div>
        @endif
    </div>

    {{-- Search bar --}}
    <form class="filter-bar" method="GET" action="{{ route('schedules') }}">
        <label class="filter-field">
            <span>FROM</span>
            <select name="from">
                <option value="all">All origins</option>
                @foreach($places as $place)
                    <option value="{{ $place }}" @selected($from === $place)>{{ $place === 'Surigao City' ? 'Surigao City Port' : $place }}</option>
                @endforeach
            </select>
        </label>
        <label class="filter-field">
            <span>TO</span>
            <select name="to">
                <option value="all">All destinations</option>
                @foreach($places as $place)
                    <option value="{{ $place }}" @selected($to === $place)>{{ $place }}</option>
                @endforeach
            </select>
        </label>
        <label class="filter-field">
            <span>DATE</span>
            <input type="date" name="date" value="2026-09-26">
        </label>
        <button type="submit" class="btn btn-dark search-trips">@include('partials.icon', ['name' => 'search']) Search trips</button>
    </form>

    <h2 class="section-title">Today’s trips</h2>

    <div class="trip-grid">
        @forelse($trips as $trip)
            <article class="trip-card">
                <div class="trip-top">
                    <h3>{{ $trip['from'] }} → {{ $trip['to'] }}</h3>
                    <div class="badges">
                        @if($trip['departed'])
                            <span class="badge solid blue">Departed</span>
                        @endif
                        <span class="badge solid {{ \App\Support\SampleData::badge($trip['status']) }}">{{ $trip['status'] }}</span>
                    </div>
                </div>
                <p class="trip-sub">{{ $trip['lantsa'] }} · Today</p>

                <div class="trip-mid">
                    <div>
                        <span class="small-label">Departure Time</span>
                        <strong class="time">@include('partials.icon', ['name' => 'clock']) {{ $trip['departure'] }}</strong>
                    </div>
                    <span class="seats"><i></i> {{ $trip['seats'] }} seats</span>
                </div>

                <div class="trip-actions">
                    @if($trip['status'] === 'Full')
                        <a href="{{ route('waitlist.form', $trip['id']) }}" class="btn btn-dark btn-sm">Wait List</a>
                    @else
                        <a href="{{ route('seat.form', $trip['id']) }}" class="btn btn-dark btn-sm">Reserved seat</a>
                        <a href="{{ route('cargo.form', $trip['id']) }}" class="btn btn-dark btn-sm">Deliver Cargo</a>
                    @endif
                </div>
            </article>
        @empty
            <p class="empty">No trips found for that search. Try “All destinations”.</p>
        @endforelse
    </div>
</div>

@if($reservation)
    @include('partials.ticket', ['ticket' => $reservation, 'open' => false])
@endif
@endsection
