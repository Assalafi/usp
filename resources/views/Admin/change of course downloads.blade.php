<style>
.coc-stat{border-radius:10px;border:none;box-shadow:0 1px 8px rgba(0,0,0,.08)}
.coc-stat .card-body{padding:15px 18px}
.coc-stat .s-icon{width:44px;height:44px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:18px;color:#fff;margin-right:12px;flex-shrink:0}
.coc-stat h3{margin:0;font-size:22px;font-weight:700;line-height:1}
.coc-stat small{color:#6c757d;font-size:12px}
.coc-wrap{max-width:170px;white-space:normal;word-wrap:break-word}
.coc-from{background:#fef3cd;color:#856404;padding:3px 8px;border-radius:4px;font-size:12px;display:inline-block;line-height:1.4}
.coc-to{background:#d1ecf1;color:#0c5460;padding:3px 8px;border-radius:4px;font-size:12px;display:inline-block;line-height:1.4;font-weight:600}
.coc-appno{font-family:monospace;font-weight:700;color:#0d6efd;font-size:12px}
.coc-date{color:#6c757d;font-size:11px}
.coc-name{font-weight:600;color:#212529}
.coc-id{color:#6c757d;font-size:11px}
.status-badge{padding:4px 8px;border-radius:4px;font-size:11px;font-weight:500}
.bulk-actions-bar{background:#f8f9fa;border-bottom:1px solid #dee2e6}
</style>

<div class="main-body">
    <div class="page-wrapper">
        <!-- [ page header ] start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Change of Course - Download Management</h5>
                        </div>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('change-of-course.admin') }}"><i class="fas fa-arrow-left me-1"></i>Back to Applications</a></li>
                            <li class="breadcrumb-item">Download Management</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ page header ] end -->
<div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-2 col-6 mb-2">
                <div class="card coc-stat">
                    <div class="card-body d-flex align-items-center">
                        <div class="s-icon" style="background:#0d6efd"><i class="fas fa-file-alt"></i></div>
                        <div><h3>{{ $applications->total() }}</h3><small>Total</small></div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="card coc-stat">
                    <div class="card-body d-flex align-items-center">
                        <div class="s-icon" style="background:#17a2b8"><i class="fas fa-hourglass-half"></i></div>
                        <div><h3>{{ $statusCounts['processing'] }}</h3><small>In Progress</small></div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="card coc-stat">
                    <div class="card-body d-flex align-items-center">
                        <div class="s-icon" style="background:#28a745"><i class="fas fa-check-circle"></i></div>
                        <div><h3>{{ $statusCounts['approved'] }}</h3><small>Approved</small></div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="card coc-stat">
                    <div class="card-body d-flex align-items-center">
                        <div class="s-icon" style="background:#ffc107"><i class="fas fa-clock"></i></div>
                        <div><h3>{{ $downloadStatusCounts['awaiting_download'] }}</h3><small>Awaiting Download</small></div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="card coc-stat">
                    <div class="card-body d-flex align-items-center">
                        <div class="s-icon" style="background:#6f42c1"><i class="fas fa-download"></i></div>
                        <div><h3>{{ $downloadStatusCounts['downloaded'] }}</h3><small>Downloaded</small></div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="card coc-stat">
                    <div class="card-body d-flex align-items-center">
                        <div class="s-icon" style="background:#fd7e14"><i class="fas fa-redo"></i></div>
                        <div><h3>{{ $downloadStatusCounts['redownloaded'] }}</h3><small>Redownloaded</small></div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Applications Table with Tabs -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">Download Management</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="badge badge-primary p-2 me-2" style="font-size:13px">{{ $applications->total() }} Total</span>
                            <span class="badge badge-success p-2 me-2" style="font-size:13px">{{ $statusCounts['approved'] }} Approved</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Download Status Tabs -->
                    <ul class="nav nav-tabs" id="downloadStatusTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                                <i class="fas fa-list me-1"></i>All Applications
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="awaiting-tab" data-bs-toggle="tab" data-bs-target="#awaiting" type="button" role="tab">
                                <i class="fas fa-clock me-1"></i>Awaiting Download ({{ $downloadStatusCounts['awaiting_download'] }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="downloaded-tab" data-bs-toggle="tab" data-bs-target="#downloaded" type="button" role="tab">
                                <i class="fas fa-download me-1"></i>Downloaded ({{ $downloadStatusCounts['downloaded'] }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="redownloaded-tab" data-bs-toggle="tab" data-bs-target="#redownloaded" type="button" role="tab">
                                <i class="fas fa-redo me-1"></i>Redownloaded ({{ $downloadStatusCounts['redownloaded'] }})
                            </button>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content" id="downloadStatusTabContent">
                        <!-- All Applications Tab -->
                        <div class="tab-pane fade show active" id="all" role="tabpanel">
                            @include('Admin.partials.change-of-course-table', ['applications' => $applications, 'downloadStats' => $downloadStats, 'tab' => 'all'])
                        </div>
                        <!-- Awaiting Download Tab -->
                        <div class="tab-pane fade" id="awaiting" role="tabpanel">
                            @php
                                $awaitingDownload = $applications->filter(function($app) use ($downloadStats) {
                                    return $app->status == 'Approved' && !isset($downloadStats[$app->id]);
                                });
                            @endphp
                            @include('Admin.partials.change-of-course-table', ['applications' => $awaitingDownload, 'downloadStats' => $downloadStats, 'tab' => 'awaiting'])
                        </div>
                        <!-- Downloaded Tab -->
                        <div class="tab-pane fade" id="downloaded" role="tabpanel">
                            @php
                                $downloadedOnce = $applications->filter(function($app) use ($downloadStats) {
                                    return isset($downloadStats[$app->id]) && $downloadStats[$app->id]->download_count == 1;
                                });
                            @endphp
                            @include('Admin.partials.change-of-course-table', ['applications' => $downloadedOnce, 'downloadStats' => $downloadStats, 'tab' => 'downloaded'])
                        </div>
                        <!-- Redownloaded Tab -->
                        <div class="tab-pane fade" id="redownloaded" role="tabpanel">
                            @php
                                $redownloaded = $applications->filter(function($app) use ($downloadStats) {
                                    return isset($downloadStats[$app->id]) && $downloadStats[$app->id]->download_count > 1;
                                });
                            @endphp
                            @include('Admin.partials.change-of-course-table', ['applications' => $redownloaded, 'downloadStats' => $downloadStats, 'tab' => 'redownloaded'])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Bulk Download Modal -->
<div class="modal fade" id="bulkDownloadModal" tabindex="-1" aria-labelledby="bulkDownloadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkDownloadModalLabel">
                    <i class="fas fa-download me-2"></i><span id="modalTitle">Bulk Download Admission Letters</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <span id="modalDescription">This will download admission letters for selected approved applications.</span>
                </div>
                
                <!-- Selected Applications Summary -->
                <div id="selectedSummary" class="card mb-3" style="display:none;">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-list me-2"></i>Selected Applications</h6>
                    </div>
                    <div class="card-body p-2">
                        <div id="selectedApplicationsList" style="max-height: 200px; overflow-y: auto;"></div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><strong>Download Options:</strong></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="downloadOption" id="downloadSeparate" value="separate" checked>
                            <label class="form-check-label" for="downloadSeparate">
                                Separate PDF files (recommended)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="downloadOption" id="downloadCombined" value="combined">
                            <label class="form-check-label" for="downloadCombined">
                                Combined PDF file (all letters in one)
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><strong>File Naming:</strong></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="namingOption" id="namingAppNo" value="appno" checked>
                            <label class="form-check-label" for="namingAppNo">
                                Application Number
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="namingOption" id="namingStudent" value="student">
                            <label class="form-check-label" for="namingStudent">
                                Student Name
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="markAsDownloaded" checked>
                    <label class="form-check-label" for="markAsDownloaded">
                        Mark all as downloaded (for tracking purposes)
                    </label>
                </div>

                <div id="downloadProgress" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Downloading admission letters...</span>
                        <span id="progressText">0/0</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div id="downloadStatus" class="mt-2"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="startBulkDownload">
                    <i class="fas fa-download me-2"></i>Start Download
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let selectedApplicationIds = [];
let isRedownloadMode = false;

function showBulkDownloadModal(applicationIds = null, redownload = false) {
    selectedApplicationIds = applicationIds || [];
    isRedownloadMode = redownload;
    
    const modal = new bootstrap.Modal(document.getElementById('bulkDownloadModal'));
    
    // Update modal content based on mode
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');
    const selectedSummary = document.getElementById('selectedSummary');
    
    if (selectedApplicationIds.length > 0) {
        // Selected applications mode
        modalTitle.innerHTML = `<i class="fas fa-download me-2"></i>${redownload ? 'Redownload' : 'Download'} Selected Admission Letters`;
        modalDescription.textContent = `This will ${redownload ? 'redownload' : 'download'} admission letters for ${selectedApplicationIds.length} selected ${redownload ? 'previously downloaded' : 'approved'} applications.`;
        
        // Show selected applications summary
        selectedSummary.style.display = 'block';
        const applicationsList = document.getElementById('selectedApplicationsList');
        applicationsList.innerHTML = '<div class="text-muted">Loading selected applications...</div>';
        
        // Fetch selected applications details
        fetch('/change-of-course/get-selected-applications', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                application_ids: selectedApplicationIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '';
                data.applications.forEach(app => {
                    html += `
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <div>
                                <strong>${app.application_no}</strong><br>
                                <small class="text-muted">${app.student_name}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success">${app.status}</span>
                                ${app.download_count > 0 ? `<br><small class="text-info">Downloaded ${app.download_count}x</small>` : ''}
                            </div>
                        </div>
                    `;
                });
                applicationsList.innerHTML = html;
            }
        })
        .catch(error => {
            applicationsList.innerHTML = '<div class="text-danger">Error loading applications</div>';
        });
    } else {
        // Bulk download all mode
        modalTitle.innerHTML = '<i class="fas fa-download me-2"></i>Bulk Download All Admission Letters';
        modalDescription.textContent = `This will download admission letters for all {{ $statusCounts['approved'] }} approved applications.`;
        selectedSummary.style.display = 'none';
    }
    
    modal.show();
}

document.getElementById('startBulkDownload').addEventListener('click', function() {
    const downloadOption = document.querySelector('input[name="downloadOption"]:checked').value;
    const namingOption = document.querySelector('input[name="namingOption"]:checked').value;
    const markAsDownloaded = document.getElementById('markAsDownloaded').checked;
    
    // Hide modal footer and show progress
    document.querySelector('#bulkDownloadModal .modal-footer').style.display = 'none';
    document.getElementById('downloadProgress').style.display = 'block';
    
    // Prepare request data
    const requestData = {
        download_option: downloadOption,
        naming_option: namingOption,
        mark_as_downloaded: markAsDownloaded,
        is_redownload: isRedownloadMode
    };
    
    if (selectedApplicationIds.length > 0) {
        requestData.application_ids = selectedApplicationIds;
    }
    
    // Start bulk download
    fetch('{{ route("change-of-course.bulk-download") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.download_url) {
                // Download combined PDF
                window.location.href = data.download_url;
            } else if (data.files && data.files.length > 0) {
                // Download separate files
                data.files.forEach(function(file, index) {
                    setTimeout(function() {
                        const link = document.createElement('a');
                        link.href = file.url;
                        link.download = file.filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        
                        // Update progress
                        updateProgress(index + 1, data.files.length);
                    }, index * 500); // Stagger downloads to avoid browser blocking
                });
            }
            
            // Show completion message
            setTimeout(function() {
                document.getElementById('downloadStatus').innerHTML = 
                    '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Download completed successfully!</div>';
                
                // Reload page after 2 seconds to show updated download stats
                setTimeout(function() {
                    location.reload();
                }, 2000);
            }, data.files ? data.files.length * 500 + 1000 : 1000);
        } else {
            document.getElementById('downloadStatus').innerHTML = 
                '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error: ' + (data.message || 'Unknown error') + '</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('downloadStatus').innerHTML = 
            '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Network error. Please try again.</div>';
    });
});

function updateProgress(current, total) {
    var percentage = Math.round((current / total) * 100);
    document.getElementById('progressBar').style.width = percentage + '%';
    document.getElementById('progressText').textContent = current + '/' + total;
}

// Reset modal when hidden
document.getElementById('bulkDownloadModal').addEventListener('hidden.bs.modal', function () {
    selectedApplicationIds = [];
    isRedownloadMode = false;
    document.querySelector('#bulkDownloadModal .modal-footer').style.display = 'flex';
    document.getElementById('downloadProgress').style.display = 'none';
    document.getElementById('downloadStatus').innerHTML = '';
    document.getElementById('progressBar').style.width = '0%';
    document.getElementById('progressText').textContent = '0/0';
});
</script>
</div>
</div>
</div>
</div>
