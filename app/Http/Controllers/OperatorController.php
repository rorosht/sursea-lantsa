<?php

namespace App\Http\Controllers;

use App\Support\SampleData;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    public function dashboard()
    {
        return view('operator.dashboard', [
            'stats' => SampleData::operatorStats(),
            'trips' => SampleData::operatorLiveTrips(),
        ]);
    }

    public function module(string $module)
    {
        return match ($module) {
            'vessel-routes' => view('operator.vessel-routes'),
            'trip-schedule' => view('operator.trip-schedule', [
                'trips' => SampleData::tripScheduleRecords('MV Isla Norte'),
            ]),
            'waiting-list' => view('operator.waiting-list', [
                'waitlist' => SampleData::waitlistRecords(),
            ]),
            'check-in' => view('operator.check-in', [
                'manifests' => SampleData::manifestRecords('MV Isla Norte'),
            ]),
            'manifest' => view('operator.manifest', [
                'manifests' => SampleData::manifestRecords('MV Isla Norte'),
            ]),
            'cargo' => view('operator.cargo', [
                'cargos' => SampleData::cargoRecords('MV Isla Norte'),
            ]),
            'reports' => view('operator.reports'),
            'notifications' => view('panel.notifications'),
            'profile' => view('operator.profile'),
            default => $this->genericModule($module),
        };
    }

    private function genericModule(string $module)
    {
        $config = SampleData::module('operator', $module);
        abort_if($config === null, 404);

        return view('panel.module', ['m' => $config]);
    }

    // ---------- MY VESSEL & ROUTES ACTIONS ----------

    public function routeRequestStore(Request $request)
    {
        $data = $request->validate([
            'route' => 'required|string|max:100',
            'port' => 'required|string|max:100',
            'fare' => 'required|string|max:30',
            'cargo_fee' => 'required|string|max:40',
            'type' => 'required|in:Passenger & Cargo,Passenger Only,Cargo Only',
            'notes' => 'nullable|string|max:200',
        ]);

        $custom = session('custom_route_requests', []);
        $reqId = 'RR-2026-0'.(3 + count($custom));
        array_unshift($custom, [
            'id' => $reqId,
            'business' => 'Isla Norte Transport',
            'operator' => 'Isla Norte Operator',
            'lantsa' => 'MV Isla Norte',
            'route' => $data['route'],
            'port' => $data['port'],
            'fare' => $data['fare'],
            'cargo_fee' => $data['cargo_fee'],
            'type' => $data['type'],
            'seats' => 40,
            'submitted' => 'Just now',
            'notes' => $data['notes'] ?? '',
            'status' => 'Pending',
        ]);
        session(['custom_route_requests' => $custom]);

        return redirect()->route('operator.module', 'vessel-routes')
            ->with('status', 'Route approval request ('.$reqId.': '.$data['route'].') submitted to the administrator.');
    }

    public function routeUpdate(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|string',
            'route' => 'required|string|max:100',
            'port' => 'required|string|max:100',
            'fare' => 'required|string|max:30',
            'cargo_fee' => 'required|string|max:40',
            'type' => 'required|in:Passenger & Cargo,Passenger Only,Cargo Only',
            'status' => 'required|in:Active,Not Available',
        ]);

        $edits = session('operator_route_edits', []);
        $edits[$data['id']] = [
            'route' => $data['route'],
            'port' => $data['port'],
            'fare' => $data['fare'],
            'cargo_fee' => $data['cargo_fee'],
            'type' => $data['type'],
            'status' => $data['status'],
        ];
        session(['operator_route_edits' => $edits]);

        return redirect()->route('operator.module', 'vessel-routes')
            ->with('status', 'Route '.$data['route'].' updated.');
    }

    public function routeDelete(string $id)
    {
        $deleted = session('operator_route_deleted', []);
        if (! in_array($id, $deleted, true)) {
            $deleted[] = $id;
        }
        session(['operator_route_deleted' => $deleted]);

        return redirect()->route('operator.module', 'vessel-routes')
            ->with('status', 'Route removed from your active routes list.');
    }

    // ---------- TRIP SCHEDULE ACTIONS ----------

    public function tripStore(Request $request)
    {
        $data = $request->validate([
            'route' => 'required|string',
            'departure' => 'required|string|max:30',
            'capacity' => 'nullable|integer|min:1|max:200',
            'status' => 'nullable|in:Available,Almost Full,Boarding,On Going,Scheduled,Completed,Not Available',
        ]);

        [$route, $port] = array_pad(explode('|', $data['route']), 2, 'Surigao City Port');
        $capacity = (int) ($data['capacity'] ?? 40);
        $status = $data['status'] ?? 'Available';

        $custom = session('custom_trips', []);
        array_unshift($custom, [
            'id' => 'TS-'.(300 + count($custom)),
            'departure' => $data['departure'],
            'arrival' => '12:00 PM',
            'date' => 'Today, Sept 26',
            'route' => $route,
            'port' => $port,
            'lantsa' => 'MV Isla Norte',
            'operator' => 'Isla Norte Transport',
            'capacity' => $capacity,
            'reserved' => 0,
            'seats_left' => $status === 'Not Available' ? 'Not Available' : ($capacity.' left'),
            'cargo_count' => 0,
            'status' => $status,
            'op_status' => $status,
        ]);
        session(['custom_trips' => $custom]);

        return redirect()->route('operator.module', 'trip-schedule')
            ->with('status', 'Added crossing '.$route.' at '.$data['departure'].' ('.$capacity.' seats, '.$status.') for MV Isla Norte.');
    }

    public function tripUpdate(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|string',
            'route' => 'required|string|max:100',
            'port' => 'required|string|max:100',
            'departure' => 'required|string|max:30',
            'capacity' => 'required|integer|min:1|max:200',
            'reserved' => 'required|integer|min:0|max:200',
            'status' => 'required|in:Available,Almost Full,Boarding,On Going,Scheduled,Completed,Not Available',
        ]);

        $capacity = (int) $data['capacity'];
        $reserved = min($capacity, (int) $data['reserved']);
        $leftCount = max(0, $capacity - $reserved);
        $seatsLeft = $data['status'] === 'Not Available'
            ? 'Not Available'
            : ($leftCount === 0 ? 'Full' : $leftCount.' left');

        $edits = session('trip_edits', []);
        $edits[$data['id']] = [
            'route' => $data['route'],
            'port' => $data['port'],
            'departure' => $data['departure'],
            'capacity' => $capacity,
            'reserved' => $reserved,
            'seats_left' => $seatsLeft,
            'status' => $data['status'],
            'op_status' => $data['status'],
        ];
        session(['trip_edits' => $edits]);

        $statuses = session('trip_statuses', []);
        $statuses[$data['id']] = $data['status'];
        session(['trip_statuses' => $statuses]);

        return redirect()->route('operator.module', 'trip-schedule')
            ->with('status', 'Trip '.$data['id'].' updated ('.$data['route'].' · '.$data['departure'].').');
    }

    public function tripStatus(Request $request, string $id)
    {
        $data = $request->validate([
            'status' => 'required|in:Available,Almost Full,Boarding,On Going,Scheduled,Completed,Not Available',
        ]);

        $statuses = session('trip_statuses', []);
        $statuses[$id] = $data['status'];
        session(['trip_statuses' => $statuses]);

        return redirect()->route('operator.module', 'trip-schedule')
            ->with('status', 'Trip status updated to '.$data['status'].'.');
    }

    public function tripDelete(string $id)
    {
        $deleted = session('trip_deleted', []);
        if (! in_array($id, $deleted, true)) {
            $deleted[] = $id;
        }
        session(['trip_deleted' => $deleted]);

        return redirect()->route('operator.module', 'trip-schedule')
            ->with('status', 'Trip '.$id.' deleted from schedule.');
    }

    // ---------- WAITING LIST ACTIONS ----------

    public function waitlistPromote(string $ref)
    {
        $wlStatuses = session('waitlist_statuses', []);
        $wlStatuses[$ref] = 'Moved to seat';

        $manifestStatuses = session('manifest_statuses', []);
        $manifestStatuses[$ref] = 'Moved to seat';

        session([
            'waitlist_statuses' => $wlStatuses,
            'manifest_statuses' => $manifestStatuses,
        ]);

        return redirect()->route('operator.module', 'waiting-list')
            ->with('status', 'Passenger '.$ref.' moved from waiting list to a confirmed seat and notified.');
    }

    public function waitlistDelete(string $ref)
    {
        $deleted = session('waitlist_deleted', []);
        if (! in_array($ref, $deleted, true)) {
            $deleted[] = $ref;
        }
        session(['waitlist_deleted' => $deleted]);

        return redirect()->route('operator.module', 'waiting-list')
            ->with('status', 'Waiting list entry '.$ref.' removed.');
    }

    // ---------- CHECK-IN & QR TICKET VERIFICATION ----------

    public function checkinVerify(Request $request)
    {
        $data = $request->validate([
            'ref' => 'required|string|max:40',
            'ticket_type' => 'nullable|in:auto,passenger,waitlist,cargo',
        ]);
        $ref = trim($data['ref']);
        $ticketType = $data['ticket_type'] ?? 'passenger';

        // 1. Check Cargo tickets if cargo selected or ref matches cargo
        foreach (SampleData::cargoRecords() as $c) {
            if (strcasecmp($c['ref'], $ref) === 0) {
                $statuses = session('cargo_statuses', []);
                $statuses[$c['ref']] = 'In transit';
                session(['cargo_statuses' => $statuses]);

                return redirect()->route('operator.module', 'check-in')
                    ->with('status', 'QR Ticket Verified [CARGO]: Tracking '.$c['ref'].' ('.$c['items'].', '.$c['qty'].' items from '.$c['sender'].' to '.$c['receiver'].') checked in and marked In transit.');
            }
        }

        // 2. Check Waiting list / Passenger manifest records
        foreach (SampleData::manifestRecords() as $m) {
            if (strcasecmp($m['ref'], $ref) === 0) {
                $statuses = session('manifest_statuses', []);
                $statuses[$m['ref']] = 'Checked in';
                session(['manifest_statuses' => $statuses]);

                $wlStatuses = session('waitlist_statuses', []);
                if (isset($wlStatuses[$m['ref']]) || $m['status'] === 'Waiting' || $ticketType === 'waitlist') {
                    $wlStatuses[$m['ref']] = 'Moved to seat';
                    session(['waitlist_statuses' => $wlStatuses]);
                }

                $label = ($m['status'] === 'Waiting' || $ticketType === 'waitlist') ? 'WAITLIST PASSENGER' : 'PASSENGER';

                return redirect()->route('operator.module', 'check-in')
                    ->with('status', 'QR Ticket Verified ['.$label.']: '.$m['name'].' (Ref: '.$m['ref'].', '.$m['passengers'].' pax, Cargo: '.($m['cargo_desc'] ?? 'None').') is now Checked in.');
            }
        }

        return redirect()->route('operator.module', 'check-in')
            ->with('status', 'QR Ticket / Reference "'.$ref.'" not found in Passenger, Waiting List, or Cargo records.');
    }

    public function checkinDelete(string $ref)
    {
        $deleted = session('manifest_deleted', []);
        if (! in_array($ref, $deleted, true)) {
            $deleted[] = $ref;
        }
        session(['manifest_deleted' => $deleted]);

        return redirect()->route('operator.module', 'check-in')
            ->with('status', 'Reservation '.$ref.' removed from check-in list.');
    }

    public function manifestExport()
    {
        $rows = [['Reference', 'Passenger', 'Contact', 'Route', 'Passengers', 'Cargo', 'Status']];
        foreach (SampleData::manifestRecords('MV Isla Norte') as $m) {
            $rows[] = [$m['ref'], $m['name'], $m['contact'], $m['route'], $m['passengers'], $m['cargo_desc'] ?? 'None', $m['status']];
        }

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'mv-isla-norte-manifest.csv', ['Content-Type' => 'text/csv']);
    }

    // ---------- CARGO ACTIONS ----------

    public function cargoStore(Request $request)
    {
        $data = $request->validate([
            'sender' => 'required|string|max:100',
            'sender_contact' => 'required|string|max:25',
            'receiver' => 'required|string|max:100',
            'receiver_contact' => 'required|string|max:25',
            'route' => 'nullable|string|max:100',
            'fee' => 'nullable|integer|min:0',
            'cargo_name' => 'nullable|array',
            'cargo_name.*' => 'nullable|string|max:80',
            'cargo_qty' => 'nullable|array',
            'cargo_qty.*' => 'nullable|integer|min:1|max:99',
            'items' => 'nullable|string|max:150',
            'qty' => 'nullable|integer|min:1|max:99',
        ]);

        $parsedItems = [];
        $totalQty = 0;
        foreach (($data['cargo_name'] ?? []) as $idx => $cName) {
            $cName = trim((string) $cName);
            if ($cName === '') {
                continue;
            }
            $cQty = max(1, (int) ($data['cargo_qty'][$idx] ?? 1));
            $parsedItems[] = $cName.' ('.$cQty.')';
            $totalQty += $cQty;
        }

        if (empty($parsedItems)) {
            $itemsText = $data['items'] ?? 'General Cargo';
            $totalQty = max(1, (int) ($data['qty'] ?? 1));
        } else {
            $itemsText = implode(', ', $parsedItems);
        }

        $fee = ! empty($data['fee']) ? (int) $data['fee'] : ($totalQty * 25);

        $custom = session('custom_cargos', []);
        $ref = sprintf('0000-%05d', 150 + count($custom));
        array_unshift($custom, [
            'ref' => $ref,
            'sender' => $data['sender'],
            'sender_contact' => $data['sender_contact'],
            'receiver' => $data['receiver'],
            'receiver_contact' => $data['receiver_contact'],
            'items' => $itemsText,
            'qty' => $totalQty,
            'route' => $data['route'] ?? 'Surigao City → Dinagat',
            'departure' => '12:00 PM',
            'lantsa' => 'MV Isla Norte',
            'fee' => $fee,
            'status' => 'Pending',
        ]);
        session(['custom_cargos' => $custom]);

        return redirect()->route('operator.module', 'cargo')
            ->with('status', 'Cargo '.$ref.' ('.$itemsText.' · ₱'.$fee.') recorded for MV Isla Norte.');
    }

    public function cargoAmount(Request $request)
    {
        $data = $request->validate([
            'ref' => 'required|string',
            'fee' => 'required|integer|min:1',
        ]);

        $edits = session('cargo_edits', []);
        $edits[$data['ref']] = array_merge($edits[$data['ref']] ?? [], [
            'fee' => (int) $data['fee'],
        ]);
        session(['cargo_edits' => $edits]);

        return redirect()->route('operator.module', 'cargo')
            ->with('status', 'Cargo '.$data['ref'].' total amount set to ₱'.number_format((int) $data['fee']).'.');
    }

    public function cargoUpdate(Request $request)
    {
        $data = $request->validate([
            'ref' => 'required|string',
            'sender' => 'required|string|max:100',
            'sender_contact' => 'required|string|max:25',
            'receiver' => 'required|string|max:100',
            'receiver_contact' => 'required|string|max:25',
            'items' => 'required|string|max:180',
            'qty' => 'required|integer|min:1|max:99',
            'fee' => 'required|integer|min:0',
            'status' => 'required|in:Pending,In transit,Delivered,Failed',
        ]);

        $edits = session('cargo_edits', []);
        $edits[$data['ref']] = [
            'sender' => $data['sender'],
            'sender_contact' => $data['sender_contact'],
            'receiver' => $data['receiver'],
            'receiver_contact' => $data['receiver_contact'],
            'items' => $data['items'],
            'qty' => (int) $data['qty'],
            'fee' => (int) $data['fee'],
            'status' => $data['status'],
        ];
        session(['cargo_edits' => $edits]);

        $statuses = session('cargo_statuses', []);
        $statuses[$data['ref']] = $data['status'];
        session(['cargo_statuses' => $statuses]);

        return redirect()->route('operator.module', 'cargo')
            ->with('status', 'Cargo '.$data['ref'].' details updated.');
    }

    public function cargoStatus(Request $request, string $ref)
    {
        $data = $request->validate(['status' => 'required|in:Pending,In transit,Delivered,Failed']);

        $statuses = session('cargo_statuses', []);
        $statuses[$ref] = $data['status'];
        session(['cargo_statuses' => $statuses]);

        return redirect()->route('operator.module', 'cargo')
            ->with('status', 'Cargo '.$ref.' updated to "'.$data['status'].'".');
    }

    public function cargoDelete(string $ref)
    {
        $deleted = session('cargo_deleted', []);
        if (! in_array($ref, $deleted, true)) {
            $deleted[] = $ref;
        }
        session(['cargo_deleted' => $deleted]);

        return redirect()->route('operator.module', 'cargo')
            ->with('status', 'Cargo '.$ref.' deleted from cargo list.');
    }

    // ---------- REPORTS, NOTIFICATIONS & PROFILE ----------

    public function reportsExport()
    {
        $rows = [
            ['Departure', 'Route', 'Passengers (Reserved/Cap)', 'Passenger Revenue (PHP)', 'Cargo Items/Cap', 'Cargo Freight (PHP)', 'Total Gross Revenue (PHP)', 'Status'],
            ['06:00 AM', 'Surigao City -> Dinagat', '40 / 40', 6000, '8 / 20', 200, 6200, 'Completed'],
            ['08:00 AM', 'Surigao City -> Cabungaan', '38 / 40', 5700, '7 / 20', 175, 5875, 'Completed'],
            ['10:00 AM', 'Surigao City -> Dinagat', '35 / 40', 5250, '6 / 20', 150, 5400, 'On Going'],
            ['12:00 PM', 'Surigao City -> Cagdianao', '12 / 40', 1800, '4 / 20', 100, 1900, 'Boarding'],
            ['01:30 PM', 'Surigao City -> Del Carmen', '10 / 40', 1500, '2 / 20', 50, 1550, 'Available'],
            ['03:00 PM', 'Dinagat -> Surigao City', '5 / 40', 750, '2 / 20', 50, 800, 'Scheduled'],
            ['TOTAL', '6 Trips', '140 / 240 seats', 21000, '29 / 120 items', 725, 21725, ''],
        ];

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'mv-isla-norte-operations-revenue.csv', ['Content-Type' => 'text/csv']);
    }

    public function notificationRead(string $id)
    {
        $read = session('operator_notif_read', []);
        if (! in_array($id, $read, true)) {
            $read[] = $id;
        }
        session(['operator_notif_read' => $read]);

        return redirect()->route('operator.module', 'notifications')
            ->with('status', 'Notification marked as read.');
    }

    public function notificationsReadAll()
    {
        session(['operator_notif_all_read' => true]);

        return redirect()->route('operator.module', 'notifications')
            ->with('status', 'All operator notifications marked as read.');
    }

    public function profileSave(Request $request)
    {
        $data = $request->validate([
            'business' => 'required|string|max:100',
            'lantsa' => 'required|string|max:100',
            'service' => 'nullable|string|max:60',
            'seats' => 'nullable|integer|min:0|max:200',
            'email' => 'required|email|max:100',
            'contact' => 'required|string|max:20',
            'address' => 'required|string|max:150',
        ]);

        $data['service'] = $data['service'] ?? 'Passenger & Cargo';
        $data['seats'] = (string) ($data['seats'] ?? '40');

        session(['operator_profile' => $data]);

        return redirect()->route('operator.module', 'profile')
            ->with('status', 'Business & vessel profile updated.');
    }

    public function profilePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        return redirect()->route('operator.module', 'profile')
            ->with('status', 'Operator account password updated.');
    }

    public function profilePreferences(Request $request)
    {
        session([
            'operator_prefs' => [
                'auto_waitlist_promote' => $request->boolean('auto_waitlist_promote'),
                'qr_sound_alert' => $request->boolean('qr_sound_alert'),
                'cargo_sms_receipt' => $request->boolean('cargo_sms_receipt'),
            ],
        ]);

        return redirect()->route('operator.module', 'profile')
            ->with('status', 'Operator operational preferences saved.');
    }
}
