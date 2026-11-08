<?php

namespace App\Http\Controllers;

use App\Support\SampleData;
use Illuminate\Http\Request;

class PassengerController extends Controller
{
    // Prices used for the "Total Payment" on the ticket.
    // 1 passenger (150) + 2 cargo items (2 x 25) = 200, same as the Figma ticket.
    const PASSENGER_FARE = 150;
    const CARGO_FEE = 25;

    // ---------------------------------------------------------------
    // PUBLIC PAGES
    // ---------------------------------------------------------------

    public function home()
    {
        return view('passenger.home', [
            'places' => SampleData::places(),
            'board' => array_slice(SampleData::routeBoard(), 0, 4),
        ]);
    }

    public function schedules(Request $request)
    {
        $from = $request->query('from', 'all');
        $to = $request->query('to', 'all');

        $trips = array_filter(SampleData::trips(), function ($trip) use ($from, $to) {
            $fromOk = ($from === 'all' || $trip['from'] === $from);
            $toOk = ($to === 'all' || $trip['to'] === $to);
            return $fromOk && $toOk;
        });

        return view('passenger.schedules', [
            'trips' => $trips,
            'places' => SampleData::places(),
            'from' => $from,
            'to' => $to,
            'reservation' => SampleData::activePassengerTicket('reservation'),
        ]);
    }

    public function routeList()
    {
        return view('passenger.routes', ['board' => SampleData::routeBoard()]);
    }

    // ---------------------------------------------------------------
    // WAITING LIST PAGE
    // ---------------------------------------------------------------

    public function waitingList()
    {
        return view('passenger.waiting-list', [
            'trips' => SampleData::waitingListTrips(),
            'waitlist' => SampleData::activePassengerTicket('waitlist'),
        ]);
    }

    // ---------------------------------------------------------------
    // SEAT RESERVATION
    // ---------------------------------------------------------------

    public function seatForm($id)
    {
        $trip = $this->findTrip($id);

        if ($trip['status'] === 'Full') {
            return redirect()->route('waitlist.form', $trip['id'])
                ->with('notice', 'That trip is full, so you can join the waiting list instead.');
        }

        return view('passenger.seat-form', ['trip' => $trip]);
    }

    public function seatStore(Request $request, $id)
    {
        $trip = $this->findTrip($id);

        if ($trip['status'] === 'Full' || $trip['left'] < 1) {
            return redirect()->route('waitlist.form', $trip['id']);
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'contact' => 'required|string|max:20',
            'passengers' => 'required|integer|min:1|max:'.$trip['left'],
            'cargo_name' => 'nullable|array',
            'cargo_name.*' => 'nullable|string|max:60',
            'cargo_qty' => 'nullable|array',
            'cargo_qty.*' => 'nullable|integer|min:1|max:99',
        ]);

        $number = $this->nextNumber('ref_counter', 0);
        $ref = sprintf('0000-%05d', $number);
        $ticket = $this->passengerTicket($trip, $data, $ref, false);

        // Automatically update available seat count based on confirmed reservation
        $booked = session('trip_booked_seats', []);
        $booked[$trip['id']] = ($booked[$trip['id']] ?? 0) + (int) $data['passengers'];
        session(['trip_booked_seats' => $booked]);

        $items = $this->cargoItems($data);
        $cargoDesc = count($items) > 0
            ? implode(', ', array_map(fn ($i) => $i['name'].' ('.$i['qty'].')', $items))
            : 'None';

        // Also record in the live Passenger Manifest for Operator & Admin portals
        $customManifests = session('custom_manifests', []);
        array_unshift($customManifests, [
            'ref' => $ref,
            'name' => $ticket['name'],
            'contact' => $ticket['contact'],
            'route' => $trip['from'].' → '.$trip['to'],
            'departure' => strtoupper($trip['departure']),
            'date' => $trip['date'],
            'lantsa' => $trip['lantsa'],
            'passengers' => $ticket['passengers'],
            'cargo_qty' => $ticket['cargo_qty'],
            'cargo_desc' => $cargoDesc,
            'total' => $ticket['total'],
            'status' => 'Reserved',
        ]);

        $customTx = session('passenger_transactions', []);
        array_unshift($customTx, [
            'id' => 'TX-'.(10 + count($customTx)),
            'ref' => $ref,
            'category' => 'Seat Reservation',
            'type' => 'passenger',
            'name' => $ticket['name'],
            'contact' => $ticket['contact'],
            'route' => $trip['from'].' → '.$trip['to'],
            'date' => $trip['date'].', '.strtoupper($trip['departure']),
            'lantsa' => $trip['lantsa'],
            'passengers' => $ticket['passengers'],
            'cargo_qty' => $ticket['cargo_qty'],
            'items' => $cargoDesc,
            'total' => $ticket['total'],
            'status' => 'Reserved',
        ]);

        $username = strtolower((string) (session('user')['username'] ?? ''));
        $reservationsByUser = session('reservations_by_user', []);
        if ($username !== '') {
            $reservationsByUser[$username] = $ticket;
        }

        session([
            'custom_manifests' => $customManifests,
            'passenger_transactions' => $customTx,
            'reservations_by_user' => $reservationsByUser,
            'reservation' => $ticket,
        ]);

        return redirect()->route('seat.form', $trip['id'])->with('show_ticket', true);
    }

    public function seatCancel()
    {
        $username = strtolower((string) (session('user')['username'] ?? ''));
        $reservationsByUser = session('reservations_by_user', []);
        unset($reservationsByUser[$username]);
        session(['reservations_by_user' => $reservationsByUser]);
        session()->forget('reservation');

        return redirect()->route('schedules')
            ->with('status', 'Your active seat reservation has been removed.');
    }

    public function reservationDelete(Request $request)
    {
        $username = strtolower((string) (session('user')['username'] ?? ''));
        $target = $request->input('target', 'reservation');
        if ($target === 'waitlist') {
            $waitlistsByUser = session('waitlists_by_user', []);
            unset($waitlistsByUser[$username]);
            session(['waitlists_by_user' => $waitlistsByUser]);
            session()->forget('waitlist');
            return redirect()->route('waiting-list')
                ->with('status', 'Your waiting list entry has been removed.');
        }

        $reservationsByUser = session('reservations_by_user', []);
        unset($reservationsByUser[$username]);
        session(['reservations_by_user' => $reservationsByUser]);
        session()->forget('reservation');
        return redirect()->route('schedules')
            ->with('status', 'Your active seat reservation has been removed.');
    }

    // ---------------------------------------------------------------
    // WAITING LIST RESERVATION
    // ---------------------------------------------------------------

    public function waitlistForm($id)
    {
        $trip = $this->findTrip($id);

        return view('passenger.waitlist-form', ['trip' => $trip]);
    }

    public function waitlistStore(Request $request, $id)
    {
        $trip = $this->findTrip($id);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'contact' => 'required|string|max:20',
            'passengers' => 'required|integer|min:1|max:10',
            'cargo_name' => 'nullable|array',
            'cargo_name.*' => 'nullable|string|max:60',
            'cargo_qty' => 'nullable|array',
            'cargo_qty.*' => 'nullable|integer|min:1|max:99',
        ]);

        $number = $this->nextNumber('ref_counter', 0);
        $ref = sprintf('0000-%05d', $number);
        $ticket = $this->passengerTicket($trip, $data, $ref, true);

        $items = $this->cargoItems($data);
        $cargoDesc = count($items) > 0
            ? implode(', ', array_map(fn ($i) => $i['name'].' ('.$i['qty'].')', $items))
            : 'None';

        $customWaitlist = session('custom_waitlist', []);
        $customWaitlist[] = [
            'id' => 'WL-'.(10 + count($customWaitlist)),
            'position' => 5 + count($customWaitlist),
            'ref' => $ref,
            'name' => $ticket['name'],
            'contact' => $ticket['contact'],
            'route' => $trip['from'].' → '.$trip['to'],
            'lantsa' => $trip['lantsa'],
            'passengers' => $ticket['passengers'],
            'cargo_qty' => $ticket['cargo_qty'],
            'status' => 'Waiting',
        ];

        $customManifests = session('custom_manifests', []);
        array_unshift($customManifests, [
            'ref' => $ref,
            'name' => $ticket['name'],
            'contact' => $ticket['contact'],
            'route' => $trip['from'].' → '.$trip['to'],
            'departure' => strtoupper($trip['departure']),
            'date' => $trip['date'],
            'lantsa' => $trip['lantsa'],
            'passengers' => $ticket['passengers'],
            'cargo_qty' => $ticket['cargo_qty'],
            'cargo_desc' => $cargoDesc,
            'total' => $ticket['total'],
            'status' => 'Waiting',
        ]);

        $customTx = session('passenger_transactions', []);
        array_unshift($customTx, [
            'id' => 'TX-'.(10 + count($customTx)),
            'ref' => $ref,
            'category' => 'Waitlist Reservation',
            'type' => 'passenger',
            'name' => $ticket['name'],
            'contact' => $ticket['contact'],
            'route' => $trip['from'].' → '.$trip['to'],
            'date' => $trip['date'].', '.strtoupper($trip['departure']),
            'lantsa' => $trip['lantsa'],
            'passengers' => $ticket['passengers'],
            'cargo_qty' => $ticket['cargo_qty'],
            'items' => $cargoDesc,
            'total' => $ticket['total'],
            'status' => 'Waiting',
        ]);

        $username = strtolower((string) (session('user')['username'] ?? ''));
        $waitlistsByUser = session('waitlists_by_user', []);
        if ($username !== '') {
            $waitlistsByUser[$username] = $ticket;
        }

        session([
            'custom_waitlist' => $customWaitlist,
            'custom_manifests' => $customManifests,
            'passenger_transactions' => $customTx,
            'waitlists_by_user' => $waitlistsByUser,
            'waitlist' => $ticket,
        ]);

        return redirect()->route('waitlist.form', $trip['id'])->with('show_ticket', true);
    }

    public function waitlistCancel()
    {
        $username = strtolower((string) (session('user')['username'] ?? ''));
        $waitlistsByUser = session('waitlists_by_user', []);
        unset($waitlistsByUser[$username]);
        session(['waitlists_by_user' => $waitlistsByUser]);
        session()->forget('waitlist');

        return redirect()->route('waiting-list')
            ->with('notice', 'Your waiting list entry has been removed.');
    }

    // ---------------------------------------------------------------
    // CARGO RESERVATION
    // ---------------------------------------------------------------

    public function cargoForm($id)
    {
        return view('passenger.cargo-form', ['trip' => $this->findTrip($id)]);
    }

    public function cargoStore(Request $request, $id)
    {
        $trip = $this->findTrip($id);

        $data = $request->validate([
            'sender_name' => 'required|string|max:100',
            'sender_contact' => 'required|string|max:20',
            'receiver_name' => 'required|string|max:100',
            'receiver_contact' => 'required|string|max:20',
            'cargo_name' => 'required|array',
            'cargo_name.*' => 'nullable|string|max:60',
            'cargo_qty' => 'nullable|array',
            'cargo_qty.*' => 'nullable|integer|min:1|max:99',
        ]);

        $items = $this->cargoItems($data);

        if (count($items) === 0) {
            return back()->withErrors(['cargo_name' => 'Please add at least one cargo item.'])->withInput();
        }

        $formattedItems = array_map(fn ($i) => $i['name'].' ('.$i['qty'].')', $items);
        $qty = array_sum(array_column($items, 'qty'));
        $number = $this->nextNumber('cargo_counter', 110);
        $ref = sprintf('0000-%05d', $number);

        $cargoTicket = [
            'type' => 'cargo',
            'ref' => $ref,
            'sender' => $data['sender_name'],
            'sender_contact' => $data['sender_contact'],
            'receiver' => $data['receiver_name'],
            'receiver_contact' => $data['receiver_contact'],
            'items' => implode(', ', $formattedItems),
            'qty' => $qty,
            'route' => $trip['from'].' → '.$trip['to'],
            'departure' => strtoupper($trip['departure']),
            'lantsa' => $trip['lantsa'],
            'fee' => $qty * self::CARGO_FEE,
            'status' => 'Pending',
        ];

        $customCargos = session('custom_cargos', []);
        array_unshift($customCargos, $cargoTicket);

        $customTx = session('passenger_transactions', []);
        array_unshift($customTx, [
            'id' => 'TX-'.(10 + count($customTx)),
            'ref' => $ref,
            'category' => 'Cargo Delivery',
            'type' => 'cargo',
            'name' => $data['sender_name'],
            'sender' => $data['sender_name'],
            'sender_contact' => $data['sender_contact'],
            'receiver' => $data['receiver_name'],
            'receiver_contact' => $data['receiver_contact'],
            'route' => $trip['from'].' → '.$trip['to'],
            'date' => $trip['date'].', '.strtoupper($trip['departure']),
            'lantsa' => $trip['lantsa'],
            'passengers' => 0,
            'cargo_qty' => $qty,
            'qty' => $qty,
            'items' => implode(', ', $formattedItems),
            'total' => $qty * self::CARGO_FEE,
            'status' => 'Pending',
        ]);

        session([
            'custom_cargos' => $customCargos,
            'passenger_transactions' => $customTx,
            'cargo' => $cargoTicket,
        ]);

        return redirect()->route('cargo.form', $trip['id'])->with('show_ticket', true);
    }

    // ---------------------------------------------------------------
    // PASSENGER NOTIFICATIONS & PROFILE HUB
    // ---------------------------------------------------------------

    public function notifications()
    {
        return view('passenger.notifications', [
            'notifications' => SampleData::passengerNotifications(),
        ]);
    }

    public function notificationRead(string $id)
    {
        $read = session('passenger_notif_read', []);
        if (! in_array($id, $read, true)) {
            $read[] = $id;
        }
        session(['passenger_notif_read' => $read]);

        return redirect()->route('passenger.notifications')
            ->with('status', 'Notification marked as read.');
    }

    public function notificationsReadAll()
    {
        session(['passenger_notif_all_read' => true]);

        return redirect()->route('passenger.notifications')
            ->with('status', 'All notifications marked as read.');
    }

    public function profile()
    {
        $baseUser = session('user', [
            'name' => 'Robert Zabala',
            'username' => 'robertzabala',
            'email' => 'passenger@sursea.example',
            'contact' => '09123123123',
        ]);
        $profileUser = array_merge($baseUser, session('passenger_profile', []));

        return view('passenger.profile', [
            'user' => $profileUser,
            'transactions' => SampleData::passengerTransactions(),
            'activities' => SampleData::passengerActivityLog(),
        ]);
    }

    public function profileSave(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:60',
            'email' => 'required|email|max:100',
            'contact' => 'required|string|max:25',
            'address' => 'nullable|string|max:150',
            'emergency_contact' => 'nullable|string|max:100',
        ]);

        session(['passenger_profile' => $data]);

        $user = session('user', []);
        $user['name'] = $data['name'];
        $user['username'] = $data['username'];
        $user['email'] = $data['email'];
        $user['contact'] = $data['contact'];
        session(['user' => $user]);

        return redirect()->route('passenger.profile', ['tab' => 'settings'])
            ->with('status', 'Passenger profile details updated.');
    }

    public function profilePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'nullable|string|min:6|confirmed',
            'new_password' => 'nullable|string|min:6|confirmed',
        ]);

        return redirect()->route('passenger.profile', ['tab' => 'security'])
            ->with('status', 'Your account password has been updated.');
    }

    public function profileSettings(Request $request)
    {
        session([
            'passenger_prefs' => [
                'sms_alerts' => $request->boolean('sms_alerts'),
                'email_receipts' => $request->boolean('email_receipts'),
                'waitlist_auto' => $request->boolean('waitlist_auto'),
            ],
        ]);

        return redirect()->route('passenger.profile')
            ->with('status', 'Notification and booking preferences saved.');
    }

    public function transactionDelete(string $ref)
    {
        $deleted = session('deleted_passenger_tx', []);
        if (! in_array($ref, $deleted, true)) {
            $deleted[] = $ref;
        }
        session(['deleted_passenger_tx' => $deleted]);

        return redirect()->route('passenger.profile')
            ->with('status', 'Transaction '.$ref.' removed from your history.');
    }

    // ---------------------------------------------------------------
    // SMALL HELPERS
    // ---------------------------------------------------------------

    private function findTrip($id): array
    {
        $trip = SampleData::trips()[(int) $id] ?? null;
        abort_if($trip === null, 404);

        return $trip;
    }

    private function nextNumber(string $key, int $start): int
    {
        $number = session($key, $start) + 1;
        session([$key => $number]);

        return $number;
    }

    private function cargoItems(array $data): array
    {
        $items = [];

        foreach (($data['cargo_name'] ?? []) as $index => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $qty = (int) ($data['cargo_qty'][$index] ?? 1);
            $items[] = ['name' => $name, 'qty' => $qty > 0 ? $qty : 1];
        }

        return $items;
    }

    private function passengerTicket(array $trip, array $data, string $ref, bool $isWaitlist): array
    {
        $items = $this->cargoItems($data);
        $cargoQty = array_sum(array_column($items, 'qty'));
        $passengers = (int) $data['passengers'];

        return [
            'type' => 'passenger',
            'ref' => $ref,
            'name' => $data['name'],
            'passengers' => $passengers,
            'cargo_qty' => $cargoQty,
            'route' => $trip['short'].', '.$trip['date'].', '.$trip['departure'],
            'lantsa' => $trip['lantsa'],
            'contact' => $data['contact'],
            'total' => ($passengers * self::PASSENGER_FARE) + ($cargoQty * self::CARGO_FEE),
            'waitlist' => $isWaitlist,
            'trip' => $trip,
        ];
    }
}
