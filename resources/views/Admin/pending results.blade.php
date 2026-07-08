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
                        <h5>{{ $code }} {{ $semester }} SEMESTER {{ $session }}</h5>
                    </div>

                </div>
            </div>

            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>RESULT</h5>
                    </div>
                    <div class="card-block">
                        <!-- [ Data table ] start -->
                        <div class="table-responsive">
                            <table class="display table nowrap table-striped table-hover dtt" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ 'ID Number' }}</th>
                                        <th>{{ 'CA' }}</th>
                                        <th>{{ 'Exam' }}</th>
                                        <th>{{ 'Total' }}</th>
                                        <th>{{ 'Grade' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $a = 0;
                                        $b = 0;
                                        $c = 0;
                                        $d = 0;
                                        $e = 0;
                                        $f = 0;
                                        $t = 0;
                                        $sn = 1;
                                        $action = 0;
                                        $approve = '';
                                    @endphp
                                    @foreach ($results as $row)
                                        @php
                                            $approve = $row->approve;
                                            $t++;
                                            if ($row->grade == 'A') {
                                                $a++;
                                            }
                                            if ($row->grade == 'B') {
                                                $b++;
                                            }
                                            if ($row->grade == 'C') {
                                                $c++;
                                            }
                                            if ($row->grade == 'D') {
                                                $d++;
                                            }
                                            if ($row->grade == 'E') {
                                                $e++;
                                            }
                                            if ($row->grade == 'F') {
                                                $f++;
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->username }}</td>
                                            <td>{{ $row->ca }}</td>
                                            <td>{{ $row->exam }}</td>
                                            <td>{{ $row->total }}</td>
                                            <td>{{ $row->grade }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <h4>A = {{ $a }} | B = {{ $b }} | C = {{ $c }} | D =
                                {{ $d }} | E = {{ $e }} | F = {{ $f }} | Total =
                                {{ $t }}</h4>

                        </div>
                        <!-- [ Data table ] end -->
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>List of Students Whose Results Hasn't Been Uploaded But Registered</h5>
                    </div>
                    <div class="card-block">
                        <!-- [ Data table ] start -->
                    <a href="#" class="btn btn-icon btn-primary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#commentAll">
                    <i class="fa fa-comment" aria-hidden="true"></i>
                </a>
                        <div class="table-responsive">
                            <table class="display table nowrap table-striped table-hover dtt" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ 'ID Number' }}</th>
                                        <th>{{ 'Comment' }}</th>
                                        <th>{{ 'Action' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                        $action = 0;
                                        $comment = 0;
                                    @endphp
                                    @foreach ($data as $row)
                                        @php
                                            if ($row->comment == 'No Comment') {
                                                $comment++;
                                                //$comment = 0;
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->username }}</td>
                                            <td>{{ $row->comment }}</td>
                                            <td>
                                                @if ($approve == 'system')
                                                    <a href="#" class="btn btn-icon btn-primary btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#updateStudent{{ $sn }}">
                                                        <i class="fa fa-comment" aria-hidden="true"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>

                                        <!-- Show modal content -->
                                        @if (strtoupper($lecturer) == strtoupper(session('username')))
                                            <div id="updateStudent{{ $sn }}" class="modal fade"
                                                tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                                                aria-hidden="true">
                                                <div class="modal-dialog modal-md" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="myModalLabel">Comment for
                                                                {{ $row->username }} result</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"><span
                                                                    aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <div class="card">
                                                            <form class="form-group" action="/comment-pending-result"
                                                                method="POST" enctype="multipart/form-data">
                                                                <div class="card-body">
                                                                    <!-- Details View Start -->
                                                                    @csrf
                                                                    <input type="hidden" name="id"
                                                                        value="{{ $row->id }}">
                                                                    <div class="form-group">
                                                                        <label for="mark">Comment</label>
                                                                        <textarea name="comment" id="comment" class="form-control" cols="30" rows="10"
                                                                            placeholder="Reason for Omission" required></textarea>
                                                                    </div>
                                                                    <!-- Details View End -->
                                                                    <button type="button" class="btn btn-info"
                                                                        data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit"
                                                                        class="btn btn-success">Submit</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- [ Data table ] end -->
                    </div>
                </div>
            </div>

            @php
                if ($approve == 'system' && strtoupper($lecturer) == strtoupper(session('username'))) {
                    $action = 1;
                } elseif ($approve == 'lecturer' && session('appointment') == 'HOD') {
                    $action = 1;
                } elseif ($approve == 'hod' && session('appointment') == 'DEAN') {
                    $action = 1;
                } elseif ($approve == 'dean' && session('unit') == 'COURSE SYSTEM') {
                    $action = 1;
                } elseif ($approve == 'cs' && session('appointment') == 'VC') {
                    $action = 1;
                } else {
                    $action = 0;
                }
            @endphp
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>SUBMIT RESULT</h5>
                    </div>
                    <div class="card-body">

                        @if ($action == 1)
                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                data-bs-target="#delete">
                                @if (DB::table('results')->where(['code' => $code, 'session' => $sessions])->value('approve') == 'cs')
                                    Approve
                                @else
                                    Submit
                                @endif
                            </button>
                            @if ($approve != 'system')
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#reject">
                                    Reject
                                </button>
                            @endif
                        @else
                            @php
                                if ($approve == 'system') {
                                    echo 'Lecturer';
                                } elseif ($approve == 'lecturer') {
                                    echo 'Department';
                                } elseif ($approve == 'hod') {
                                    echo 'Faculty';
                                } elseif ($approve == 'dean') {
                                    echo 'Course System';
                                } elseif ($approve == 'cs') {
                                    echo 'Senate';
                                }
                            @endphp
                        @endif

                    </div>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->


<div id="commentAll" class="modal fade"
tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
aria-hidden="true">
<div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="myModalLabel">Comment All</h5>
            <button type="button" class="btn-close"
                data-bs-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
        </div>
        <div class="card">
            <form class="form-group" action="/comment-pending-result"
                method="POST" enctype="multipart/form-data">
                <div class="card-body">
                    <!-- Details View Start -->
                    @csrf
                    <input type="hidden" name="ids"
                        value="{{ $pendingIds }}">
                    <div class="form-group">
                        <label for="mark">Comment</label>
                        <textarea name="comment" id="comment" class="form-control" cols="30" rows="10"
                            placeholder="Reason for Omission" required></textarea>
                    </div>
                    <!-- Details View End -->
                    <button type="button" class="btn btn-info"
                        data-bs-dismiss="modal">Close</button>
                    <button type="submit"
                        class="btn btn-success">Submit All</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<div id="delete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card text-center">
                @if ($comment == 0 || $approve != 'system')
                    <div class="card-body">
                        <h4>Are You Sure</h4>
                    </div>
                    <form class="form-group" action="create approve results" method="POST"
                        enctype="multipart/form-data">
                        <div class="card-body">
                            <!-- Details View Start -->
                            @csrf
                            <input type="hidden" name="code" value="{{ $code }}">
                            <input type="hidden" name="session" value="{{ $session }}">
                            <input type="hidden" name="semester" value="{{ $semester }}">
                            <input type="hidden" name="approve" value="{{ $approve }}">
                            <!-- Details View End -->
                            <button type="button" class="btn btn-info" data-bs-dismiss="modal">No</button>
                            {{-- <button type="submit" class="btn btn-success">Yes</button> --}}
                            <button type="submit" id="submitt" onclick="submittt()"
                                class="btn btn-primary">Yes</button>
                            <button id="loadingg" style="display: none" class="btn btn-primary" type="button"
                                disabled>
                                <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                <span role="status">Please Wait...</span>
                            </button>
                        </div>
                    </form>
                @else
                    <div class="card-body">
                        <h5>You need to comment on each omission student before taking this action</h5>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div id="reject" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card text-center">

                @if (1)
                    <div class="card-body">
                        <h4>Are You Sure, you want REJECT {{ $code }} result???</h4>
                    </div>
                    <form class="form-group" action="update approve results" method="POST"
                        enctype="multipart/form-data">
                        <div class="card-body">
                            <!-- Details View Start -->
                            @csrf
                            <input type="hidden" name="code" value="{{ $code }}">
                            <input type="hidden" name="session" value="{{ $sessions }}">
                            <input type="hidden" name="semester" value="{{ $semester }}">
                            <input type="hidden" name="approve" value="{{ $approve }}">
                            <div class="form-group">
                                <label for="">Reason for rejection</label>
                                <textarea name="comment" class="form-control" id="" cols="30" rows="10"
                                    placeholder="Reason for rejection"></textarea>
                            </div>
                            <!-- Details View End -->
                            <button type="button" class="btn btn-info" data-bs-dismiss="modal">No</button>
                            <button type="submit" class="btn btn-danger">Yes, Reject</button>
                        </div>
                    </form>
                @else
                    <div class="card-body">
                        <h5>You need to comment on each omission student before taking this action</h5>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function submittt() {
        var name = document.getElementById('sub_committee').value;
        //alert(name);
        if (name != '') {
            document.getElementById('submitt').style.display = 'none';
            document.getElementById('loadingg').style.display = 'inline';
        }

    }
</script>
