@php
    use Illuminate\Support\Facades\DB;
@endphp
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                <div class="alert alert-primary text-center p-3">
                                    <h4 class="mb-1">{{ $applicants }}</h4>
                                    <small class="text-muted">Total Applicants</small>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                <div class="alert alert-warning text-center p-3">
                                    <h4 class="mb-1">{{ $pending }}</h4>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                <div class="alert alert-info text-center p-3">
                                    <h4 class="mb-1">{{ $submitted }}</h4>
                                    <small class="text-muted">Submitted</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                                <div class="alert alert-success text-center p-3">
                                    <h4 class="mb-1">{{ $admitted }}</h4>
                                    <small class="text-muted">Admitted</small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                                <div class="alert alert-danger text-center p-3">
                                    <h4 class="mb-1">{{ $rejected }}</h4>
                                    <small class="text-muted">Rejected</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-header">
                        <h5>Applicants List</h5>
                    </div>
                    <div class="card-block">

                        {{-- <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createStudent"><i class="fas fa-plus"></i> {{ ('Add New') }}</a> --}}
                        <button href="#" class="btn btn-dark uploadAction" data-bs-toggle="modal"
                            data-bs-target="#importStudent"><i class="fas fa-upload"></i> {{ 'Upload' }}</button>

                    </div>

                    <div class="card-block">
                        <form class="needs-validation" novalidate method="GET" action="#">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <h6 class="mb-3">Search & Filter Applicants</h6>
                                </div>
                            </div>
                            <div class="row gx-2">
                                <!-- Global Search -->
                                <div class="form-group col-md-3">
                                    <label for="search">Search</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="{{ $filters['search'] ?? '' }}" 
                                           placeholder="Search by name, ID...">
                                </div>
                                
                                <!-- Faculty Filter -->
                                <div class="form-group col-md-2">
                                    <label for="facultyf">Faculty</label>
                                    <select class="form-control faculty" lang="f" name="faculty" id="facultyf">
                                        <option value="">All Faculties</option>
                                        @foreach ($faculty as $roww)
                                            <option value="{{ $roww->code }}" 
                                                    {{ ($filters['faculty'] ?? '') == $roww->code ? 'selected' : '' }}>
                                                {{ $roww->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Status Filter -->
                                <div class="form-group col-md-2">
                                    <label for="statusf">Status</label>
                                    <select class="form-control" id="statusf" name="status">
                                        <option value="">All Status</option>
                                        <option value="Pending" {{ ($filters['status'] ?? '') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="Submitted" {{ ($filters['status'] ?? '') == 'Submitted' ? 'selected' : '' }}>Submitted</option>
                                        <option value="Admitted" {{ ($filters['status'] ?? '') == 'Admitted' ? 'selected' : '' }}>Admitted</option>
                                        <option value="Rejected" {{ ($filters['status'] ?? '') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                                
                                <!-- Mode Filter -->
                                <div class="form-group col-md-2">
                                    <label for="modef">Mode of Entry</label>
                                    <select class="form-control" id="modef" name="mode">
                                        <option value="">All Modes</option>
                                        <option value="UTME" {{ ($filters['mode'] ?? '') == 'UTME' ? 'selected' : '' }}>UTME</option>
                                        <option value="DE" {{ ($filters['mode'] ?? '') == 'DE' ? 'selected' : '' }}>DE</option>
                                    </select>
                                </div>
                                
                                <!-- Session Filter -->
                                <div class="form-group col-md-2">
                                    <label for="session">Session</label>
                                    <select class="form-control" id="session" name="session">
                                        <option value="">All Sessions</option>
                                        <option value="2026/2027" {{ ($filters['session'] ?? '') == '2026/2027' ? 'selected' : '' }}>2026/2027</option>
                                        <option value="2025/2026" {{ ($filters['session'] ?? '') == '2025/2026' ? 'selected' : '' }}>2025/2026</option>
                                        <option value="2024/2025" {{ ($filters['session'] ?? '') == '2024/2025' ? 'selected' : '' }}>2024/2025</option>
                                    </select>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="form-group col-md-1">
                                    <label>&nbsp;</label>
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-info me-2" title="Filter">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <a href="{{ request()->url() }}" class="btn btn-secondary" title="Clear Filters">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
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
                            <table id="export-table" class="display table nowrap table-striped table-hover"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ 'Student ID' }}</th>
                                        <th>{{ 'Name' }}</th>
                                        <th>{{ 'Faculty' }}</th>
                                        {{-- <th>{{ ('Department') }}</th> --}}
                                        <th>{{ 'Program' }}</th>
                                        <th>{{ 'Gender' }}</th>
                                        <th>{{ 'Mode of Entry' }}</th>
                                        <th>{{ 'Status' }}</th>
                                        <th>{{ 'Action' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = ($data->currentPage() - 1) * $data->perPage() + 1;
                                    @endphp
                                    @forelse ($data as $row)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->username }} </td>
                                            <td>{{ $row->fullname }}</td>
                                            <td>{{ DB::table('faculty')->where('code', $row->faculty)->value('title') }}
                                            </td>
                                            {{-- <td>{{ $row -> department }}</td> --}}
                                            <td>{{ DB::table('program')->where('code', $row->program)->value('title') }}
                                            </td>
                                            <td>{{ $row->gender }}</td>
                                            <td>
                                                <span class="badge badge-{{ $row->mode == 'UTME' ? 'primary' : 'secondary' }}">
                                                    {{ $row->mode }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($row->status == 'Admitted')
                                                    <span class="badge badge-success">Admitted</span>
                                                @elseif($row->status == 'Rejected')
                                                    <span class="badge badge-danger">Rejected</span>
                                                @elseif($row->status == 'Submitted')
                                                    <span class="badge badge-primary">Submitted</span>
                                                @else
                                                    <span class="badge badge-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                <!-- View Details Button -->
                                                <a href="/admin/applicant/{{ $row->id }}" class="btn btn-info btn-sm" 
                                                   title="View Details">
                                                    <i class="far fa-eye"></i>
                                                </a>
                                                
                                                <!-- Quick Status Actions -->
                                                @if($row->status == 'Pending' || $row->status == 'Submitted')
                                                    <!-- Admit Button -->
                                                    <button type="button" class="btn btn-success btn-sm me-1" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#admitApplicant{{ $row->id }}" 
                                                            title="Admit">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    
                                                    <!-- Reject Button -->
                                                    <button type="button" class="btn btn-danger btn-sm me-1" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#rejectApplicant{{ $row->id }}" 
                                                            title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                                
                                                <!-- Edit Button -->
                                                <button type="button" class="btn btn-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#updateStudent{{ $row->id }}" 
                                                        title="Edit">
                                                    <i class="far fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Admit Applicant Modal -->
                                        <div id="admitApplicant{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-check me-2"></i>Admit Applicant
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="/admin/admit-applicant" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="applicant_id" value="{{ $row->id }}">
                                                        <div class="modal-body">
                                                            <div class="alert alert-success">
                                                                <i class="fas fa-info-circle me-2"></i>
                                                                You are about to admit <strong>{{ $row->fullname }}</strong> to <strong>{{ DB::table('program')->where('code', $row->program)->value('title') }}</strong>.
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label for="admission_remarks{{ $row->id }}">Admission Remarks (Optional)</label>
                                                                <textarea class="form-control" id="admission_remarks{{ $row->id }}" name="remarks" rows="3" placeholder="Enter any remarks or special notes for this admission..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="fas fa-check me-2"></i>Confirm Admission
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Reject Applicant Modal -->
                                        <div id="rejectApplicant{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-times me-2"></i>Reject Applicant
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="/admin/reject-applicant" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="applicant_id" value="{{ $row->id }}">
                                                        <div class="modal-body">
                                                            <div class="alert alert-danger">
                                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                                You are about to reject <strong>{{ $row->fullname }}</strong>'s application for <strong>{{ DB::table('program')->where('code', $row->program)->value('title') }}</strong>.
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label for="rejection_reason{{ $row->id }}">Reason for Rejection <span class="text-danger">*</span></label>
                                                                <select class="form-control" id="rejection_reason{{ $row->id }}" name="rejection_reason" required>
                                                                    <option value="">Select rejection reason...</option>
                                                                    <option value="Insufficient JAMB Score">Insufficient JAMB Score</option>
                                                                    <option value="Poor SSCE Results">Poor SSCE Results</option>
                                                                    <option value="Missing Required Documents">Missing Required Documents</option>
                                                                    <option value="Program Capacity Full">Program Capacity Full</option>
                                                                    <option value="Failed to Meet Entry Requirements">Failed to Meet Entry Requirements</option>
                                                                    <option value="Incomplete Application">Incomplete Application</option>
                                                                    <option value="Other">Other (Specify in remarks)</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label for="rejection_remarks{{ $row->id }}">Additional Remarks</label>
                                                                <textarea class="form-control" id="rejection_remarks{{ $row->id }}" name="remarks" rows="3" placeholder="Provide detailed explanation for the rejection..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">
                                                                <i class="fas fa-times me-2"></i>Confirm Rejection
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Show modal content -->
                                        <div id="updateStudent{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Update Student</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card">
                                                        <form class="form-group" action="update-student"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="page" value="new">
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->user_id }}">
                                                                <div class="form-group">
                                                                    <label for="jamb_no">Jamb NO</label>
                                                                    <input type="text" name="username"
                                                                        id="jamb_no" class="form-control"
                                                                        value="{{ $row->username }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="surname">Surname</label>
                                                                    <input type="text" name="surname"
                                                                        id="surname" class="form-control"
                                                                        value="{{ $row->surname }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="first_name">First Name</label>
                                                                    <input type="text" name="first_name"
                                                                        id="first_name" class="form-control"
                                                                        value="{{ $row->first_name }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="middle_name">Other Name</label>
                                                                    <input type="text" name="middle_name"
                                                                        id="middle_name" class="form-control"
                                                                        value="{{ $row->other_name }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="gender">Gender</label>
                                                                    <select class="form-control" id="gender"
                                                                        name="gender" required>
                                                                        <option value="{{ $row->gender }}">Select
                                                                            Option</option>
                                                                        <option value="Female">Female</option>
                                                                        <option value="Male">Male</option>
                                                                    </select>
                                                                </div>
                                                                {{-- <div class="form-group">
                                                                <label for="gender">Gender</label>
                                                                <select class="form-control" id="gender" name="gender" required>
                                                                    <option value="{{ $row->gender }}">Select Option</option>
                                                                    <option value="F">Female</option>
                                                                    <option value="M">Male</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="faculty{{ ($row -> id) + 3 }}">Faculty</label>
                                                                <select class="form-control faculty" id="faculty{{ ($row -> id) + 3 }}" name="faculty" lang="{{ ($row -> id) + 3 }}" required>
                                                                    <option value="{{ $row -> faculty }}">Select Option</option>
                                                                    @foreach ($faculty as $roww)
                                                                        <option value="{{ $roww -> code }}">{{ $roww -> title }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="department{{ ($row -> id) + 3 }}">Department</label>
                                                                <select class="form-control department" id="department{{ ($row -> id) + 3 }}" lang="{{ ($row -> id) + 3 }}" name="department" required>
                                                                    <option value="{{ $row -> department }}">Select Faculty First</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="program">Program</label>
                                                                <select class="form-control" id="program{{ ($row -> id) + 3 }}" name="program" lang="{{ ($row -> id) + 3 }}" required>
                                                                    <option value="{{ $row -> program }}">Select Department First</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="mode_of_entry">Mode of Entry</label>
                                                                <select class="form-control" id="mode_of_entry" name="mode_of_entry" required>
                                                                    <option value="{{ $row->mode_of_entry }}">Select Option</option>
                                                                    <option value="UTME">UTME</option>
                                                                    <option value="DE">DE</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="level_of_entry">Level of Entry</label>
                                                                <select class="form-control" id="level_of_entry" name="level_of_entry" required>
                                                                    <option value="{{ $row->level_of_entry }}">Select Option</option>
                                                                    <option value="100">100</option>
                                                                    <option value="200">200</option>
                                                                    <option value="300">300</option>
                                                                    <option value="400">400</option>
                                                                    <option value="500">500</option>
                                                                    <option value="600">600</option>
                                                                </select>
                                                            </div> --}}
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="submit"
                                                                    class="btn btn-success">Update</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <div class="alert alert-info mb-0">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    No applicants found matching your criteria.
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
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
<div id="importStudent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Upload New Applicants</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <div class="card-body">
                    <a href="{{ url('uploads/New Applicant Upload.xlsx') }}" download="New Applicant Upload.xlsx"><i
                            class="fas fa-download"></i> Download Template</a>
                </div>

                <form class="form-group" action="upload-applicant" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <input type="hidden" name="upload_type" value="new">
                        <div class="form-group">
                            <label for="faculty1">Faculty</label>
                            <select class="form-control faculty" id="faculty1" name="faculty" lang="1"
                                required>
                                <option value="">Select Option</option>
                                @foreach ($faculty as $row)
                                    <option value="{{ $row->code }}">{{ $row->title }}</option>
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
                                class="form-control" required>
                        </div>
                        <!-- Details View End -->
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Upload Applicants</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Show modal content -->
<div id="createStudent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Create New Applicant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group" action="create-applicant" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="jamb_no">Jamb NO</label>
                            <input type="text" name="jamb_no" id="jamb_no" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="surname">Surname</label>
                            <input type="text" name="surname" id="surname" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="middle_name">Other Name</label>
                            <input type="text" name="middle_name" id="middle_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select class="form-control" id="gender" name="gender" required>
                                <option value="">Select Option</option>
                                <option value="Female">Female</option>
                                <option value="Male">Male</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="faculty2">Faculty</label>
                            <select class="form-control faculty" id="faculty2" name="faculty" lang="2"
                                required>
                                <option value="">Select Option</option>
                                @foreach ($faculty as $row)
                                    <option value="{{ $row->code }}">{{ $row->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department2">Department</label>
                            <select class="form-control department" id="department2" name="department"
                                lang="2" required>
                                <option value="">Select Faculty First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="program1">Program</label>
                            <select class="form-control" id="program2" name="program" lang="2" required>
                                <option value="">Select Department First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="mode">Mode of Entry</label>
                            <select class="form-control" id="mode" name="mode" required>
                                <option value="">Select Option</option>
                                <option value="UTME">UTME</option>
                                <option value="DE">DE</option>
                            </select>
                        </div>
                        <!-- Details View End -->
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
