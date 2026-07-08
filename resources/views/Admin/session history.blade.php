<script>
    $(document).ready(function() {
        $('#checkAll').click(function(e) {
            if ($(this).is(':checked')) {
                $('.itemCheckbox').prop('checked', true);
            } else {
                $('.itemCheckbox').prop('checked', false);
            }
        });

        // Update the selected IDs when the bulk delete modal is shown
        $('#bulkDeleteModal').on('show.bs.modal', function() {
            let selected = [];
            $('.itemCheckbox:checked').each(function() {
                selected.push($(this).val());
            });

            if (selected.length === 0) {
                alert('Please select at least one record to delete!');
                return false;
            }

            $('#selected-ids').val(selected.join(','));
        });
    });
</script>

<div class="p-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h4>Session History Management</h4>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                data-bs-target="#addSessionHistoryModal">
                <i class="fas fa-plus"></i> Add New
            </button>
            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                data-bs-target="#bulkDeleteModal">
                <i class="fas fa-trash"></i> Delete Selected
            </button>
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                data-bs-target="#importSessionHistoryModal">
                <i class="fas fa-file-import"></i> Import CSV
            </button>
            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                data-bs-target="#generateSessionHistoryModal">
                <i class="fas fa-cogs"></i> Generate from Entry
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Username Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="/session history" method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" id="username" name="username"
                            placeholder="Filter by username" value="{{ $username ?? '' }}">
                        <button class="btn btn-primary" type="submit">Filter</button>
                        @if (isset($username) && $username)
                            <a href="/session history" class="btn btn-secondary">Clear</a>
                        @endif
                    </div>
                    <div class="form-text">Enter a username to search for session history records</div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="checkAll"></th>
                            <th>SN</th>
                            <th>Username</th>
                            <th>Session</th>
                            <th>Level</th>
                            <th>Total Unit</th>
                            <th>Product</th>
                            <th>CGPA</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($data) && count($data) > 0)
                            @foreach ($data as $item)
                                <tr>
                                    <td><input type="checkbox" class="itemCheckbox" value="{{ $item->id }}"></td>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->username }}</td>
                                    <td>{{ $item->session }}</td>
                                    <td>{{ $item->level }}</td>
                                    <td>{{ $item->total_unit }}</td>
                                    <td>{{ $item->product }}</td>
                                    <td>{{ $item->cgpa }}</td>
                                    <td>{{ $item->status }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#editSessionHistoryModal{{ $item->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal{{ $item->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editSessionHistoryModal{{ $item->id }}" tabindex="-1"
                                    aria-labelledby="editSessionHistoryModalLabel{{ $item->id }}"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"
                                                    id="editSessionHistoryModalLabel{{ $item->id }}">Edit Session
                                                    History</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <form action="/update session history" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label for="username" class="form-label">Username</label>
                                                            <input type="text" class="form-control" id="username"
                                                                name="username" value="{{ $item->username }}"
                                                                required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="session" class="form-label">Session</label>
                                                            <input type="text" class="form-control" id="session"
                                                                name="session" value="{{ $item->session }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4">
                                                            <label for="level" class="form-label">Level</label>
                                                            <input type="text" class="form-control" id="level"
                                                                name="level" value="{{ $item->level }}" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="total_unit" class="form-label">Total
                                                                Unit</label>
                                                            <input type="number" step="0.01" class="form-control"
                                                                id="total_unit" name="total_unit"
                                                                value="{{ $item->total_unit }}">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="product" class="form-label">Product</label>
                                                            <input type="number" step="0.01" class="form-control"
                                                                id="product" name="product"
                                                                value="{{ $item->product }}">
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label for="cgpa" class="form-label">CGPA</label>
                                                            <input type="number" step="0.01" class="form-control"
                                                                id="cgpa" name="cgpa"
                                                                value="{{ $item->cgpa }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="status" class="form-label">Status</label>
                                                            <select class="form-select" id="status"
                                                                name="status">
                                                                <option value="">Select Status</option>
                                                                <option value="PROCEED"
                                                                    {{ $item->status == 'PROCEED' ? 'selected' : '' }}>
                                                                    PROCEED</option>
                                                                <option value="REPEAT"
                                                                    {{ $item->status == 'REPEAT' ? 'selected' : '' }}>
                                                                    REPEAT</option>
                                                                <option value="PENDING"
                                                                    {{ $item->status == 'PENDING' ? 'selected' : '' }}>
                                                                    PENDING</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1"
                                    aria-labelledby="deleteModalLabel{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $item->id }}">
                                                    Delete Session History</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete this session history record?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Close</button>
                                                <form action="/delete session history" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="id"
                                                        value="{{ $item->id }}">
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="11" class="text-center">No records found</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $data->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addSessionHistoryModal" tabindex="-1" aria-labelledby="addSessionHistoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSessionHistoryModalLabel">Add New Session History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/create session history" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="col-md-6">
                                <label for="session" class="form-label">Session</label>
                                <input type="text" class="form-control" id="session" name="session" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="level" class="form-label">Level</label>
                                <input type="text" class="form-control" id="level" name="level" required>
                            </div>
                            <div class="col-md-4">
                                <label for="total_unit" class="form-label">Total Unit</label>
                                <input type="number" step="0.01" class="form-control" id="total_unit"
                                    name="total_unit">
                            </div>
                            <div class="col-md-4">
                                <label for="product" class="form-label">Product</label>
                                <input type="number" step="0.01" class="form-control" id="product"
                                    name="product">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cgpa" class="form-label">CGPA</label>
                                <input type="number" step="0.01" class="form-control" id="cgpa"
                                    name="cgpa">
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Select Status</option>
                                    <option value="PROCEED">PROCEED</option>
                                    <option value="REPEAT">REPEAT</option>
                                    <option value="PENDING">PENDING</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importSessionHistoryModal" tabindex="-1"
        aria-labelledby="importSessionHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importSessionHistoryModalLabel">Import Session History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/upload session history" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">CSV File</label>
                            <input type="file" class="form-control" id="file" name="file" required
                                accept=".csv">
                            <div class="form-text">CSV should have the columns: username, session, level, total_unit,
                                product, cgpa, status</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Generate Session History Modal -->
    <div class="modal fade" id="generateSessionHistoryModal" tabindex="-1"
        aria-labelledby="generateSessionHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="generateSessionHistoryModalLabel">Generate Session History from Entry
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/generate-session-history" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> This will <strong>delete all existing session
                                history</strong> for the selected students and create new records based on their
                            session_of_entry.
                        </div>
                        <div class="mb-3">
                            <label for="facultyGen" class="form-label">Faculty <small
                                    class="text-muted">(Optional)</small></label>
                            <select class="form-control faculty" id="facultyGen" name="faculty" lang="Gen">
                                <option value="">All Faculties</option>
                                @foreach ($faculty as $row)
                                    <option value="{{ $row->code }}">{{ $row->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="departmentGen" class="form-label">Department <small
                                    class="text-muted">(Optional)</small></label>
                            <select class="form-control department" id="departmentGen" name="department"
                                lang="Gen">
                                <option value="">Select Faculty First</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="programGen" class="form-label">Program <small
                                    class="text-muted">(Optional)</small></label>
                            <select class="form-control program" id="programGen" name="program" lang="Gen">
                                <option value="">Select Department First</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="sessionGen" class="form-label">Session of Entry <span
                                    class="text-danger">*</span></label>
                            <select class="form-control" id="sessionGen" name="session" required>
                                <option value="">Select Session</option>
                                @foreach ($session as $row)
                                    <option value="{{ $row->title }}">{{ $row->title }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Students with this session_of_entry will be selected</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-warning">Generate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Modal -->
    <div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkDeleteModalLabel">Delete Selected Session History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the selected session history records?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <form action="/delete session history" method="POST">
                        @csrf
                        <input type="hidden" name="ids" id="selected-ids">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
