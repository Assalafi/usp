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
                        <h5>Hostel Recipients</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-auto">
                            {{-- <div class="card-block">
                                <form action="#" method="POST">
                                    @csrf
                                    <div class="row gx-2">
                                        <input type="hidden" id="type" value="1">
                                        <input type="hidden" id="page" value="ajax/admin/recipients">
                                        <div class="form-group col-md-6">
                                            <select class="form-control" id="filterHall" required>
                                                <option value="">SELECT HALL</option>
                                                @foreach ($hall as $halls)
                                                    <option value="{{ $halls -> hall }}">{{ $halls -> hall }}</option>
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
                            </div> --}}
                            <div class="card-block">
                                <form method="GET" action="#" required>
                                    @csrf
                                    <input type="hidden" name="hostel">
                                    <div class="row gx-2">
                                        <div class="form-group col-md-3">
                                            <label for="occupant">Occupant ID NO.</label>
                                            <input type="text" class="form-control" name="occupant" id="occupant">
                                            <div class="invalid-feedback">You Must Type ID NO.</div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <button type="submit" class="btn btn-info btn-filter"><i
                                                    class="fas fa-search"></i> {{ 'Search' }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="card-block">
                                <form class="needs-validation" novalidate method="GET" action="#">
                                    @csrf
                                    <input type="hidden" name="filter">
                                    <div class="row gx-2">
                                        <div class="form-group col-md-3">
                                            <label>HALLS</label>
                                            <select class="form-control" name="hall" required>
                                                <option value="">SELECT HALL</option>
                                                @foreach ($hall as $halls)
                                                    <option value="{{ $halls->hall }}">{{ $halls->hall }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">You Must Select Hall</div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>PAYMENT</label>
                                            <select class="form-control" id="hostel_payment" name="hostel_payment"
                                                required>
                                                <option value="">SELECT PAYMENT OPTION</option>
                                                <option value="1">PAID</option>
                                                <option value="0">UNPAID</option>
                                            </select>
                                            <div class="invalid-feedback">You Must Select This Option</div>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>TYPE</label>
                                            <select class="form-control" id="bed_type" name="bed_type">
                                                <option value="">SELECT TYPE OPTION</option>
                                                <option value="0">ONLINE</option>
                                                <option value="1">RESERVE</option>
                                                <option value="2">NEW STUDENT</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>DATE-TIME</label>
                                            <input type="datetime-local" name="date" id="date"
                                                class="form-control" required>
                                            <div class="invalid-feedback">You Must Select Date and Time</div>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <button type="submit" class="btn btn-info btn-filter"><i
                                                    class="fas fa-search"></i> {{ 'Search' }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="card-block">
                                <form class="needs-validation" novalidate method="GET" action="#">
                                    @csrf
                                    <input type="hidden" name="hostel">
                                    <input type="hidden" name="status" value="1">
                                    <div class="row gx-2">
                                        <div class="form-group col-md-3">
                                            <label>HALLS</label>
                                            <select class="form-control" name="hall" required>
                                                <option value="">SELECT HALL</option>
                                                @foreach ($hall as $halls)
                                                    <option value="{{ $halls->hall }}">{{ $halls->hall }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">You Must Select Hall</div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>PAYMENT</label>
                                            <select class="form-control" id="hostel_payment" name="hostel_payment"
                                                required>
                                                <option value="1">SELECT PAYMENT OPTION</option>
                                                <option value="1">PAID</option>
                                                <option value="0">UNPAID</option>
                                            </select>
                                            <div class="invalid-feedback">You Must Select This Option</div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>TYPE</label>
                                            <select class="form-control" id="bed_type" name="bed_type">
                                                <option value="">SELECT TYPE OPTION</option>
                                                <option value="0">ONLINE</option>
                                                <option value="1">RESERVE</option>
                                                <option value="2">NEW STUDENT</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <button type="submit" class="btn btn-info btn-filter"><i
                                                    class="fas fa-search"></i> {{ 'Search' }}</button>
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
                                        <th>Hall</th>
                                        <th>Block</th>
                                        <th>Room</th>
                                        <th>Bed</th>
                                        <th>Status</th>
                                        <th>Type</th>
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
                                                @if ($row->bed_type == 0)
                                                    Online
                                                @elseif($row->bed_type == 1)
                                                    Reserve
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
                                                @if ($row->hostel_payment == 1)
                                                    <a href="/print-permit/{{ $row->id }}"
                                                        class="btn btn-info btn-sm" <i class="fas fa-print"></i>
                                                        Permit
                                                    </a>
                                                @endif
                                                <button type="button" class="btn btn-secondary btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#payModal{{ $row->id }}">{{ $row->hostel_payment == '1' ? 'Unpaid' : 'Pay' }}
                                                </button>

                                                <button type="button" class="btn btn-danger btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#showModal{{ $row->id }}">
                                                    <i class="fas fa-minus"></i> Revoke
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- Show modal content -->
                                        <div id="showModal{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Revoke Warning</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <form action="revoke" method="POST">
                                                        <div class="card-body">
                                                            <!-- Details View Start -->
                                                            @csrf
                                                            <div class="form-group">
                                                                <input type="hidden" name="id" id="id"
                                                                    value="{{ $row->id }}">
                                                            </div>
                                                            <!-- Details View End -->
                                                            <button type="button" class="btn btn-info"
                                                                data-bs-dismiss="modal">Close</button>
                                                            <button type="submit"
                                                                class="btn btn-danger">Revoke</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="payModal{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">
                                                            {{ $row->hostel_payment == '1' ? 'Unpaid' : 'Payment' }}
                                                            Warning</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <form action="hostel-payment" method="POST">
                                                        <div class="card-body">
                                                            <!-- Details View Start -->
                                                            @csrf
                                                            <div class="form-group">
                                                                <input type="hidden" name="id" id="id"
                                                                    value="{{ $row->id }}">
                                                                <input type="hidden" name="hostel_payment"
                                                                    id="hostel_payment"
                                                                    value="{{ $row->hostel_payment == '1' ? '0' : '1' }}">
                                                            </div>
                                                            <!-- Details View End -->
                                                            <button type="button" class="btn btn-info"
                                                                data-bs-dismiss="modal">Close</button>
                                                            <button type="submit"
                                                                class="btn btn-secondary">{{ $row->hostel_payment == '1' ? 'Unpaid' : 'Pay' }}</button>
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
