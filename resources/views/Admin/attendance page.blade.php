@php
    $sn = 1;
    $total = DB::table('attendance')
        ->where(['course' => $code, 'session' => session('system_session')])
        ->distinct('date')
        ->count();
@endphp
@foreach ($students as $row)
    @if ($date == 'All')
        <tr>
            <td>{{ $sn++ }}</td>
            <td>{{ $row->username }}</td>
            <td colspan="2">{{ $total == 0 ? 0 : ($row->attendance / $total) * 100 }}%</td>
        </tr>
    @else
        <tr>
            <td>{{ $sn++ }}</td>
            <td>{{ $row->username }}</td>
            <td>{{ $row->attendance == 1 ? 'Present' : 'Absent' }}</td>
            {{-- <td><a href="/attendance/{{ $row -> id }}" class="btn btn-primary btn-sm">Details</a></td> --}}
            <td>
                <a href="#" class="btn btn-icon btn-primary btn-sm updateAction" data-bs-toggle="modal"
                    data-bs-target="#updateStudent{{ $row->id }}">
                    <i class="far fa-edit"></i>
                </a>

                <button type="button" class="btn btn-icon btn-danger btn-sm deleteAction" data-bs-toggle="modal"
                    data-bs-target="#delete{{ $row->id }}">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr>
    @endif

    <!-- Show modal content -->
    <div id="updateStudent{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog"
        aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Update</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="card">
                    <form class="form-group" action="create {{ $page }}" method="POST"
                        enctype="multipart/form-data">
                        <div class="card-body">
                            <!-- Details View Start -->
                            @csrf
                            {{-- <input type="hidden" name="course" value="{{ $row->code }}"> --}}
                            <div class="form-group">
                                <label for="file" class="form-label">PDF
                                    File</label>
                                <input type="file" id="file" name="pdf" class="form-control" required>
                            </div>
                            <!-- Details View End -->
                            <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div id="delete{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="card text-center">
                    <div class="card-body">
                        <h4>Are You Sure</h4>
                    </div>
                    <form class="form-group" action="delete {{ $page }}" method="POST"
                        enctype="multipart/form-data">
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
@endforeach
