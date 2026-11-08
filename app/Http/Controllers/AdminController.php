<?php

namespace App\Http\Controllers;

use App\Support\SampleData;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'pending' => SampleData::pendingCount(),
            'applications' => array_slice(SampleData::applications(), 0, 3),
            'lantsas' => SampleData::lantsaPreview(),
            'report' => SampleData::dailyReport(),
        ]);
    }

    // ---------------------------------------------------------------
    // OPERATOR APPROVALS & ROUTE REQUESTS
    // ---------------------------------------------------------------

    public function approvals()
    {
        return view('admin.approvals', [
            'applications' => SampleData::applications(),
            'pending' => SampleData::pendingCount(),
        ]);
    }

    public function approvalShow(string $id)
    {
        $apps = SampleData::applications();
        $app = $apps[$id] ?? null;
        abort_if($app === null, 404);

        return view('admin.approval-review', [
            'app' => $app,
            'documents' => SampleData::documents(),
        ]);
    }

    public function approvalDecide(Request $request, string $id)
    {
        $apps = SampleData::applications();
        abort_if(! isset($apps[$id]), 404);

        $data = $request->validate([
            'decision' => 'required|in:Approved,Needs info,Rejected',
            'note' => 'nullable|string|max:500',
        ]);

        $decisions = $request->session()->get('decisions', []);
        $decisions[$id] = $data['decision'];
        $request->session()->put('decisions', $decisions);

        if (! empty($data['note'])) {
            $notes = $request->session()->get('decision_notes', []);
            $notes[$id] = $data['note'];
            $request->session()->put('decision_notes', $notes);
        }

        return redirect()->back()
            ->with('status', 'Application '.$id.' updated to "'.$data['decision'].'".');
    }

    public function approvalDelete(string $id)
    {
        $deleted = session('deleted_applications', []);
        if (! in_array($id, $deleted, true)) {
            $deleted[] = $id;
        }
        session(['deleted_applications' => $deleted]);

        return redirect()->route('admin.approvals')
            ->with('status', 'Operator application '.$id.' deleted.');
    }

    public function routeRequestDecide(Request $request, string $id)
    {
        $data = $request->validate([
            'decision' => 'required|in:Approved,Rejected',
        ]);

        $decisions = session('route_request_decisions', []);
        $decisions[$id] = $data['decision'];
        session(['route_request_decisions' => $decisions]);

        return redirect()->back()
            ->with('status', 'Operator route request '.$id.' marked as '.$data['decision'].'.');
    }

    public function routeRequestDelete(string $id)
    {
        $deleted = session('route_request_deleted', []);
        if (! in_array($id, $deleted, true)) {
            $deleted[] = $id;
        }
        session(['route_request_deleted' => $deleted]);

        return redirect()->back()
            ->with('status', 'Operator route request '.$id.' deleted.');
    }

    // ---------------------------------------------------------------
    // OPERATORS DIRECTORY
    // ---------------------------------------------------------------

    public function operators()
    {
        return view('admin.operators', [
            'operators' => SampleData::operators(),
            'pending' => SampleData::pendingCount(),
        ]);
    }

    public function operatorsExport()
    {
        $rows = [['Business', 'Assigned Vessel', 'Primary Route', 'Status']];
        foreach (SampleData::operators() as $op) {
            $rows[] = [$op['business'], $op['vessel'], $op['route'], $op['status']];
        }

        return $this->csvDownload('sursea-operators.csv', $rows);
    }

    // ---------------------------------------------------------------
    // NETWORK REPORTS
    // ---------------------------------------------------------------

    public function reports()
    {
        return view('admin.reports', [
            'routes' => SampleData::routeActivity(),
            'report' => SampleData::dailyReport(),
        ]);
    }

    public function reportsExport()
    {
        $rows = [['Route', 'Completed Trips', 'Passengers Checked In', 'Cargo Items']];
        foreach (SampleData::routeActivity() as $r) {
            $rows[] = $r;
        }
        $rows[] = ['TOTAL', 18, 624, 86];

        return $this->csvDownload('sursea-daily-report.csv', $rows);
    }

    // ---------------------------------------------------------------
    // WORKSPACE SETTINGS, ACCOUNT MANAGEMENT & PROFILE
    // ---------------------------------------------------------------

    public function settings()
    {
        return view('admin.settings', [
            'settings' => session('workspace_settings', SampleData::defaultSettings()),
        ]);
    }

    public function settingsSave(Request $request)
    {
        $data = $request->validate([
            'workspace_name' => 'required|string|max:100',
            'service_area' => 'required|string|max:100',
            'timezone' => 'required|string|max:100',
            'support_contact' => 'required|email|max:100',
        ]);

        session(['workspace_settings' => $data]);

        return redirect()->route('admin.settings')->with('status', 'Workspace settings saved.');
    }

    public function accountStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'role' => 'required|in:Admin,Operator,Passenger',
            'password' => 'required|string|min:6',
        ]);

        $accounts = session('admin_managed_accounts', [
            ['id' => 'ACC-1', 'name' => 'Administrator', 'email' => 'admin@sursea.example', 'role' => 'Admin', 'status' => 'Active'],
            ['id' => 'ACC-2', 'name' => 'Isla Norte Operator', 'email' => 'islanorte@sursea.example', 'role' => 'Operator', 'status' => 'Active'],
            ['id' => 'ACC-3', 'name' => 'Marajaw Operator', 'email' => 'marajaw@sursea.example', 'role' => 'Operator', 'status' => 'Active'],
            ['id' => 'ACC-4', 'name' => 'Robert Zabala', 'email' => 'passenger@sursea.example', 'role' => 'Passenger', 'status' => 'Active'],
        ]);

        $accounts[] = [
            'id' => 'ACC-'.(count($accounts) + 1),
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'status' => 'Active',
        ];
        session(['admin_managed_accounts' => $accounts]);

        return redirect()->route('admin.settings')
            ->with('status', 'Account created for '.$data['name'].' ('.$data['role'].').');
    }

    public function accountDelete(string $id)
    {
        $accounts = session('admin_managed_accounts', [
            ['id' => 'ACC-1', 'name' => 'Administrator', 'email' => 'admin@sursea.example', 'role' => 'Admin', 'status' => 'Active'],
            ['id' => 'ACC-2', 'name' => 'Isla Norte Operator', 'email' => 'islanorte@sursea.example', 'role' => 'Operator', 'status' => 'Active'],
            ['id' => 'ACC-3', 'name' => 'Marajaw Operator', 'email' => 'marajaw@sursea.example', 'role' => 'Operator', 'status' => 'Active'],
            ['id' => 'ACC-4', 'name' => 'Robert Zabala', 'email' => 'passenger@sursea.example', 'role' => 'Passenger', 'status' => 'Active'],
        ]);

        $accounts = array_values(array_filter($accounts, fn ($a) => $a['id'] !== $id));
        session(['admin_managed_accounts' => $accounts]);

        return redirect()->route('admin.settings')
            ->with('status', 'Account '.$id.' removed.');
    }

    public function passwordUpdate(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        return redirect()->back()
            ->with('status', 'Administrator password updated.');
    }

    public function profile()
    {
        return view('admin.profile');
    }

    public function profileSave(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'title' => 'required|string|max:120',
            'email' => 'required|email|max:100',
            'contact' => 'required|string|max:30',
            'department' => 'required|string|max:120',
            'office' => 'required|string|max:150',
        ]);

        session(['admin_profile' => $data]);

        $user = session('user', []);
        $user['name'] = $data['name'];
        $user['email'] = $data['email'];
        session(['user' => $user]);

        return redirect()->route('admin.profile')
            ->with('status', 'Administrator profile updated.');
    }

    public function notificationRead(string $id)
    {
        $read = session('admin_notif_read', []);
        if (! in_array($id, $read, true)) {
            $read[] = $id;
        }
        session(['admin_notif_read' => $read]);

        return redirect()->route('admin.module', 'notifications')
            ->with('status', 'Notification marked as read.');
    }

    public function notificationsReadAll()
    {
        session(['admin_notif_all_read' => true]);

        return redirect()->route('admin.module', 'notifications')
            ->with('status', 'All notifications marked as read.');
    }

    // ---------------------------------------------------------------
    // MODULES: LANTSA & ROUTES, TRIP SCHEDULE, MANIFEST, CARGO, NOTIFICATIONS
    // ---------------------------------------------------------------

    public function module(string $module)
    {
        return match ($module) {
            'lantsa-routes' => view('admin.lantsa-routes', [
                'lantsas' => SampleData::lantsaRecords(),
            ]),
            'trip-schedule' => view('admin.trip-schedule', [
                'trips' => SampleData::tripScheduleRecords(),
                'lantsas' => SampleData::lantsaRecords(),
            ]),
            'passenger-manifest' => view('admin.passenger-manifest', [
                'manifests' => SampleData::manifestRecords(),
            ]),
            'cargo' => view('admin.cargo', [
                'cargos' => SampleData::cargoRecords(),
            ]),
            'notifications' => view('panel.notifications'),
            default => $this->genericModule($module),
        };
    }

    private function genericModule(string $module)
    {
        $config = SampleData::module('admin', $module);
        abort_if($config === null, 404);

        return view('panel.module', ['m' => $config]);
    }

    // ---------- LANTSA & ROUTES ACTIONS ----------

    public function lantsaStore(Request $request)
    {
        $data = $request->validate([
            'lantsa' => 'required|string|max:100',
            'operator' => 'required|string|max:100',
            'type' => 'required|in:Passenger & Cargo,Passenger Only,Cargo Only',
            'route' => 'required|string|max:100',
            'seats' => 'required|integer|min:0|max:200',
            'port' => 'nullable|string|max:100',
        ]);

        $custom = session('custom_lantsas', []);
        array_unshift($custom, [
            'id' => 'LR-'.(10 + count($custom)),
            'lantsa' => $data['lantsa'],
            'operator' => $data['operator'],
            'type' => $data['type'],
            'route' => $data['route'],
            'port' => $data['port'] ?? 'Surigao City Port',
            'seats' => $data['type'] === 'Cargo Only' ? 0 : (int) $data['seats'],
            'cargo_limit' => $data['type'] === 'Passenger Only' ? '0 items' : '60 items',
            'fare' => 150,
            'status' => 'Active',
        ]);
        session(['custom_lantsas' => $custom]);

        return redirect()->route('admin.module', 'lantsa-routes')
            ->with('status', $data['lantsa'].' ('.$data['type'].') was registered and assigned to '.$data['route'].'.');
    }

    public function lantsaStatus(Request $request, string $id)
    {
        $data = $request->validate(['status' => 'required|in:Active,Suspended']);
        $statuses = session('lantsa_statuses', []);
        $statuses[$id] = $data['status'];
        session(['lantsa_statuses' => $statuses]);

        return redirect()->route('admin.module', 'lantsa-routes')
            ->with('status', 'Vessel operating status updated to '.$data['status'].'.');
    }

    public function lantsaDelete(string $id)
    {
        $deleted = session('lantsa_deleted', []);
        if (! in_array($id, $deleted, true)) {
            $deleted[] = $id;
        }
        session(['lantsa_deleted' => $deleted]);

        return redirect()->route('admin.module', 'lantsa-routes')
            ->with('status', 'Vessel '.$id.' removed from directory.');
    }

    // ---------- TRIP SCHEDULE ACTIONS ----------

    public function tripStore(Request $request)
    {
        $data = $request->validate([
            'lantsa' => 'required|string',
            'route' => 'required|string',
            'departure' => 'required|string|max:30',
            'status' => 'required|in:Scheduled,Boarding,On Going',
        ]);

        [$lantsa, $operator, $seats] = array_pad(explode('|', $data['lantsa']), 3, '40');
        [$route, $port] = array_pad(explode('|', $data['route']), 2, 'Surigao City Port');

        $custom = session('custom_trips', []);
        array_unshift($custom, [
            'id' => 'TS-'.(200 + count($custom)),
            'departure' => $data['departure'],
            'arrival' => '12:00 PM',
            'date' => 'Today, Sept 26',
            'route' => $route,
            'port' => $port,
            'lantsa' => $lantsa,
            'operator' => $operator,
            'capacity' => (int) $seats,
            'reserved' => 0,
            'seats_left' => ((int) $seats).' left',
            'cargo_count' => 0,
            'status' => $data['status'],
            'op_status' => $data['status'],
        ]);
        session(['custom_trips' => $custom]);

        return redirect()->route('admin.module', 'trip-schedule')
            ->with('status', 'New crossing scheduled for '.$lantsa.' on '.$route.' at '.$data['departure'].'.');
    }

    public function tripStatus(Request $request, string $id)
    {
        $data = $request->validate([
            'status' => 'required|in:Scheduled,Boarding,On Going,Completed,Cancelled',
        ]);

        $statuses = session('trip_statuses', []);
        $statuses[$id] = $data['status'];
        session(['trip_statuses' => $statuses]);

        return redirect()->route('admin.module', 'trip-schedule')
            ->with('status', 'Trip '.$id.' status updated to '.$data['status'].'.');
    }

    public function tripsExport()
    {
        $rows = [['Trip ID', 'Departure', 'Route', 'Port', 'Lantsa', 'Operator', 'Reserved', 'Capacity', 'Seats Left', 'Status']];
        foreach (SampleData::tripScheduleRecords() as $t) {
            $rows[] = [$t['id'], $t['departure'], $t['route'], $t['port'], $t['lantsa'], $t['operator'], $t['reserved'], $t['capacity'], $t['seats_left'], $t['status']];
        }

        return $this->csvDownload('sursea-trip-schedules.csv', $rows);
    }

    // ---------- PASSENGER MANIFEST & QR SCANNER ACTIONS ----------

    public function manifestStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'contact' => 'required|string|max:20',
            'route' => 'required|string|max:100',
            'lantsa' => 'required|string|max:100',
            'passengers' => 'required|integer|min:1|max:20',
            'status' => 'required|in:Checked in,Reserved,Waiting',
        ]);

        $custom = session('custom_manifests', []);
        $ref = sprintf('0000-%05d', 20 + count($custom));
        array_unshift($custom, [
            'ref' => $ref,
            'name' => $data['name'],
            'contact' => $data['contact'],
            'route' => $data['route'],
            'departure' => '12:00 PM',
            'date' => '26-09-2026',
            'lantsa' => $data['lantsa'],
            'passengers' => (int) $data['passengers'],
            'cargo_qty' => 0,
            'cargo_desc' => 'None',
            'total' => ((int) $data['passengers']) * 150,
            'status' => $data['status'],
        ]);
        session(['custom_manifests' => $custom]);

        return redirect()->route('admin.module', 'passenger-manifest')
            ->with('status', 'Passenger '.$data['name'].' added to manifest (Reference: '.$ref.').');
    }

    public function manifestVerify(Request $request)
    {
        $data = $request->validate([
            'ref' => 'required|string|max:40',
            'ticket_type' => 'nullable|in:auto,passenger,waitlist,cargo',
        ]);
        $ref = trim($data['ref']);
        $ticketType = $data['ticket_type'] ?? 'passenger';

        // 1. Check Cargo tickets
        foreach (SampleData::cargoRecords() as $c) {
            if (strcasecmp($c['ref'], $ref) === 0) {
                $statuses = session('cargo_statuses', []);
                $statuses[$c['ref']] = 'In transit';
                session(['cargo_statuses' => $statuses]);

                return redirect()->route('admin.module', 'passenger-manifest')
                    ->with('status', 'QR Ticket Verified [CARGO]: Tracking '.$c['ref'].' ('.$c['items'].', '.$c['qty'].' items on '.$c['lantsa'].') verified and marked In transit.');
            }
        }

        // 2. Check Passenger & Waitlist tickets
        foreach (SampleData::manifestRecords() as $m) {
            if (strcasecmp($m['ref'], $ref) === 0) {
                $statuses = session('manifest_statuses', []);
                $statuses[$m['ref']] = 'Checked in';
                session(['manifest_statuses' => $statuses]);

                $label = ($m['status'] === 'Waiting' || $ticketType === 'waitlist') ? 'WAITLIST PASSENGER' : 'PASSENGER';

                return redirect()->route('admin.module', 'passenger-manifest')
                    ->with('status', 'QR Ticket Verified ['.$label.'] '.$m['ref'].': '.$m['name'].' ('.$m['passengers'].' pax) is now Checked in for '.$m['lantsa'].'.');
            }
        }

        return redirect()->route('admin.module', 'passenger-manifest')
            ->with('status', 'QR Ticket / Reference "'.$ref.'" was not found in Passenger, Waiting List, or Cargo records.');
    }

    public function manifestStatus(Request $request, string $ref)
    {
        $data = $request->validate(['status' => 'required|in:Checked in,Reserved,Waiting,Moved to seat']);

        $statuses = session('manifest_statuses', []);
        $statuses[$ref] = $data['status'];
        session(['manifest_statuses' => $statuses]);

        return redirect()->route('admin.module', 'passenger-manifest')
            ->with('status', 'Reservation '.$ref.' updated to "'.$data['status'].'".');
    }

    public function manifestDelete(string $ref)
    {
        $deleted = session('manifest_deleted', []);
        if (! in_array($ref, $deleted, true)) {
            $deleted[] = $ref;
        }
        session(['manifest_deleted' => $deleted]);

        return redirect()->route('admin.module', 'passenger-manifest')
            ->with('status', 'Manifest entry '.$ref.' deleted.');
    }

    public function manifestExport()
    {
        $rows = [['Reference Code', 'Passenger Name', 'Contact', 'Route', 'Departure', 'Lantsa', 'Passengers', 'Cargo', 'Total Paid', 'Status']];
        foreach (SampleData::manifestRecords() as $m) {
            $rows[] = [$m['ref'], $m['name'], $m['contact'], $m['route'], $m['departure'], $m['lantsa'], $m['passengers'], $m['cargo_desc'] ?? 'None', $m['total'], $m['status']];
        }

        return $this->csvDownload('sursea-passenger-manifest.csv', $rows);
    }

    // ---------- CARGO ACTIONS ----------

    public function cargoStore(Request $request)
    {
        $data = $request->validate([
            'sender' => 'required|string|max:100',
            'sender_contact' => 'required|string|max:20',
            'receiver' => 'required|string|max:100',
            'receiver_contact' => 'required|string|max:20',
            'lantsa' => 'required|string|max:100',
            'route' => 'required|string|max:100',
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

        $custom = session('custom_cargos', []);
        $ref = sprintf('0000-%05d', 120 + count($custom));
        array_unshift($custom, [
            'ref' => $ref,
            'sender' => $data['sender'],
            'sender_contact' => $data['sender_contact'],
            'receiver' => $data['receiver'],
            'receiver_contact' => $data['receiver_contact'],
            'items' => $itemsText,
            'qty' => $totalQty,
            'route' => $data['route'],
            'departure' => '12:00 PM',
            'lantsa' => $data['lantsa'],
            'fee' => $totalQty * 25,
            'status' => 'Pending',
        ]);
        session(['custom_cargos' => $custom]);

        return redirect()->route('admin.module', 'cargo')
            ->with('status', 'Cargo delivery '.$ref.' ('.$itemsText.') registered for '.$data['lantsa'].'.');
    }

    public function cargoStatus(Request $request, string $ref)
    {
        $data = $request->validate(['status' => 'required|in:Pending,In transit,Delivered,Failed']);

        $statuses = session('cargo_statuses', []);
        $statuses[$ref] = $data['status'];
        session(['cargo_statuses' => $statuses]);

        return redirect()->route('admin.module', 'cargo')
            ->with('status', 'Cargo '.$ref.' status updated to "'.$data['status'].'".');
    }

    public function cargoDelete(string $ref)
    {
        $deleted = session('cargo_deleted', []);
        if (! in_array($ref, $deleted, true)) {
            $deleted[] = $ref;
        }
        session(['cargo_deleted' => $deleted]);

        return redirect()->route('admin.module', 'cargo')
            ->with('status', 'Cargo process '.$ref.' deleted.');
    }

    public function cargoExport()
    {
        $rows = [['Tracking ID', 'Sender', 'Sender Contact', 'Receiver', 'Receiver Contact', 'Items', 'Qty', 'Route', 'Lantsa', 'Status']];
        foreach (SampleData::cargoRecords() as $c) {
            $rows[] = [$c['ref'], $c['sender'], $c['sender_contact'], $c['receiver'], $c['receiver_contact'], $c['items'], $c['qty'], $c['route'], $c['lantsa'], $c['status']];
        }

        return $this->csvDownload('sursea-cargo-deliveries.csv', $rows);
    }

    // ---------------------------------------------------------------
    // CSV HELPER
    // ---------------------------------------------------------------

    private function csvDownload(string $filename, array $rows)
    {
        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
