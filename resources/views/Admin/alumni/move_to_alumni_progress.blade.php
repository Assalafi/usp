<div class="card">
    <div class="card-header">
        <h3 class="card-title">Move Students to Alumni</h3>
    </div>
    <div class="card-body">
        <form id="conversion-scope-form" class="border rounded p-3 mb-4 bg-light">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="conversion-session" class="form-label fw-semibold">Academic session</label>
                    <select id="conversion-session" class="form-control" required>
                        <option value="">Select session</option>
                        @foreach ($sessions as $session)
                            <option value="{{ $session->title }}" {{ $session->title === ($currentSession ?? null) ? 'selected' : '' }}>{{ $session->title }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Only students with no result in this session will be considered.</small>
                </div>
                <div class="col-md-6">
                    <label for="username-prefix" class="form-label fw-semibold">Matric-number cutoff <span class="text-muted fw-normal">(optional)</span></label>
                    <select id="username-prefix" class="form-control">
                        <option value="">All matric-number years</option>
                        @for ($prefix = (int) date('y'); $prefix >= 0; $prefix--)
                            @php($formattedPrefix = str_pad((string) $prefix, 2, '0', STR_PAD_LEFT))
                            <option value="{{ $formattedPrefix }}">{{ $formattedPrefix }} and earlier</option>
                        @endfor
                    </select>
                    <small class="text-muted">For example, <strong>22 and earlier</strong> includes IDs beginning 22, 21, 20, and so on.</small>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button type="submit" id="start-conversion" class="btn btn-success">
                        <i class="fas fa-graduation-cap me-1"></i> Start conversion
                    </button>
                    <a href="{{ route('alumni.index') }}" class="btn btn-secondary">Back to Alumni List</a>
                </div>
            </div>
        </form>

        <div id="status-message" class="alert alert-info">Choose a session and start the conversion.</div>
        <div class="progress mb-3">
            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
        </div>
        <div id="log-container"
            style="height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; background-color: #f5f5f5;">
            <ul id="log-list" class="list-unstyled mb-0"></ul>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const progressBar = document.getElementById('progress-bar');
        const statusMessage = document.getElementById('status-message');
        const logList = document.getElementById('log-list');
        const logContainer = document.getElementById('log-container');
        const form = document.getElementById('conversion-scope-form');
        const startButton = document.getElementById('start-conversion');
        let eventSource = null;

        function addLog(message, type) {
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

        function resetButton() {
            startButton.disabled = false;
            startButton.innerHTML = '<i class="fas fa-graduation-cap me-1"></i> Start conversion';
        }

        function startConversion(event) {
            event.preventDefault();
            const selectedSession = document.getElementById('conversion-session').value;
            const usernamePrefix = document.getElementById('username-prefix').value;

            if (!selectedSession) {
                statusMessage.textContent = 'Please select an academic session first.';
                statusMessage.className = 'alert alert-warning';
                return;
            }

            if (eventSource) {
                eventSource.close();
            }

            progressBar.style.width = '0%';
            progressBar.textContent = '0%';
            progressBar.setAttribute('aria-valuenow', '0');
            progressBar.classList.remove('bg-success', 'bg-danger');
            progressBar.classList.add('progress-bar-animated');
            logList.innerHTML = '';
            statusMessage.className = 'alert alert-info';
            statusMessage.textContent = 'Connecting to the conversion process...';
            startButton.disabled = true;
            startButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Processing...';

            const url = new URL("{{ route('alumni.move_to_alumni_stream') }}", window.location.origin);
            url.searchParams.set('session', selectedSession);
            if (usernamePrefix) {
                url.searchParams.set('username_prefix', usernamePrefix);
            }

            eventSource = new EventSource(url.toString());

            eventSource.addEventListener('status', function(event) {
                const data = JSON.parse(event.data);
                statusMessage.textContent = data.message;
                addLog('<strong>Status:</strong> ' + data.message);
            });

            eventSource.addEventListener('progress', function(event) {
                const data = JSON.parse(event.data);
                const progress = parseInt(data.progress, 10);
                progressBar.style.width = progress + '%';
                progressBar.textContent = progress + '%';
                progressBar.setAttribute('aria-valuenow', progress);
                addLog(data.message);
            });

            eventSource.addEventListener('finished', function(event) {
                const data = JSON.parse(event.data);
                statusMessage.textContent = data.message;
                progressBar.classList.remove('progress-bar-animated');
                if (data.message.includes('Success') || data.message.includes('No students')) {
                    progressBar.classList.add('bg-success');
                } else {
                    progressBar.classList.add('bg-danger');
                }
                addLog('<strong>Finished:</strong> ' + data.message, data.message.includes('Success') ? 'success' : 'error');
                eventSource.close();
                resetButton();
            });

            eventSource.onerror = function() {
                statusMessage.textContent = 'The conversion connection was interrupted. Check the server log before trying again.';
                statusMessage.className = 'alert alert-danger';
                addLog('Connection error. No further records were processed by this browser session.', 'error');
                eventSource.close();
                resetButton();
            };
        }

        form.addEventListener('submit', startConversion);
    });
</script>