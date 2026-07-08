@php
    use Illuminate\Support\Facades\DB;
@endphp
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Certificate Management</h5>
                    </div>
                    <div class="card-block">
                        <button class="btn btn-dark uploadAction" data-bs-toggle="modal" data-bs-target="#importGraduated">
                            <i class="fas fa-upload"></i> Upload Graduated Students
                        </button>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#batchGenerateModal">
                            <i class="fas fa-file-pdf"></i> Generate Batch PDF
                        </button>
                    </div>
                    <div class="card-block">
                        <form class="needs-validation" novalidate method="GET" action="/certificate">
                            @csrf
                            <div class="row gx-2">
                                <div class="form-group col-md-2">
                                    <label for="username">ID Number</label>
                                    <input type="text" name="username" id="username" class="form-control" value="{{ request('username') }}">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="filter_faculty">Faculty</label>
                                    <select class="form-control faculty" id="filter_faculty" name="faculty" lang="0">
                                        <option value="">All</option>
                                        @foreach ($faculty as $row)
                                            <option value="{{ $row->code }}" {{ request('faculty') == $row->code ? 'selected' : '' }}>{{ $row->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="filter_program">Program</label>
                                    <select class="form-control" id="filter_program" name="program" lang="0">
                                        <option value="">All</option>
                                        @foreach ($programs as $row)
                                            <option value="{{ $row->code }}" {{ request('program') == $row->code ? 'selected' : '' }}>{{ $row->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="filter_grad">Graduation Date</label>
                                    <select class="form-control" id="filter_grad" name="graduation_date">
                                        <option value="">All</option>
                                        @foreach ($graduation_years as $yr)
                                            <option value="{{ $yr }}" {{ request('graduation_date') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <button type="submit" class="btn btn-info btn-filter"><i class="fas fa-search"></i> Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-block">
                        <div class="table-responsive">
                            <table class="display table nowrap table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Cert. ID</th>
                                        <th>ID Number</th>
                                        <th>Name</th>
                                        <th>Program</th>
                                        <th>Class of Degree</th>
                                        <th>Graduation Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sn = ($data->currentPage() - 1) * $data->perPage() + 1; @endphp
                                    @foreach ($data as $row)
                                        @php $prog = DB::table('program')->where('code', $row->program)->first(); @endphp
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->certificate_id }}</td>
                                            <td>{{ $row->username }}</td>
                                            <td>{{ $row->fullname }}</td>
                                            <td>{{ $prog->title ?? $row->program }}</td>
                                            <td>{{ $row->class_of_degree }}</td>
                                            <td>{{ $row->graduation_date }}</td>
                                            <td>
                                                <a href="/print-certificate-pdf?id={{ $row->id }}" target="_blank" class="btn btn-icon btn-success btn-sm" title="Generate PDF"><i class="fas fa-file-pdf"></i></a>
                                                <button type="button" class="btn btn-icon btn-primary btn-sm updateAction" data-bs-toggle="modal" data-bs-target="#edit{{ $row->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button type="button" class="btn btn-icon btn-danger btn-sm deleteAction" data-bs-toggle="modal" data-bs-target="#delete{{ $row->id }}" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                            </td>
                                        </tr>
                                        <div id="edit{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-md" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Record</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <form action="/update certificate" method="POST">
                                                        <div class="card-body">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                                            <div class="form-group">
                                                                <label>Student</label>
                                                                <input type="text" class="form-control" value="{{ $row->fullname }} ({{ $row->username }})" disabled>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Class of Degree</label>
                                                                <input type="text" name="class_of_degree" class="form-control" value="{{ $row->class_of_degree }}" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Graduation Date</label>
                                                                <input type="text" name="graduation_date" class="form-control" value="{{ $row->graduation_date }}">
                                                            </div>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary">Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="delete{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Warning...</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card text-center">
                                                        <div class="card-body"><h4>Are You Sure?</h4><p>Delete {{ $row->fullname }}?</p></div>
                                                        <form action="/delete certificate" method="POST">
                                                            <div class="card-body">
                                                                @csrf
                                                                <input type="hidden" name="id" value="{{ $row->id }}">
                                                                <button type="button" class="btn btn-info" data-bs-dismiss="modal">No</button>
                                                                <button type="submit" class="btn btn-danger">Yes</button>
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
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <span class="text-muted">Showing {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }} of {{ $data->total() ?? 0 }} results</span>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">{{ $data->links('pagination::bootstrap-4') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Content-->

<!-- Upload Modal -->
<div id="importGraduated" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Graduated Students</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">Excel format: <strong>SN | ID No | Class of Degree</strong></p>
                    <a href="/download-certificate-template" class="btn btn-info btn-sm mb-3"><i class="fas fa-download"></i> Download Template</a>
                </div>
                <form action="/upload certificate" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        @csrf
                        <div class="form-group">
                            <label for="graduation_date">Graduation Date <span class="text-danger">*</span></label>
                            <input type="date" name="graduation_date" id="graduation_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="cert_file">Excel File <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="cert_file" accept=".xlsx, .xls" class="form-control" required>
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Batch Generate Modal -->
<div id="batchGenerateModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Batch Certificates</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form action="/print-certificate-batch-pdf" method="GET" target="_blank">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="batch_grad">Graduation Date</label>
                            <select class="form-control" name="graduation_date" id="batch_grad">
                                <option value="">-- All --</option>
                                @foreach ($graduation_years as $yr)
                                    <option value="{{ $yr }}">{{ $yr }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="batch_program">Program</label>
                            <select class="form-control" id="batch_program" name="program" lang="2">
                                <option value="">-- All --</option>
                                @foreach ($programs as $row)
                                    <option value="{{ $row->code }}">{{ $row->code }}: {{ $row->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Generate PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
