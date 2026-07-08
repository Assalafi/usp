@php
    function getMonthsDiff($startDate, $endDate) {
    $startDateObject = new DateTime($startDate);
    $endDateObject = new DateTime($endDate);
    $interval = $startDateObject->diff($endDateObject);
    return ($interval->y * 12) + $interval->m;
}
@endphp
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
                        {{-- <button href="#" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#import"><i class="fas fa-upload"></i> {{ ('Import') }}</button> --}}
                    </div>
                    <div class="card-block">
                        <form class="needs-validation" novalidate method="GET" action="#">
                            @csrf
                            <div class="row gx-2">
                                <div class="form-group col-md-3">
                                    <label for="class">Class</label>
                                    <select class="form-control fixedClass" lang="cf" name="class" id="class">
                                        <option value="">Select Option</option>
                                        @foreach ($class as $roww)
                                            <option value="{{ $roww -> class }}">{{ $roww -> class }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="descriptioncf">Description</label>
                                    <select class="form-control" id="descriptioncf" name="description">
                                        <option value="">Select Class First</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="ncoa">NCOA</label>
                                    <input type="text" name="ncoa" class="form-control">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="location">Location</label>
                                    <select class="form-control" name="location" id="location">
                                        <option value="">Select Option</option>
                                        @foreach ($locations as $roww)
                                            <option value="{{ $roww -> location }}">{{ $roww -> location }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="month">Month</label>
                                    <select name="month" id="month" class="form-control">
                                        <option value="">Select Month</option>
                                        <option value="01">January</option>
                                        <option value="02">Febuary</option>
                                        <option value="03">March</option>
                                        <option value="04">April</option>
                                        <option value="05">May</option>
                                        <option value="06">June</option>
                                        <option value="07">July</option>
                                        <option value="08">August</option>
                                        <option value="09">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="year">Year</label>
                                    <select name="year" id="year" class="form-control">
                                        <option value="">Select Year</option>
                                        @for ($y = 2005; $y < 2300; $y++)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <button type="submit" class="btn btn-info btn-filter"><i class="fas fa-search"></i> {{ ('Filter') }}</button>
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
                                        <th>{{ ('Class') }}</th>
                                        <th>{{ ('Description') }}</th>
                                        <th>{{ ('NCOA') }}</th>
                                        <th>{{ ('Cost') }}</th>
                                        <th>{{ ('Capitalization Date') }}</th>
                                        <th>{{ ('Cummulative Depreciation') }}</th>
                                        <th>{{ ('Monthly Depreciation') }}</th>
                                        <th>{{ ('Annual Depreciation') }}</th>
                                        <th>{{ ('Net Book Value') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                    @php
                                        $per = 0;
                                        $startDate = date('d-m-Y', strtotime($row -> capitalization));
                                        $endDate = date('d-m-Y');
                                        $per = ($row -> depreciation)/100;
                                        $amountM = ((($row -> cost) * $per)/12) * getMonthsDiff($startDate, $endDate);
                                        $amountY = (($row -> cost) * $per);
                                        $amount = (($row -> cost) * $per)/12;

                                    @endphp
                                    <tr>
                                        <td>{{ $sn++ }}</td>
                                        <td>{{ $row -> class }}</td>
                                        <td>{{ $row -> description }}</td>
                                        <td>{{ $row -> ncoa }}</td>
                                        <td>N{{ number_format($row -> cost,2) }}</td>
                                        <td>{{ date('d-m-Y', strtotime($row -> capitalization)) }}</td>
                                        <td>N{{ number_format($amountM, 2) }}</td>
                                        <td>N{{ number_format($amount, 2) }}</td>
                                        <td>N{{ number_format($amountY, 2) }}</td>
                                        <td>N{{ number_format($row-> cost - $amountM, 2) }}</td>
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
                            <label for="class">Class</label>
                            <select name="class" id="class" lang="1" class="form-control fixedClass" required>
                                <option value="">Select Option</option>
                                @foreach ($class as $row)
                                    <option value="{{ $row -> class }}">{{ $row -> class }}</option>
                                @endforeach

                            </select>
                        </div>
                        <div class="form-group">
                            <label for="description1">Description</label>
                            <select name="description" id="description1" class="form-control" required>

                            </select>
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" name="location" id="location" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="reference">Reference No</label>
                            <input type="text" name="reference" id="reference" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="cost">Cost</label>
                            <input type="number" name="cost" id="cost" class="form-control" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="capitalization">Capitalization Date</label>
                            <input type="date" name="capitalization" id="capitalization" class="form-control" required>
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
