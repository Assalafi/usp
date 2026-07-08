<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Password Update Progress</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding: 2rem;
            line-height: 1.6;
        }

        #progress-container {
            width: 100%;
            max-width: 600px;
            background-color: #e9ecef;
            border-radius: 0.25rem;
        }

        /* The ID for the progress bar div must be "progressBar" */
        #progressBar {
            width: 0%;
            height: 35px;
            background-color: #0d6efd;
            text-align: center;
            line-height: 35px;
            color: white;
            border-radius: 0.25rem;
            transition: width 0.4s ease;
        }

        #log {
            margin-top: 1rem;
            border: 1px solid #ccc;
            padding: 10px;
            height: 300px;
            overflow-y: auto;
            background-color: #f8f9fa;
            font-family: monospace;
            font-size: 0.9em;
        }

        button {
            font-size: 1rem;
            padding: 10px 15px;
            cursor: pointer;
        }

        button:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }
    </style>
</head>

<body>

    <h1>Account Password Update</h1>
    <p>Press the button to begin the process. Do not close this window until it is complete.</p>
    <button id="startButton">Start Process</button>
    <hr style="margin: 1.5rem 0;">

    <h3>Progress</h3>
    <div id="progress-container">
        <div id="progressBar">0%</div>
    </div>

    <h3>Log</h3>
    <div id="log">Waiting to start...</div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ DOM fully loaded. The script is now running.');

            const startButton = document.getElementById('startButton');
            const progressBar = document.getElementById('progressBar'); // This will now find the element
            const logDiv = document.getElementById('log');

            console.log('🔍 Searching for elements:');
            console.log('  - Start Button:', startButton);
            console.log('  - Progress Bar:', progressBar);
            console.log('  - Log Div:', logDiv);

            if (!startButton || !progressBar || !logDiv) {
                console.error(
                    '❌ CRITICAL ERROR: One or more HTML elements were not found. Check your element IDs.');
                return;
            }

            startButton.addEventListener('click', () => {
                console.log('🔘 Start button clicked!');

                startButton.disabled = true;
                startButton.textContent = 'Processing...';
                logDiv.innerHTML = '';
                progressBar.style.width = '0%';
                progressBar.textContent = '0%';

                const url = "{{ route('admin.password.progress') }}";
                console.log('🔌 Creating EventSource to connect to URL:', url);

                const eventSource = new EventSource(url);

                eventSource.onopen = function() {
                    console.log('✅ Connection to server established successfully.');
                    logDiv.innerHTML += '<div>Server connection established. Waiting for data...</div>';
                };

                const updateListener = event => {
                    console.log('✉️ Received event:', {
                        type: event.type,
                        data: event.data
                    });
                    const data = JSON.parse(event.data);
                    logDiv.innerHTML += `<div>${data.message}</div>`;
                    logDiv.scrollTop = logDiv.scrollHeight;

                    if (data.progress !== undefined) {
                        progressBar.style.width = data.progress + '%';
                        progressBar.textContent = data.progress + '%';
                    }
                };

                eventSource.addEventListener('status', updateListener);
                eventSource.addEventListener('progress', updateListener);

                eventSource.addEventListener('finished', event => {
                    console.log('🏁 Received FINISHED event. Closing connection.');
                    const data = JSON.parse(event.data);
                    logDiv.innerHTML +=
                        `<div style="font-weight: bold; color: green;">${data.message}</div>`;
                    startButton.disabled = false;
                    startButton.textContent = 'Start Process Again';
                    eventSource.close();
                });

                eventSource.onerror = (err) => {
                    console.error('❌ ERROR: EventSource failed.', err);
                    logDiv.innerHTML +=
                        `<div style="font-weight: bold; color: red;">Server connection failed. Check console and Network tab for details.</div>`;
                    startButton.disabled = false;
                    startButton.textContent = 'Retry Process';
                    eventSource.close();
                };
            });

            console.log('👍 Event listener attached to the start button.');
        });
    </script>

</body>

</html>
