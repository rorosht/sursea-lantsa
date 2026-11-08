<div class="table-wrap">
    <table class="data">
        <thead>
            <tr>
                <th>Business</th>
                <th>Lantsa</th>
                <th>Service Type</th>
                <th>Submitted</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($applications as $app)
                <tr>
                    <td>
                        <strong>{{ $app['business'] }}</strong>
                        <br>
                        <small class="muted">Operator registration · {{ $app['owner'] ?? '' }}</small>
                    </td>
                    <td>
                        <strong>{{ $app['lantsa'] }}</strong>
                        <br>
                        <small class="muted">{{ $app['route'] ?? 'Surigao City → Dinagat' }}</small>
                    </td>
                    <td>
                        <span class="badge {{ \App\Support\SampleData::badge($app['service'] ?? 'Passenger & Cargo') }}">{{ $app['service'] ?? 'Passenger & Cargo' }}</span>
                    </td>
                    <td class="muted">{{ $app['submitted'] }}</td>
                    <td>
                        <span class="badge {{ \App\Support\SampleData::badge($app['status']) }}">{{ $app['status'] }}</span>
                    </td>
                    <td>
                        <div class="action-group">
                            <a href="{{ route('admin.approvals.show', $app['id']) }}" class="btn btn-blue btn-sm">
                                Review @include('partials.icon', ['name' => 'arrow-right'])
                            </a>

                            @if($app['status'] !== 'Rejected')
                                <form method="POST" action="{{ route('admin.approvals.decide', $app['id']) }}">
                                    @csrf
                                    <input type="hidden" name="decision" value="Rejected">
                                    <button type="submit" class="btn btn-outline-red btn-sm">Reject</button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.approvals.delete', $app['id']) }}" onsubmit="return confirm('Delete this operator application permanently?');">
                                @csrf
                                <button type="submit" class="btn btn-danger-outline btn-sm" title="Delete application">
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
