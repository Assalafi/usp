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
                        <h5>STATUS | {{ isset($_GET['session']) ? $_GET['session'] : session('system_session') }} ACADEMIC SESSION</h5>
                    </div>
                    <div class="card-block">
                        NIL
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ strtoupper($page) }} | {{ isset($_GET['session']) ? $_GET['session'] : session('system_session') }} ACADEMIC SESSION</h5>
                    </div>
                    <div class="card-block">
                        <form class="needs-validation" novalidate method="GET" action="#">
                            @csrf
                            <div class="row gx-2">
                                <div class="form-group col-md-3">
                                    <label for="session">OTHER SESSION</label>
                                    <select class="form-control" id="session" name="session" required>
                                        <option value="">Select Option</option>
                                        @foreach ($sessions as $ses)
                                            <option value="{{ $ses -> title }}">{{ $ses -> title }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"> You must select Session </div>
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
                            <table id="export-table" class="display table nowrap table-striped table-hover"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ 'COURSE' }}</th>
                                        <th>{{ 'CA' }}</th>
                                        <th>{{ 'EXAM' }}</th>
                                        <th>{{ 'TOTAL' }}</th>
                                        <th>{{ 'GRADE' }}</th>
                                        <th>{{ 'LEVEL' }}</th>
                                        <th>{{ 'SEMESTER' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>
                                                {{ $row->code }}
                                            </td>
                                            <td>
                                                {{ $row->ca }}
                                            </td>
                                            <td>
                                                {{ $row->exam }}
                                            </td>
                                            <td>
                                                {{ $row->total }}
                                            </td>
                                            <td>
                                                {{ $row->grade }}
                                            </td>
                                            <td>
                                                {{ $row->level }}
                                            </td>
                                            <td>
                                                {{ $row->semester }}
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
