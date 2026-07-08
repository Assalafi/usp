<div class="pcoded-content">
    <div class="page-header card">
        <div class="row align-items-end">
            <div class="col-lg-8">
                <div class="page-header-title">
                    <i class="fas fa-calendar-alt bg-c-blue"></i>
                    <div class="d-inline">
                        <h5>Restore Alumni by Year {{ $year }}</h5>
                        <span>Restoring alumni from year {{ $year }} back to student status</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="page-header-breadcrumb">
                    <ul class=" breadcrumb breadcrumb-title">
                        <li class="breadcrumb-item">
                            <a href="/"><i class="feather icon-home"></i></a>
                        </li>
                        <li class="breadcrumb-item"><a href="{{ route('alumni.index') }}">Alumni</a></li>
                        <li class="breadcrumb-item active">Restore by Year</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Restore Alumni from Year {{ $year }} Progress</h3>
                                </div>
                                <div class="card-body">
                                    <div id="status-message" class="alert alert-info">Starting process...</div>
                                    
                                    <div class="progress mb-3">
                                        <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                            role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                            0%
                                        </div>
                                    </div>

                                    <div id="log-container" 
                                        style="height: 300px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px; background-color: #f5f5f5;">
                                        <ul id="log-list" class="list-unstyled"></ul>
                                    </div>

                                    <div class="mt-3">
                                        <a href="{{ route('alumni.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Back to Alumni List
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusMessage = document.getElementById('status-message');
    const progressBar = document.getElementById('progress-bar');
    const logList = document.getElementById('log-list');
    const logContainer = document.getElementById('log-container');
    const year = '{{ $year }}';

    // Start the SSE connection
    const eventSource = new EventSource("{{ route('alumni.restore_by_year_stream') }}?year=" + year);

    eventSource.addEventListener('status', function(e) {
        const data = JSON.parse(e.data);
        statusMessage.textContent = data.message;
        statusMessage.className = 'alert alert-info';
        
        const li = document.createElement('li');
        li.innerHTML = `<strong>[${new Date().toLocaleTimeString()}]</strong> ${data.message}`;
        logList.appendChild(li);
        logContainer.scrollTop = logContainer.scrollHeight;
    });

    eventSource.addEventListener('progress', function(e) {
        const data = JSON.parse(e.data);
        
        // Update progress bar
        progressBar.style.width = data.progress + '%';
        progressBar.setAttribute('aria-valuenow', data.progress);
        progressBar.textContent = data.progress + '%';
        
        // Add to log
        const li = document.createElement('li');
        const icon = data.message.startsWith('✓') ? '✓' : '✗';
        const colorClass = data.message.startsWith('✓') ? 'text-success' : 'text-danger';
        li.innerHTML = `<span class="${colorClass}">[${new Date().toLocaleTimeString()}] ${data.message}</span>`;
        logList.appendChild(li);
        logContainer.scrollTop = logContainer.scrollHeight;
        
        // Update status message
        statusMessage.textContent = `Processing... ${data.progress}% complete`;
    });

    eventSource.addEventListener('finished', function(e) {
        const data = JSON.parse(e.data);
        
        // Update final status
        statusMessage.textContent = data.message;
        statusMessage.className = data.message.includes('Error') ? 'alert alert-warning' : 'alert alert-success';
        
        // Update progress bar to 100%
        progressBar.style.width = '100%';
        progressBar.setAttribute('aria-valuenow', 100);
        progressBar.textContent = '100%';
        progressBar.classList.remove('progress-bar-animated');
        
        // Add final log
        const li = document.createElement('li');
        li.innerHTML = `<strong class="text-primary">[${new Date().toLocaleTimeString()}] ${data.message}</strong>`;
        logList.appendChild(li);
        logContainer.scrollTop = logContainer.scrollHeight;
        
        // Close the connection
        eventSource.close();
    });

    eventSource.onerror = function(e) {
        console.error('EventSource error:', e);
        statusMessage.textContent = 'Error: Connection to server lost. Process may still be running.';
        statusMessage.className = 'alert alert-danger';
        
        const li = document.createElement('li');
        li.innerHTML = `<span class="text-danger">[${new Date().toLocaleTimeString()}] Connection error occurred</span>`;
        logList.appendChild(li);
        
        eventSource.close();
    };
});
</script>
