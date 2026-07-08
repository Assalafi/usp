@php
    use Illuminate\Support\Facades\DB;
@endphp

<!-- Start Content-->
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ $page }}</h5>
            </div>
            <div class="card-block">
                <a href="/admin/siwes/export?faculty={{ request('faculty', '') }}&department={{ request('department', '') }}&level={{ request('level', '') }}&program={{ request('program', '') }}&siwes_year={{ request('siwes_year', '') }}" class="btn btn-success">
                    <i class="fas fa-download"></i> Export
                </a>
            </div>
            <div class="card-block">
                <form class="needs-validation" novalidate method="GET" action="/admin/siwes">
                    @csrf
                    <div class="row gx-2">
                        @if(session('username') == 'su' || session('appointment') == 'SIWES')
                        <div class="form-group col-md-2">
                            <label for="facultyf">Faculty</label>
                            <select class="form-control faculty" lang="f" name="faculty" id="facultyf">
                                <option value="">Select Option</option>
                                @foreach ($faculty as $roww)
                                    <option value="{{ $roww->code }}" @if(request('faculty') == $roww->code) selected @endif>{{ $roww->code }}: {{ $roww->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="departmentf">Department</label>
                            <select class="form-control department" lang="f" id="departmentf" name="department">
                                <option value="">Select Faculty First</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="programf">Program</label>
                            <select class="form-control" id="programf" lang="f" name="program">
                                <option value="">Select Department First</option>
                            </select>
                        </div>
                        @endif
                        <div class="form-group col-md-1">
                            <label for="level">Level</label>
                            <select class="form-control" id="level" name="level">
                                <option value="">Select Option</option>
                                @for ($i = 1; $i <= 7; $i++)
                                    <option value="{{ $i * 100 }}" @if(request('level') == $i * 100) selected @endif>{{ $i * 100 }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="siwes_year">SIWES Year</label>
                            <select class="form-control" id="siwes_year" name="siwes_year">
                                <option value="">Select Option</option>
                                @foreach ($siwesYears as $year)
                                    <option value="{{ $year->siwes_year }}" @if(request('siwes_year') == $year->siwes_year) selected @endif>{{ $year->siwes_year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-1">
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
                <!-- [ Data table ] start -->
                <div class="table-responsive">
                    <table id="export-table" class="display table nowrap table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>S/NO</th>
                                <th>NAME OF STUDENT</th>
                                <th>MATRIC NUMBER</th>
                                <th>COURSE OF STUDY</th>
                                <th>LEVEL OF STUDY</th>
                                <th>SIWES YEAR</th>
                                <th>STUDENT EMAIL ADDRESS</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $sn = 1; @endphp
                            @foreach ($data as $student)
                                <tr>
                                    <td>{{ $sn++ }}</td>
                                    <td>{{ strtoupper($student->first_name . ' ' . $student->last_name . ' ' . $student->other_name) }}</td>
                                    <td>{{ strtoupper($student->matric_number) }}</td>
                                    <td>{{ strtoupper($student->course_of_study) }}</td>
                                    <td>{{ $student->level_of_study }}</td>
                                    <td>{{ $student->siwes_year }}</td>
                                    <td>{{ $student->student_email_address }}</td>
                                    <td>
                                        <a href="/admin/siwes/view/{{ $student->id }}" class="btn btn-icon btn-success btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
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
                            {{ $data->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
