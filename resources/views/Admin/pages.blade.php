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
                        <h5>{{ strtoupper($page) }}</h5>
                    </div>
                    <div class="card-block">
                        
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createStudent"><i class="fas fa-plus"></i> {{ ('Add New') }}</a>
                        <button href="#" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#importStudent"><i class="fas fa-upload"></i> {{ ('Import') }}</button>
                        
                    </div>
                    
                    <div class="card-block">
                        <form class="needs-validation" novalidate method="GET" action="#">
                            @csrf
                            <div class="row gx-2">
                                <div class="form-group col-md-2">
                                    <label for="main">Page Group</label>
                                    <input type="text" name="main" id="main" class="form-control">
                                </div>
                                <div class="form-group col-md-1">
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
                                        <th>{{ ('Navigation Order') }}</th>
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
                                        <td>{{ $row -> main_order }}</td>
                                        <td>
                                            <a href="#" class="btn btn-icon btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#updateStudent{{ $sn }}">
                                                <i class="far fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Show modal content -->
                                    <div id="updateStudent{{ $sn }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-xl" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="myModalLabel">Update</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="card">
                                                    <form class="form-group" action="update-student" method="POST" enctype="multipart/form-data">
                                                        <div class="card-body">
                                                            <!-- Details View Start -->
                                                            @csrf
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="main">Main Navigation</label>
                                <input type="text" name="main" value="{{ $row -> main }}" id="main" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="main_order">Order</label>
                                <input type="number" name="main_order" value="{{ $row -> main_order }}" id="main_order" class="form-control" required>
                            </div>
                        </div>
                        @foreach (DB::table('pages')->where(['main' => $row -> main])->orderBy('sub_order', 'ASC')->get() as $pages)
                            <div class="row">
                                <input type="hidden" name="id[]" value="{{ $pages -> id }}">
                                <div class="form-group col-md-4">
                                    <label for="page">Page</label>
                                    <input type="text" name="page[]" id="page" value="{{ $pages -> page }}" class="form-control" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="link">Link</label>
                                    <input type="text" name="link[]" value="{{ $pages -> link }}" id="link" class="form-control" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="action">Action Button</label>
                                    <input type="text" name="action[]" value="upload,create,update,delete" id="action" class="form-control" required>
                                </div>
                                <div class="form-group col-md-1">
                                    <label for="sub_order">Order</label>
                                    <input type="number" name="sub_order[]" value="{{ $pages -> sub_order }}" id="sub_order" class="form-control" value="1" required>
                                </div>
                                    
                            </div>
                        @endforeach
                            
                                                        </div>
                                                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-success">Update</button>
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
    <div class="modal-dialog modal-xl" role="document">
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
                            <div class="form-group col-md-6">
                                <label for="main">Main Navigation</label>
                                <input type="text" name="main" id="main" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="main_order">Order</label>
                                <input type="number" name="main_order" id="main_order" class="form-control" required>
                            </div>
                        </div>
                        <div class="row" id="inp1">
                            <div class="form-group col-md-4">
                                <label for="page">Page</label>
                                <input type="text" name="page[]" id="page" class="form-control" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="link">Link</label>
                                <input type="text" name="link[]" id="link" class="form-control" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="action">Action</label>
                                <input type="text" name="action[]" value="upload,create,update,delete" id="action" class="form-control" required>
                            </div>
                            <div class="form-group col-md-1">
                                <label for="sub_order">Order</label>
                                <input type="number" name="sub_order[]" id="sub_order" class="form-control" value="1" required>
                            </div>
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
            html += '<div class="row" id="inp'+val+'"><div class="form-group col-md-4"><label for="page">Page</label><input type="text" name="page[]" id="page" class="form-control" required></div><div class="form-group col-md-4"><label for="link">Link</label><input type="text" name="link[]" id="link" class="form-control" required></div><div class="form-group col-md-3"><label for="action">Action</label><input type="text" name="action[]" value="upload,create,update,delete" id="action" class="form-control" required></div><div class="form-group col-md-1"><label for="sub_order">Order</label><input type="number" name="sub_order[]" id="sub_order" class="form-control" value="'+val+'" required></div></div>';

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