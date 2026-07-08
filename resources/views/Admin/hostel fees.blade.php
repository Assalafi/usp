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
                    <div class="card-block">
                        <!-- [ Data table ] start -->
                        <div class="table-responsive">
                            <table id="export-table" class="display table nowrap table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ ('HOSTEL') }}</th>
                                        <th>{{ ('BLOCK') }}</th>
                                        <th>{{ ('CATEGORY') }}</th>
                                        <th>{{ ('AMOUNT') }}</th>
                                        <th>{{ ('PAYMENT METHOD') }}</th>
                                        <th>{{ ('ACTION') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                    <tr>
                                        @php
                                            $amount = DB::table('hostel')->where(['hall' => $row -> hall, 'category' => $row -> category, 'block' => $row -> block])->value('amount')
                                        @endphp
                                        <td>{{ $sn++ }}</td>
                                        <td>{{ $row -> hall }}</td>
                                        <td>{{ $row -> block }}</td>
                                        <td>{{ $row -> category }}</td>
                                        <td>N{{ number_format($amount,2) }}</td>
                                        <td>{{ $row -> payment_method }}</td>
                                        <td>
                                            <a href="#" class="btn btn-icon btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#updateStudent{{ $sn }}">
                                                <i class="far fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Show modal content -->
                                    <div id="updateStudent{{ $sn }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-sm" role="document">
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
                                                            <input type="hidden" name="hall" value="{{ $row->hall }}">
                                                            <input type="hidden" name="block" value="{{ $row->block }}">
                                                            <input type="hidden" name="category" value="{{ $row->category }}">
                                                            <div class="form-group">
                                                                <label for="amount">Amount</label>
                                                                <input type="number" name="amount" id="amount" value="{{ $amount }}" class="form-control" required>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label for="payment_method">Payment Method</label>
                                                                <select name="payment_method" id="payment_method" class="form-control">
                                                                    <option value="{{ $row->payment_method }}">Selected: {{ $row->payment_method }}</option>
                                                                    <option value="Online">Online</option>
                                                                    <option value="Bank">Bank</option>
                                                                </select>
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