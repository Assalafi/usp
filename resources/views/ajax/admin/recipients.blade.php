@php
    use App\Models\User;
    use App\Models\Student;
@endphp
@php
    $sn = 1;
@endphp
@foreach( $data as $row )
@php
  $name = Student::select('fullname')->where('username', $row->occupant)->first();
@endphp
<tr>
    <td>
        {{ $sn++ }}
    </td>
    <td>
        {{ $row->hall }}
    </td>
    <td>
        {{ $row->block }}
    </td>
    <td>
        {{ $row->room }}
    </td>
    <td>
        {{ $row->bed }}
    </td>
    <td>
        @if ($row->status == 0)
            Available
        @elseif($row->status == 1)
            Occupied
        @endif
    </td>
    <td>
        @if ($row->bed_type == 0)
            Online
        @elseif($row->bed_type == 1)
            Reserve
        @endif
    </td>
    <td>
        {{ $row->occupant }}
    </td>
    <td>
        @if ($name)
            {{ $name['fullname'] }}
        @endif
    </td>
    <td>
        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#showModal{{ $row->id }}">
            <i class="fas fa-minus"></i> Revoke
        </button>
    </td>
</tr>
<!-- Show modal content -->
<div id="showModal{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Revoke Warning</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="revoke" method="POST">
                <div class="card-body">
                    <!-- Details View Start -->
                    @csrf
                    <div class="form-group">
                        <input type="hidden" name="id" id="id" value="{{ $row->id }}">
                    </div>
                    <!-- Details View End -->
                <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger">Revoke</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
