
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Online Bed Space</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-auto">
                            <div class="card-block">
                                <form action="#" method="POST">
                                    @csrf
                                    <div class="row gx-2">
                                        <input type="hidden" id="type" value="0">
                                        <input type="hidden" id="page" value="ajax/admin/online bed space">
                                        <div class="form-group col-md-6">
                                            <select class="form-control" id="filterHall" required>
                                                <option value="">SELECT HALL</option>
                                                @foreach ($hall as $hall)
                                                    <option value="{{ $hall -> hall }}">{{ $hall -> hall }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <select class="form-control" id="filterCategory" required>
                                                <option value="">SELECT CATEGORY</option>
                                                <option value="CONVENTIONAL">CONVENTIONAL</option>
                                                <option value="NONCONVENTIONAL">NON-CONVENTIONAL</option>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>  
                </div>
            </div>

            <div class="col-sm-12" id="mainbody">
                <div class="card">
                    <div class="card-block">
                        <!-- [ Data table ] start -->
                        <div class="table-responsive">
                            <table id="export-table" class="display table nowrap table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Hall</th>
                                        <th>Block</th>
                                        <th>Room</th>
                                        <th>Bed</th>
                                        <th>Occupant</th>
                                    </tr>
                                </thead>
                                <tbody id="getFilter">
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
                                </tbody>
                            </table>
                        </div>
                        <!-- [ Data table ] end -->
                    </div>
                </div>
            </div>
            <div class="col-sm-12" style="display: none;" id="wait">
                <div class="spinner-grow" role="status">
                  <span class="visually-hidden"></span>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>