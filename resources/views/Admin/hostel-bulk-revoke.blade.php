<style>
    .hostel-bulk-page { max-width: 1180px; margin: 0 auto; padding: 20px 14px 48px; color: #1f2937; }
    .hostel-bulk-hero { border-radius: 22px; padding: 24px 26px; color: #fff; background: linear-gradient(135deg,#173f5f,#126e82 58%,#0b9b8a); box-shadow: 0 14px 35px rgba(20,63,95,.18); }
    .hostel-bulk-hero h1 { margin: 0 0 7px; color: #fff !important; font-size: clamp(1.35rem,2.3vw,2rem); font-weight: 700; }
    .hostel-bulk-hero p { margin: 0; max-width: 760px; color: rgba(255,255,255,.9) !important; line-height: 1.55; }
    .hostel-bulk-card { background: #fff; border: 1px solid #e7edf3; border-radius: 18px; margin-top: 18px; box-shadow: 0 10px 28px rgba(15,42,67,.06); overflow: hidden; }
    .hostel-bulk-card-header { padding: 17px 20px; border-bottom: 1px solid #edf1f5; display:flex; gap:12px; align-items:center; justify-content:space-between; flex-wrap:wrap; }
    .hostel-bulk-card-header h2 { margin:0; font-size:1.08rem; font-weight:700; }
    .hostel-bulk-card-body { padding: 20px; }
    .hostel-bulk-grid { display:grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap:14px; }
    .hostel-bulk-field label { display:block; font-size:.78rem; font-weight:700; color:#516171; margin:0 0 7px; text-transform:uppercase; letter-spacing:.03em; }
    .hostel-bulk-field select { width:100%; min-height:44px; border:1px solid #d9e2eb; border-radius:10px; padding:8px 11px; background:#fff; color:#243342; }
    .hostel-bulk-scope { display:flex; gap:10px; flex-wrap:wrap; margin-top:16px; }
    .hostel-bulk-scope label { display:flex; align-items:center; gap:8px; border:1px solid #dce5ed; border-radius:11px; padding:10px 13px; cursor:pointer; background:#fbfdff; }
    .hostel-bulk-actions { display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top:20px; }
    .hostel-bulk-btn { border:0; border-radius:10px; padding:11px 16px; font-weight:700; cursor:pointer; transition:transform .15s,box-shadow .15s; }
    .hostel-bulk-btn:hover { transform:translateY(-1px); box-shadow:0 7px 15px rgba(15,42,67,.12); }
    .hostel-bulk-btn-primary { background:#0b7f73; color:#fff; }
    .hostel-bulk-btn-danger { background:#c0394d; color:#fff; }
    .hostel-bulk-btn-light { background:#edf3f7; color:#26465e; }
    .hostel-bulk-btn:disabled { opacity:.55; cursor:not-allowed; transform:none; box-shadow:none; }
    .hostel-bulk-note { margin:15px 0 0; padding:12px 14px; border-radius:10px; background:#fff7e6; color:#765317; font-size:.9rem; line-height:1.5; }
    .hostel-bulk-summary { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
    .hostel-bulk-stat { border:1px solid #e7edf3; border-radius:14px; padding:15px; background:#fbfdff; }
    .hostel-bulk-stat strong { display:block; font-size:1.55rem; color:#174d68; }
    .hostel-bulk-stat span { color:#6a7c8b; font-size:.82rem; }
    .hostel-bulk-progress { display:none; margin-top:20px; }
    .hostel-bulk-progress.is-active { display:block; }
    .hostel-bulk-progress-row { display:flex; justify-content:space-between; gap:10px; font-size:.88rem; margin-bottom:7px; color:#506372; }
    .hostel-bulk-progress-track { height:14px; border-radius:20px; background:#e8eef3; overflow:hidden; }
    .hostel-bulk-progress-bar { height:100%; width:0; background:linear-gradient(90deg,#0b7f73,#17b6a4); transition:width .25s ease; }
    .hostel-bulk-status { margin-top:14px; font-size:.92rem; color:#557080; min-height:20px; }
    .hostel-bulk-list { margin-top:18px; border:1px solid #dcebe6; border-radius:12px; overflow:hidden; background:#fff; }
    .hostel-bulk-list-header { padding:12px 14px; background:#f6fbf9; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; }
    .hostel-bulk-list-header strong { color:#185b4d; }
    .hostel-bulk-list .table { margin:0; }
    .hostel-bulk-list .table th { white-space:nowrap; font-size:.72rem; text-transform:uppercase; color:#5c716f; background:#f8fbfa; }
    .hostel-bulk-list .table td { white-space:nowrap; color:#294459; vertical-align:middle; }
    .hostel-bulk-list-scroll { max-height:380px; overflow:auto; }
    .hostel-bulk-list-nav { display:flex; align-items:center; justify-content:flex-end; gap:8px; padding:10px 12px; border-top:1px solid #e5efeb; }
    .hostel-bulk-list-nav button { border:1px solid #cfe1db; background:#fff; color:#176a5a; border-radius:7px; padding:5px 10px; font-size:.8rem; }
    .hostel-bulk-list-nav button:disabled { opacity:.45; cursor:not-allowed; }
    @media (max-width: 850px) { .hostel-bulk-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width: 560px) { .hostel-bulk-page { padding:12px 9px 35px; } .hostel-bulk-hero { padding:20px 18px; border-radius:17px; } .hostel-bulk-card-body { padding:15px; } .hostel-bulk-grid,.hostel-bulk-summary { grid-template-columns:1fr; } .hostel-bulk-actions .hostel-bulk-btn { width:100%; } }
</style>

<div class="hostel-bulk-page" id="hostelBulkRevokePage"
     data-preview-url="{{ url('hostel bulk revoke/preview') }}"
     data-process-url="{{ url('hostel bulk revoke/process') }}">
    <section class="hostel-bulk-hero">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
            <div><h1>Bulk revoke hostel allocations</h1>
            <p>Safely release a group of occupied beds using location and payment filters. Each batch is processed in a transaction so the page stays responsive even with many records.</p></div>
            <a href="{{ url('hostel recipients') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to recipients</a>
        </div>
    </section>

    <section class="hostel-bulk-card">
        <div class="hostel-bulk-card-header"><h2>Choose the records to release</h2><span class="text-muted small">No changes are made during preview</span></div>
        <div class="hostel-bulk-card-body">
            <form id="hostelBulkRevokeForm">
                @csrf
                <div class="hostel-bulk-grid">
                    @foreach ([['hall','Hall',$halls],['block','Block',$blocks],['room','Room',$rooms],['bed','Bed',$beds]] as $filter)
                        <div class="hostel-bulk-field">
                            <label for="bulk-{{ $filter[0] }}">{{ $filter[1] }}</label>
                            <select id="bulk-{{ $filter[0] }}" name="{{ $filter[0] }}">
                                <option value="all">All {{ strtolower($filter[1]) }}s</option>
                                @foreach ($filter[2] as $value)<option value="{{ $value }}">{{ $value }}</option>@endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
                <div class="hostel-bulk-scope" aria-label="Payment scope">
                    <label><input type="radio" name="payment_scope" value="unpaid" checked> Unpaid allocations only</label>
                    <label><input type="radio" name="payment_scope" value="all"> All allocations, including paid</label>
                </div>
                <div class="hostel-bulk-note"><strong>What this does:</strong> the active occupant is removed, the payment status is cleared, the existing payment method is retained, the bed is set to Reserved (bed type 1), and invoices remain available for audit. Paid records are never silently deleted.</div>
                <div class="hostel-bulk-actions">
                    <button type="submit" class="hostel-bulk-btn hostel-bulk-btn-primary" id="bulkPreviewBtn"><i class="fas fa-search"></i> Preview matching allocations</button>
                    <button type="button" class="hostel-bulk-btn hostel-bulk-btn-light" id="bulkResetBtn">Reset filters</button>
                </div>
            </form>
        </div>
    </section>

    <section class="hostel-bulk-card" id="bulkResultCard" style="display:none">
        <div class="hostel-bulk-card-header"><h2>Preview</h2><span id="bulkPreviewLabel" class="text-muted small"></span></div>
        <div class="hostel-bulk-card-body">
            <div class="hostel-bulk-summary">
                <div class="hostel-bulk-stat"><strong id="bulkTotal">0</strong><span>Matching occupied beds</span></div>
                <div class="hostel-bulk-stat"><strong id="bulkUnpaid">0</strong><span>Currently unpaid</span></div>
                <div class="hostel-bulk-stat"><strong id="bulkPaid">0</strong><span>Currently paid</span></div>
            </div>
            <div class="hostel-bulk-actions">
                <button type="button" class="hostel-bulk-btn hostel-bulk-btn-danger" id="bulkStartBtn"><i class="fas fa-unlock-alt"></i> Revoke these allocations</button>
            </div>
            <div class="hostel-bulk-progress" id="bulkProgress">
                <div class="hostel-bulk-progress-row"><span id="bulkProgressText">Preparing…</span><strong id="bulkProgressPercent">0%</strong></div>
                <div class="hostel-bulk-progress-track"><div class="hostel-bulk-progress-bar" id="bulkProgressBar"></div></div>
                <div class="hostel-bulk-status" id="bulkStatus"></div>
            </div>
            <div id="bulkCompletion" class="hostel-bulk-note" style="display:none; margin-top:18px; background:#eefaf5; color:#185b4d;">
                <strong>Revocation completed</strong>
                <div id="bulkCompletionText" style="margin-top:5px;"></div>
                <button type="button" class="hostel-bulk-btn hostel-bulk-btn-primary" id="bulkExportBtn" style="margin-top:13px;"><i class="fas fa-file-csv"></i> Export revocation summary</button>
                <div id="bulkRevokedSection" class="hostel-bulk-list" style="display:none; text-align:left;">
                    <div class="hostel-bulk-list-header"><strong><i class="fas fa-list-check me-1"></i> Students and spaces released</strong><span id="bulkRevokedCount" class="small text-muted"></span></div>
                    <div class="hostel-bulk-list-scroll"><table class="table table-sm"><thead><tr><th>Student ID</th><th>Hall</th><th>Block</th><th>Room</th><th>Bed</th><th>Previous payment</th></tr></thead><tbody id="bulkRevokedBody"></tbody></table></div>
                    <div class="hostel-bulk-list-nav"><button type="button" id="bulkListPrev">Previous</button><span id="bulkListPage" class="small text-muted"></span><button type="button" id="bulkListNext">Next</button></div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
(function () {
    const root = document.getElementById('hostelBulkRevokePage');
    if (!root) return;
    const form = document.getElementById('hostelBulkRevokeForm');
    const previewBtn = document.getElementById('bulkPreviewBtn');
    const resetBtn = document.getElementById('bulkResetBtn');
    const resultCard = document.getElementById('bulkResultCard');
    const startBtn = document.getElementById('bulkStartBtn');
    const progress = document.getElementById('bulkProgress');
    const status = document.getElementById('bulkStatus');
    const completion = document.getElementById('bulkCompletion');
    const completionText = document.getElementById('bulkCompletionText');
    const exportBtn = document.getElementById('bulkExportBtn');
    const revokedSection = document.getElementById('bulkRevokedSection');
    const revokedCount = document.getElementById('bulkRevokedCount');
    const revokedBody = document.getElementById('bulkRevokedBody');
    const listPrev = document.getElementById('bulkListPrev');
    const listNext = document.getElementById('bulkListNext');
    const listPage = document.getElementById('bulkListPage');
    let preview = null;
    let revokedRecords = [];
    let revokedPage = 1;

    function csrf() { return form.querySelector('input[name="_token"]').value; }
    function payload(lastId) {
        const data = new FormData(form);
        if (lastId !== undefined) data.set('last_id', String(lastId));
        data.set('batch_size', '100');
        return data;
    }
    function setBusy(button, busy, label) { button.disabled = busy; if (busy) button.dataset.label = button.innerHTML; if (busy) button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + label; else if (button.dataset.label) button.innerHTML = button.dataset.label; }
    async function jsonRequest(url, body) {
        const response = await fetch(url, { method:'POST', body, headers:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'} });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.message || 'The request could not be completed.');
        return data;
    }
    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        setBusy(previewBtn, true, 'Checking records…');
        try {
            preview = await jsonRequest(root.dataset.previewUrl, payload());
            document.getElementById('bulkTotal').textContent = preview.total.toLocaleString();
            document.getElementById('bulkPaid').textContent = preview.paid.toLocaleString();
            document.getElementById('bulkUnpaid').textContent = preview.unpaid.toLocaleString();
            document.getElementById('bulkPreviewLabel').textContent = preview.total ? 'Review the count before continuing' : 'No matching active allocations';
            resultCard.style.display = '';
            resultCard.scrollIntoView({behavior:'smooth', block:'start'});
            startBtn.disabled = preview.total < 1;
        } catch (error) { alert(error.message); }
        finally { setBusy(previewBtn, false); }
    });
    function renderRevokedPage() {
        const pageSize = 100;
        const totalPages = Math.max(1, Math.ceil(revokedRecords.length / pageSize));
        revokedPage = Math.max(1, Math.min(revokedPage, totalPages));
        const start = (revokedPage - 1) * pageSize;
        revokedBody.innerHTML = '';
        revokedRecords.slice(start, start + pageSize).forEach(function (row) {
            const tr = document.createElement('tr');
            [row.occupant, row.hall, row.block, row.room, row.bed, row.previous_payment].forEach(function (value) {
                const td = document.createElement('td'); td.textContent = value || '—'; tr.appendChild(td);
            });
            revokedBody.appendChild(tr);
        });
        revokedCount.textContent = revokedRecords.length.toLocaleString() + ' record(s)';
        listPage.textContent = 'Page ' + revokedPage + ' of ' + totalPages;
        listPrev.disabled = revokedPage <= 1; listNext.disabled = revokedPage >= totalPages;
    }
    listPrev.addEventListener('click', function () { if (revokedPage > 1) { revokedPage--; renderRevokedPage(); } });
    listNext.addEventListener('click', function () { const pages = Math.ceil(revokedRecords.length / 100); if (revokedPage < pages) { revokedPage++; renderRevokedPage(); } });
    resetBtn.addEventListener('click', function () { form.reset(); resultCard.style.display='none'; preview=null; revokedRecords=[]; revokedPage=1; completion.style.display='none'; revokedSection.style.display='none'; });
    startBtn.addEventListener('click', async function () {
        if (!preview || !preview.total) return;
        if (!window.confirm('Revoke ' + preview.total.toLocaleString() + ' allocation(s)? Their active payment flag will be cleared and the beds will return to Reserved (bed type 1). Existing invoices will remain for audit.')) return;
        setBusy(startBtn, true, 'Revoking…');
        previewBtn.disabled = true; resetBtn.disabled = true; progress.classList.add('is-active');
        let lastId = 0, processed = 0;
        revokedRecords = [];
        try {
            while (true) {
                const result = await jsonRequest(root.dataset.processUrl, payload(lastId));
                processed += Number(result.processed || 0);
                revokedRecords = revokedRecords.concat(Array.isArray(result.items) ? result.items : []);
                lastId = Number(result.last_id || lastId);
                const percent = preview.total ? Math.min(100, Math.round((processed / preview.total) * 100)) : 100;
                document.getElementById('bulkProgressBar').style.width = percent + '%';
                document.getElementById('bulkProgressPercent').textContent = percent + '%';
                document.getElementById('bulkProgressText').textContent = processed.toLocaleString() + ' of ' + preview.total.toLocaleString() + ' processed';
                if (result.done) break;
            }
            status.textContent = 'Completed successfully. ' + processed.toLocaleString() + ' allocation(s) were revoked. You can return to Hostel Recipients to confirm the updated list.';
            status.style.color = '#16705e';
            const paid = revokedRecords.filter(row => row.previous_payment === 'Confirmed').length;
            const unpaid = revokedRecords.length - paid;
            const locations = new Set(revokedRecords.map(row => row.hall).filter(Boolean)).size;
            completionText.textContent = processed.toLocaleString() + ' allocation(s) were released; ' + paid.toLocaleString() + ' previously paid and ' + unpaid.toLocaleString() + ' previously pending. ' + locations.toLocaleString() + ' hall(s) were affected. Payment methods and invoice history were preserved.';
            completion.style.display = '';
            revokedPage = 1;
            renderRevokedPage();
            revokedSection.style.display = revokedRecords.length ? '' : 'none';
            startBtn.disabled = true;
        } catch (error) { status.textContent = error.message; status.style.color = '#b4233c'; }
        finally { setBusy(startBtn, false); previewBtn.disabled = false; resetBtn.disabled = false; }
    });
    function csvCell(value) { return '"' + String(value ?? '').replace(/"/g, '""') + '"'; }
    exportBtn.addEventListener('click', function () {
        if (!revokedRecords.length) return;
        const headers = ['Allocation ID','Student ID','Hall','Block','Room','Bed','Previous payment status','Payment method','Result'];
        const lines = [headers.map(csvCell).join(',')].concat(revokedRecords.map(row => [row.id,row.occupant,row.hall,row.block,row.room,row.bed,row.previous_payment,row.payment_method,row.result].map(csvCell).join(',')));
        const blob = new Blob(['\ufeff' + lines.join('\r\n')], {type:'text/csv;charset=utf-8;'});
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'hostel-revocation-summary-' + new Date().toISOString().slice(0,10) + '.csv';
        document.body.appendChild(link); link.click(); link.remove(); URL.revokeObjectURL(link.href);
    });
})();
</script>
