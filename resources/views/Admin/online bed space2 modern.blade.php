@php
    $halls = collect($halls ?? []);
    $blocks = collect($blocks ?? []);
    $rooms = collect($rooms ?? []);
    $beds = collect($beds ?? []);
@endphp

<style>
    .obs-page { --obs-blue:#168acb; --obs-ink:#18324b; --obs-muted:#6e8190; --obs-line:#e2ebf1; background:#f4f8fb; }
    .obs-page .obs-hero { color:#fff; border-radius:18px; padding:1.35rem 1.5rem; background:linear-gradient(120deg,#0c6fa6,#21a6dd); box-shadow:0 12px 28px rgba(20,100,150,.16); }
    .obs-page .obs-hero h3 { margin:0 0 .35rem; color:#fff; font-weight:800; }
    .obs-page .obs-hero p { margin:0; color:rgba(255,255,255,.86); line-height:1.55; }
    .obs-page .obs-hero-icon { width:54px; height:54px; display:grid; place-items:center; border-radius:15px; color:#fff; background:rgba(255,255,255,.16); font-size:1.4rem; }
    .obs-page .obs-card { border:1px solid var(--obs-line); border-radius:16px; background:#fff; box-shadow:0 7px 24px rgba(25,67,95,.055); }
    .obs-page .obs-stat { height:100%; padding:1rem; border:1px solid var(--obs-line); border-radius:13px; background:#fff; }
    .obs-page .obs-stat strong { display:block; color:var(--obs-ink); font-size:1.45rem; line-height:1.1; }
    .obs-page .obs-stat small { display:block; margin-top:.3rem; color:var(--obs-muted); }
    .obs-page .obs-stat i { display:grid; width:35px; height:35px; place-items:center; margin-bottom:.65rem; border-radius:10px; color:#0a78b6; background:#e4f4fc; }
    .obs-page .obs-stat.green i { color:#167d53; background:#e5f7ef; }
    .obs-page .obs-stat.amber i { color:#9c6804; background:#fff4d9; }
    .obs-page .obs-stat.red i { color:#a34852; background:#ffebed; }
    .obs-page .form-label { color:#476174; font-size:.73rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
    .obs-page .form-control, .obs-page .form-select { min-height:42px; border-color:#d8e5ed; border-radius:10px; }
    .obs-page .form-control:focus, .obs-page .form-select:focus { border-color:var(--obs-blue); box-shadow:0 0 0 .2rem rgba(22,138,203,.13); }
    .obs-page .obs-table-wrap { overflow:auto; }
    .obs-page .table { min-width:980px; margin:0; }
    .obs-page .table thead th { white-space:nowrap; color:#567082; background:#f5f9fc; border-bottom:1px solid var(--obs-line); font-size:.7rem; letter-spacing:.04em; text-transform:uppercase; }
    .obs-page .table tbody td { color:#294459; vertical-align:middle; white-space:nowrap; }
    .obs-page .table tbody tr:hover { background:#f8fcfe; }
    .obs-page .obs-pill { display:inline-flex; align-items:center; gap:.3rem; border-radius:999px; padding:.3rem .58rem; font-size:.72rem; font-weight:800; }
    .obs-page .obs-pill.available { color:#177b51; background:#e4f7ed; }
    .obs-page .obs-pill.occupied { color:#9a5f00; background:#fff3d8; }
    .obs-page .obs-pill.flagged { color:#a34852; background:#ffebed; }
    .obs-page .obs-pill.unflagged { color:#176a94; background:#e7f4fb; }
    .obs-page .obs-pagination .page-link { border-color:#dce7ee; border-radius:8px !important; color:#176a94; margin-left:.2rem; }
    .obs-page .obs-pagination .page-item.active .page-link { color:#fff; border-color:var(--obs-blue); background:var(--obs-blue); }
    .obs-page .obs-pagination .page-item.disabled .page-link { color:#aab8c1; background:#f7fafc; }
    .obs-page .obs-notice { display:none; border-radius:10px; padding:.7rem .9rem; font-size:.86rem; }
    .obs-page .obs-notice.show { display:block; }
    .obs-page .obs-notice.success { color:#176b49; background:#e7f7ef; }
    .obs-page .obs-notice.error { color:#9b3e49; background:#ffebed; }
    .obs-page .obs-check { width:18px; height:18px; cursor:pointer; }
    @media (max-width:767.98px) {
        .obs-page .obs-hero { padding:1.1rem; border-radius:14px; }
        .obs-page .obs-hero h3 { font-size:1.15rem; }
        .obs-page .obs-stat strong { font-size:1.2rem; }
        .obs-page .obs-card-header { align-items:flex-start !important; flex-direction:column; }
    }
</style>

<div class="main-body obs-page">
    <div class="page-wrapper">
        <div class="row g-3">
            <div class="col-12">
                <section class="obs-hero d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <h3><i class="fas fa-eye-slash me-2"></i>Online bed-space controls</h3>
                        <p>Review the online spaces in the four managed hostels, then flag or unflag one or more spaces without leaving this page.</p>
                    </div>
                    <div class="obs-hero-icon d-none d-sm-grid"><i class="fas fa-bed"></i></div>
                </section>
            </div>

            <div class="col-12">
                <div class="row g-3">
                    <div class="col-6 col-lg-3"><div class="obs-stat"><i class="fas fa-layer-group"></i><strong id="obsStatTotal">0</strong><small>Matching spaces</small></div></div>
                    <div class="col-6 col-lg-3"><div class="obs-stat green"><i class="fas fa-circle-check"></i><strong id="obsStatAvailable">0</strong><small>Available</small></div></div>
                    <div class="col-6 col-lg-3"><div class="obs-stat amber"><i class="fas fa-user-check"></i><strong id="obsStatOccupied">0</strong><small>Occupied</small></div></div>
                    <div class="col-6 col-lg-3"><div class="obs-stat red"><i class="fas fa-flag"></i><strong id="obsStatFlagged">0</strong><small>Flagged</small></div></div>
                </div>
            </div>

            <div class="col-12">
                <div class="obs-card p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3 obs-card-header">
                        <div><h5 class="mb-1" style="color:#18324b">Find online spaces</h5><small class="text-muted">All blocks are included. Use any combination of filters.</small></div>
                        <button type="button" class="btn btn-light border btn-sm" id="obsReset"><i class="fas fa-rotate-left me-1"></i>Reset</button>
                    </div>
                    <form id="obsFilters">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-6 col-lg-3"><label class="form-label" for="obsSearch">Search</label><input class="form-control" id="obsSearch" name="search" placeholder="Hall, block, room, bed or occupant" autocomplete="off"></div>
                            <div class="col-12 col-md-6 col-lg-2"><label class="form-label" for="obsHall">Hall</label><select class="form-select" id="obsHall" name="hall"><option value="all">All halls</option>@foreach($halls as $hall)<option value="{{ $hall }}">{{ $hall }}</option>@endforeach</select></div>
                            <div class="col-12 col-md-6 col-lg-2"><label class="form-label" for="obsBlock">Block</label><select class="form-select" id="obsBlock" name="block"><option value="all">All blocks</option>@foreach($blocks as $block)<option value="{{ $block }}">{{ $block }}</option>@endforeach</select></div>
                            <div class="col-6 col-md-3 col-lg-1"><label class="form-label" for="obsRoom">Room</label><select class="form-select" id="obsRoom" name="room"><option value="all">All</option>@foreach($rooms as $room)<option value="{{ $room }}">{{ $room }}</option>@endforeach</select></div>
                            <div class="col-6 col-md-3 col-lg-1"><label class="form-label" for="obsBed">Bed</label><select class="form-select" id="obsBed" name="bed"><option value="all">All</option>@foreach($beds as $bed)<option value="{{ $bed }}">{{ $bed }}</option>@endforeach</select></div>
                            <div class="col-12 col-md-6 col-lg-2"><label class="form-label" for="obsStatus">Occupancy</label><select class="form-select" id="obsStatus" name="status"><option value="all">All spaces</option><option value="available">Available</option><option value="occupied">Occupied</option></select></div>
                            <div class="col-12 col-md-6 col-lg-2"><label class="form-label" for="obsFlag">Flag status</label><select class="form-select" id="obsFlag" name="flag"><option value="all">Flagged and unflagged</option><option value="unflagged">Unflagged only</option><option value="flagged">Flagged only</option></select></div>
                            <div class="col-12 col-md-6 col-lg-1"><button type="submit" class="btn btn-primary w-100" id="obsApply"><i class="fas fa-search me-1"></i>Find</button></div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-12">
                <div class="obs-card">
                    <div class="p-3 d-flex align-items-center justify-content-between gap-3 flex-wrap">
                        <div><h5 class="mb-1" style="color:#18324b">Online spaces</h5><small class="text-muted" id="obsRange">Loading…</small></div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-danger btn-sm" id="obsFlagSelected"><i class="fas fa-flag me-1"></i>Flag selected</button>
                            <button type="button" class="btn btn-outline-success btn-sm" id="obsUnflagSelected"><i class="fas fa-flag-checkered me-1"></i>Unflag selected</button>
                        </div>
                    </div>
                    <div class="obs-notice mx-3 mb-3" id="obsNotice" role="status"></div>
                    <div class="obs-table-wrap">
                        <table class="table table-hover align-middle" id="obsTable">
                            <thead><tr><th><input class="obs-check" type="checkbox" id="obsSelectAll" aria-label="Select all visible spaces"></th><th>#</th><th>Hall</th><th>Block</th><th>Room</th><th>Bed</th><th>Payment</th><th>Occupancy</th><th>Flag status</th><th>Action</th></tr></thead>
                            <tbody id="obsRows"><tr><td colspan="10" class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading spaces…</td></tr></tbody>
                        </table>
                    </div>
                    <div class="obs-pagination d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 p-3 border-top">
                        <small class="text-muted" id="obsPaginationLabel"></small>
                        <nav aria-label="Online spaces pages"><ul class="pagination pagination-sm mb-0" id="obsPagination"></ul></nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const root = document.querySelector('.obs-page');
    if (!root) return;
    const csrf = root.closest('body')?.querySelector('input[name="_token"]')?.value || @json(csrf_token());
    const endpoint = @json(url('obs/data'));
    const flagEndpoint = @json(url('obs/flag'));
    const unflagEndpoint = @json(url('obs/unflag'));
    const state = { page: 1, loading: false };
    const $ = (id) => document.getElementById(id);

    function filters() {
        const params = new URLSearchParams();
        ['search','hall','block','room','bed','status','flag'].forEach((name) => {
            const element = $('obs' + name.charAt(0).toUpperCase() + name.slice(1));
            if (element && element.value && element.value !== 'all') params.set(name, element.value);
        });
        params.set('per_page', '50');
        params.set('page', String(state.page));
        return params;
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
    }

    function showNotice(message, type = 'success') {
        const notice = $('obsNotice');
        notice.textContent = message;
        notice.className = 'obs-notice mx-3 mb-3 show ' + type;
        window.setTimeout(() => notice.classList.remove('show'), 4500);
    }

    function setStats(stats) {
        $('obsStatTotal').textContent = Number(stats.total || 0).toLocaleString();
        $('obsStatAvailable').textContent = Number(stats.available || 0).toLocaleString();
        $('obsStatOccupied').textContent = Number(stats.occupied || 0).toLocaleString();
        $('obsStatFlagged').textContent = Number(stats.flagged || 0).toLocaleString();
    }

    function renderPagination(meta) {
        const pagination = $('obsPagination');
        pagination.innerHTML = '';
        const add = (label, page, disabled = false, active = false) => {
            const li = document.createElement('li');
            li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
            const button = document.createElement('button');
            button.type = 'button'; button.className = 'page-link'; button.textContent = label;
            button.disabled = disabled;
            if (!disabled && !active) button.addEventListener('click', () => { state.page = page; load(); });
            li.appendChild(button); pagination.appendChild(li);
        };
        add('Previous', Math.max(1, meta.current_page - 1), meta.current_page <= 1);
        const start = Math.max(1, meta.current_page - 2), end = Math.min(meta.last_page, meta.current_page + 2);
        for (let page = start; page <= end; page++) add(String(page), page, false, page === meta.current_page);
        add('Next', Math.min(meta.last_page, meta.current_page + 1), meta.current_page >= meta.last_page);
        $('obsPaginationLabel').textContent = meta.total ? 'Showing ' + meta.from + '–' + meta.to + ' of ' + meta.total + ' spaces' : 'No spaces match these filters';
    }

    function renderRows(rows, meta) {
        const tbody = $('obsRows');
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center py-5 text-muted"><i class="fas fa-bed d-block mb-2" style="font-size:1.8rem"></i>No online spaces match these filters.</td></tr>';
            $('obsSelectAll').checked = false;
            return;
        }
        tbody.innerHTML = rows.map((row, index) => {
            const number = ((meta.current_page - 1) * meta.per_page) + index + 1;
            const occupied = Number(row.status) === 1;
            const flagged = Number(row.flag) === 1;
            return '<tr>'
                + '<td><input class="obs-check obs-row-check" type="checkbox" value="' + row.id + '" aria-label="Select ' + escapeHtml(row.hall) + ' room ' + escapeHtml(row.room) + ' bed ' + escapeHtml(row.bed) + '"></td>'
                + '<td>' + number + '</td>'
                + '<td><strong>' + escapeHtml(row.hall) + '</strong></td>'
                + '<td>' + escapeHtml(row.block || '—') + '</td>'
                + '<td>' + escapeHtml(row.room || '—') + '</td>'
                + '<td>' + escapeHtml(row.bed || '—') + '</td>'
                + '<td><span class="obs-pill unflagged"><i class="fas fa-globe"></i>Online</span></td>'
                + '<td><span class="obs-pill ' + (occupied ? 'occupied' : 'available') + '"><i class="fas ' + (occupied ? 'fa-user-check' : 'fa-circle-check') + '"></i>' + (occupied ? 'Occupied' : 'Available') + '</span><small class="d-block text-muted mt-1">' + escapeHtml(row.occupant || 'vacant') + '</small></td>'
                + '<td><span class="obs-pill ' + (flagged ? 'flagged' : 'unflagged') + '"><i class="fas ' + (flagged ? 'fa-flag' : 'fa-flag-checkered') + '"></i>' + (flagged ? 'Flagged' : 'Unflagged') + '</span></td>'
                + '<td><button type="button" class="btn btn-sm ' + (flagged ? 'btn-outline-success' : 'btn-outline-danger') + ' obs-row-action" data-id="' + row.id + '" data-flag="' + (flagged ? '0' : '1') + '"><i class="fas ' + (flagged ? 'fa-flag-checkered' : 'fa-flag') + ' me-1"></i>' + (flagged ? 'Unflag' : 'Flag') + '</button></td>'
                + '</tr>';
        }).join('');
        $('obsSelectAll').checked = false;
    }

    async function load() {
        if (state.loading) return;
        state.loading = true;
        $('obsApply').disabled = true;
        $('obsApply').innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading';
        $('obsRows').innerHTML = '<tr><td colspan="10" class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading spaces…</td></tr>';
        try {
            const response = await fetch(endpoint + '?' + filters().toString(), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }});
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'The spaces could not be loaded.');
            setStats(payload.stats || {});
            renderRows(payload.data || [], payload.meta || { current_page:1, last_page:1, per_page:50, total:0, from:0, to:0 });
            renderPagination(payload.meta || { current_page:1, last_page:1, per_page:50, total:0, from:0, to:0 });
        } catch (error) {
            $('obsRows').innerHTML = '<tr><td colspan="10" class="text-center py-5 text-danger">' + escapeHtml(error.message) + '</td></tr>';
            showNotice(error.message, 'error');
        } finally {
            state.loading = false;
            $('obsApply').disabled = false;
            $('obsApply').innerHTML = '<i class="fas fa-search me-1"></i>Find';
        }
    }

    function selectedIds() {
        return Array.from(root.querySelectorAll('.obs-row-check:checked')).map((input) => Number(input.value)).filter(Boolean);
    }

    async function updateFlags(ids, action) {
        if (!ids.length) { showNotice('Select at least one bed space first.', 'error'); return; }
        const button = action === 'flag' ? $('obsFlagSelected') : $('obsUnflagSelected');
        const original = button.innerHTML;
        button.disabled = true; button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving';
        try {
            const response = await fetch(action === 'flag' ? flagEndpoint : unflagEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ ids })
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'The update could not be completed.');
            showNotice(payload.message || 'Updated successfully.');
            await load();
        } catch (error) {
            showNotice(error.message, 'error');
        } finally {
            button.disabled = false; button.innerHTML = original;
        }
    }

    $('obsFilters').addEventListener('submit', (event) => { event.preventDefault(); state.page = 1; load(); });
    $('obsReset').addEventListener('click', () => {
        $('obsFilters').reset();
        ['obsHall','obsBlock','obsRoom','obsBed','obsStatus','obsFlag'].forEach((id) => { if ($(id)) $(id).value = 'all'; });
        state.page = 1; load();
    });
    $('obsSelectAll').addEventListener('change', (event) => root.querySelectorAll('.obs-row-check').forEach((input) => { input.checked = event.target.checked; }));
    $('obsFlagSelected').addEventListener('click', () => updateFlags(selectedIds(), 'flag'));
    $('obsUnflagSelected').addEventListener('click', () => updateFlags(selectedIds(), 'unflag'));
    $('obsRows').addEventListener('click', (event) => {
        const button = event.target.closest('.obs-row-action');
        if (button) updateFlags([Number(button.dataset.id)], button.dataset.flag === '1' ? 'flag' : 'unflag');
    });
    load();
})();
</script>
