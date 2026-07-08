@php
    use App\Models\User;
    use App\Models\Student;
@endphp
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Flag Bed Space</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-auto">
                            <div class="card-block">
                                <form action="#" method="POST">
                                    @csrf
                                    <div class="row gx-2">
                                        <input type="hidden" id="type" value="1">
                                        <input type="hidden" id="page" value="ajax/admin/available bed space">
                                        <div class="form-group col-md-6">
                                            <select class="form-control" id="filterHall" required>
                                                <option value="">SELECT HALL</option>
                                                @foreach ($hall as $hall)
                                                    <option value="{{ $hall->hall }}">{{ $hall->hall }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <select class="form-control" id="filterCategory" required>
                                                <option value="">SELECT CATEGORY</option>
                                                <option value="CONVENTIONAL">CONVENTIONAL</option>
                                                <option value="NONCONVENTIONAL">NON-CONVENTIONAL</option>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-12" id="mainbody">
                <div class="card">
                    <div class="card-block">
                        <!-- [ Data table ] start -->
                        <div class="table-responsive">
                            <table id="export-table" class="display table nowrap table-striped table-hover"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>ID</th>
                                        <th>Hall</th>
                                        <th>Block</th>
                                        <th>Room</th>
                                        <th>Bed</th>
                                        <th>Status</th>
                                        <th>Occupant</th>
                                        <th>Name</th>
                                        <th>Payment</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="getFilter">
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                        @php
                                            $name = Student::select('fullname')
                                                ->where('username', $row->occupant)
                                                ->first();
                                        @endphp
                                        <tr>
                                            <td>
                                                {{ $sn++ }}
                                            </td>
                                            <td>
                                                {{ $row->id }}
                                            </td>
                                            <td>
                                                {{ $row->hall }}
                                            </td>
                                            <td>
                                                {{ $row->block }}
                                            </td>
                                            <td>
                                                {{ $row->room }}
                                            </td>
                                            <td>
                                                {{ $row->bed }}
                                            </td>
                                            <td>
                                                @if ($row->status == 0)
                                                    Available
                                                @elseif($row->status == 1)
                                                    Occupied
                                                @endif
                                            </td>
                                            <td>
                                                {{ $row->occupant }}
                                            </td>
                                            <td>
                                                @if ($name)
                                                    {{ $name['fullname'] }}
                                                @endif
                                            </td>
                                            <td>
                                                {{ $row->hostel_payment }}
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#showModal{{ $row->id }}">
                                                    <i class="fas fa-plus"></i> Assign
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- Show modal content -->
                                        <div id="showModal{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Warning Flag</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <form action="ab" method="POST">
                                                        <div class="card-body">
                                                            <!-- Details View Start -->
                                                            @csrf
                                                            <div class="form-group">
                                                                <label for="username">ID Number</label>
                                                                <input type="text" class="form-control"
                                                                    name="username" id="username" required
                                                                    placeholder="Enter ID Number">
                                                                <input type="hidden" name="id" id="id"
                                                                    value="{{ $row->id }}">
                                                            </div>
                                                            <!-- Details View End -->
                                                            <button type="button" class="btn btn-info"
                                                                data-bs-dismiss="modal">Close</button>
                                                            <button type="submit"
                                                                class="btn btn-success">Assign</button>
                                                        </div>
                                                    </form>
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
            <div class="col-sm-12" style="display: none;" id="wait">
                <div class="spinner-grow" role="status">
                    <span class="visually-hidden"></span>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
