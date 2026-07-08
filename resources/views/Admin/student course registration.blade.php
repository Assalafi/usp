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
            <h4>Student Course Registration Management</h4>
        </div>
        {{-- Add register Courses Button if username is not empty --}}
        @if (isset($username))
            <div class="col-md-3 text-end">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                    data-bs-target="#registerModal">
                    <i class="fas fa-plus"></i> Register Courses
                </button>
            </div>
        @else
            <div class="col-md-3 text-end">
                <button type="button" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Select Student for Course Registration
                </button>
            </div>
        @endif
        <div class="col-md-3 text-end">
            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                data-bs-target="#bulkDeleteModal">
                <i class="fas fa-trash"></i> Delete Selected
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

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="/student course registration" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="username" name="username"
                            placeholder="Filter by username" value="{{ $username ?? '' }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="course_code" class="form-label">Course Code</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="course_code" name="course_code"
                            placeholder="Filter by code" value="{{ $course_code ?? '' }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="session" class="form-label">Session</label>
                    <select class="form-select" id="session" name="session">
                        <option value="">All Sessions</option>
                        @if (isset($sessions))
                            @foreach ($sessions as $sessionItem)
                                <option value="{{ $sessionItem->title }}"
                                    {{ isset($session) && $session == $sessionItem->title ? 'selected' : '' }}>
                                    {{ $sessionItem->title }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="semester" class="form-label">Semester</label>
                    <select class="form-select" id="semester" name="semester">
                        <option value="">All Semesters</option>
                        <option value="FIRST" {{ isset($semester) && $semester == 'FIRST' ? 'selected' : '' }}>FIRST
                        </option>
                        <option value="SECOND" {{ isset($semester) && $semester == 'SECOND' ? 'selected' : '' }}>SECOND
                        </option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <div>
                        <button class="btn btn-primary" type="submit">Filter</button>
                        <a href="/student course registration" class="btn btn-secondary">Clear</a>
                    </div>
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
                            <th>Course Code</th>
                            <th>Unit</th>
                            <th>Total</th>
                            <th>Grade</th>
                            <th>Level</th>
                            <th>Type</th>
                            <th>Semester</th>
                            <th>Session</th>
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
                                    <td>{{ $item->code }}</td>
                                    <td>{{ $item->unit }}</td>
                                    <td>{{ $item->total }}</td>
                                    <td>{{ $item->grade }}</td>
                                    <td>{{ $item->level }}</td>
                                    <td>{{ $item->type }}</td>
                                    <td>{{ $item->semester }}</td>
                                    <td>{{ $item->session }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal{{ $item->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1"
                                    aria-labelledby="deleteModalLabel{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $item->id }}">
                                                    Delete
                                                    Course Registration</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete the course registration for:
                                                <ul>
                                                    <li><strong>Username:</strong> {{ $item->username }}</li>
                                                    <li><strong>Course Code:</strong> {{ $item->code }}</li>
                                                    <li><strong>Session:</strong> {{ $item->session }}</li>
                                                    <li><strong>Semester:</strong> {{ $item->semester }}</li>
                                                </ul>
                                                <p class="text-danger"><strong>This action cannot be undone!</strong>
                                                </p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <form action="/delete student course registration" method="POST">
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
                                <td colspan="10" class="text-center">No records found. Please apply filters to view
                                    data.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                @if ($data->hasPages())
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card-footer">
                                <div class="row align-items-center">
                                    <div class="col-sm-6">
                                        <p class="text-muted mb-0">
                                            Showing {{ $data->firstItem() }} to {{ $data->lastItem() }} of
                                            {{ $data->total() }} results
                                        </p>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="d-flex justify-content-end">
                                            {{ $data->links('pagination::bootstrap-4') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bulk Delete Modal -->
    <div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkDeleteModalLabel">Delete Selected Course Registrations</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the selected course registration records?
                    <p class="text-danger mt-3"><strong>Warning:</strong> This action cannot be undone and will
                        permanently remove all selected records!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="/delete student course registration" method="POST">
                        @csrf
                        <input type="hidden" name="ids" id="selected-ids">
                        <button type="submit" class="btn btn-danger">Delete Selected</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Register Course Modal if username is not empty, loaded from $getProgramCourses load them in table with check box to select multiple courses submit code,semester,level,type --}}
    @if (isset($getProgramCourses))
        <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="registerModalLabel">Register Course for {{ $username }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="/registerCoursesManually" method="POST">
                            @csrf
                            <input type="hidden" name="username" value="{{ $username }}">
                            {{-- Add selection of session from current year/previous to 2008/2009 using for loop --}}
                            <div class="form-group">
                                <label for="session">Session</label>
                                <select class="form-control" name="session" id="session" required>
                                    <option value="">Select Session</option>
                                    @for ($i = date('Y'); $i >= 2008; $i--)
                                        <option value="{{ $i }}/{{ $i + 1 }}">
                                            {{ $i }}/{{ $i + 1 }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>SN</th>
                                            <th>Course Code</th>
                                            <th>Unit</th>
                                            <th>Level</th>
                                            <th>Type</th>
                                            <th>Semester</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($getProgramCourses as $item)
                                            {{-- arrange them as array of objects in the checkbox name="courses[]" like [{"id": 1, "code": "CS101", "unit": 3, "level": 100, "type": "Core", "semester": 1, "session": "2022/2023"}, {"id": 2, "code": "CS102", "unit": 3, "level": 100, "type": "Core", "semester": 2, "session": "2022/2023"}] --}}

                                            <tr>
                                                <td><input type="checkbox" value="{{ json_encode($item) }}"
                                                        name="courses[]"></td>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->code }}</td>
                                                <td>{{ $item->unit }}</td>
                                                <td>{{ $item->level }}</td>
                                                <td>{{ $item->type }}</td>
                                                <td>{{ $item->semester }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Register</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
