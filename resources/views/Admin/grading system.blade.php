<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <!-- [ Card ] start -->
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ strtoupper($page) }}
                            <a href="#" class="btn btn-primary createAction" data-bs-toggle="modal"
                                data-bs-target="#create"><i class="fas fa-plus"></i> {{ 'Add New' }}</a>
                        </h5>
                    </div>
                </div>
            </div>

            <div class="card-block">
                <form class="needs-validation" novalidate method="POST" action="/update {{ $page }}">
                    @csrf
                    <div class="row gx-2">
                        <div class="form-group col-md-3">
                            <label for="name">Grading <span>*</span></label>
                            <select class="form-control" name="name" id="name" required>
                                <option value="">Select Option</option>
                                @foreach ($grading as $roww)
                                    <option value="{{ $roww -> name }}">{{ $roww -> name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"> You must select Grading </div>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="facultyf">Faculty <span>*</span></label>
                            <select class="form-control faculty" lang="f" name="faculty" id="facultyf" required>
                                <option value="">Select Option</option>
                                @foreach ($faculty as $roww)
                                    <option value="{{ $roww -> code }}">{{ $roww -> title }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"> You must select Faculty </div>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="departmentf">Department</label>
                            <select class="form-control department" lang="f" id="departmentf" name="department">
                                <option value="">Select Faculty First</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="programf">Program</label>
                            <select class="form-control" id="programf" lang="f" name="program">
                                <option value="">Select Department First</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <button type="submit" class="btn btn-info btn-filter"><i class="fa fa-cloud-upload" aria-hidden="true"></i> {{ ('Apply') }}</button>
                        </div>
                    </div>
                </form>
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
                                        <th>{{ 'Ref' }}</th>
                                        <th>{{ 'Name' }}</th>
                                        <th>{{ 'From' }}</th>
                                        <th>{{ 'To' }}</th>
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
                                            <td>{{ $row->ref }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->from }}</td>
                                            <td>{{ $row->to }}</td>
                                            <td>
                                                <button type="button" class="btn btn-icon btn-info btn-sm deleteAction"
                                                    data-bs-toggle="modal" data-bs-target="#view{{ $sn }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                <button type="button"
                                                    class="btn btn-icon btn-danger btn-sm deleteAction"
                                                    data-bs-toggle="modal" data-bs-target="#delete{{ $sn }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <div id="delete{{ $sn }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"><span
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
                                                                <input type="hidden" name="ref"
                                                                    value="{{ $row->ref }}">
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
            <!-- [ Card ] end -->
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<div id="create" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Create</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form card-body row" action="create grading system" method="POST">
                    @csrf
                    <div class="form-group col-md-4">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="from">From</label>
                        <select name="from" id="from" class="form-control">
                            <option value="">Select Option</option>
                            @for ($date = 2001; $date <= 3100; $date++)
                                <option value="{{ $date }}">{{ $date }}</option>
                            @endfor
                        </select>

                    </div>
                    <div class="form-group col-md-4">
                        <label for="to">To</label>
                        <select name="to" id="to" class="form-control" required>
                            <option value="">Select Option</option>
                            <option value="current">To Date</option>
                            @for ($date = 2001; $date <= 3100; $date++)
                                <option value="{{ $date }}">{{ $date }}</option>
                            @endfor
                        </select>
                    </div>
                    <div id="repeat">
                        <div class="row" id="inputFormField">
                            <div class="form-group col-md-2">
                                <label for="min_score">Min Score</label>
                                <input type="number" name="min_score[]" id="min_score" class="form-control" step="0.01"
                                    required>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="max_score">Max Score</label>
                                <input type="number" name="max_score[]" id="max_score" class="form-control" step="0.01"
                                    required>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="grade">Grade</label>
                                <input type="text" name="grade[]" id="grade" class="form-control" required>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="remark">Remark</label>
                                <select name="remark[]" id="remark" class="form-control">
                                    <option value="">Select Option</option>
                                    <option value="PASSED">PASSED</option>
                                    <option value="FAILED">FAILED</option>
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="point">Point</label>
                                <input type="number" name="point[]" id="point" class="form-control" step="0.01" required>
                            </div>
                            <div class="form-group col-md-2">
                                <button id="removeField" type="button" class="btn btn-danger btn-filter"><i
                                        class="fas fa-trash-alt"></i> Remove</button>
                            </div>
                        </div>
                    </div>
                    <div id="newField" class="clearfix row"></div>
                    <div class="card-block text-center">
                        <button style="border-radius: 30px; width: 50px; height: 50px; font-size: 24px;"
                            id="addField" type="button" class="btn btn-info">+</button>
                    </div>
                    <div class="card-block text-center">
                        <button type="submit" id="submitButton" onclick="return submittt()"
                            class="btn btn-primary">Submit</button>
                    </div>
                    <div id="waiting" style="display: none" class="card-block text-center alert alert-info">
                        <h4>Please Wait...</h4>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@php
    $x = 1;
@endphp
@foreach ($data as $row)
@php
    $x++;
@endphp
<div id="view{{ $x }}" class="modal fade" tabindex="-1"
    role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title" id="myModalLabel">{{ $row -> name }} {{ $row -> from }} TO {{ $row -> to == 'current' ? 'DATE' : $row -> to }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card text-center">
                <div class="card-body">
                    <table
                        class="display table nowrap table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>MIN SCORE</th>
                                <th>MAX SCORE</th>
                                <th>GRADE</th>
                                <th>POINT</th>
                                <th>REMARK</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 1;
                            @endphp
                            @foreach (DB::table('grading_system')->where('ref', $row->ref)->orderBy('min_score', 'ASC')->get() as $item)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $item->min_score }}</td>
                                    <td>{{ $item->max_score }}</td>
                                    <td>{{ $item->grade }}</td>
                                    <td>{{ $item->point }}</td>
                                    <td>{{ $item->remark }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endforeach
<!-- End Content-->
<script type="text/javascript">
    //alert('Hii');
    var hall = '';
    var course = '';
    $(document).on('click', '#addField', function() {
        var html = '';
        var htmls = document.getElementById('repeat').innerHTML;
        var day_no = 'day';
        var day = 'day';
        var faculty = 'Faculty';
        html += '<hr/>';
        html +=
            '<form href="create" class="formD" method="POST">@csrf<div id="inputFormField" class="card-block">';
        html += '<div class="row">';
        html +=
            '<div class="form-group col-md-2"><label for="course">Course<span>*</span></label><select class="form-control select2 must" name="course" id="course" required><option value="">Select Option</option></select> <div class="invalid-feedback"> {{ __('required_field') }} {{ __('field_subject') }}</div></div>';
        html +=
            '<div class="form-group col-md-2"><label for="lecturer">Lecturer<span>*</span></label> <input class="form-control must" type="text" name="lecturer" id="lecturer"> <div class="invalid-feedback"> {{ __('required_field') }} {{ __('field_teacher') }} </div> </div>';
        html +=
            '<div class="form-group col-md-2"> <label for="hall">Hall<span>*</span></label> <select class="form-control select2 must" name="hall" id="hall" required> <option value="">Select Option</option></select> <div class="invalid-feedback"> {{ 'required_field' }} {{ __('field_room') }} {{ __('field_no') }} </div></div>';
        html +=
            '<div class="form-group col-md-2"> <label for="date">Date<span>*</span></label><input type="date" class="form-control must" name="date" id="date" required><div class="invalid-feedback"> </div></div>';
        html +=
            '<div class="form-group col-md-2"> <label for="start">Starting At<span>*</span></label><input type="time" class="form-control must" name="start" id="start" required><div class="invalid-feedback"> </div></div>';
        html +=
            '<div class="form-group col-md-2"> <label for="end">Ending At<span>*</span></label> <input type="time" class="form-control must" name="end" id="end" required> <div class="invalid-feedback"> {{ __('required_field') }} {{ __('field_time') }} {{ __('field_to') }} </div> </div>';
        html +=
            '<div class="form-group col-md-2"><button id="removeField" type="button" class="btn btn-danger btn-filter"><i class="fas fa-trash-alt"></i> Remove</button></div>';
        html += '</div></form>';

        $('#newField').append(htmls);

        // Time Picker
        $('.time').bootstrapMaterialDatePicker({
            date: false,
            shortTime: true,
            format: 'HH:mm'
        });
    });

    // remove Field
    $(document).on('click', '#removeField', function() {
        $(this).closest('#inputFormField').remove();

        // Time Picker
        $('.time').bootstrapMaterialDatePicker({
            date: false,
            shortTime: true,
            format: 'HH:mm'
        });
    });


    // Delete Routine
    function deleteRoutine(id) {

        let _url = '/delete {{ $page }}';
        let _token = $('input[name="_token"]').val();
        $.ajax({
            type: 'POST',
            url: _url,
            data: {
                id: id,
                _token: _token
            },
            success: function(data) {
                swal("Success", "Done!!!", "success");
            },
            error: function(error) {
                swal("Error", "Something went wrong", "error");
            }
        });

        $("#deleteRoutine-" + id).hide();
        $("#delete_routine-" + id).attr("checked", "checked");
    }

    function submittt() {
        //alert('Hii');
        document.getElementById('submitButton').style.display = 'none';
        document.getElementById('waiting').style.display = 'block';
    }
</script>
