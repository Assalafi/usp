
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ strtoupper($page) }}</h5>
                    </div>
                    <div class="card-block">
                        
                        <a href="#" class="btn btn-primary createAction" data-bs-toggle="modal" data-bs-target="#create"><i class="fas fa-plus"></i> {{ ('Add New') }}</a>
                        
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="card">
                    <div class="card-block">
                        <!-- [ Data table ] start -->
                        <div class="table-responsive">
                            <table id="export-table" class="display table nowrap table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ ('Title') }}</th>
                                        <th>{{ ('Starting Date') }}</th>
                                        <th>{{ ('Ending Dtate') }}</th>
                                        <th>{{ ('Status') }}</th>
                                        <th>{{ ('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                    <tr>
                                        <td>{{ $sn++ }}</td>
                                        <td>{{ $row -> title }}</td>
                                        <td>{{ $row -> start }}</td>
                                        <td>{{ $row -> end }}</td>
                                        <td>
                                            
                                            @if ($row -> status == '1')
                                                {{ 'ACTIVE' }}
                                            @else
                                                {{ 'INACTIVE' }}
                                            @endif
                                        </td>
                                        <td>
                                            <a href="#" class="btn btn-icon btn-primary btn-sm updateAction" data-bs-toggle="modal" data-bs-target="#updateStudent{{ $row->id }}">
                                                <i class="far fa-edit"></i>
                                            </a>

                                            <button type="button" class="btn btn-icon btn-danger btn-sm deleteAction" data-bs-toggle="modal" data-bs-target="#delete{{ $row->id }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Show modal content -->
                                    <div id="updateStudent{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-sm" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="myModalLabel">Update</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="card">
                                                    <form class="form-group" action="update {{ $page }}" method="POST" enctype="multipart/form-data">
                                                        <div class="card-body">
                                                            <!-- Details View Start -->
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                                            <div class="form-group">
                                                                <label for="title">Title</label>
                                                                <select class="form-control" id="title" name="title" required>
                                                                    <option value="{{ $row -> title }}">Select Option</option>
                                                                    @for ($i = 2020; $i < 3000; $i++)
                                                                        <option value="{{ $i.'/'.($i+1) }}">{{ $i.'/'.($i+1) }}</option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="start">Starting Date</label>
                                                                <input type="date" name="start" value="{{ $row->start }}" id="start" class="form-control" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="end">Ending Date</label>
                                                                <input type="date" name="end" value="{{ $row->end }}" id="end" class="form-control" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="status">Status</label>
                                                                <select class="form-control" id="status" name="status" required>
                                                                    <option value="{{ $row->status }}">Select Option</option>
                                                                    <option value="1">Active</option>
                                                                    <option value="0">Inactive</option>
                                                                </select>
                                                            </div>
                                                            <!-- Details View End -->
                                                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-success">Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="delete{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-sm" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="card text-center">
                                                    <div class="card-body">
                                                        <h4>Are You Sure</h4>
                                                    </div>
                                                    <form class="form-group" action="delete {{ $page }}" method="POST" enctype="multipart/form-data">
                                                        <div class="card-body">
                                                            <!-- Details View Start -->
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                                            <!-- Details View End -->
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
                        <!-- [ Data table ] end -->
                    </div>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->
<!-- Show modal content -->
<div id="import" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Upload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <div class="card-body">
                    <a href="#"><i class="fas fa-download"></i> Download Template</a>
                </div>
                
                <form class="form-group" action="upload {{ $page }}" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="file"></label>
                            <input type="file" name="file" id="file" accept=".xlsx, .xls" class="form-control">
                        </div>
                        <!-- Details View End -->
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Assign</button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
</div>

<!-- Show modal content -->
<div id="create" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Create</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group" action="create {{ $page }}" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="title">Title</label>
                            <select class="form-control" id="title" name="title" required>
                                <option value="">Select Option</option>
                                @for ($i = 2020; $i < 3000; $i++)
                                    <option value="{{ $i.'/'.($i+1) }}">{{ $i.'/'.($i+1) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="start">Starting Date</label>
                            <input type="date" name="start" id="start" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="end">Ending Date</label>
                            <input type="date" name="end" id="end" class="form-control" required>
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