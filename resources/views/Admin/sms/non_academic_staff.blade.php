<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Non-Academic Staff (Degree 1)</h3>
        <a href="{{ route('sms.reset_progress') }}" class="btn btn-danger btn-sm">Reset Passwords & Notify All</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Phone</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staff as $member)
                        <tr>
                            <td>{{ $member->username }}</td>
                            <td>{{ $member->phone }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center">No staff members found matching the criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination and Results Info -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <span class="text-muted">
                        Showing {{ $staff->firstItem() ?? 0 }} to {{ $staff->lastItem() ?? 0 }}
                        of {{ $staff->total() ?? 0 }} results
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-end">
                    {{ $staff->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
