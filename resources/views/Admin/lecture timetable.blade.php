@php
    function abbreviateMiddleName($name)
    {
        $nameParts = explode(' ', $name);
        if (count($nameParts) === 3) {
            $nameParts[1] = substr($nameParts[1], 0, 1) . '.';
            $abbreviatedName = implode(' ', $nameParts);
            return $abbreviatedName;
        }
        return $name;
    }
@endphp
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
                                    <label for="level">Level</label>
                                    <select name="level" class="form-control" id="level" required>
                                        <option value="{{ isset($_GET['level']) ? $_GET['level'] : 'Level' }}">{{ isset($_GET['level']) ? $_GET['level'] : 'Select Option' }}</option>
                                        <option value="100">100</option>
                                        <option value="200">200</option>
                                        <option value="300">300</option>
                                        <option value="400">400</option>
                                        <option value="500">500</option>
                                        <option value="600">600</option>
                                    </select>
                                    <div class="invalid-feedback"> You must select Level </div>
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

            <div class="col-sm-12">
                @isset($_GET['faculty'])
                    <div class="card">
                        @php
                            $weekdays = ['1', '2', '3', '4', '5', '6'];
                        @endphp
                        <ul class="nav nav-pills mb-3 card-block" id="myTab" role="tablist">
                            <li>
                                <a href="#" style="color: rgb(255, 68, 0)" class="btn btn-info createAction"
                                    data-bs-toggle="modal" data-bs-target="#create">
                                    {{ 'Add New' }}</a>
                            </li>
                            @foreach ($weekdays as $weekday)
                                <li class="nav-item">
                                    <a class="nav-link @if ($weekday == 1) active @endif text-uppercase"
                                        id="day{{ $weekday }}-tab" data-bs-toggle="tab" href="#day{{ $weekday }}"
                                        role="tab" aria-controls="day{{ $weekday }}" aria-selected="true">
                                        @if ($weekday == 1)
                                            {{ $day[$weekday] = 'Monday' }}
                                        @elseif($weekday == 2)
                                            {{ $day[$weekday] = 'Tuesday' }}
                                        @elseif($weekday == 3)
                                            {{ $day[$weekday] = 'Wednesday' }}
                                        @elseif($weekday == 4)
                                            {{ $day[$weekday] = 'Thursday' }}
                                        @elseif($weekday == 5)
                                            {{ $day[$weekday] = 'Friday' }}
                                        @elseif($weekday == 6)
                                            {{ $day[$weekday] = 'Saturday' }}
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                            <li>
                                <a style="color:rgb(255, 255, 255)" href="/lecture-timetable/{{ $_GET['faculty'] }}"
                                    class="btn btn-info btn-sm">Preview</a>
                            </li>

                        </ul>
                        <div class="tab-content" id="myTabContent">
                            @foreach ($weekdays as $weekday)
                                <div class="tab-pane fade @if ($weekday == 1) show active @endif"
                                    id="day{{ $weekday }}" role="tabpanel"
                                    aria-labelledby="day{{ $weekday }}-tab">
                                    <div class="">
                                        <div class="row">
                                            <div class="col-md-12 card-body">
                                                <!-- [ Data table ] start -->
                                                <div class="table-responsive">
                                                    <table id="export-table"
                                                        class="display table nowrap table-striped table-hover"
                                                        style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>{{ 'Course' }}</th>
                                                                <th>{{ 'Hall' }}</th>
                                                                {{-- <th>{{ 'Faculty' }}</th> --}}
                                                                <th>{{ 'Date' }}</th>
                                                                <th>{{ 'From' }}</th>
                                                                <th>{{ 'To' }}</th>
                                                                <th>{{ 'Lecturers' }}</th>
                                                                <th>{{ 'Comment' }}</th>
                                                                <th>{{ 'Status' }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $sn = 1;
                                                            @endphp
                                                            @foreach ($data->where('day_no', $weekday)->where('faculty', $_GET['faculty']) as $row)
                                                                @php

                                                                    $staffs = DB::table('course_allocation')
                                                                        ->where(['course' => $row->course])
                                                                        ->select('name')
                                                                        ->orderBy('type', 'ASC')
                                                                        ->get();
                                                                    $lecturer = '';
                                                                    foreach ($staffs as $staff) {
                                                                        $lecturer .=
                                                                            abbreviateMiddleName($staff->name) . ' | ';
                                                                    }
                                                                @endphp
                                                                <tr>
                                                                    <td>{{ $sn++ }}</td>
                                                                    <td>{{ $row->course }}</td>
                                                                    <td>{{ $row->hall }}</td>
                                                                    {{-- <td>{{ $row->faculty }}</td> --}}
                                                                    <td>{{ $row->day }}</td>
                                                                    <td>{{ date('h:i A', strtotime($row->start)) }}
                                                                    </td>
                                                                    <td>{{ date('h:i A', strtotime($row->end)) }}
                                                                    </td>
                                                                    <td>{{ $lecturer }}</td>
                                                                    <td>{{ $row->comment }}</td>
                                                                    <td>
                                                                        <button type="button"
                                                                            class="btn btn-icon btn-danger btn-sm deleteAction"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#delete{{ $row->id }}">
                                                                            <i class="fas fa-trash-alt"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>

                                                                <!-- Show modal content -->
                                                                <div id="delete{{ $row->id }}" class="modal fade"
                                                                    tabindex="-1" role="dialog"
                                                                    aria-labelledby="myModalLabel" aria-hidden="true">
                                                                    <div class="modal-dialog modal-sm" role="document">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="myModalLabel">
                                                                                    Warning...</h5>
                                                                                <button type="button" class="btn-close"
                                                                                    data-bs-dismiss="modal"
                                                                                    aria-label="Close"><span
                                                                                        aria-hidden="true">&times;</span></button>
                                                                            </div>
                                                                            <div class="card text-center">
                                                                                <div class="card-body">
                                                                                    <h4>Are You Sure</h4>
                                                                                </div>
                                                                                <form class="form-group"
                                                                                    action="delete {{ $page }}"
                                                                                    method="POST"
                                                                                    enctype="multipart/form-data">
                                                                                    <div class="card-body">
                                                                                        <!-- Details View Start -->
                                                                                        @csrf
                                                                                        <input type="hidden"
                                                                                            name="id"
                                                                                            value="{{ $row->id }}">
                                                                                        <!-- Details View End -->
                                                                                        <button type="button"
                                                                                            class="btn btn-info"
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
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endisset
            </div>
            <!-- [ Card ] end -->
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->
<!-- Show modal content -->
@isset($_GET['faculty'])
    @php
        if (isset($_GET['level'])) {
            $courses = $courses->where('faculty', $_GET['faculty'])->where('level', $_GET['level']);
        } else {
            $courses = $courses->where('faculty', $_GET['faculty']);
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
                                        @foreach ($courses->where('faculty', $_GET['faculty']) as $subject)
                                            <option value="{{ $subject->code }}">{{ $subject->code }} -
                                                {{ $subject->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- <div class="form-group col-md-12">
                                    <label for="lecturer">Lecturer <span>*</span></label>
                                    <input type="text" class="form-control must" value="{{ old('lecturer') }}"
                                        name="lecturer">
                                </div> --}}
                                <div class="form-group col-md-12">
                                    <label for="hall">Hall<span>*</span></label>
                                    <select class="form-control select2 must" name="hall" id="hall" required>
                                        <option value="{{ old('hall') }}">Select Option</option>
                                        @foreach ($hall->where('faculty', $_GET['faculty']) as $room)
                                            <option value="{{ $room->hall }}">{{ $room->hall }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="day_no">Day<span>*</span></label>
                                    <select name="day_no" class="form-control" id="day_no" required>
                                        <option value="">Select Day</option>
                                        <option value="1">Monday</option>
                                        <option value="2">Tuesday</option>
                                        <option value="3">Wednesday</option>
                                        <option value="4">Thursday</option>
                                        <option value="5">Friday</option>
                                        <option value="6">Saturday</option>
                                    </select>
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
                                <div class="form-group col-md-12">
                                    <label for="semester">Semester<span>*</span></label>
                                    <select name="semester" class="form-control" id="semester" required>
                                        <option value="">Select Semester</option>
                                        <option value="FIRST">FIRST</option>
                                        <option value="SECOND">SECOND</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    @foreach ($faculty as $fac)
                                        <input type="checkbox" name="fac[]" id="{{ $fac -> code }}" value="{{ $fac -> code }}" checked>
                                        <label for="{{ $fac -> code }}">{{ $fac -> title }}</label>
                                        <br>
                                    @endforeach
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="comment">Comment</label>
                                    <textarea name="comment" class="form-control" cols="30" rows="5"></textarea>
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
