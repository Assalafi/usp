@php
    // $accreditated = DB::table('students')
    // ->select('vflag')
    // ->where(['vflag' => '1'])
    // ->whereRaw('LOWER(state_origin) = ?', ['borno'])
    // ->count();
    use App\Models\Student;
    // $accreditated = Student::where(['school_fee' => '1'])
    //     ->select('school_fee')
    //     ->count();

    $accreditated = DB::table('students')
        ->select('vflag')
        ->whereRaw('LOWER(state_origin) = ?', ['borno'])
        ->count();
    $cast = DB::table('election_votes')
        ->select('username')
        ->where('category', $category)
        ->distinct('username')
        ->count();
@endphp
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ strtoupper($page) }} | Accreditated: {{ number_format($accreditated) }} | Vote Cast:
                            {{ number_format($cast) }} | Yet to vote: {{ number_format($accreditated - $cast) }}</h5>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="card-block">
                                <a href="#" class="btn btn-primary createAction" data-bs-toggle="modal"
                                    data-bs-target="#create"><i class="fas fa-plus"></i> {{ 'Add New' }}</a>
                                {{-- Select acc_type Staff or Student, upon select submit the form, and retain the selected value in the form --}}
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="card-block">
                                <form method="GET" action="#">
                                    <select name="acc_type" id="acc_type" class="form-control"
                                        onchange="this.form.submit()">
                                        <option value="Hide"
                                            {{ old('acc_type', request('acc_type')) == 'Hide' ? 'selected' : '' }}>Hide
                                        </option>
                                        <option value="Staff"
                                            {{ old('acc_type', request('acc_type')) == 'Staff' ? 'selected' : '' }}>
                                            Staff</option>
                                        <option value="Student"
                                            {{ old('acc_type', request('acc_type')) == 'Student' ? 'selected' : '' }}>
                                            Student</option>
                                        <option value="Alumni"
                                            {{ old('acc_type', request('acc_type')) == 'Alumni' ? 'selected' : '' }}>
                                            Alumni</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                        {{-- <button href="#" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#import"><i class="fas fa-upload"></i> {{ ('Import') }}</button> --}}
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-block">
                        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                            @php
                                $active = 1;
                            @endphp
                            @foreach ($poss->where('category', 'General') as $pos)
                                <li class="nav-item">
                                    <a class="nav-link @if ($active == 1) active @endif"
                                        id="pills-{{ $pos->id }}-tab" data-bs-toggle="pill"
                                        href="#pills-{{ $pos->id }}" role="tab"
                                        aria-controls="pills-{{ $pos->id }}"
                                        aria-selected="true">{{ $pos->position }}</a>
                                </li>
                                @php
                                    $active = 2;
                                @endphp
                            @endforeach

                        </ul>
                        <div class="tab-content" id="pills-tabContent">
                            @php
                                $active = 1;
                            @endphp
                            @foreach ($poss->where('category', 'General') as $pos)
                                <div class="tab-pane fade @if ($active == 1) show active @endif"
                                    id="pills-{{ $pos->id }}" role="tabpanel"
                                    aria-labelledby="pills-{{ $pos->id }}-tab">
                                    <!-- [ Data table ] start -->
                                    <div class="table-responsive">
                                        <table id="election-table{{ $pos->id }}"
                                            class="display table nowrap table-striped table-hover" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Photo</th>
                                                    <th>{{ 'Candidate' }}</th>
                                                    <th>{{ 'Name' }}</th>
                                                    <th>{{ 'Gender' }}</th>
                                                    {{-- <th>{{ 'Faculty' }}</th> --}}
                                                    <th>{{ 'Program' }}</th>
                                                    <th>{{ 'Level' }}</th>
                                                    <th>{{ 'Vote' }}</th>
                                                    <th>{{ 'Action' }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $sn = 1;
                                                @endphp
                                                @foreach ($data->where('category', 'General')->where('position', $pos->position)->where('acc_type', request('acc_type')) as $row)
                                                    <tr>
                                                        <td>{{ $sn++ }}</td>
                                                        <td><a href="{{ asset('storage/picture/' . $row->picture) }}"><img
                                                                    src="{{ asset('storage/picture/' . $row->picture) }}"
                                                                    class="card-img-top img-radius img-fluid"
                                                                    style="width: 50px; height: 50px"
                                                                    alt="{{ $row->picture }}"></a></td>
                                                        <td>{{ $row->candidate }}</td>
                                                        <td>{{ $row->name }}</td>
                                                        <td>{{ $row->gender }}</td>
                                                        {{-- <td>{{ $row->faculty }}</td> --}}
                                                        <td>{{ $row->program_title }}</td>
                                                        <td>{{ $row->level }}</td>
                                                        <td>{{ $row->vote }}</td>
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
                                                        tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog modal-sm" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="myModalLabel">Warning...
                                                                    </h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal" aria-label="Close"><span
                                                                            aria-hidden="true">&times;</span></button>
                                                                </div>
                                                                <div class="card text-center">
                                                                    <div class="card-body">
                                                                        <h4>Are You Sure</h4>
                                                                    </div>
                                                                    <form class="form-group"
                                                                        action="delete {{ $page }}"
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
                                @php
                                    $active = 2;
                                @endphp
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->

<!-- Show modal content -->
<div id="create" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Create</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group" action="create election candidates" method="POST"
                    enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <input type="hidden" name="category" value="{{ $category }}">
                        <div class="form-group">
                            <label for="candidate">Candidate ID NO.</label>
                            <input type="text" name="candidate" id="candidate" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="position">Position</label>
                            <select class="form-control" id="position" name="position" required>
                                <option value="">Select Option</option>
                                @foreach ($poss->where('category', $category) as $position)
                                    <option value="{{ $position->id }}">{{ $position->position }}</option>
                                @endforeach
                            </select>
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
