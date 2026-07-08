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
                        <a href="#" class="btn btn-primary createAction" data-bs-toggle="modal"
                            data-bs-target="#create"><i class="fas fa-plus"></i> {{ 'Add New' }}</a>

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
                                        {{-- <th>{{ 'Code' }}</th> --}}
                                        <th>{{ 'Number' }}</th>
                                        <th>{{ 'Title' }}</th>
                                        <th>{{ 'Faculty' }}</th>
                                        <th>{{ 'Department' }}</th>
                                        <th>{{ 'Duration' }}</th>
                                        <th>{{ 'Degree Awarded' }}</th>
                                        <th>{{ 'Courses Status' }}</th>
                                        <th>{{ 'Action' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            {{-- <td>{{ $row->code }}</td> --}}
                                            <td>{{ sprintf('%02d', $row->no) }}</td>
                                            <td>{{ $row->title }}
                                                <br>
                                                <i>{{ $row->award_title ?? 'No Award Title...' }}</i>
                                            </td>
                                            <td>{{ DB::table('faculty')->where('code', $row->faculty)->value('title') }}
                                            </td>
                                            {{-- <td>{{ $row -> department }}</td> --}}
                                            <td>{{ DB::table('department')->where('code', $row->department)->value('title') }}
                                            </td>
                                            <td>{{ $row->duration }}</td>
                                            <td>{{ $row->award }}</td>
                                            <td>
                                                @if ($row->courses == '1')
                                                    {{ 'ACTIVE' }}
                                                @else
                                                    {{ 'INACTIVE' }}
                                                @endif
                                            </td>
                                            <td>
                                                <a href="/program-courses/{{ $row -> id }}" class="btn btn-icon btn-secondary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-icon btn-primary btn-sm updateAction"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#updateStudent{{ $row->id }}">
                                                    <i class="far fa-edit"></i>
                                                </button>
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
                                                                <div class="form-group">
                                                                    <label
                                                                        for="faculty{{ $row->id + 3 }}">Faculty</label>
                                                                    <select class="form-control faculty"
                                                                        id="faculty{{ $row->id + 3 }}"
                                                                        name="faculty" lang="{{ $row->id + 3 }}"
                                                                        required>
                                                                        <option value="{{ $row->faculty }}">Select
                                                                            Option</option>
                                                                        @foreach ($faculty as $roww)
                                                                            <option value="{{ $roww->code }}">
                                                                                {{ $roww->title }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label
                                                                        for="department{{ $row->id + 3 }}">Department</label>
                                                                    <select class="form-control department"
                                                                        id="department{{ $row->id + 3 }}"
                                                                        lang="{{ $row->id + 3 }}" name="department"
                                                                        required>
                                                                        <option value="{{ $row->department }}">Select
                                                                            Faculty First</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="code">Code</label>
                                                                    <input type="text" name="code" id="code"
                                                                        class="form-control"
                                                                        value="{{ $row->code }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="no">Number</label>
                                                                    <input type="number" name="no" id="no"
                                                                        class="form-control"
                                                                        value="{{ $row->no }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="title">Title</label>
                                                                    <input type="text" name="title" id="title"
                                                                        class="form-control"
                                                                        value="{{ $row->title }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="duration">Program Duration</label>
                                                                    <input type="number" name="duration" id="duration"
                                                                        value="{{ $row->duration }}"
                                                                        class="form-control" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="de">DE Student ID, Start
                                                                        at</label>
                                                                    <input type="number" name="de"
                                                                        id="de" value="{{ $row->de }}"
                                                                        class="form-control" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="award">Degree Awarded e.g
                                                                        B.Eng</label>
                                                                    <input type="text" name="award"
                                                                        id="award" value="{{ $row->award }}"
                                                                        class="form-control" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="award_title">Degree Awarded Title</label>
                                                                    <input type="text" name="award_title"
                                                                        id="award_title" value="{{ $row->award_title }}"
                                                                        class="form-control" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="courses">Courses Status</label>
                                                                    <select class="form-control" id="courses"
                                                                        name="courses" required>
                                                                        <option value="{{ $row->courses }}">Select
                                                                            Option</option>
                                                                        <option value="1">Active</option>
                                                                        <option value="0">Inactive</option>
                                                                    </select>
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
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->

<!-- Show modal content -->

<div id="import" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Upload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <div class="card-body">
                    <a href="#"><i class="fas fa-download"></i> Download Template</a>
                </div>

                <form class="form-group" action="upload {{ $page }}" method="POST"
                    enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="file"></label>
                            <input type="file" name="file" id="file" accept=".xlsx, .xls"
                                class="form-control">
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
                        <div class="form-group">
                            <label for="faculty2">Faculty</label>
                            <select class="form-control faculty" id="faculty2" name="faculty" lang="2"
                                required>
                                <option value="">Select Option</option>
                                @foreach ($faculty as $row)
                                    <option value="{{ $row->code }}">{{ $row -> code }}: {{ $row->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="department2">Department</label>
                            <select class="form-control department" id="department2" name="department"
                                lang="2" required>
                                <option value="">Select Faculty First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="code">Code</label>
                            <input type="text" name="code" id="code" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="no">Number</label>
                            <input type="number" name="no" id="no" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" name="title" id="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="duration">Program Duration</label>
                            <input type="number" name="duration" id="duration" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="de">DE Student ID, Start at</label>
                            <input type="number" name="de" id="de" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="award">Degree Awarded e.g B.Eng</label>
                            <input type="text" name="award" id="award" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="award_title">Degree Awarded Title</label>
                            <input type="text" name="award_title" id="award_title" class="form-control" required>
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
