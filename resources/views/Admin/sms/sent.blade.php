<div class="card">
    <div class="card-header">
        <h3 class="card-title">Sent SMS Messages</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('sms.sent') }}" method="GET" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Filter by Username..." value="{{ $filters['username'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label for="sent_status" class="form-label">Status</label>
                    <select id="sent_status" name="sent_status" class="form-select">
                        <option value="">All</option>
                        <option value="failed" {{ ($filters['sent_status'] ?? '') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="resend_count" class="form-label">Resend Batch #</label>
                    <input type="number" id="resend_count" name="resend_count" class="form-control" value="{{ request('resend_count', 2) }}" min="1">
                </div>
                <div class="col-md-5">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('sms.sent') }}" class="btn btn-secondary">Clear</a>
                    <a id="resendAllBtn" href="{{ route('sms.resend_all_progress', request()->query()) }}" class="btn btn-warning">Resend All (Filtered)</a>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Username</th>
                        <th>Subject</th>
                        <th>Sender</th>
                        <th>Recipients</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Sent #</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sentSms as $sms)
                        <tr>
                            <td>{{ $sms->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $sms->username }}</td>
                            <td>{{ $sms->subject ?? 'N/A' }}</td>
                            <td>{{ $sms->sender_name }}</td>
                            <td>{{ Str::limit($sms->recipients, 30) }}</td>
                            <td>{{ Str::limit($sms->message, 50) }}</td>
                            <td>
                                @if ($sms->status == 'success')
                                    <span class="badge bg-success">Success</span>
                                @elseif($sms->status == 'failed')
                                    <span class="badge bg-danger">Failed</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                            <td><span class="badge bg-secondary">{{ $sms->sent }}</span></td>
                            <td>
                                <form action="{{ route('sms.resend_single', $sms->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to resend this SMS?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-info">Resend</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="d-flex align-items-center">
            <span class="text-muted">
                Showing {{ $sentSms->firstItem() ?? 0 }} to {{ $sentSms->lastItem() ?? 0 }}
                of {{ $sentSms->total() ?? 0 }} results
            </span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="d-flex justify-content-end">
            {{ $sentSms->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const resendBtn = document.getElementById('resendAllBtn');
        const resendCountInput = document.getElementById('resend_count');

        function updateResendLink() {
            const baseUrl = "{{ route('sms.resend_all_progress') }}";
            const currentParams = new URLSearchParams(window.location.search);
            currentParams.set('resend_count', resendCountInput.value);
            resendBtn.href = `${baseUrl}?${currentParams.toString()}`;
        }

        // Update on page load
        updateResendLink();

        // Update when the input changes
        resendCountInput.addEventListener('input', updateResendLink);
    });
</script>
@endpush
