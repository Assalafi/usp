<style>
.iut-stat{border-radius:10px;border:none;box-shadow:0 1px 8px rgba(0,0,0,.08)}
.iut-stat .card-body{padding:15px 18px}
.iut-stat .s-icon{width:44px;height:44px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:18px;color:#fff;margin-right:12px;flex-shrink:0}
.iut-stat h3{margin:0;font-size:22px;font-weight:700;line-height:1}
.iut-stat small{color:#6c757d;font-size:12px}
.iut-wrap{max-width:180px;white-space:normal;word-wrap:break-word}
.iut-from{background:#fef3cd;color:#856404;padding:3px 8px;border-radius:4px;font-size:12px;display:inline-block;line-height:1.4}
.iut-to{background:#d1ecf1;color:#0c5460;padding:3px 8px;border-radius:4px;font-size:12px;display:inline-block;line-height:1.4;font-weight:600}
.iut-appno{font-family:monospace;font-weight:700;color:#0d6efd;font-size:12px}
.iut-date{color:#888;font-size:10px}
.iut-name{font-weight:600;color:#212529;font-size:13px}
.iut-sub{color:#888;font-size:11px}
.iut-table{table-layout:fixed}
.iut-table thead th{background:#343a40!important;color:#fff!important;font-size:12px;font-weight:600;padding:10px 12px;white-space:nowrap;border:none}
.iut-table thead th:nth-child(1){width:40px}
.iut-table thead th:nth-child(2){width:140px}
.iut-table thead th:nth-child(3){width:180px}
.iut-table thead th:nth-child(4){width:200px}
.iut-table thead th:nth-child(5){width:200px}
.iut-table thead th:nth-child(6){width:80px}
.iut-table thead th:nth-child(7){width:160px}
.iut-table thead th:nth-child(8){width:120px}
.iut-table tbody td{padding:10px 12px;vertical-align:middle;font-size:13px;border-top:1px solid #eee;word-wrap:break-word;overflow:hidden}
.iut-table tbody tr:hover{background:#f0f4ff}
.bg-purple{background:#6f42c1!important}
</style>
<div class="main-body">
<div class="page-wrapper">
<div class="page-header"><div class="page-block"><div class="row align-items-center"><div class="col-md-12">
<div class="page-header-title"><h5 class="m-b-10">Inter-University Transfer Applications</h5></div>
<ul class="breadcrumb"><li class="breadcrumb-item"><a href="/dash"><i class="feather icon-home"></i></a></li>
<li class="breadcrumb-item">Inter-University Transfer</li></ul>
</div></div></div></div>

<!-- Stats Row -->
@php
    $itCounts = [
        'processing' => DB::table('inter_university_transfer')->whereNotIn('status',['Approved','Rejected'])->where('payment_status','Paid')->count(),
        'approved' => DB::table('inter_university_transfer')->where('status','Approved')->count(),
        'rejected' => DB::table('inter_university_transfer')->where('status','Rejected')->count(),
    ];
@endphp
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-2">
        <div class="card iut-stat">
            <div class="card-body d-flex align-items-center">
                <div class="s-icon" style="background:#0d6efd"><i class="fas fa-exchange-alt"></i></div>
                <div><h3>{{ $applications->total() }}</h3><small>Total</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-2">
        <div class="card iut-stat">
            <div class="card-body d-flex align-items-center">
                <div class="s-icon" style="background:#17a2b8"><i class="fas fa-hourglass-half"></i></div>
                <div><h3>{{ $itCounts['processing'] }}</h3><small>In Progress</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-2">
        <div class="card iut-stat">
            <div class="card-body d-flex align-items-center">
                <div class="s-icon" style="background:#28a745"><i class="fas fa-check-circle"></i></div>
                <div><h3>{{ $itCounts['approved'] }}</h3><small>Approved</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-2">
        <div class="card iut-stat">
            <div class="card-body d-flex align-items-center">
                <div class="s-icon" style="background:#dc3545"><i class="fas fa-times-circle"></i></div>
                <div><h3>{{ $itCounts['rejected'] }}</h3><small>Rejected</small></div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-filter mr-2"></i> Filter Applications <small class="text-muted">({{ $userRole }})</small></h5>
            </div>
            <div class="card-body">
                <form method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-5 mb-2">
                            <label class="mb-1"><strong>Status</strong></label>
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="Awaiting UNIMAID HOD" {{ request('status')=='Awaiting UNIMAID HOD'?'selected':'' }}>Awaiting HOD</option>
                                <option value="Awaiting UNIMAID Dean" {{ request('status')=='Awaiting UNIMAID Dean'?'selected':'' }}>Awaiting Dean</option>
                                <option value="Awaiting Provost" {{ request('status')=='Awaiting Provost'?'selected':'' }}>Awaiting Provost</option>
                                <option value="Awaiting Registrar" {{ request('status')=='Awaiting Registrar'?'selected':'' }}>Awaiting Registrar</option>
                                <option value="Awaiting VC" {{ request('status')=='Awaiting VC'?'selected':'' }}>Awaiting VC</option>
                                <option value="Approved" {{ request('status')=='Approved'?'selected':'' }}>Approved</option>
                                <option value="Rejected" {{ request('status')=='Rejected'?'selected':'' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button type="submit" class="btn btn-primary btn-block w-100">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="/inter-university-transfer/admin" class="btn btn-outline-secondary btn-block w-100">
                                <i class="fas fa-redo mr-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Applications Table -->
<div class="row">
<div class="col-md-12">
<div class="card">
<div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Applications List</h5>
    <span class="badge badge-primary p-2" style="font-size:13px">{{ $applications->total() }} Total</span>
</div>
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover iut-table mb-0" id="transferTable">
<thead>
<tr>
    <th>#</th>
    <th>App No</th>
    <th>Applicant</th>
    <th>From Institution</th>
    <th>Transfer To</th>
    <th>Type</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
@forelse($applications as $key => $app)
@php
$statusColors = ['Awaiting UNIMAID HOD'=>'warning','Awaiting UNIMAID Dean'=>'warning','Awaiting Provost'=>'purple','Awaiting Registrar'=>'info','Awaiting VC'=>'dark','Approved'=>'success','Rejected'=>'danger'];
$progTitle = DB::table('program')->where('code', $app->new_program)->value('title');
$deptTitle = DB::table('department')->where('code', $app->new_department)->value('title');
$color = $statusColors[$app->status] ?? 'secondary';
@endphp
<tr>
<td>{{ $applications->firstItem() + $key }}</td>
<td>
    <span class="iut-appno">{{ $app->application_no }}</span><br>
    <span class="iut-date">{{ $app->created_at ? date('d M Y', strtotime($app->created_at)) : '-' }}</span>
</td>
<td>
    <span class="iut-name">{{ $app->surname }} {{ $app->first_name }}</span><br>
    <span class="iut-sub">{{ $app->email }}</span>
</td>
<td class="iut-wrap"><span class="iut-from">{{ $app->present_institution }}</span></td>
<td class="iut-wrap">
    <span class="iut-to">{{ $progTitle }}</span><br>
    <small class="text-muted">{{ $deptTitle }}</small>
</td>
<td><span class="badge badge-{{ $app->transfer_type=='within_nigeria'?'primary':'secondary' }}">{{ $app->transfer_type=='within_nigeria'?'Nigeria':'Abroad' }}</span></td>
<td><span class="badge badge-{{ $color }} p-1" style="font-size:11px;white-space:nowrap">{{ $app->status }}</span></td>
<td style="white-space:nowrap">
    <a href="/inter-university-transfer/show/{{ $app->id }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> View</a>
    @if(session('accType') == 'Admin')
    <a href="{{ route('inter-transfer.bulk-edit', $app->id) }}" class="btn btn-sm btn-warning" title="Bulk Edit"><i class="fas fa-edit"></i></a>
    @endif
</td>
</tr>
@empty
<tr>
    <td colspan="8" class="text-center p-5">
        <i class="fas fa-inbox" style="font-size:36px;color:#ccc"></i>
        <p class="text-muted mt-2 mb-0">No applications found</p>
    </td>
</tr>
@endforelse
</tbody>
</table>
</div>
<!-- Pagination and Results Info -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <span class="text-muted">
                                        Showing {{ $applications->firstItem() ?? 0 }} to {{ $applications->lastItem() ?? 0 }}
                                        of {{ $applications->total() ?? 0 }} results
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    {{ $applications->appends(request()->query())->links('pagination::bootstrap-4') }}
                                </div>
                            </div>
                        </div>
</div>
</div>
</div>
</div>
</div></div>
<script>$(document).ready(function(){$('#transferTable').DataTable({paging:false,info:false,searching:true,ordering:true});});</script>
