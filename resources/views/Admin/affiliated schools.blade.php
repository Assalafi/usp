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
                        <h5>Affiliated Schools</h5>
                    </div>
                    <div class="card-block d-flex align-items-center gap-2 flex-wrap">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createAffiliatedSchool">
                            <i class="fas fa-plus"></i> Add Affiliated School
                        </button>
                        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#uploadAffiliatedSchools">
                            <i class="fas fa-upload"></i> Upload Affiliated Schools
                        </button>
                    </div>
                    <div class="card-block">
                        <div class="table-responsive">
                            <table class="display table nowrap table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>School Name</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sn = ($data->currentPage() - 1) * $data->perPage() + 1; @endphp
                                    @foreach ($data as $row)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->status == '1' ? 'Active' : 'Inactive' }}</td>
                                            <td>
                                                <button type="button" class="btn btn-icon btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#edit{{ $row->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                                                <button type="button" class="btn btn-icon btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#delete{{ $row->id }}" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                            </td>
                                        </tr>
                                        <div id="edit{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit School</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <form action="/update-affiliated-school" method="POST">
                                                        <div class="card-body">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                                            <div class="form-group">
                                                                <label>School Name</label>
                                                                <input type="text" name="name" class="form-control" value="{{ $row->name }}" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Status</label>
                                                                <select name="status" class="form-control">
                                                                    <option value="1" {{ $row->status == '1' ? 'selected' : '' }}>Active</option>
                                                                    <option value="0" {{ $row->status == '0' ? 'selected' : '' }}>Inactive</option>
                                                                </select>
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
                                                        <div class="card-body"><h4>Are You Sure?</h4><p>Delete {{ $row->name }}?</p></div>
                                                        <form action="/delete-affiliated-school" method="POST">
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

<!-- Create Modal -->
<div id="createAffiliatedSchool" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Affiliated School</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form action="/create-affiliated-school" method="POST">
                    <div class="card-body">
                        @csrf
                        <div class="form-group">
                            <label for="name">School Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadAffiliatedSchools" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Affiliated Schools</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">Excel format: <strong>SN | School Name</strong></p>
                    <a href="/download-affiliated-schools-template" class="btn btn-info btn-sm mb-3"><i class="fas fa-download"></i> Download Template</a>
                </div>
                <form action="/upload-affiliated-schools-list" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        @csrf
                        <div class="form-group">
                            <label for="aff_schools_file">Excel File <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="aff_schools_file" accept=".xlsx, .xls, .csv" class="form-control" required>
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
