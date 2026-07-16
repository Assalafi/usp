<div class="card">
    <div class="card-header">
        <h3 class="card-title">Staff Password Reset Progress</h3>
    </div>
    <div class="card-body">
        <div id="status-message" class="alert alert-info">Starting process...</div>
        <div class="progress mb-3">
            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
        </div>
        <div id="log-container"
            style="height: 300px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px; background-color: #f5f5f5;">
            <ul id="log-list" class="list-unstyled"></ul>
        </div>
        <div class="mt-3">
            <a href="/staff" class="btn btn-secondary">Back to Staff List</a>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const progressBar = document.getElementById('progress-bar');
        const statusMessage = document.getElementById('status-message');
        const logList = document.getElementById('log-list');
        const logContainer = document.getElementById('log-container');

        // Build query string from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const queryString = urlParams.toString();
        const streamUrl = "{{ route('staff.reset_password_stream') }}" + (queryString ? '?' + queryString : '');

        function addLog(message, type = 'info') {
            const li = document.createElement('li');
            li.innerHTML = message;
            if (type === 'error') {
                li.style.color = 'red';
            } else if (type === 'success') {
                li.style.color = 'green';
            }
            logList.appendChild(li);
            logContainer.scrollTop = logContainer.scrollHeight;
        }

        const eventSource = new EventSource(streamUrl);

        eventSource.addEventListener('status', function(event) {
            const data = JSON.parse(event.data);
            statusMessage.textContent = data.message;
            addLog(`<strong>Status:</strong> ${data.message}`);
        });

        eventSource.addEventListener('progress', function(event) {
            const data = JSON.parse(event.data);
            const progress = parseInt(data.progress);
            progressBar.style.width = progress + '%';
            progressBar.textContent = progress + '%';
            progressBar.setAttribute('aria-valuenow', progress);
            addLog(data.message);
        });

        eventSource.addEventListener('finished', function(event) {
            const data = JSON.parse(event.data);
            statusMessage.textContent = data.message;
            progressBar.classList.remove('progress-bar-animated');
            if (data.message.includes('Success')) {
                progressBar.classList.add('bg-success');
            } else {
                progressBar.classList.add('bg-danger');
            }
            addLog(`<strong>Finished:</strong> ${data.message}`, data.message.includes('Success') ?
                'success' : 'error');
            eventSource.close();
        });

        eventSource.onerror = function(err) {
            statusMessage.textContent = 'An error occurred. Connection closed.';
            statusMessage.className = 'alert alert-danger';
            addLog('❌ Stream connection error. Please check the browser console and server logs.', 'error');
            eventSource.close();
        };
    });
</script>
