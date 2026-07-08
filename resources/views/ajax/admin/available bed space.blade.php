
@php
    $sn = 1;
@endphp
@foreach( $data as $row )
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
        @if ($row->status == 1)
            {{ $row->occupant }}
        @elseif($row->status == 0)
            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#showModal{{ $row->id }}">
                <i class="fas fa-plus"></i> Assign
            </button>
        @endif
    </td>
</tr>
<!-- Show modal content -->
<div id="showModal{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Warning</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="assign bed" method="POST">
                <div class="card-body">
                    <!-- Details View Start -->
                    @csrf
                    <div class="form-group">
                        <label for="username">ID Number</label>
                        <input type="text" class="form-control" name="username" id="username" required placeholder="Enter ID Number">
                        <input type="hidden" name="id" id="id" value="{{ $row->id }}">
                    </div>
                    <!-- Details View End -->
                <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach