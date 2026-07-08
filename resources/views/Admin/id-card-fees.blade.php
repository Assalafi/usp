<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>ID CARD PAYMENTS</h5>
                    </div>
                    <div class="card-block row">
                        <div class="col-md-12">
                            <form class="needs-validation" novalidate method="GET" action="#">
                                @csrf
                                <div class="row gx-2">
                                    <div class="form-group col-md-2">
                                        <label for="facultyf">Faculty <span>*</span></label>
                                        <select class="form-control faculty" lang="f" name="faculty"
                                            id="facultyf">
                                            <option value="{{ $_GET['faculty'] ?? 'all' }}">
                                                {{ $_GET['faculty'] ?? 'Select Option' }}</option>
                                            @foreach ($faculty as $roww)
                                                <option value="{{ $roww->code }}">{{ $roww->title }}
                                                    ({{ $roww->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"> You must select FACULTY </div>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="departmentf">Department</label>
                                        <select class="form-control department" lang="f" id="departmentf"
                                            name="department">
                                            <option value="{{ $_GET['department'] ?? 'all' }}">
                                                {{ $_GET['department'] ?? 'Select Faculty First' }}</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="programf">Program</label>
                                        <select class="form-control" id="programf" lang="f" name="program">
                                            <option value="{{ $_GET['program'] ?? 'all' }}">
                                                {{ $_GET['program'] ?? 'Select Department First' }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <label for="status">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="{{ $_GET['status'] ?? 'Paid' }}">
                                                {{ $_GET['status'] ?? 'Select Option' }}</option>
                                            <option value="Paid">Paid</option>
                                            <option value="Pending">Pending</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <label for="status">Start At</label>
                                        <input type="date" name="start" id="start" class="form-control"
                                            placeholder="Start At" value="{{ $_GET['start'] ?? '2024-01-01' }}">
                                    </div>
                                    <div class="col-md-1">
                                        <label for="status">End At</label>
                                        <input type="date" name="end" id="end" class="form-control"
                                            placeholder="End At" value="{{ $_GET['end'] ?? date('Y-m-d') }}">
                                    </div>
                                    {{-- session --}}
                                    <div class="col-md-1">
                                        <label for="session">Session</label>
                                        <select class="form-control" id="session" name="session">
                                            <option value="{{ $_GET['session'] ?? '2024/2025' }}">
                                                {{ $_GET['session'] ?? 'Select Option' }}</option>
                                            <option value="2024/2025">2024/2025</option>
                                            <option value="2023/2024">2023/2024</option>
                                            <option value="2022/2023">2022/2023</option>
                                            <option value="2021/2022">2021/2022</option>
                                            <option value="2020/2021">2020/2021</option>
                                            <option value="2019/2020">2019/2020</option>
                                            <option value="2018/2019">2018/2019</option>
                                            <option value="2017/2018">2017/2018</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <button type="submit" style="width: 100%"
                                                class="btn btn-info btn-filter"><i class="fas fa-search"></i>
                                                {{ 'Filter' }}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

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
                                        <th>{{ 'ID NUMBER' }}</th>
                                        <th>{{ 'NAME' }}</th>
                                        <th>{{ 'RRR' }}</th>
                                        <th>{{ 'FACULTY' }}</th>
                                        <th>{{ 'DEPARTMENT' }}</th>
                                        <th>{{ 'PROGRAM' }}</th>
                                        <th>{{ 'STATUS' }}</th>
                                        <th>{{ 'DATE' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->student->username }}</td>
                                            <td>{{ $row->student->fullname }}</td>
                                            <td>{{ $row->rrr }}</td>
                                            <td>{{ $row->facultys->title ?? 'N/A' }}</td>
                                            <td>{{ $row->departments->title ?? 'N/A' }}</td>
                                            <td>{{ $row->programs->title ?? 'N/A' }}</td>
                                            <td>{{ $row->status }}</td>
                                            <td>{{ date('D, d M Y', strtotime($row->updated_at)) }}</td>
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
