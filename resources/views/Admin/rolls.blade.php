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
                        <h5>
                            @isset($_GET['username'])
                                {{ $_GET['username'] }}
                            @endisset
                            {{ strtoupper($page) }}</h5>
                    </div>
                    <div class="card-block">

                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createStudent"><i class="fas fa-plus"></i> {{ ('Add New') }}</a>
                        <form class="needs-validation" novalidate method="GET" action="#">
                            @csrf
                            <div class="row gx-2">
                                <div class="form-group col-md-2">
                                    <label for="username">SP Number <span>*</span></label>
                                    <select class="form-control" lang="f" name="username" id="username">
                                        <option value="">Select Option</option>
                                        @foreach ($sp as $sp)
                                            <option value="{{ $sp -> username }}">{{ $sp -> username }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
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
                                        <th>{{ ('Navigation') }}</th>
                                        <th>{{ ('Page') }}</th>
                                        <th>{{ ('Faculty') }}</th>
                                        <th>{{ ('Actions') }}</th>
                                        <th>{{ ('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                    <tr>
                                        <td>{{ $sn++ }}</td>
                                        <td>{{ $row -> main }}</td>
                                        <td>{{ $row -> page }}</td>
                                        <td>
                                            @if ($row->faculty == 'all')
                                                All
                                            @else
                                                {{ DB::table('faculty')->where('code', $row->faculty)->value('title') }}
                                            @endif
                                        </td>
                                        <td>{{ $row -> action }}</td>
                                        <td>
                                            <a href="#" class="btn btn-icon btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#updateStudent{{ $row->id }}">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-icon btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#delete{{ $row->id }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Show modal content -->
                                    <div id="delete{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-sm" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="card text-center">
                                                    <div class="card-body">
                                                        <h4>Are You Sure</h4>
                                                    </div>
                                                    <form class="form-group" action="delete {{ $page }}" method="POST" enctype="multipart/form-data">
                                                        <div class="card-body">
                                                            <!-- Details View Start -->
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                                            <!-- Details View End -->
                                                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">No</button>
                                                        <button type="submit" class="btn btn-danger">Yes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="updateStudent{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="myModalLabel">Update</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="card">
                                                    <form class="form-group" action="update {{ $page }}" method="POST" enctype="multipart/form-data">
                                                        <div class="card-body">
                                                            <!-- Details View Start -->
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $row->id }}">
                                                            <div class="form-group">
                                                                <label for="action">Action</label>
                                                                <input type="text" name="action" value="{{ $row -> action }}" id="action" class="form-control" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="faculty1">Faculty</label>
                                                                <select class="form-control faculty" id="faculty1" name="faculty" lang="1" required>
                                                                    <option value="{{ $row -> faculty }}">Select Option</option>
                                                                    <option value="all">All</option>
                                                                    @foreach ($faculty as $rows)
                                                                        <option value="{{ $rows -> code }}">{{ $rows -> title }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="department1">Department</label>
                                                                <select class="form-control department" id="department1" name="department" lang="1">
                                                                    <option value="{{ $row -> department }}">Select Option</option>
                                                                    <option value="all">All</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="program1">Program</label>
                                                                <select class="form-control" id="program1" name="program" lang="1">
                                                                    <option value="{{ $row -> program }}">Select Option</option>
                                                                    <option value="all">All</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="level">Level</label>
                                                                <select class="form-control" id="level" name="level" required>
                                                                    <option value="{{ $row -> level }}">Select Option</option>
                                                                    <option value="all">All</option>
                                                                    @for ($i = 1; $i <= 7; $i++)
                                                                        <option value="{{ $i*100 }}">{{ $i*100 }}</option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                        </div>

                                                        </div>
                                                        <button type="submit" class="btn btn-success">Update</button>
                                                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
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
<div id="importStudent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                        <input type="hidden" name="upload_type" value="new">
                        <div class="form-group">
                            <label for="file"></label>
                            <input type="file" name="file" id="file" accept=".xlsx, .xls" class="form-control">
                        </div>
                        <!-- Details View End -->
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Upload</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Show modal content -->
<div id="createStudent" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Create</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group" action="create {{ $page }}" method="POST" enctype="multipart/form-data">
                    <div class="card-body" id="newRow">
                        <!-- Details View Start -->
                        @csrf
                        <div class="row">
                            <div class="form-group">
                                <label for="username">SP NO.</label>
                                <input type="text" name="username" id="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="faculty2">Faculty</label>
                                <select class="form-control faculty" id="faculty2" name="faculty" lang="2" required>
                                    <option value="all">Select Option</option>
                                    @foreach ($faculty as $row)
                                        <option value="{{ $row -> code }}">{{ $row -> title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="department2">Department</label>
                                <select class="form-control department" id="department2" name="department" lang="2">
                                    <option value="all">Select Faculty First</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="program1">Program</label>
                                <select class="form-control" id="program2" name="program" lang="2">
                                    <option value="all">Select Department First</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="level">Level</label>
                                <select class="form-control" id="level" name="level" required>
                                    <option value="all">Select Option</option>
                                    @for ($i = 1; $i <= 7; $i++)
                                        <option value="{{ $i*100 }}">{{ $i*100 }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            @foreach (DB::table('pages')->groupBy('main','main_order')->orderBy('main_order', 'ASC')->get('main') as $group)
                            <fieldset class="row gx-2 scheduler-border">
                                <legend>{{ $group -> main }}</legend>
                                @foreach (DB::table('pages')->where(['main' => $group -> main])->orderBy('sub_order', 'ASC')->get() as $pages)
                                <div class="form-group">
                                    <input type="checkbox" name="ids[]" id="page{{ $pages -> id }}" value="{{ $pages -> id }}" class="">
                                    <label for="page{{ $pages -> id }}">{{ $pages -> page }} ({{ $pages -> action }})</label>
                                    <input type="text" name="action{{ $pages -> id }}" value="{{ $pages -> action }}" id="page{{ $pages -> action }}" class="form-control">
                                </div>
                                @endforeach
                            </fieldset>
                            @endforeach
                        </div>
                        <!-- Details View End -->
                    </div>
                    <button type="button" class="btn btn-info" id="addRow">Add Row</button>
                    <button type="button" class="btn btn-danger" id="deleteRow">Delete Last Row</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Create</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
        let val = 1;
        $(document).on('click', '#addRow', function () {
            var html = '';
            val++;
            //alert(day+' '+faculty);
            html += '<hr/>';
            html += '<div class="row" id="inp'+val+'"><div class="form-group col-md-4"><label for="page">Page</label><input type="text" name="page[]" id="page" class="form-control" required></div><div class="form-group col-md-4"><label for="link">Link</label><input type="text" name="link[]" id="link" class="form-control" required></div><div class="form-group col-md-4"><label for="sub_order">Order</label><input type="number" name="sub_order[]" id="sub_order" class="form-control" value="'+val+'" required></div></div>';

            $('#newRow').append(html);

            // Time Picker
            $('.time').bootstrapMaterialDatePicker({
                date: false,
                shortTime: true,
                format: 'HH:mm'
            });
        });

        // remove Field
        $(document).on('click', '#deleteRow', function () {
            if(val > 0){
                $('#inp'+val).remove();
                --val;
            }

        });
</script>
