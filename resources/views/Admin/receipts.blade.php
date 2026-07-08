<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>RECEIPTS</h5>
                        <a href="/fees due" class="btn btn-secondary float-end me-2">
                            <i class="fas fa-arrow-left"></i> Back to Fees Collection
                        </a>
                    </div>
                    <div class="card-block">
                        <form class="needs-validation" novalidate method="GET" action="/admin/receipts">
                            @csrf
                            <div class="row gx-2">
                                <div class="form-group col-md-2">
                                    <label for="facultyr">Faculty</label>
                                    <select class="form-control faculty" lang="r" name="faculty" id="facultyr">
                                        <option value="">{{ $_GET['faculty'] ?? 'Select Faculty' }}</option>
                                        @foreach ($faculty as $roww)
                                            <option value="{{ $roww->code }}" {{ (isset($_GET['faculty']) && $_GET['faculty'] == $roww->code) ? 'selected' : '' }}>
                                                {{ $roww->title }} ({{ $roww->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="departmentr">Department</label>
                                    <select class="form-control department" lang="r" id="departmentr" name="department">
                                        <option value="{{ $_GET['department'] ?? '' }}">
                                            {{ $_GET['department'] ?? 'Select Faculty First' }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="programr">Program</label>
                                    <select class="form-control" id="programr" lang="r" name="program">
                                        <option value="{{ $_GET['program'] ?? '' }}">
                                            {{ $_GET['program'] ?? 'Select Dept First' }}</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="description">Description</label>
                                    <select class="form-control" id="description" name="description">
                                        <option value="">Select Option</option>
                                        <option value="UNIVERSITY OF MAIDUGURI-1000127 FEES" {{ (isset($_GET['description']) && $_GET['description'] == 'UNIVERSITY OF MAIDUGURI-1000127 FEES') ? 'selected' : '' }}>SCHOOL FEES</option>
                                        <option value="HOSTEL-MAINTENANCE/FEES" {{ (isset($_GET['description']) && $_GET['description'] == 'HOSTEL-MAINTENANCE/FEES') ? 'selected' : '' }}>HOSTEL FEES</option>
                                        <option value="ID CARDS" {{ (isset($_GET['description']) && $_GET['description'] == 'ID CARDS') ? 'selected' : '' }}>ID CARDS</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">Select Option</option>
                                        <option value="Paid" {{ (isset($_GET['status']) && $_GET['status'] == 'Paid') ? 'selected' : '' }}>Paid</option>
                                        <option value="Pending" {{ (isset($_GET['status']) && $_GET['status'] == 'Pending') ? 'selected' : '' }}>Pending</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="fees_type">Sponsor</label>
                                    <select class="form-control" id="fees_type" name="fees_type">
                                        <option value="">Select Option</option>
                                        <option value="nelfund" {{ (isset($_GET['fees_type']) && $_GET['fees_type'] == 'nelfund') ? 'selected' : '' }}>NELFUND</option>
                                        <option value="others" {{ (isset($_GET['fees_type']) && $_GET['fees_type'] == 'others') ? 'selected' : '' }}>Others (Self Sponsor)</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="session">Session</label>
                                    <select class="form-control" id="session" name="session">
                                        <option value="">Select Session</option>
                                        <option value="2028/2029" {{ (isset($_GET['session']) && $_GET['session'] == '2028/2029') ? 'selected' : '' }}>2028/2029</option>
                                        <option value="2027/2028" {{ (isset($_GET['session']) && $_GET['session'] == '2027/2028') ? 'selected' : '' }}>2027/2028</option>
                                        <option value="2026/2027" {{ (isset($_GET['session']) && $_GET['session'] == '2026/2027') ? 'selected' : '' }}>2026/2027</option>
                                        <option value="2025/2026" {{ (isset($_GET['session']) && $_GET['session'] == '2025/2026') ? 'selected' : '' }}>2025/2026</option>
                                        <option value="2024/2025" {{ (isset($_GET['session']) && $_GET['session'] == '2024/2025') ? 'selected' : '' }}>2024/2025</option>
                                        <option value="2023/2024" {{ (isset($_GET['session']) && $_GET['session'] == '2023/2024') ? 'selected' : '' }}>2023/2024</option>
                                        <option value="2022/2023" {{ (isset($_GET['session']) && $_GET['session'] == '2022/2023') ? 'selected' : '' }}>2022/2023</option>
                                        <option value="2021/2022" {{ (isset($_GET['session']) && $_GET['session'] == '2021/2022') ? 'selected' : '' }}>2021/2022</option>
                                        <option value="2020/2021" {{ (isset($_GET['session']) && $_GET['session'] == '2020/2021') ? 'selected' : '' }}>2020/2021</option>
                                        <option value="2019/2020" {{ (isset($_GET['session']) && $_GET['session'] == '2019/2020') ? 'selected' : '' }}>2019/2020</option>
                                        <option value="2018/2019" {{ (isset($_GET['session']) && $_GET['session'] == '2018/2019') ? 'selected' : '' }}>2018/2019</option>
                                        <option value="2017/2018" {{ (isset($_GET['session']) && $_GET['session'] == '2017/2018') ? 'selected' : '' }}>2017/2018</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" style="width: 100%" class="btn btn-info btn-filter">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>
                            @if ($data->count() > 0)
                                {{ $data->count() }} Record(s) Found
                            @else
                                Use the filters above to search for invoices
                            @endif
                        </h5>
                        @if ($data->count() > 0)
                            <div>
                                @if (isset($_GET['status']) && $_GET['status'] == 'Paid')
                                    <a href="/admin/receipts/download-all?{{ http_build_query(request()->query()) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-download"></i> Download All Receipts (PDF)
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="card-block">
                        <!-- [ Data table ] start -->
                        <div class="table-responsive">
                            <table id="export-table" class="display table nowrap table-striped table-hover"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ 'NAME' }}</th>
                                        <th>{{ 'AMOUNT' }}</th>
                                        <th>{{ 'RRR' }}</th>
                                        <th>{{ 'DESCRIPTION' }}</th>
                                        <th>{{ 'SPONSOR' }}</th>
                                        <th>{{ 'SESSION' }}</th>
                                        <th>{{ 'STATUS' }}</th>
                                        <th>{{ 'DATE' }}</th>
                                        <th>{{ 'RECEIPT' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sn = 1; @endphp
                                    @foreach ($data as $row)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>N{{ number_format($row->amount, 2) }}</td>
                                            <td>{{ $row->rrr }}</td>
                                            <td>{{ $row->description }}</td>
                                            <td>{{ ($row->fees_type ?? null) === 'nelfund' ? 'NELFUND' : 'Self Sponsor' }}</td>
                                            <td>{{ $row->session }}</td>
                                            <td>
                                                @if ($row->status == 'Paid')
                                                    <span class="badge bg-success">Paid</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">{{ $row->status }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $row->updated_at ? date('d-m-y', strtotime($row->updated_at)) : 'N/A' }}</td>
                                            <td>
                                                @if ($row->status == 'Paid')
                                                    <a href="{{ route('print.receipt', $row->rrr ?? '12345678') }}"
                                                        target="_blank"
                                                        class="btn btn-icon btn-info btn-sm" title="Print Receipt">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
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
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->

<script>
$(document).ready(function() {
    $('#bulkPrintBtn').on('click', function() {
        var links = [];
        $('#export-table tbody tr').each(function() {
            var link = $(this).find('a[title="Print Receipt"]').attr('href');
            if (link) links.push(link);
        });
        if (links.length === 0) {
            alert('No paid receipts to print.');
            return;
        }
        if (!confirm('This will open ' + links.length + ' receipt(s) in new tabs. Continue?')) return;
        links.forEach(function(link) {
            window.open(link, '_blank');
        });
    });
});
</script>
