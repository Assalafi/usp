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
        {{ $row->occupant }}
    </td>
</tr>
@endforeach