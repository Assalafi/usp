@php($faceSearchScope = $scope ?? 'ug_students')

<button type="button" class="btn btn-outline-primary ms-1" data-bs-toggle="modal" data-bs-target="#faceSearchModal">
    <i class="fas fa-camera me-1"></i> Search by photo
</button>

<div class="modal fade" id="faceSearchModal" tabindex="-1" aria-labelledby="faceSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="faceSearchModalLabel">Find a record by photo</h5>
                    <small class="text-muted">Use a clear, front-facing photo. Results are suggestions for administrator confirmation.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="faceSearchForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="scope" value="{{ $faceSearchScope }}">
                    <div class="row g-3 align-items-stretch">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Photo to search</label>
                            <input type="file" name="photo" id="faceSearchPhoto" class="form-control" accept="image/jpeg,image/png" required>
                            <div class="form-text">Choose a file from the device. This field does not force the camera to open.</div>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button type="button" class="btn btn-outline-secondary" id="faceSearchOpenCamera"><i class="fas fa-video me-1"></i>Use camera</button>
                                <button type="button" class="btn btn-outline-danger d-none" id="faceSearchStopCamera">Stop camera</button>
                            </div>
                            <div class="mt-3 d-none" id="faceSearchCameraBox">
                                <video id="faceSearchVideo" class="w-100 rounded border" autoplay playsinline></video>
                                <button type="button" class="btn btn-primary w-100 mt-2" id="faceSearchCapture"><i class="fas fa-camera me-1"></i>Capture photo</button>
                                <canvas id="faceSearchCanvas" class="d-none"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <label class="form-label fw-semibold d-block text-start">Preview</label>
                            <div class="border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden" style="min-height:220px;">
                                <img id="faceSearchPreview" src="" alt="Search photo preview" class="img-fluid d-none" style="max-height:260px;object-fit:contain;">
                                <span id="faceSearchPreviewEmpty" class="text-muted px-3">Your selected photo will appear here.</span>
                            </div>
                        </div>
                    </div>
                    <div id="faceSearchError" class="alert alert-danger d-none mt-3 mb-0"></div>
                    <button type="submit" id="faceSearchSubmit" class="btn btn-primary w-100 mt-3">
                        <span class="face-search-submit-text"><i class="fas fa-search me-1"></i>Search records</span>
                        <span class="face-search-submit-loading d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Analysing photo…</span>
                    </button>
                </form>
                <div id="faceSearchSummary" class="small text-muted mt-3 d-none"></div>
                <div id="faceSearchResults" class="row g-3 mt-1"></div>
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                const modal = document.getElementById('faceSearchModal');
                if (!modal || modal.dataset.bound === '1') return;
                modal.dataset.bound = '1';
                const form = document.getElementById('faceSearchForm');
                const input = document.getElementById('faceSearchPhoto');
                const preview = document.getElementById('faceSearchPreview');
                const empty = document.getElementById('faceSearchPreviewEmpty');
                const error = document.getElementById('faceSearchError');
                const results = document.getElementById('faceSearchResults');
                const summary = document.getElementById('faceSearchSummary');
                const submit = document.getElementById('faceSearchSubmit');
                const video = document.getElementById('faceSearchVideo');
                const canvas = document.getElementById('faceSearchCanvas');
                const cameraBox = document.getElementById('faceSearchCameraBox');
                const openCamera = document.getElementById('faceSearchOpenCamera');
                const stopCamera = document.getElementById('faceSearchStopCamera');
                const capture = document.getElementById('faceSearchCapture');
                let stream = null;
                let capturedFile = null;

                function showError(message) { error.textContent = message; error.classList.toggle('d-none', !message); }
                function showPreview(file) {
                    if (!file) return;
                    capturedFile = file;
                    preview.src = URL.createObjectURL(file);
                    preview.classList.remove('d-none');
                    empty.classList.add('d-none');
                }
                function closeCamera() {
                    if (stream) stream.getTracks().forEach(track => track.stop());
                    stream = null; cameraBox.classList.add('d-none'); stopCamera.classList.add('d-none'); openCamera.classList.remove('d-none');
                }
                input.addEventListener('change', () => { capturedFile = null; showPreview(input.files[0]); showError(''); });
                openCamera.addEventListener('click', async () => {
                    showError('');
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) { showError('Camera access is not available in this browser. Choose a photo file instead.'); return; }
                    try { stream = await navigator.mediaDevices.getUserMedia({video: {facingMode: 'user'}, audio: false}); video.srcObject = stream; cameraBox.classList.remove('d-none'); stopCamera.classList.remove('d-none'); openCamera.classList.add('d-none'); }
                    catch (e) { showError('The camera could not be opened. Check the browser permission or choose a photo file instead.'); }
                });
                stopCamera.addEventListener('click', closeCamera);
                capture.addEventListener('click', () => {
                    if (!video.videoWidth) { showError('The camera is not ready yet. Please wait a moment and try again.'); return; }
                    canvas.width = video.videoWidth; canvas.height = video.videoHeight; canvas.getContext('2d').drawImage(video, 0, 0);
                    canvas.toBlob(blob => { capturedFile = new File([blob], 'camera-photo.jpg', {type: 'image/jpeg'}); showPreview(capturedFile); closeCamera(); }, 'image/jpeg', .94);
                });
                form.addEventListener('submit', async (event) => {
                    event.preventDefault(); showError(''); results.innerHTML = ''; summary.classList.add('d-none');
                    const file = capturedFile || input.files[0];
                    if (!file) { showError('Choose a photo or capture one with the camera first.'); return; }
                    const body = new FormData(form); body.set('photo', file);
                    submit.disabled = true; submit.querySelector('.face-search-submit-text').classList.add('d-none'); submit.querySelector('.face-search-submit-loading').classList.remove('d-none');
                    try {
                        const response = await fetch('{{ route('admin.face-search') }}', {method: 'POST', body, headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || Object.values(data.errors || {}).flat()[0] || 'The photo could not be analysed.');
                        summary.textContent = `${data.indexed} indexed records searched. Only a high-confidence result should be treated as a likely match.`; summary.classList.remove('d-none');
                        if (!data.results || !data.results.length) { results.innerHTML = '<div class="col-12"><div class="alert alert-warning mb-0">No indexed candidate was found. Try a sharper, front-facing photo.</div></div>'; return; }
                        results.innerHTML = data.results.map(item => { const m=item.metadata||{}; const state=item.match_state==='high_confidence'?'success':(item.match_state==='review'?'warning':'secondary'); return `<div class="col-md-6"><div class="card h-100 border-${state}"><div class="card-body d-flex gap-3"><img src="${m.photo_url||''}" alt="" class="rounded border" style="width:72px;height:88px;object-fit:cover" onerror="this.style.display='none'"><div class="min-w-0"><div class="fw-semibold">${m.name||'Unnamed record'}</div><div class="small text-muted">${m.identifier||''}</div><div class="small">${m.faculty||''}${m.department?' · '+m.department:''}</div><span class="badge text-bg-${state} mt-2">${item.confidence} · ${(item.score*100).toFixed(1)}%</span></div></div></div></div>`; }).join('');
                    } catch (e) { showError(e.message || 'The photo could not be analysed.'); }
                    finally { submit.disabled = false; submit.querySelector('.face-search-submit-text').classList.remove('d-none'); submit.querySelector('.face-search-submit-loading').classList.add('d-none'); }
                });
                modal.addEventListener('hidden.bs.modal', closeCamera);
            })();
        </script>
    @endpush
@endonce
