@php
    use Illuminate\Support\Facades\DB;
    $protocol =
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443
            ? 'https://'
            : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $requestUri = $_SERVER['REQUEST_URI'];
    //$fullUrl = $protocol . $host . $requestUri;
    // Parse the query string from the request URI
    $queryString = parse_url($requestUri, PHP_URL_QUERY);

    // Store the key-value pairs in an associative array
    parse_str($queryString, $queryParams);
    $requestUri = $queryString;
@endphp
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Alumni List</h5>
                    </div>
                    <div class="card-block">
                        <button href="#" class="btn btn-dark uploadAction" data-bs-toggle="modal"
                            data-bs-target="#importStudent"><i class="fas fa-upload"></i> {{ 'Import' }}</button>
                        <a href="{{ route('alumni.move_to_alumni_progress') }}" class="btn btn-success uploadAction">
                            <i class="fas fa-graduation-cap"></i> Move Students to Alumni
                        </a>
                        <button type="button" class="btn btn-warning uploadAction" id="restoreSelectedBtn" disabled>
                            <i class="fas fa-undo"></i> Restore Selected (<span id="selectedCount">0</span>)
                        </button>
                        <button type="button" class="btn btn-info uploadAction" data-bs-toggle="modal"
                            data-bs-target="#restoreByYearModal">
                            <i class="fas fa-calendar-alt"></i> Restore by Year
                        </button>
                        
                        <button href="#" class="btn btn-dark uploadAction" data-bs-toggle="modal"
                            data-bs-target="#importStudents"><i class="fas fa-upload"></i> {{ 'Move Student to Alumni' }}</button>
                        {{-- for degree import --}}

                    </div>
                    <div class="card-block">
                        <form class="needs-validation" novalidate method="GET" action="#">
                            @csrf
                            <div class="row gx-2">
                                <div class="form-group col-md-2">
                                    <label for="username">Username</label>
                                    <input type="text" name="username" id="username" class="form-control">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" name="phone" id="phone" class="form-control">
                                </div>
                                <div class="form-group col-md-2">
                                    <button type="submit" class="btn btn-info btn-filter"><i class="fas fa-search"></i>
                                        {{ 'Filter' }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-block">
                        <!-- [ Data table ] start -->
                        <div class="table-responsive">
                            <table id="" class="display table nowrap table-striped table-hover"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAll" class="form-check-input"></th>
                                        <th>#</th>
                                        <th>{{ 'Username' }}</th>
                                        <th>{{ 'Name' }}</th>
                                        <th>{{ 'Phone' }}</th>
                                        <th>{{ 'Email' }}</th>
                                        <th>{{ 'Action' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input alumni-checkbox"
                                                    value="{{ $row->id }}"></td>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->username }}</td>
                                            <td>{{ $row->fullname }}</td>
                                            <td>{{ $row->email }}</td>
                                            <td>{{ $row->phone }}</td>
                                            <td>

                                                <button type="button"
                                                    class="btn btn-icon btn-danger btn-sm deleteAction"
                                                    data-bs-toggle="modal" data-bs-target="#delete{{ $row->id }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                                {{-- Update picture --}}
                                                <button type="button"
                                                    class="btn btn-icon btn-primary btn-sm updatePicture deleteAction"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#updatePicture{{ $row->id }}">
                                                    <i class="fas fa-image"></i>
                                                </button>
                                                {{-- Restore to student --}}
                                                <button type="button"
                                                    class="btn btn-icon btn-warning btn-sm restoreAction"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#restore{{ $row->id }}">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <div id="delete{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card text-center">
                                                        <div class="card-body">
                                                            <h4>Are You Sure</h4>
                                                        </div>
                                                        <form class="form-group" action="delete {{ $page }}"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->user_id }}">
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">No</button>
                                                                <button type="submit"
                                                                    class="btn btn-danger">Yes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Update picture modal --}}
                                        <div id="updatePicture{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-md" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Update Picture</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card">


                                                        <div class="">
                                                            <center>
                                                                @if ($row->picture != 'none')
                                                                    <div class="mb-2">
                                                                        <img src="{{ asset('storage/picture/' . $row->picture) }}"
                                                                            alt="Current Picture"
                                                                            style="width: 200px; height: 200px; object-fit: cover; border-radius: 8px;">
                                                                        <small class="d-block text-muted">Current
                                                                            picture</small>
                                                                    </div>
                                                                @else
                                                                    <div class="mb-2">
                                                                        <img src="{{ asset('storage/picture/default.jpg') }}"
                                                                            alt="Current Picture..."
                                                                            style="width: 200px; height: 200px; object-fit: cover; border-radius: 8px;">
                                                                        <small class="d-block text-muted">Current
                                                                            picture</small>
                                                                    </div>
                                                                @endif
                                                            </center>

                                                        </div>

                                                        <form class="form-group" action="update alumni"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->user_id }}">
                                                                <div class="form-group">
                                                                    <label for="picture">Picture</label>
                                                                    <input type="file" name="picture"
                                                                        class="form-control" required>
                                                                </div>
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="submit"
                                                                    class="btn btn-success">Upload</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Restore to student modal --}}
                                        <div id="restore{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Confirm Restore</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card text-center">
                                                        <div class="card-body">
                                                            <h4>Restore {{ $row->fullname }}?</h4>
                                                            <p>This will move {{ $row->username }} from alumni back to
                                                                active student status.</p>
                                                        </div>
                                                        <form class="form-group"
                                                            action="/admin/restore-alumni/{{ $row->id }}"
                                                            method="POST">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->id }}">
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit"
                                                                    class="btn btn-warning">Restore</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- [ Data table ] end -->

                        <!-- Pagination and Results Info -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <span class="text-muted">
                                        Showing {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }}
                                        of {{ $data->total() ?? 0 }} results
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    {{ $data->links('pagination::bootstrap-4') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->

<!-- Show modal content -->
<div id="importStudents" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Move Student to Alumni</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <div class="card-body">
                    <a href="{{ url('uploads/New Student Upload.xlsx') }}" download="New Student Upload.xlsx"><i
                            class="fas fa-download"></i> Download Template</a>
                </div>

                <form class="form-group" action="upload-student" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <input type="hidden" name="upload_type" value="old">
                        <div class="form-group">
                            <label for="faculty1">Faculty</label>
                            <select class="form-control faculty" id="faculty1" name="faculty" lang="1"
                                required>
                                <option value="">Select Option</option>
                                @foreach ($faculty as $row)
                                    <option value="{{ $row->code }}">{{ $row->code }}: {{ $row->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department1">Department</label>
                            <select class="form-control department" id="department1" name="department"
                                lang="1" required>
                                <option value="">Select Faculty First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="program1">Program</label>
                            <select class="form-control" id="program1" name="program" lang="1" required>
                                <option value="">Select Department First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="file"></label>
                            <input type="file" name="file" id="file" accept=".xlsx, .xls"
                                class="form-control">
                        </div>
                        <!-- Details View End -->
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Upload</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Restore by Year Modal -->
<div id="restoreByYearModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Restore Alumni by Year</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <p>Select the graduation year to restore alumni back to student status.</p>
                    <form id="restoreByYearForm" action="{{ route('alumni.restore_by_year_progress') }}"
                        method="GET">
                        <div class="form-group">
                            <label for="yearSelect">Graduation Year</label>
                            <select name="year" id="yearSelect" class="form-control" required>
                                <option value="">-- Select Year --</option>
                                @php
                                    $currentYear = date('Y');
                                    for ($i = $currentYear; $i >= $currentYear - 10; $i--) {
                                        echo "<option value='{$i}'>{$i}</option>";
                                    }
                                @endphp
                            </select>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-info">Proceed</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Restore Selected Modal -->
<div id="restoreSelectedModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Restore Selected Alumni</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="card text-center">
                <div class="card-body">
                    <h4>Restore Selected Alumni?</h4>
                    <p>This will move the selected alumni records back to active student status.</p>
                    <div id="selectedList" class="text-start mb-3" style="max-height: 200px; overflow-y: auto;">
                    </div>
                </div>
                <form id="restoreSelectedForm" method="POST" action="{{ route('alumni.restore_selected') }}">
                    <div class="card-body">
                        @csrf
                        <input type="hidden" name="selected_ids" id="selectedIds">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Restore Selected</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const alumniCheckboxes = document.querySelectorAll('.alumni-checkbox');
        const restoreSelectedBtn = document.getElementById('restoreSelectedBtn');
        const selectedCountSpan = document.getElementById('selectedCount');
        const restoreSelectedModal = new bootstrap.Modal(document.getElementById('restoreSelectedModal'));
        const restoreSelectedForm = document.getElementById('restoreSelectedForm');
        const selectedIdsInput = document.getElementById('selectedIds');
        const selectedListDiv = document.getElementById('selectedList');

        function updateSelectedCount() {
            const checkedBoxes = document.querySelectorAll('.alumni-checkbox:checked');
            const count = checkedBoxes.length;
            selectedCountSpan.textContent = count;
            restoreSelectedBtn.disabled = count === 0;
        }

        function updateSelectedList() {
            const checkedBoxes = document.querySelectorAll('.alumni-checkbox:checked');
            const alumniNames = [];

            checkedBoxes.forEach(checkbox => {
                const row = checkbox.closest('tr');
                const username = row.cells[2].textContent; // Username column
                const fullname = row.cells[3].textContent; // Name column
                alumniNames.push(`${fullname} (${username})`);
            });

            selectedListDiv.innerHTML = alumniNames.length > 0 ?
                '<strong>Selected Alumni:</strong><br>' + alumniNames.join('<br>') :
                '';
        }

        // Select all functionality
        selectAllCheckbox.addEventListener('change', function() {
            alumniCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });

        // Individual checkbox functionality
        alumniCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedCount();

                // Update select all checkbox state
                const allChecked = Array.from(alumniCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(alumniCheckboxes).some(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            });
        });

        // Restore selected button
        restoreSelectedBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.alumni-checkbox:checked');
            const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);

            selectedIdsInput.value = selectedIds.join(',');
            updateSelectedList();
            restoreSelectedModal.show();
        });

        // Form submission
        restoreSelectedForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const selectedIds = formData.get('selected_ids').split(',');

            // Submit via AJAX or normal form
            this.submit();
        });
    });
</script>

<!-- Show modal content -->
<div id="importStudent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Upload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <div class="card-body">
                    <a href="#"><i class="fas fa-download"></i> Download Template</a>
                </div>

                <form class="form-group" action="/import-alumni" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <input type="hidden" name="upload_type" value="new">
                        <div class="form-group">
                            <label for="file"></label>
                            <input type="file" name="file" id="file" accept=".xlsx, .xls"
                                class="form-control" required>
                        </div>
                        <!-- Details View End -->
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Upload</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
