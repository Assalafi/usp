@php
    use Illuminate\Support\Facades\DB;
    use App\Models\Student;
    $data = Student::where('id', $id)->get();
    $sessions = DB::table('session')->orderBy('title', 'DESC')->get();

    $hall = 'Nil';
    $block = 'Nil';
    $room = 'Nil';
    $bed = 'Nil';
@endphp
@foreach ($data as $row)
    @php

        foreach (
            DB::table('hostel')->where('occupant', $row->username)->select('hall', 'block', 'room', 'bed')->get()
            as $hostel
        ) {
            $hall = $hostel->hall;
            $block = $hostel->block;
            $room = $hostel->room;
            $bed = $hostel->bed;
        }
        $addCourses = DB::table('program_course_registration')
            ->where('program', $row->program)
            ->orderBy('code', 'ASC')
            ->get();
    @endphp
    <!-- Start Content-->
    <div class="main-body">
        <div class="page-wrapper">
            <!-- [ Main Content ] start -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card user-card user-card-1">
                        <div class="card-body pb-0">
                            <div class="media user-about-block align-items-center mt-0 mb-3">
                                <div class="position-relative d-inline-block">
                                    <img src="{{ asset('storage/picture/' . $row->picture) }}"
                                        class="img-radius img-fluid wid-80" alt="{{ __('field_photo') }}">
                                    <div class="certificated-badge">
                                        <i class="fas fa-certificate text-primary bg-icon"></i>
                                        <i class="fas fa-check front-icon text-white"></i>
                                    </div>
                                </div>
                                <div class="media-body ms-3">
                                    <h6 class="mb-1">
                                        {{ $row->first_name . ' ' . $row->last_name . ' ' . $row->other_name }}</h6>
                                    <p class="mb-0 text-muted">{{ $row->username }}</p>
                                </div>
                            </div>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <span class="f-w-500"><i class="far fa-envelope m-r-10"></i>{{ 'Email' }} :
                                </span>
                                <span class="float-end">{{ $row->contact_email }}</span>
                            </li>
                            <li class="list-group-item">
                                <span class="f-w-500"><i class="fas fa-phone-alt m-r-10"></i>{{ 'Phone' }} :
                                </span>
                                <span class="float-end">{{ $row->contact_phone }}</span>
                            </li>
                            <li class="list-group-item">
                                <span class="f-w-500"><i class="fas fa-graduation-cap m-r-10"></i>{{ 'Department' }} :
                                </span>
                                <span title="{{ 'ID Format' }}: {{ $row->id_format }}"
                                    class="float-end">{{ DB::table('department')->where('code', $row->department)->value('title') }}</span>
                            </li>
                            <li class="list-group-item">
                                <span class="f-w-500"><i class="fas fa-graduation-cap m-r-10"></i>{{ 'Program' }} :
                                </span>
                                <span
                                    class="float-end">{{ DB::table('program')->where('code', $row->program)->value('title') }}</span>
                            </li>
                            <li class="list-group-item border-bottom-0">
                                <span class="f-w-500"><i class="far fa-question-circle m-r-10"></i>{{ 'Jamb No' }}
                                    : </span>
                                <span class="float-end">{{ $row->jamb_no }}</span>
                            </li>
                        </ul>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col">
                                    <h6 class="mb-1"></h6>
                                    <p class="mb-0"></p>
                                </div>
                                <div class="col border-start">
                                    <h6 class="mb-1">
                                        2
                                    </h6>
                                    <p class="mb-0"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-block">
                            <div class="">
                                <div class="row">
                                    <div class="col-md-6">
                                        <fieldset class="row gx-2 scheduler-border">
                                            <p><mark class="text-primary">{{ 'Father Name' }}:</mark>
                                                {{ $row->father_name }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Mother' }}:</mark>
                                                {{ $row->mother_name }}</p>
                                            <hr />

                                            <p><mark class="text-primary">{{ 'Gender' }}:</mark>
                                                @if ($row->gender == 'M')
                                                    {{ 'Male' }}
                                                @elseif($row->gender == 'F')
                                                    {{ 'Female' }}
                                                @endif
                                            </p>
                                            <hr />

                                            <p><mark class="text-primary">{{ 'Date of Birth' }}:</mark>
                                                {{ $row->date_of_birth }}
                                            </p>
                                            <hr />

                                            <p><mark class="text-primary">{{ 'Emergency Phone' }}:</mark>
                                                {{ $row->contact_phone }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Religion' }}:</mark>
                                                {{ $row->religion }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Nationality' }}:</mark>
                                                {{ $row->nationality }}</p>
                                            <hr />

                                            <p><mark class="text-primary">{{ 'Marital Status' }}:</mark>
                                                {{ $row->marital_status }}
                                            </p>
                                            <hr />

                                            <p><mark class="text-primary">{{ 'Blood Group' }}:</mark>
                                                {{ $row->blood_group }}
                                            </p>
                                            <hr />

                                            <p><mark class="text-primary">{{ 'NIN' }}:</mark>
                                                {{ $row->nin }}</p>
                                            <hr />
                                        </fieldset>
                                    </div>
                                    <div class="col-md-6">
                                        <fieldset class="row gx-2 scheduler-border">
                                            <legend>{{ 'Present' }} {{ 'Address' }}</legend>
                                            <p><mark class="text-primary">{{ 'Address' }}:</mark>
                                                {{ $row->home_address }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Phone' }}:</mark>
                                                {{ $row->home_phone }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Email' }}:</mark>
                                                {{ $row->home_email }}</p>
                                        </fieldset>

                                        <fieldset class="row gx-2 scheduler-border">
                                            <legend>{{ 'Permanent' }} {{ 'Address' }}</legend>
                                            <p><mark class="text-primary">{{ 'Address' }}:</mark>
                                                {{ $row->contact_address }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Phone' }}:</mark>
                                                {{ $row->contact_phone }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Email' }}:</mark>
                                                {{ $row->contact_email }}</p>
                                        </fieldset>

                                        <fieldset class="row gx-2 scheduler-border">
                                            <legend>Hostel Details</legend>
                                            <p><mark class="text-primary">{{ 'Hall' }}:</mark>
                                                {{ $hall }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Block' }}:</mark>
                                                {{ $block }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Room' }}:</mark>
                                                {{ $room }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Bed' }}:</mark>
                                                {{ $bed }}</p>
                                            <hr />
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="pills-transcript-tab" data-bs-toggle="pill"
                                        href="#pills-transcript" role="tab" aria-controls="pills-transcript"
                                        aria-selected="true">{{ 'Transcript' }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="pills-educational-tab" data-bs-toggle="pill"
                                        href="#pills-educational" role="tab" aria-controls="pills-educational"
                                        aria-selected="false">{{ 'Educational Info' }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="pills-courses-tab" data-bs-toggle="pill"
                                        href="#pills-courses" role="tab" aria-controls="pills-courses"
                                        aria-selected="false">{{ 'Registered Courses' }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="pills-fees-tab" data-bs-toggle="pill" href="#pills-fees"
                                        role="tab" aria-controls="pills-fees"
                                        aria-selected="false">{{ 'Fees' }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="pills-notes-tab" data-bs-toggle="pill"
                                        href="#pills-notes" role="tab" aria-controls="pills-notes"
                                        aria-selected="false">{{ 'Note' }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="pills-leave-tab" data-bs-toggle="pill"
                                        href="#pills-leave" role="tab" aria-controls="pills-leave"
                                        aria-selected="false">{{ 'Student Leave' }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="pills-documents-tab" data-bs-toggle="pill"
                                        href="#pills-documents" role="tab" aria-controls="pills-documents"
                                        aria-selected="false">{{ 'Documents' }}</a>
                                </li>
                            </ul>
                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="pills-transcript" role="tabpanel"
                                    aria-labelledby="pills-transcript-tab">
                                    <div class="clearfix"></div>
                                    <div class="table-responsive">
                                        <div class="card-header">
                                            <h5>Semester</h5>
                                        </div>
                                        <!-- [ Data table ] start -->
                                        <div class="table-responsive">
                                            <table class="display table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>{{ 'Course Code' }}</th>
                                                        <th>{{ 'Course Title' }}</th>
                                                        <th>{{ 'Unit' }}</th>
                                                        <th>{{ 'Score' }}</th>
                                                        <th>{{ 'Grade' }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2">{{ __('Total') }}</th>
                                                        <th>{{ '0' }}</th>
                                                        <th>{{ number_format((float) 3.65, 2, '.', '') }}</th>
                                                        <th></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        <!-- [ Data table ] end -->
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pills-educational" role="tabpanel"
                                    aria-labelledby="pills-educational-tab">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <fieldset class="row gx-2 scheduler-border">
                                                <p><mark class="text-primary">{{ 'Batch' }}:</mark>
                                                    {{ 'Batch' }}</p>
                                                <hr />
                                                <p><mark class="text-primary">{{ 'Program' }}:</mark>
                                                    {{ 'Program' }}</p>
                                                <hr />
                                                <p><mark class="text-primary">{{ 'Session' }}:</mark>
                                                    {{ 'Session' }}</p>
                                                <hr />
                                                <p><mark class="text-primary">{{ 'Semester' }}:</mark>
                                                    {{ 'Semester' }}</p>
                                                <hr />
                                                <p><mark class="text-primary">{{ 'Section' }}:</mark>
                                                    {{ 'Section' }}</p>
                                                <hr />

                                                <p><mark class="text-primary">{{ 'Status' }}:</mark>
                                                    <span class="badge badge-primary">{{ 'Title' }}</span>
                                                </p>
                                                <hr />
                                            </fieldset>
                                        </div>
                                        <div class="col-md-4">
                                            <fieldset class="row gx-2 scheduler-border">
                                                <legend>{{ __('School Information') }}</legend>
                                                <p><mark class="text-primary">{{ 'School Name' }}:</mark>
                                                    {{ $row->school_name }}</p>
                                                <hr />
                                                <p><mark class="text-primary">{{ 'field_exam_id' }}:</mark>
                                                    {{ $row->school_exam_id }}</p>
                                                <hr />
                                                <p><mark class="text-primary">{{ 'Graduation Year' }}:</mark>
                                                    {{ 'Graduation Year' }}</p>
                                                <hr />
                                                <p><mark class="text-primary">{{ 'Graduation Point' }}:</mark>
                                                    {{ 'Graduation Point' }}</p>
                                                <hr />
                                            </fieldset>

                                            <fieldset class="row gx-2 scheduler-border">
                                                <legend>{{ 'College Information' }}</legend>
                                                <p><mark class="text-primary">{{ 'Collage Name' }}:</mark>
                                                    {{ 'Collage Name' }}</p>
                                                <hr />
                                                <p><mark class="text-primary">{{ 'Exam Id' }}:</mark>
                                                    {{ 'Exam Id' }}</p>
                                                <hr />
                                                <p><mark class="text-primary">{{ 'Graduation Year' }}:</mark>
                                                    {{ 'Graduation Year' }}</p>
                                                <hr />
                                                <p><mark class="text-primary">{{ 'Graduation Point' }}:</mark>
                                                    {{ 'Graduation Point' }}</p>
                                                <hr />
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pills-courses" role="tabpanel"
                                    aria-labelledby="pills-courses-tab">
                                    <div class="clearfix"></div>
                                    <div class="table-responsive">
                                        <div class="card-header">
                                            <h5>Registered Courses
                                                <button type="button" class="btn btn-info btn-sm createAction"
                                                    data-bs-toggle="modal" data-bs-target="#add">
                                                    Add
                                                </button>
                                            </h5>
                                        </div>
                                        <!-- [ Data table ] start -->
                                        <div class="table-responsive">
                                            <table class="display table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ 'Course Code' }}</th>
                                                        <th>{{ 'Unit' }}</th>
                                                        <th>{{ 'Level' }}</th>
                                                        <th>{{ 'Semester' }}</th>
                                                        <th>{{ 'Session' }}</th>
                                                        <th>{{ 'Status' }}</th>
                                                        <th>{{ 'Action' }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $sn = 1;
                                                        $rcourses = DB::table('student_course_registration')
                                                            ->where(['username' => $row->username])
                                                            ->orderBy('level', 'ASC')
                                                            ->orderBy('semester', 'ASC')
                                                            ->get();
                                                    @endphp
                                                    @foreach ($rcourses as $course)
                                                        <tr>
                                                            <td>{{ $sn++ }}</td>
                                                            <td>{{ $course->code }}</td>
                                                            <td>{{ $course->unit }}</td>
                                                            <td>{{ $course->level }}</td>
                                                            <td>{{ $course->semester }}</td>
                                                            <td>{{ $course->session }}</td>
                                                            <td>{{ $course->status }}</td>
                                                            <td>
                                                                <button type="button"
                                                                    class="btn btn-danger btn-sm deleteAction"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#drop{{ $course->id }}">
                                                                    Drop
                                                                </button>
                                                            </td>
                                                        </tr>

                                                        <div id="drop{{ $course->id }}" class="modal fade"
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
                                                                    <div class="card">
                                                                        <div class="card-body">
                                                                            ARE YOU SURE, YOU WANT TO DROP
                                                                            {{ $course->code }}?
                                                                        </div>
                                                                        <form class="form-group"
                                                                            action="/drop-student-course"
                                                                            method="POST"
                                                                            enctype="multipart/form-data">
                                                                            <div class="card-body">
                                                                                <!-- Details View Start -->
                                                                                @csrf
                                                                                <input type="hidden" name="id"
                                                                                    value="{{ $course->id }}">
                                                                                <!-- Details View End -->
                                                                                <button type="button"
                                                                                    class="btn btn-info"
                                                                                    data-bs-dismiss="modal">Close</button>
                                                                                <button type="submit"
                                                                                    class="btn btn-danger">Yes
                                                                                    Drop</button>
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
                                        <div id="add" class="modal fade" tabindex="-1" role="dialog"
                                            aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card">
                                                        <form class="form-group" action="/add-student-course"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="program"
                                                                    value="{{ $row->program }}">
                                                                <input type="hidden" name="username"
                                                                    value="{{ $row->username }}">
                                                                <div class="form-group">
                                                                    <label for="">Course Code (eg.
                                                                        TST100)</label>
                                                                    <select name="code" id=""
                                                                        class="form-control" required>
                                                                        <option value="">Select Option</option>
                                                                        @foreach ($addCourses as $add)
                                                                            <option value="{{ $add->code }}">
                                                                                {{ $add->code }}</option>
                                                                        @endforeach

                                                                    </select>
                                                                </div>
                                                                {{-- add session history from 2023/2024 to current session, generate here using php dont use $sessions --}}
                                                                <div class="form-group">
                                                                    <label for="">Session</label>
                                                                    <select name="session" id=""
                                                                        class="form-control" required>
                                                                        <option value="">Select Option</option>
                                                                        @foreach ($sessions as $session)
                                                                            <option value="{{ $session->title }}">
                                                                                {{ $session->title }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="form-group">
                                                                    <button type="button" class="btn btn-info"
                                                                        data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit"
                                                                        class="btn btn-danger">Add</button>
                                                                </div>
                                                                <!-- Details View End -->
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pills-fees" role="tabpanel"
                                    aria-labelledby="pills-fees-tab">
                                    <!-- [ Data table ] start -->
                                    <div class="table-responsive">
                                        <table id="basic-table" class="display table nowrap table-striped table-hover"
                                            style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>{{ 'Session' }}</th>
                                                    <th>{{ 'Semester' }}</th>
                                                    <th>{{ 'Fee Type' }}</th>
                                                    <th>{{ 'Fee' }}</th>
                                                    <th>{{ 'Fine Amount' }}</th>
                                                    <th>{{ 'Total Amount' }}</th>
                                                    <th>{{ 'Status' }}</th>
                                                    <th>{{ 'Pay Date' }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- [ Data table ] end -->
                                </div>
                                <div class="tab-pane fade" id="pills-notes" role="tabpanel"
                                    aria-labelledby="pills-notes-tab">
                                    <!-- [ Data table ] start -->
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>{{ 'Date' }}</th>
                                                    <th>{{ 'Title' }}</th>
                                                    <th>{{ 'Note' }}</th>
                                                    <th>{{ 'Attach' }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- [ Data table ] end -->
                                </div>
                                <div class="tab-pane fade" id="pills-leave" role="tabpanel"
                                    aria-labelledby="pills-leave-tab">
                                    <!-- [ Data table ] start -->
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>{{ 'Leave Date' }}</th>
                                                    <th>{{ 'Days' }}</th>
                                                    <th>{{ 'Apply date' }}</th>
                                                    <th>{{ 'Status' }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- [ Data table ] end -->
                                </div>
                                <div class="tab-pane fade" id="pills-documents" role="tabpanel"
                                    aria-labelledby="pills-documents-tab">
                                    <!-- [ Data table ] start -->
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>{{ 'Title' }}</th>
                                                    <th>{{ 'Document' }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td></td>
                                                    <td>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- [ Data table ] end -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <!-- End Content-->
@endforeach
