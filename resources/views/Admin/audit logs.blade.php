@php
    use App\Models\Audit;
@endphp
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Audit Logs</h5>
                    </div>
                    <div class="card-block">
                        <form class="needs-validation" novalidate method="GET" action="#">
                            @csrf
                            <div class="row gx-2 mb-3">
                                <div class="form-group col-md-2">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        value="{{ $filters['username'] ?? '' }}" placeholder="Search username...">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="page_filter">Page</label>
                                    <select class="form-control" id="page_filter" name="page_filter">
                                        <option value="">All Pages</option>
                                        @foreach ($allPages ?? [] as $page)
                                            <option value="{{ $page }}" {{ ($filters['page_filter'] ?? '') == $page ? 'selected' : '' }}>{{ $page }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="method">Method</label>
                                    <select class="form-control" id="method" name="method">
                                        <option value="">All Methods</option>
                                        <option value="GET" {{ ($filters['method'] ?? '') == 'GET' ? 'selected' : '' }}>GET (View)</option>
                                        <option value="POST" {{ ($filters['method'] ?? '') == 'POST' ? 'selected' : '' }}>POST (Action)</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="ip_address">IP Address</label>
                                    <input type="text" class="form-control" id="ip_address" name="ip_address"
                                        value="{{ $filters['ip_address'] ?? '' }}" placeholder="Search IP...">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="date_from">Date From</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from"
                                        value="{{ $filters['date_from'] ?? '' }}">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="date_to">Date To</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to"
                                        value="{{ $filters['date_to'] ?? '' }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-info me-2"><i class="fas fa-search"></i> Filter</button>
                                    <a href="{{ request()->url() }}" class="btn btn-secondary"><i class="fas fa-times"></i> Clear</a>
                                </div>
                            </div>
                        </form>
                        <div class="border-top pt-3 mt-2">
                            <form id="audit-delete-form" action="{{ url('/audit-logs/delete') }}" method="POST">
                                @csrf
                                <input type="hidden" name="username" value="{{ $filters['username'] ?? '' }}">
                                <input type="hidden" name="page_filter" value="{{ $filters['page_filter'] ?? '' }}">
                                <input type="hidden" name="method" value="{{ $filters['method'] ?? '' }}">
                                <input type="hidden" name="ip_address" value="{{ $filters['ip_address'] ?? '' }}">
                                <input type="hidden" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                                <input type="hidden" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                                <button type="submit" id="audit-delete-button" class="btn btn-outline-danger">
                                    <i class="fas fa-trash-alt me-1"></i> Delete filtered logs in batches
                                </button>
                                <span class="small text-muted ms-2">Only records matching the filters above will be removed.</span>
                                <div id="audit-delete-status" class="small mt-2" role="status" aria-live="polite"></div>
                            </form>
                        </div>
                    </div>

                    <div class="card-block">
                        <div class="row mb-3">
                            <div class="col-lg-2 col-md-4 col-sm-6">
                                <div class="alert alert-primary text-center p-3">
                                    <h4 class="mb-1">{{ $data->total() }}</h4>
                                    <small class="text-muted">Total Logs</small>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="export-table" class="display table nowrap table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date/Time</th>
                                        <th>Username</th>
                                        <th>Account Type</th>
                                        <th>Appointment</th>
                                        <th>Page</th>
                                        <th>Method</th>
                                        <th>IP Address</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sn = ($data->currentPage() - 1) * $data->perPage() + 1; @endphp
                                    @forelse ($data as $row)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->created_at }}</td>
                                            <td>{{ $row->username ?? 'Guest' }}</td>
                                            <td>{{ $row->acc_type ?? '-' }}</td>
                                            <td>{{ $row->appointment ?? '-' }}</td>
                                            <td><span class="badge badge-info">{{ $row->page }}</span></td>
                                            <td>
                                                <span class="badge badge-{{ $row->method == 'POST' ? 'warning' : 'primary' }}">
                                                    {{ $row->method }}
                                                </span>
                                            </td>
                                            <td>{{ $row->ip_address }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info audit-detail-btn"
                                                    data-datetime="{{ $row->created_at }}"
                                                    data-username="{{ $row->username ?? 'Guest' }}"
                                                    data-acctype="{{ $row->acc_type ?? '-' }}"
                                                    data-appointment="{{ $row->appointment ?? '-' }}"
                                                    data-page="{{ $row->page }}"
                                                    data-url="{{ $row->url }}"
                                                    data-method="{{ $row->method }}"
                                                    data-ip="{{ $row->ip_address }}"
                                                    data-useragent="{{ $row->user_agent }}"
                                                    data-payload="{{ $row->payload ? htmlspecialchars(json_encode(json_decode($row->payload), JSON_PRETTY_PRINT)) : '' }}"
                                                    title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">No audit logs found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $data->appends($filters)->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Single Shared Detail Modal -->
<div id="auditDetailModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Audit Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr><th width="30%">Date/Time</th><td id="modal-datetime"></td></tr>
                    <tr><th>Username</th><td id="modal-username"></td></tr>
                    <tr><th>Account Type</th><td id="modal-acctype"></td></tr>
                    <tr><th>Appointment</th><td id="modal-appointment"></td></tr>
                    <tr><th>Page</th><td id="modal-page"></td></tr>
                    <tr><th>Full URL</th><td id="modal-url" style="word-break: break-all;"></td></tr>
                    <tr><th>Method</th><td id="modal-method"></td></tr>
                    <tr><th>IP Address</th><td id="modal-ip"></td></tr>
                    <tr><th>User Agent</th><td id="modal-useragent" style="word-break: break-all;"></td></tr>
                    <tr id="modal-payload-row" style="display:none;"><th>Payload</th><td><pre id="modal-payload" style="white-space: pre-wrap;"></pre></td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.audit-detail-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('modal-datetime').textContent = this.dataset.datetime;
            document.getElementById('modal-username').textContent = this.dataset.username;
            document.getElementById('modal-acctype').textContent = this.dataset.acctype;
            document.getElementById('modal-appointment').textContent = this.dataset.appointment;
            document.getElementById('modal-page').textContent = this.dataset.page;
            document.getElementById('modal-url').textContent = this.dataset.url;
            document.getElementById('modal-method').textContent = this.dataset.method;
            document.getElementById('modal-ip').textContent = this.dataset.ip;
            document.getElementById('modal-useragent').textContent = this.dataset.useragent;
            var payloadRow = document.getElementById('modal-payload-row');
            var payloadEl = document.getElementById('modal-payload');
            if (this.dataset.payload) {
                payloadRow.style.display = '';
                payloadEl.textContent = this.dataset.payload;
            } else {
                payloadRow.style.display = 'none';
            }
            var modal = new bootstrap.Modal(document.getElementById('auditDetailModal'));
            modal.show();
        });
    });

    var deleteForm = document.getElementById('audit-delete-form');
    var deleteButton = document.getElementById('audit-delete-button');
    var deleteStatus = document.getElementById('audit-delete-status');
    if (deleteForm && deleteButton) {
        deleteForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            if (!window.confirm('Delete all audit logs matching the current filters? This cannot be undone.')) {
                return;
            }

            deleteButton.disabled = true;
            deleteButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting…';
            deleteStatus.className = 'small mt-2 text-info';
            deleteStatus.textContent = 'Preparing the deletion…';

            var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || deleteForm.querySelector('[name="_token"]')?.value;
            var cutoffId = '';
            var deletedTotal = 0;
            var rounds = 0;

            try {
                while (rounds < 100000) {
                    rounds++;
                    var body = new URLSearchParams(new FormData(deleteForm));
                    if (cutoffId) body.set('cutoff_id', cutoffId);
                    var response = await fetch(deleteForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: body.toString()
                    });
                    var result = await response.json();
                    if (!response.ok) {
                        throw new Error(result.message || 'The server could not complete the deletion.');
                    }
                    cutoffId = result.cutoff_id || cutoffId;
                    deletedTotal += Number(result.deleted || 0);
                    deleteStatus.textContent = 'Deleted ' + deletedTotal.toLocaleString() + ' record(s)…';
                    if (result.done) {
                        deleteStatus.className = 'small mt-2 text-success';
                        deleteStatus.textContent = 'Completed. Deleted ' + deletedTotal.toLocaleString() + ' matching audit record(s). Reloading…';
                        window.setTimeout(function() { window.location.reload(); }, 600);
                        return;
                    }
                }
                throw new Error('The deletion was stopped after reaching the safety limit. Refresh and continue if records remain.');
            } catch (error) {
                deleteStatus.className = 'small mt-2 text-danger';
                deleteStatus.textContent = error.message || 'The deletion could not be completed.';
                deleteButton.disabled = false;
                deleteButton.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Try deletion again';
            }
        });
    }
});
</script>
