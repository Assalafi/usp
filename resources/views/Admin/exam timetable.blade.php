<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <!-- [ Card ] start -->
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ 'Add' }} / {{ 'Edit' }}</h5>
                    </div>

                    <div class="card-block">
                        <form class="needs-validation" novalidate method="get" action="#">
                            <div class="row gx-2">
                                <div class="form-group col-md-3">
                                    <label for="faculty">Faculty <span>*</span></label>
                                    <select class="form-control" name="faculty" id="faculty" required>
                                        <option value="{{ isset($_GET['faculty']) ? $_GET['faculty'] : '' }}">Select
                                            Option</option>
                                        @foreach ($faculty as $roww)
                                            <option value="{{ $roww->code }}">{{ $roww->title }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"> You must select FACULTY </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="level">Level (Optional)</label>
                                    <select name="level" class="form-control" id="level">
                                        <option value="">Select Option</option>
                                        <option value="100">100</option>
                                        <option value="200">200</option>
                                        <option value="300">300</option>
                                        <option value="400">400</option>
                                        <option value="500">500</option>
                                        <option value="600">600</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <button type="submit" class="btn btn-info btn-filter"><i class="fas fa-search"></i>
                                        {{ 'Filter' }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @isset($_GET['faculty'])
                <div class="col-md-12">
                    <div class="card-block">
                        <div class="row">
                            <div class="col-md-6">
                                <a style="width:30%" href="#" class="btn btn-primary createAction"
                                    data-bs-toggle="modal" data-bs-target="#create"><i class="fas fa-plus"></i>
                                    {{ 'Add New' }}</a>
                            </div>
                            <div class="col-md-6">
                                <a style="width:30%; float:right" href="/exam-timetable/{{ $_GET['faculty'] }}"
                                    class="btn btn-primary btn-sm nav-link">Preview</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="card">
                        <div class="card-block">
                            <!-- [ Data table ] start -->
                            <div class="table-responsive">
                                <table id="export-table" class="display table nowrap table-striped table-hover"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ 'Course' }}</th>
                                            <th>{{ 'Hall' }}</th>
                                            <th>{{ 'Faculty' }}</th>
                                            <th>{{ 'Date' }}</th>
                                            <th>{{ 'From' }}</th>
                                            <th>{{ 'To' }}</th>
                                            <th>{{ 'Lecturers' }}</th>
                                            <th>{{ 'Status' }}</th>
                                            <th>{{ 'Action' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $sn = 1;
                                        @endphp
                                        @foreach ($data->where('faculty', $_GET['faculty']) as $row)
                                            <tr>
                                                <td>{{ $sn++ }}</td>
                                                <td>{{ $row->course }}</td>
                                                <td>{{ $row->hall }}</td>
                                                <td>{{ $row->faculty }}</td>
                                                <td>{{ $row->date }}</td>
                                                <td>{{ date('h:i A', strtotime($row->start)) }}</td>
                                                <td>{{ date('h:i A', strtotime($row->end)) }}</td>
                                                <td>{{ $row->lecturer }}</td>
                                                <td>

                                                    @if ($row->status == '1')
                                                        {{ 'ACTIVE' }}
                                                    @else
                                                        {{ 'INACTIVE' }}
                                                    @endif
                                                </td>
                                                <td>
                                                    {{-- <a href="#" class="btn btn-icon btn-primary btn-sm updateAction" data-bs-toggle="modal" data-bs-target="#updateStudent{{ $row->id }}">
                                                    <i class="far fa-edit"></i>
                                                </a> --}}

                                                    <button type="button"
                                                        class="btn btn-icon btn-danger btn-sm deleteAction"
                                                        data-bs-toggle="modal" data-bs-target="#delete{{ $row->id }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Show modal content -->
                                            <div id="updateStudent{{ $row->id }}" class="modal fade" tabindex="-1"
                                                role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-sm" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="myModalLabel">Update</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"><span
                                                                    aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <div class="card">
                                                            <form class="form-group" action="update {{ $page }}"
                                                                method="POST" enctype="multipart/form-data">
                                                                <div class="card-body">
                                                                    <!-- Details View Start -->
                                                                    @csrf
                                                                    <input type="hidden" name="id"
                                                                        value="{{ $row->id }}">
                                                                    <input type="text" name="faculty"
                                                                        value="{{ $_GET['faculty'] }}" hidden>
                                                                    <div class="form-group col-md-12">
                                                                        <label for="course">Course<span>*</span></label>
                                                                        <select class="form-control select2 must"
                                                                            name="course" id="course" required>
                                                                            <option value="{{ $row->course }}">Selected
                                                                                {{ $row->course }}</option>
                                                                            @foreach ($course->where('faculty', $_GET['faculty']) as $subject)
                                                                                <option value="{{ $subject->code }}">
                                                                                    {{ $subject->code }} -
                                                                                    {{ $subject->title }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group col-md-12">
                                                                        <label for="lecturer">Lecturer
                                                                            <span>*</span></label>
                                                                        <input type="text" value="{{ $row->lecturer }}"
                                                                            class="form-control must" name="lecturer">
                                                                    </div>
                                                                    <div class="form-group col-md-12">
                                                                        <label for="hall">Hall<span>*</span></label>
                                                                        <select class="form-control select2 must"
                                                                            name="hall" id="hall" required>
                                                                            <option value="{{ $row->hall }}">Selected
                                                                                {{ $row->hall }}</option>
                                                                            @foreach ($hall as $room)
                                                                                <option value="{{ $room->hall }}">
                                                                                    {{ $room->hall }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group col-md-12">
                                                                        <label for="date">Date<span>*</span></label>
                                                                        <input type="date" value="{{ $row->date }}"
                                                                            class="form-control must" name="date"
                                                                            id="date" required>
                                                                    </div>
                                                                    <div class="form-group col-md-12">
                                                                        <label for="start">Start
                                                                            At<span>*</span></label>
                                                                        <input type="time" value="{{ $row->start }}"
                                                                            class="form-control must" name="start"
                                                                            id="start" required>
                                                                    </div>
                                                                    <div class="form-group col-md-12">
                                                                        <label for="end">Ending At
                                                                            <span>*</span></label>
                                                                        <input type="time" class="form-control must"
                                                                            name="end" id="end"
                                                                            value="{{ $row->end }}" required>
                                                                    </div>
                                                                    <!-- Details View End -->
                                                                    <button type="button" class="btn btn-info"
                                                                        data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit"
                                                                        class="btn btn-success">Update</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="delete{{ $row->id }}" class="modal fade" tabindex="-1"
                                                role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-sm" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"><span
                                                                    aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <div class="card text-center">
                                                            <div class="card-body">
                                                                <h4>Are You Sure</h4>
                                                            </div>
                                                            <form class="form-group" action="delete {{ $page }}"
                                                                method="POST" enctype="multipart/form-data">
                                                                <div class="card-body">
                                                                    <!-- Details View Start -->
                                                                    @csrf
                                                                    <input type="hidden" name="id"
                                                                        value="{{ $row->id }}">
                                                                    <!-- Details View End -->
                                                                    <button type="button" class="btn btn-info"
                                                                        data-bs-dismiss="modal">No</button>
                                                                    <button type="submit"
                                                                        class="btn btn-danger">Yes</button>
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
            @endisset
            <!-- [ Card ] end -->
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>

<!-- Show modal content -->
@isset($_GET['faculty'])
    @php
        if (isset($_GET['level'])) {
            $course = $course->where('faculty', $_GET['faculty'])->where('level', $_GET['level']);
        } else {
            $course = $course->where('faculty', $_GET['faculty']);
        }
    @endphp
    <div id="create" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Create</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="card">
                    <form class="form-group" action="create {{ $page }}" method="POST"
                        enctype="multipart/form-data">
                        <div class="card-body">
                            <!-- Details View Start -->
                            @csrf
                            <div class="row">
                                <input type="text" name="faculty" value="{{ $_GET['faculty'] }}" hidden>
                                <div class="form-group col-md-12">
                                    <label for="course">Course<span>*</span></label>
                                    <select class="form-control select2 must" name="course" id="course" required>
                                        <option value="">Select Option</option>
                                        @foreach ($course->where('faculty', $_GET['faculty']) as $subject)
                                            <option value="{{ $subject->code }}">{{ $subject->code }} -
                                                {{ $subject->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="lecturer">Lecturer <span>*</span></label>
                                    <input type="text" class="form-control must" value="{{ old('lecturer') }}"
                                        name="lecturer">
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="hall">Hall<span>*</span></label>
                                    <select class="form-control select2 must" name="hall" id="hall" required>
                                        <option value="{{ old('hall') }}">Select Option</option>
                                        @foreach ($hall as $room)
                                            <option value="{{ $room->hall }}">{{ $room->hall }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="date">Date<span>*</span></label>
                                    <input type="date" class="form-control must" value="{{ old('date') }}"
                                        name="date" id="date" required>
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="start">Start At<span>*</span></label>
                                    <input type="time" class="form-control must" value="07:00" name="start"
                                        id="start" required>
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="end">Ending At <span>*</span></label>
                                    <input type="time" class="form-control must" name="end" id="end"
                                        value="18:00" required>
                                </div>
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
@endisset
