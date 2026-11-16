@php
    $admissionSummary = session('admission_upload_summary');
    $summaryDetails = collect($admissionSummary['details'] ?? [])->take(250);
    $oldFaculty = old('faculty');
    $oldDepartment = old('department');
    $oldProgram = old('program');
@endphp

<style>
    .admit-upload-page { --au-ink:#17324d; --au-muted:#6f8395; --au-line:#dce8f0; --au-blue:#1478b8; --au-blue-dark:#0b4f83; --au-soft:#eef7fc; --au-green:#168456; --au-amber:#a56805; --au-red:#b23a43; padding:24px 18px 42px; background:linear-gradient(180deg,#f4f9fc 0,#fff 38%); min-height:calc(100vh - 110px); }
    .admit-upload-shell { max-width:1180px; margin:0 auto; }
    .admit-hero { position:relative; overflow:hidden; border-radius:22px; padding:28px 30px; color:#fff; background:linear-gradient(120deg,#0d5388 0%,#1688c3 58%,#27a2d1 100%); box-shadow:0 18px 42px rgba(12,77,121,.2); }
    .admit-hero:after { content:""; position:absolute; width:260px; height:260px; right:-85px; top:-120px; border:34px solid rgba(255,255,255,.1); border-radius:50%; }
    .admit-hero-content { position:relative; z-index:1; max-width:780px; }
    .admit-kicker { display:inline-flex; align-items:center; gap:8px; margin-bottom:10px; color:#d9f3ff; font-size:.72rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
    .admit-hero h1 { margin:0 0 8px; color:#fff; font-size:clamp(1.45rem,3vw,2.15rem); font-weight:800; }
    .admit-hero p { max-width:720px; margin:0; color:#e5f5fc; line-height:1.55; }
    .admit-back { display:inline-flex; align-items:center; gap:7px; margin-bottom:18px; color:#246486; font-weight:700; text-decoration:none; }
    .admit-back:hover { color:#0b4f83; }
    .admit-card { margin-top:18px; border:1px solid var(--au-line); border-radius:18px; background:#fff; box-shadow:0 10px 28px rgba(31,76,107,.08); }
    .admit-card-head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px 24px; border-bottom:1px solid #edf3f7; }
    .admit-card-head h2 { margin:0; color:var(--au-ink); font-size:1.05rem; font-weight:800; }
    .admit-card-head p { margin:5px 0 0; color:var(--au-muted); font-size:.82rem; }
    .admit-step { display:inline-flex; align-items:center; gap:7px; color:#557187; font-size:.75rem; font-weight:700; white-space:nowrap; }
    .admit-step span { display:grid; place-items:center; width:25px; height:25px; border-radius:50%; color:#fff; background:var(--au-blue); font-size:.72rem; }
    .admit-card-body { padding:24px; }
    .admit-label { display:block; margin:0 0 7px; color:#294c64; font-size:.78rem; font-weight:800; }
    .admit-field { min-height:46px; border:1px solid #cfdfe9; border-radius:11px; color:#213f55; background:#fff; }
    .admit-field:focus { border-color:#4fa8d5; box-shadow:0 0 0 .2rem rgba(25,129,185,.12); }
    .admit-help { margin:7px 0 0; color:#8397a6; font-size:.72rem; line-height:1.45; }
    .admit-note { display:flex; gap:11px; align-items:flex-start; margin-bottom:22px; padding:13px 15px; border:1px solid #cbe5f3; border-radius:12px; color:#285b78; background:var(--au-soft); font-size:.8rem; line-height:1.5; }
    .admit-note i { margin-top:2px; color:var(--au-blue); }
    .admit-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:24px; }
    .admit-primary { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:44px; padding:0 19px; border:0; border-radius:10px; color:#fff !important; background:linear-gradient(120deg,#0c6da7,#1593c8); font-weight:800; box-shadow:0 7px 16px rgba(15,117,174,.2); }
    .admit-primary:hover { color:#fff !important; background:linear-gradient(120deg,#095c8e,#0f80ae); }
    .admit-secondary { display:inline-flex; align-items:center; justify-content:center; min-height:44px; padding:0 17px; border:1px solid #c8dce8; border-radius:10px; color:#3c6176 !important; background:#fff; font-weight:700; text-decoration:none; }
    .admit-progress { display:none; margin-top:20px; padding:15px 16px; border:1px solid #cfe5f1; border-radius:12px; background:#f5fbfe; }
    .admit-progress.is-visible { display:block; }
    .admit-progress-line { display:flex; align-items:center; gap:10px; color:#245d79; font-size:.78rem; font-weight:700; }
    .admit-progress-line .spinner-border { width:1rem; height:1rem; border-width:2px; }
    .admit-progress-track { height:7px; margin-top:11px; overflow:hidden; border-radius:99px; background:#dbeaf2; }
    .admit-progress-track span { display:block; width:38%; height:100%; border-radius:inherit; background:linear-gradient(90deg,#1580bb,#6bd4ec); animation:admit-progress 1.2s ease-in-out infinite; }
    @keyframes admit-progress { 0%{transform:translateX(-110%)} 100%{transform:translateX(290%)} }
    .admit-alert { margin-bottom:18px; padding:13px 15px; border-radius:11px; font-size:.8rem; }
    .admit-alert-danger { color:#8b3039; border:1px solid #f0cbd0; background:#fff4f5; }
    .admit-report { overflow:hidden; }
    .admit-report-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:20px 24px; color:#fff; background:linear-gradient(120deg,#123e61,#176f9c); }
    .admit-report-head h2 { margin:0; color:#fff; font-size:1.05rem; font-weight:800; }
    .admit-report-head p { margin:5px 0 0; color:#d5edf8; font-size:.78rem; }
    .admit-report-head .badge { padding:7px 10px; color:#145d3f; background:#d9f7e9; font-size:.7rem; }
    .admit-path { display:flex; flex-wrap:wrap; gap:7px; padding:16px 24px 0; }
    .admit-path span { padding:7px 10px; border:1px solid #d9e8ef; border-radius:999px; color:#416276; background:#f6fafc; font-size:.72rem; font-weight:700; }
    .admit-stats { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:11px; padding:17px 24px 21px; }
    .admit-stat { padding:14px; border:1px solid #e1ebf1; border-radius:13px; background:#fbfdfe; }
    .admit-stat strong { display:block; color:var(--au-ink); font-size:1.35rem; line-height:1; }
    .admit-stat small { display:block; margin-top:7px; color:#718899; font-size:.7rem; font-weight:700; }
    .admit-stat.success strong { color:var(--au-green); }.admit-stat.info strong { color:var(--au-blue); }.admit-stat.warn strong { color:var(--au-amber); }.admit-stat.danger strong { color:var(--au-red); }
    .admit-details { padding:0 24px 24px; }
    .admit-details h3 { margin:0 0 11px; color:var(--au-ink); font-size:.88rem; font-weight:800; }
    .admit-table-wrap { overflow:auto; border:1px solid #e1ebf1; border-radius:12px; }
    .admit-table { width:100%; min-width:650px; margin:0; border-collapse:collapse; }
    .admit-table th { padding:11px 12px; color:#5a7385; background:#f3f8fb; font-size:.68rem; letter-spacing:.04em; text-align:left; text-transform:uppercase; white-space:nowrap; }
    .admit-table td { padding:11px 12px; border-top:1px solid #edf2f5; color:#36566b; font-size:.76rem; vertical-align:top; }
    .admit-empty { padding:18px; color:#718899; font-size:.78rem; text-align:center; }
    @media (max-width:760px) { .admit-upload-page{padding:16px 10px 30px}.admit-hero{padding:23px 20px;border-radius:18px}.admit-card-head{display:block;padding:18px}.admit-step{margin-top:12px}.admit-card-body{padding:18px}.admit-actions{display:grid;grid-template-columns:1fr}.admit-secondary,.admit-primary{width:100%}.admit-report-head{display:block;padding:18px}.admit-report-head .badge{display:inline-block;margin-top:12px}.admit-path{padding:14px 18px 0}.admit-stats{grid-template-columns:repeat(2,minmax(0,1fr));padding:15px 18px 19px}.admit-stat:last-child{grid-column:1/-1}.admit-details{padding:0 18px 18px} }
</style>

<div class="admit-upload-page">
    <div class="admit-upload-shell">
        <a class="admit-back" href="{{ url('applicants') }}"><i class="fas fa-arrow-left"></i> Back to applicants</a>

        <section class="admit-hero">
            <div class="admit-hero-content">
                <div class="admit-kicker"><i class="fas fa-file-import"></i> Admission processing</div>
                <h1>Admit applicants from a published list</h1>
                <p>Choose the academic placement once, upload the official workbook, and review exactly what was admitted, already admitted, or left unchanged.</p>
            </div>
        </section>

        @if ($errors->any())
            <div class="admit-card admit-alert admit-alert-danger">
                <strong><i class="fas fa-circle-exclamation me-1"></i> Please correct the highlighted items.</strong>
                <ul class="mb-0 mt-2 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="admit-card">
            <div class="admit-card-head">
                <div><h2>Prepare the admission upload</h2><p>The workbook is matched by JAMB number. The selected faculty, department and programme will be applied to every matched row.</p></div>
                <div class="admit-step"><span>1</span> Select and upload</div>
            </div>
            <div class="admit-card-body">
                <div class="admit-note"><i class="fas fa-shield-halved"></i><div><strong>Safe matching</strong><br>Only applicants found by their JAMB number are changed. Pending, submitted and previously rejected records are admitted; existing admitted records keep their admission details while their academic placement is refreshed.</div></div>
                <form id="admitUploadForm" method="POST" action="{{ route('admin.admit-student-upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-lg-4"><label class="admit-label" for="admitFaculty">Faculty / College <span class="text-danger">*</span></label><select class="form-select admit-field" id="admitFaculty" name="faculty" required data-selected="{{ $oldFaculty }}"><option value="">Select faculty / college</option>@foreach ($faculty as $row)<option value="{{ $row->code }}" {{ $oldFaculty === $row->code ? 'selected' : '' }}>{{ $row->title }}</option>@endforeach</select><p class="admit-help">The academic entity for everyone in this workbook.</p></div>
                        <div class="col-lg-4"><label class="admit-label" for="admitDepartment">Department <span class="text-danger">*</span></label><select class="form-select admit-field" id="admitDepartment" name="department" required data-selected="{{ $oldDepartment }}"><option value="">Select faculty first</option></select><p class="admit-help">Only departments belonging to the selected faculty are shown.</p></div>
                        <div class="col-lg-4"><label class="admit-label" for="admitProgram">Programme <span class="text-danger">*</span></label><select class="form-select admit-field" id="admitProgram" name="program" required data-selected="{{ $oldProgram }}"><option value="">Select department first</option></select><p class="admit-help">Only programmes belonging to the selected department are shown.</p></div>
                    </div>
                    <div class="row g-3 align-items-end mt-1"><div class="col-lg-8"><label class="admit-label" for="admitFile">Admission workbook (.xlsx or .xls) <span class="text-danger">*</span></label><input class="form-control admit-field" type="file" id="admitFile" name="file" accept=".xlsx,.xls" required><p class="admit-help">Use the official admission list. The JAMB number may appear in any column, as long as it is present in each row.</p></div><div class="col-lg-4"><a class="admit-secondary w-100" href="{{ url('applicants') }}"><i class="fas fa-arrow-left me-2"></i>Return without uploading</a></div></div>
                    <div class="admit-actions"><button class="admit-primary" id="admitSubmit" type="submit"><i class="fas fa-file-circle-check"></i> Process admission list</button></div>
                    <div class="admit-progress" id="admitProgress" aria-live="polite"><div class="admit-progress-line"><span class="spinner-border text-primary" role="status" aria-hidden="true"></span><span id="admitProgressText">Uploading and checking JAMB numbers…</span></div><div class="admit-progress-track"><span></span></div></div>
                </form>
            </div>
        </section>

        @if ($admissionSummary)
            <section class="admit-card admit-report">
                <div class="admit-report-head"><div><h2><i class="fas fa-chart-column me-2"></i>Admission processing report</h2><p>The workbook has finished processing. Review the totals and any rows that were not changed.</p></div><span class="badge rounded-pill">Completed</span></div>
                @if (!empty($admissionSummary['academic']))<div class="admit-path"><span><i class="fas fa-building-columns me-1"></i>{{ $admissionSummary['academic']['faculty'] }}</span><span><i class="fas fa-sitemap me-1"></i>{{ $admissionSummary['academic']['department'] }}</span><span><i class="fas fa-graduation-cap me-1"></i>{{ $admissionSummary['academic']['program'] }}</span></div>@endif
                <div class="admit-stats"><div class="admit-stat info"><strong>{{ number_format((int) ($admissionSummary['checked'] ?? 0)) }}</strong><small>Rows checked</small></div><div class="admit-stat success"><strong>{{ number_format((int) ($admissionSummary['admitted'] ?? 0)) }}</strong><small>Newly admitted</small></div><div class="admit-stat info"><strong>{{ number_format((int) ($admissionSummary['already_admitted'] ?? 0)) }}</strong><small>Already admitted</small></div><div class="admit-stat warn"><strong>{{ number_format((int) ($admissionSummary['not_found'] ?? 0)) }}</strong><small>JAMB not found</small></div><div class="admit-stat danger"><strong>{{ number_format((int) (($admissionSummary['skipped'] ?? 0) + ($admissionSummary['invalid'] ?? 0))) }}</strong><small>Not changed</small></div></div>
                <div class="admit-details">
                    <h3>Rows requiring attention</h3>
                    @if ($summaryDetails->isEmpty())
                        <div class="admit-empty">Every checked row was matched and processed successfully.</div>
                    @else
                        <div class="admit-table-wrap">
                            <table class="admit-table">
                                <thead><tr><th>Workbook row</th><th>JAMB number</th><th>What happened</th></tr></thead>
                                <tbody>
                                    @foreach ($summaryDetails as $detail)
                                        <tr><td>{{ $detail['row'] ?? '—' }}</td><td>{{ $detail['jamb'] ?? '—' }}</td><td>{{ $detail['reason'] ?? 'No change was made.' }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if (count($admissionSummary['details'] ?? []) > 250)
                            <p class="admit-help mt-2">Showing the first 250 attention rows out of {{ count($admissionSummary['details']) }}.</p>
                        @endif
                    @endif
                </div>
            </section>
        @endif
    </div>
</div>

<script>
(() => {
    const form = document.getElementById('admitUploadForm');
    if (!form) return;
    const faculty = document.getElementById('admitFaculty');
    const department = document.getElementById('admitDepartment');
    const program = document.getElementById('admitProgram');
    const token = form.querySelector('input[name="_token"]')?.value || '';
    const selectedDepartment = department.dataset.selected || '';
    const selectedProgram = program.dataset.selected || '';
    const post = (url, values) => fetch(url, { method:'POST', credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'}, body:new URLSearchParams({...values, _token:token}) }).then(response => { if (!response.ok) throw new Error('Unable to load academic options.'); return response.text(); });
    const reset = (select, label) => { select.innerHTML = `<option value="">${label}</option>`; select.disabled = true; };
    const loadPrograms = async (keep = '') => { reset(program, 'Loading programmes…'); if (!department.value) { reset(program, 'Select department first'); return; } try { program.innerHTML = await post('/program ajax', {faculty:faculty.value, dept:department.value}); program.disabled = false; if (keep && [...program.options].some(option => option.value === keep)) program.value = keep; } catch (error) { reset(program, 'Unable to load programmes'); } };
    const loadDepartments = async (keep = '') => { reset(department, 'Loading departments…'); reset(program, 'Select department first'); if (!faculty.value) { reset(department, 'Select faculty first'); return; } try { department.innerHTML = await post('/department ajax', {faculty:faculty.value}); department.disabled = false; if (keep && [...department.options].some(option => option.value === keep)) department.value = keep; await loadPrograms(selectedProgram); } catch (error) { reset(department, 'Unable to load departments'); } };
    faculty.addEventListener('change', () => loadDepartments());
    department.addEventListener('change', () => loadPrograms());
    if (faculty.value) loadDepartments(selectedDepartment);
    form.addEventListener('submit', () => { const submit = document.getElementById('admitSubmit'); submit.disabled = true; submit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing…'; document.getElementById('admitProgress').classList.add('is-visible'); });
})();
</script>
