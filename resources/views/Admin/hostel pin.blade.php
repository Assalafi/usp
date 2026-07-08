
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Hostel Pin</h5>
                    </div>
                    @if (session('accType') == 'Admin')
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card-block">
                                <form action="generate hostel pin" method="POST">
                                    @csrf
                                    <div class="row gx-2">
                                        <div class="form-group col-md-6">
                                            <label for="pins">Number of Pins</label>
                                            <input type="number" class="form-control" name="pins" id="pins" required placeholder="Enter Pins Number">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <button type="submit" class="btn btn-info btn-filter"><i class="fas fa-gear"></i>Generate</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card-block">
                                <form action="print hostel pin" method="POST">
                                    @csrf
                                    <div class="row gx-2">
                                        <div class="form-group col-md-6">
                                            <label for="pins">Batch</label>
                                            <select class="form-control" name="batch" id="pins" required>
                                                <option value="">Select Batch</option>
                                                <option value="All">All</option>
                                                @foreach ($batch as $rows)
                                                    <option value="{{ $rows->batch }}">{{ $rows->batch }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <button type="submit" class="btn btn-info btn-filter"><i class="fas fa-print"></i>Print</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card-block">
                                <form action="delete hostel pin" method="POST">
                                    @csrf
                                    <div class="row gx-2">
                                        <div class="form-group col-md-6">
                                            <label for="pins">Batch</label>
                                            <select class="form-control" name="batch" id="pins" required>
                                                <option value="">Select Batch</option>
                                                <option value="All">All</option>
                                                @foreach ($batch as $rows)
                                                    <option value="{{ $rows->batch }}">{{ $rows->batch }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <button type="submit" class="btn btn-info btn-filter"><i class="fas fa-trash"></i>Delete</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card-block">
                                <label for="pins">Assign Pin</label>
                                <br>
                                <button href="#" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#importStudent"><i class="fas fa-upload"></i> {{ ('Import') }}</button>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="card-block">
                        <form class="needs-validation" novalidate method="GET" action="#">
                            @csrf
                            <input type="hidden" name="filter">
                            <div class="row gx-2">
                                <div class="form-group col-md-4">
                                    <label for="username">ID NO.</label>
                                    <input type="text" name="username" id="username" class="form-control">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="pin">Pin</label>
                                    <input type="text" name="pin" id="pin" class="form-control">
                                </div>
                                <div class="form-group col-md-2">
                                    <button type="submit" class="btn btn-info btn-filter"><i class="fas fa-search"></i> {{ ('Search') }}</button>
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
                            <table id="export-table" class="display table nowrap table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Student ID</th>
                                        <th>Pin</th>
                                        <th>Status</th>
                                        <th>Batch</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                  @foreach( $data as $row )
                                    <tr>
                                        <td>
                                            {{ $sn++ }}
                                        </td>
                                        <td>
                                            {{ $row->username }}
                                        </td>
                                        <td>
                                            {{ $row->pin }}
                                        </td>
                                        <td>
                                            {{ $row->status }}
                                        </td>
                                        <td>
                                            {{ $row->batch }}
                                        </td>
                                        <td>
                                            {{ $row->created_at }}
                                        </td>
                                    </tr>
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

<div id="importStudent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Upload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">

                <form class="form-group" action="upload-pin" method="POST" enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="pins">Batch</label>
                            <select class="form-control" name="batch" id="pins" required>
                                <option value="">Select Batch</option>
                                @foreach ($batch as $rows)
                                    <option value="{{ $rows->batch }}">{{ $rows->batch }}</option>
                                @endforeach
                            </select>
                        </div>
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
