<!-- Enhanced Change of Course Table with Checkboxes -->
<div class="table-responsive">
    <!-- Bulk Actions Bar -->
    <div class="bulk-actions-bar d-flex justify-content-between align-items-center p-3 border-bottom" style="background:#f8f9fa;">
        <div class="d-flex align-items-center">
            <div class="form-check me-3">
                <input class="form-check-input" type="checkbox" id="selectAllTop" onchange="toggleSelectAll()">
                <label class="form-check-label" for="selectAllTop">
                    <strong>Select All</strong>
                </label>
            </div>
            <span id="selectedCount" class="badge bg-info">0 selected</span>
        </div>
        <div class="bulk-action-buttons">
            @if(request()->tab == 'awaiting' || request()->tab == 'all')
            <button type="button" class="btn btn-success btn-sm me-2" onclick="bulkDownloadSelected()" id="bulkDownloadBtn" disabled>
                <i class="fas fa-download me-1"></i>Download Selected (<span id="approvedCount">0</span>)
            </button>
            @endif
            @if(request()->tab == 'downloaded' || request()->tab == 'redownloaded' || request()->tab == 'all')
            <button type="button" class="btn btn-warning btn-sm me-2" onclick="bulkRedownloadSelected()" id="bulkRedownloadBtn" disabled>
                <i class="fas fa-redo me-1"></i>Redownload Selected (<span id="redownloadableCount">0</span>)
            </button>
            @endif
            <button type="button" class="btn btn-info btn-sm" onclick="exportSelectedData()" id="exportBtn" disabled>
                <i class="fas fa-file-excel me-1"></i>Export Selected
            </button>
        </div>
    </div>

    <table class="table table-hover coc-table mb-0">
        <thead>
            <tr>
                <th width="40px">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAllHeader" onchange="toggleSelectAll()">
                    </div>
                </th>
                <th width="40px">#</th>
                <th width="140px">App No</th>
                <th width="180px">Student</th>
                <th width="180px">From Dept</th>
                <th width="180px">To Dept</th>
                <th width="160px">Status</th>
                <th width="120px">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applications as $index => $app)
                @php
                    $fromDept = DB::table('department')->where('code', $app->current_department)->value('title');
                    $toDept = DB::table('department')->where('code', $app->new_department)->value('title');
                    $downloadCount = isset($downloadStats[$app->id]) ? $downloadStats[$app->id]->download_count : 0;
                    $lastDownloaded = isset($downloadStats[$app->id]) ? $downloadStats[$app->id]->last_downloaded : null;
                    $isDownloadable = $app->status == 'Approved';
                    $isRedownloadable = $isDownloadable && $downloadCount > 0;
                @endphp
                <tr data-application-id="{{ $app->id }}" data-download-count="{{ $downloadCount }}" data-status="{{ $app->status }}">
                    <td>
                        <div class="form-check">
                            <input class="form-check-input row-checkbox" type="checkbox" value="{{ $app->id }}" onchange="updateSelectedCount()">
                        </div>
                    </td>
                    <td>{{ (method_exists($applications, 'firstItem') ? $applications->firstItem() : 0) + $index }}</td>
                    <td>
                        <span class="coc-appno">{{ $app->application_no }}</span><br>
                        <span class="coc-date">{{ date('d M Y', strtotime($app->created_at)) }}</span>
                    </td>
                    <td>
                        <span class="coc-name">{{ $app->student_name }}</span><br>
                        <span class="coc-id">{{ $app->username }}</span>
                    </td>
                    <td class="coc-wrap"><span class="coc-from">{{ $fromDept }}</span></td>
                    <td class="coc-wrap"><span class="coc-to">{{ $toDept }}</span></td>
                    <td>
                        @php
                            $sc = ['Approved'=>'success','Rejected'=>'danger','Payment Pending'=>'warning','Awaiting Provost'=>'purple','Awaiting VC'=>'dark'];
                            $color = $sc[$app->status] ?? 'info';
                        @endphp
                        <span class="status-badge bg-{{ $color }} {{ in_array($color, ['warning']) ? 'text-dark' : 'text-white' }}">{{ $app->status }}</span>
                        
                        <!-- Download Status Indicator -->
                        @if($isDownloadable)
                            @if($downloadCount == 0)
                                <br><small class="text-warning">
                                    <i class="fas fa-clock"></i> Awaiting Download
                                </small>
                            @elseif($downloadCount == 1)
                                <br><small class="text-success">
                                    <i class="fas fa-download"></i> Downloaded once
                                    @if($lastDownloaded)
                                        <br>Last: {{ date('M j, H:i', strtotime($lastDownloaded)) }}
                                    @endif
                                </small>
                            @else
                                <br><small class="text-info">
                                    <i class="fas fa-redo"></i> Downloaded {{ $downloadCount }} times
                                    @if($lastDownloaded)
                                        <br>Last: {{ date('M j, H:i', strtotime($lastDownloaded)) }}
                                    @endif
                                </small>
                            @endif
                        @endif
                    </td>
                    <td style="white-space:nowrap">
                        <div class="btn-group" role="group">
                            <a href="{{ route('change-of-course.show', $app->id) }}" class="btn btn-sm btn-primary" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            @if($isDownloadable)
                                <a href="{{ route('change-of-course.admission-letter', $app->id) }}" 
                                   target="_blank" 
                                   class="btn btn-sm btn-{{ $downloadCount > 0 ? 'warning' : 'success' }}" 
                                   title="{{ $downloadCount > 0 ? 'Redownload' : 'Download' }} Admission Letter"
                                   onclick="trackSingleDownload({{ $app->id }})">
                                   <i class="fas fa-file-pdf"></i>
                                   @if($downloadCount > 0)
                                   <span class="badge bg-light text-dark ms-1" style="font-size:9px">{{ $downloadCount }}</span>
                                   @endif
                                </a>
                            @endif
                            
                            @if(session('accType') == 'Admin')
                                <a href="{{ route('change-of-course.bulk-edit', $app->id) }}" class="btn btn-sm btn-warning" title="Bulk Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center p-5">
                        <i class="fas fa-inbox" style="font-size:36px;color:#ccc"></i>
                        <p class="text-muted mt-2 mb-0">
                            @if(request()->tab == 'awaiting')
                                No applications awaiting download
                            @elseif(request()->tab == 'downloaded')
                                No applications downloaded once
                            @elseif(request()->tab == 'redownloaded')
                                No applications redownloaded
                            @else
                                No applications found
                            @endif
                        </p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(request()->tab == 'all' && method_exists($applications, 'links'))
<!-- Pagination and Results Info (only show on main tab) -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="d-flex align-items-center">
            <span class="text-muted">
                Showing {{ method_exists($applications, 'firstItem') ? $applications->firstItem() : 1 }} to {{ method_exists($applications, 'lastItem') ? $applications->lastItem() : $applications->count() }}
                of {{ method_exists($applications, 'total') ? $applications->total() : $applications->count() }} results
            </span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="d-flex justify-content-end">
            {{ $applications->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endif

<script>
// Checkbox and selection functionality
function toggleSelectAll() {
    const selectAllTop = document.getElementById('selectAllTop');
    const selectAllHeader = document.getElementById('selectAllHeader');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    
    // Get the current state - if any checkbox is checked, we want to uncheck all
    const currentlyChecked = Array.from(checkboxes).some(cb => cb.checked);
    const newState = !currentlyChecked;
    
    // Update all row checkboxes
    checkboxes.forEach(checkbox => {
        checkbox.checked = newState;
    });
    
    // Synchronize both select all checkboxes
    if (selectAllTop) {
        selectAllTop.checked = newState;
        selectAllTop.indeterminate = false;
    }
    if (selectAllHeader) {
        selectAllHeader.checked = newState;
        selectAllHeader.indeterminate = false;
    }
    
    updateSelectedCount();
}

function updateSelectedCount() {
    const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
    const selectedCount = selectedCheckboxes.length;
    const totalCheckboxes = document.querySelectorAll('.row-checkbox').length;
    
    // Update selected count display
    const selectedCountElement = document.getElementById('selectedCount');
    if (selectedCountElement) {
        selectedCountElement.textContent = selectedCount + ' selected';
    }
    
    // Update select all checkbox states
    const selectAllTop = document.getElementById('selectAllTop');
    const selectAllHeader = document.getElementById('selectAllHeader');
    
    const shouldCheckAll = selectedCount === totalCheckboxes && totalCheckboxes > 0;
    const isIndeterminate = selectedCount > 0 && selectedCount < totalCheckboxes;
    
    if (selectAllTop) {
        selectAllTop.checked = shouldCheckAll;
        selectAllTop.indeterminate = isIndeterminate;
    }
    
    if (selectAllHeader) {
        selectAllHeader.checked = shouldCheckAll;
        selectAllHeader.indeterminate = isIndeterminate;
    }
    
    // Update bulk download button
    let approvedCount = 0;
    let redownloadableCount = 0;
    
    selectedCheckboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        if (row) {
            const status = row.dataset.status;
            const downloadCount = parseInt(row.dataset.downloadCount || 0);
            
            if(status === 'Approved') {
                approvedCount++;
                if(downloadCount > 0) {
                    redownloadableCount++;
                }
            }
        }
    });
    
    // Update button states
    const bulkDownloadBtn = document.getElementById('bulkDownloadBtn');
    const bulkRedownloadBtn = document.getElementById('bulkRedownloadBtn');
    const exportBtn = document.getElementById('exportBtn');
    
    if(bulkDownloadBtn) {
        bulkDownloadBtn.disabled = approvedCount === 0;
        const approvedCountElement = document.getElementById('approvedCount');
        if (approvedCountElement) {
            approvedCountElement.textContent = approvedCount;
        }
    }
    
    if(bulkRedownloadBtn) {
        bulkRedownloadBtn.disabled = redownloadableCount === 0;
        const redownloadableCountElement = document.getElementById('redownloadableCount');
        if (redownloadableCountElement) {
            redownloadableCountElement.textContent = redownloadableCount;
        }
    }
    
    if(exportBtn) {
        exportBtn.disabled = selectedCount === 0;
    }
}

function getSelectedApplicationIds() {
    const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
    return Array.from(selectedCheckboxes).map(checkbox => parseInt(checkbox.value));
}

function bulkDownloadSelected() {
    const selectedIds = getSelectedApplicationIds();
    if(selectedIds.length === 0) {
        alert('Please select at least one application to download.');
        return;
    }
    
    // Show bulk download modal with selected applications
    showBulkDownloadModal(selectedIds);
}

function bulkRedownloadSelected() {
    const selectedIds = getSelectedApplicationIds();
    if(selectedIds.length === 0) {
        alert('Please select at least one application to redownload.');
        return;
    }
    
    // Show bulk download modal with selected applications for redownload
    showBulkDownloadModal(selectedIds, true);
}

function exportSelectedData() {
    const selectedIds = getSelectedApplicationIds();
    if(selectedIds.length === 0) {
        alert('Please select at least one application to export.');
        return;
    }
    
    // Implement export functionality
    window.location.href = '/change-of-course/export?ids=' + selectedIds.join(',');
}

function trackSingleDownload(applicationId) {
    // Track individual download via AJAX
    fetch('/change-of-course/track-download', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            application_id: applicationId,
            download_type: 'single'
        })
    }).catch(error => console.log('Tracking error:', error));
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
});
</script>
