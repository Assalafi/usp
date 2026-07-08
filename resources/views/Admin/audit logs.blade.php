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
});
</script>
