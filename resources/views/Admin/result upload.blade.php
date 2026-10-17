@php
    $selectedCourse = old('course', $uploadContext['course'] ?? '');
    $selectedSession = old('session', $uploadContext['session'] ?? ($currentSession ?? ''));
    $selectedSemester = old('semester', $uploadContext['semester'] ?? '');
    $selectedPer = old('per', $uploadContext['per'] ?? '');
    $report = $uploadReport ?? null;
    $details = collect($report['details'] ?? []);
    $issues = $details->whereIn('status', ['skipped', 'failed']);
    $successes = $details->where('status', 'success');
@endphp

<div class="result-upload-page">
    <style>
        .result-upload-page{--ru-blue:#176fa8;--ru-ink:#17324d;--ru-muted:#71869a;--ru-border:#dce8f1;--ru-soft:#f5f9fc;min-height:calc(100vh - 70px);padding:clamp(12px,2.6vw,30px);background:#f3f7fb;color:var(--ru-ink)}
        .result-upload-shell{max-width:1120px;margin:0 auto}
        .result-upload-back{display:inline-flex;align-items:center;gap:8px;margin:0 0 12px;color:#2677a8;text-decoration:none;font-size:.78rem;font-weight:800}
        .result-upload-back:hover{color:#124d73}
        .result-upload-hero{display:flex;justify-content:space-between;align-items:center;gap:20px;background:linear-gradient(135deg,#12689c,#3ea1e4);color:#fff;border-radius:18px;padding:clamp(18px,3vw,28px);box-shadow:0 12px 28px rgba(30,94,139,.18);margin-bottom:14px}
        .result-upload-hero h1{font-size:clamp(1.25rem,3vw,1.85rem);margin:0 0 5px;font-weight:800;letter-spacing:-.02em}
        .result-upload-hero p{margin:0;max-width:650px;color:rgba(255,255,255,.87);font-size:.78rem;line-height:1.55}
        .result-upload-hero-icon{width:54px;height:54px;display:grid;place-items:center;border-radius:16px;background:rgba(255,255,255,.16);font-size:1.35rem;flex:0 0 auto}
        .result-upload-layout{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:14px;align-items:start}
        .result-upload-card{background:#fff;border:1px solid var(--ru-border);border-radius:16px;box-shadow:0 7px 22px rgba(28,63,93,.055);overflow:hidden}
        .result-upload-card-head{padding:16px 18px;border-bottom:1px solid #edf3f7;display:flex;justify-content:space-between;gap:12px;align-items:flex-start}
        .result-upload-card-head h2{font-size:1rem;margin:0 0 3px;color:var(--ru-ink)}
        .result-upload-card-head p{font-size:.7rem;color:var(--ru-muted);margin:0;line-height:1.5}
        .result-upload-form{padding:18px}
        .result-upload-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}
        .result-upload-field{min-width:0}
        .result-upload-field.full{grid-column:1/-1}
        .result-upload-field label{display:flex;align-items:center;gap:5px;color:#39556c;font-size:.7rem;font-weight:800;margin-bottom:6px}
        .result-upload-field label span{color:#d04d5b}
        .result-upload-field select,.result-upload-field input[type=file]{width:100%;border:1px solid #cbdce8;border-radius:10px;background:#fff;color:#234761;padding:10px 11px;font-size:.78rem;min-height:43px;outline:none}
        .result-upload-field select:focus,.result-upload-field input[type=file]:focus{border-color:#3ea1e4;box-shadow:0 0 0 3px rgba(62,161,228,.13)}
        .result-upload-field small{display:block;margin-top:5px;color:#8295a5;font-size:.64rem;line-height:1.45}
        .result-upload-actions{display:flex;align-items:center;justify-content:flex-end;gap:9px;border-top:1px solid #edf3f7;margin-top:17px;padding-top:15px}
        .result-upload-btn{border:0;border-radius:10px;padding:10px 15px;display:inline-flex;align-items:center;justify-content:center;gap:8px;font-size:.76rem;font-weight:800;text-decoration:none;cursor:pointer;transition:.16s ease}
        .result-upload-btn.primary{background:#167db5;color:#fff;box-shadow:0 5px 12px rgba(22,125,181,.2)}
        .result-upload-btn.primary:hover{background:#105e8a;transform:translateY(-1px)}
        .result-upload-btn.secondary{background:#edf5fa;color:#2c6385}
        .result-upload-btn:disabled{opacity:.7;cursor:wait;transform:none}
        .result-upload-help{padding:16px;background:var(--ru-soft);border-bottom:1px solid var(--ru-border)}
        .result-upload-help h3{font-size:.78rem;margin:0 0 8px;color:#2a526e}
        .result-upload-help ol{padding-left:18px;margin:0;color:#71869a;font-size:.68rem;line-height:1.7}
        .result-upload-template{display:flex;align-items:center;gap:10px;margin-top:12px;padding:10px 11px;background:#fff;border:1px dashed #b7d1e1;border-radius:10px;color:#2677a8;text-decoration:none;font-size:.7rem;font-weight:800}
        .result-upload-template i{font-size:1rem}
        .result-upload-error{padding:11px 13px;border:1px solid #f1c3c8;border-radius:10px;background:#fff3f4;color:#a33c4a;font-size:.72rem;line-height:1.5;margin-bottom:13px}
        .result-upload-error ul{margin:6px 0 0;padding-left:17px}
        .result-upload-notice{padding:12px 14px;border-radius:11px;background:#eef8ff;border:1px solid #c9e7f7;color:#2d6686;font-size:.72rem;line-height:1.5;margin-bottom:14px}
        .result-upload-report{margin-top:14px}
        .result-upload-report-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;padding:17px 18px;border-bottom:1px solid #edf3f7}
        .result-upload-report-head h2{font-size:1rem;margin:0 0 4px;color:var(--ru-ink)}
        .result-upload-report-head p{font-size:.68rem;color:var(--ru-muted);margin:0;line-height:1.45}
        .result-upload-badge{display:inline-flex;align-items:center;gap:6px;padding:7px 9px;border-radius:999px;background:#eaf8ef;color:#187343;font-size:.65rem;font-weight:800;white-space:nowrap}
        .result-upload-stats{display:grid;grid-template-columns:repeat(5,1fr);gap:8px;padding:14px 18px;border-bottom:1px solid #edf3f7}
        .result-upload-stat{padding:11px 10px;border:1px solid #e3edf3;border-radius:11px;background:#fbfdff;min-width:0}
        .result-upload-stat strong,.result-upload-stat small{display:block}
        .result-upload-stat strong{font-size:1.15rem;color:#1e4e70}
        .result-upload-stat small{font-size:.61rem;color:#8093a3;margin-top:2px;white-space:nowrap}
        .result-upload-stat.success strong{color:#13824c}.result-upload-stat.warning strong{color:#af7100}.result-upload-stat.danger strong{color:#bd4050}.result-upload-stat.purple strong{color:#7351a6}
        .result-upload-table-wrap{overflow:auto;padding:0 18px 17px}
        .result-upload-table-title{font-size:.76rem;font-weight:800;color:#38566e;margin:16px 0 8px}
        .result-upload-table{width:100%;border-collapse:separate;border-spacing:0;font-size:.68rem;min-width:620px}
        .result-upload-table th{padding:9px 8px;text-align:left;background:#f5f9fc;color:#668096;font-size:.61rem;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;border-top:1px solid #e5eef4;border-bottom:1px solid #e5eef4}
        .result-upload-table td{padding:9px 8px;border-bottom:1px solid #edf2f6;color:#526d80;vertical-align:top}
        .result-upload-table tr:last-child td{border-bottom:0}
        .result-upload-status{display:inline-flex;align-items:center;gap:5px;padding:4px 7px;border-radius:999px;font-size:.59rem;font-weight:800;white-space:nowrap}
        .result-upload-status.success{background:#eaf8ef;color:#187343}.result-upload-status.skipped{background:#fff6df;color:#946500}.result-upload-status.failed{background:#fff0f2;color:#ad394a}
        .result-upload-empty{padding:15px 0;color:#8295a5;font-size:.7rem}
        .result-upload-spinner{display:none;width:14px;height:14px;border:2px solid rgba(255,255,255,.45);border-top-color:#fff;border-radius:50%;animation:result-upload-spin .7s linear infinite}
        .result-upload-btn.is-loading .result-upload-spinner{display:inline-block}
        @keyframes result-upload-spin{to{transform:rotate(360deg)}}
        @media(max-width:850px){.result-upload-layout{grid-template-columns:1fr}.result-upload-help{order:2}.result-upload-report{order:3}}
        @media(max-width:620px){.result-upload-page{padding:9px}.result-upload-hero{border-radius:14px;padding:16px}.result-upload-hero-icon{width:44px;height:44px;border-radius:12px;font-size:1.1rem}.result-upload-hero p{font-size:.7rem}.result-upload-card-head,.result-upload-form,.result-upload-report-head,.result-upload-stats,.result-upload-table-wrap{padding-left:13px;padding-right:13px}.result-upload-grid{grid-template-columns:1fr;gap:11px}.result-upload-field.full{grid-column:auto}.result-upload-actions{flex-direction:column-reverse;align-items:stretch}.result-upload-btn{width:100%}.result-upload-stats{grid-template-columns:repeat(2,1fr)}.result-upload-stat:last-child{grid-column:1/-1}.result-upload-report-head{display:block}.result-upload-badge{margin-top:10px}.result-upload-table{min-width:580px}}
    </style>

    <div class="result-upload-shell">
        <a class="result-upload-back" href="{{ url('/results') }}"><i class="fas fa-arrow-left"></i> Back to course results</a>

        <section class="result-upload-hero">
            <div>
                <h1>Upload course results</h1>
                <p>Choose the course and academic context, upload the official spreadsheet, and review every row before moving on to the approval stages.</p>
            </div>
            <div class="result-upload-hero-icon"><i class="fas fa-file-import"></i></div>
        </section>

        @if ($errors->any())
            <div class="result-upload-error">
                <strong><i class="fas fa-circle-exclamation"></i> Please correct the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (!empty($uploadError))
            <div class="result-upload-error"><strong><i class="fas fa-triangle-exclamation"></i> Upload not completed.</strong> {{ $uploadError }}</div>
        @endif

        <div class="result-upload-layout">
            <section class="result-upload-card">
                <div class="result-upload-card-head">
                    <div>
                        <h2>Upload spreadsheet</h2>
                        <p>Only rows that pass all checks are saved as lecturer-submitted results.</p>
                    </div>
                    <span class="result-upload-badge"><i class="fas fa-shield-halved"></i> Safe row checks</span>
                </div>
                <form class="result-upload-form" method="POST" action="{{ route('results.upload') }}" enctype="multipart/form-data" id="resultUploadForm">
                    @csrf
                    <div class="result-upload-grid">
                        <div class="result-upload-field full">
                            <label for="resultCourse">Course <span>*</span></label>
                            <select id="resultCourse" name="course" required>
                                <option value="">Select a course</option>
                                @foreach ($lecturerCourses as $course)
                                    <option value="{{ $course->code }}"{{ (string) $selectedCourse === (string) $course->code ? ' selected' : '' }}>
                                        {{ $course->code }}{{ $course->title ? ' — ' . $course->title : '' }}{{ isset($course->unit) ? ' (' . $course->unit . ' units)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($isStaffUploader && $lecturerCourses->isEmpty())
                                <small>You do not currently have a MAIN course allocation.</small>
                            @else
                                <small>Staff can upload only for courses assigned to their account.</small>
                            @endif
                        </div>
                        <div class="result-upload-field">
                            <label for="resultSession">Academic session <span>*</span></label>
                            <select id="resultSession" name="session" required>
                                <option value="">Select session</option>
                                @foreach ($sessions as $item)
                                    <option value="{{ $item->title }}"{{ (string) $selectedSession === (string) $item->title ? ' selected' : '' }}>{{ $item->title }}{{ (string) $item->title === (string) $currentSession ? ' — current' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="result-upload-field">
                            <label for="resultSemester">Semester <span>*</span></label>
                            <select id="resultSemester" name="semester" required>
                                <option value="">Select semester</option>
                                @include('partials.semester_options', ['selectedSemester' => $selectedSemester, 'semesters' => ['FIRST', 'SECOND', 'THIRD'], 'includeResit' => true])
                            </select>
                        </div>
                        <div class="result-upload-field">
                            <label for="resultPer">Score format <span>*</span></label>
                            <select id="resultPer" name="per" required>
                                <option value="">Select score format</option>
                                <option value="70"{{ $selectedPer === '70' ? ' selected' : '' }}>30 / 70 (CA / Exam)</option>
                                <option value="30"{{ $selectedPer === '30' ? ' selected' : '' }}>70 / 30 (CA / Exam)</option>
                                <option value="60"{{ $selectedPer === '60' ? ' selected' : '' }}>40 / 60 (CA / Exam)</option>
                                <option value="40"{{ $selectedPer === '40' ? ' selected' : '' }}>60 / 40 (CA / Exam)</option>
                                <option value="100"{{ $selectedPer === '100' ? ' selected' : '' }}>0 / 100 (Exam only)</option>
                            </select>
                            <small>The spreadsheet must contain the CA and examination values in columns C and D.</small>
                        </div>
                        <div class="result-upload-field">
                            <label for="resultFile">Result spreadsheet <span>*</span></label>
                            <input id="resultFile" type="file" name="file" accept=".xlsx,.xls" required>
                            <small>Use the official Excel template. Maximum file size: 15 MB.</small>
                        </div>
                    </div>
                    <div class="result-upload-actions">
                        <a class="result-upload-btn secondary" href="{{ url('uploads/result template.xlsx') }}" download="result template.xlsx"><i class="fas fa-download"></i> Download template</a>
                        <button class="result-upload-btn primary" type="submit" id="resultUploadSubmit"><span class="result-upload-spinner"></span><i class="fas fa-cloud-arrow-up result-upload-submit-icon"></i><span class="result-upload-submit-text">Process spreadsheet</span></button>
                    </div>
                </form>
            </section>

            <aside class="result-upload-card result-upload-help">
                <h3><i class="fas fa-circle-info"></i> Before you upload</h3>
                <ol>
                    <li>Download and use the official template.</li>
                    <li>Keep one student per row.</li>
                    <li>Use the student matric number in column B.</li>
                    <li>Enter CA in column C and exam in column D.</li>
                    <li>Review the report for rows that were not saved.</li>
                </ol>
                <a class="result-upload-template" href="{{ url('uploads/result template.xlsx') }}" download="result template.xlsx"><i class="fas fa-file-excel"></i><span>Download Excel template</span></a>
            </aside>
        </div>

        @if ($report)
            <section class="result-upload-card result-upload-report">
                <div class="result-upload-report-head">
                    <div>
                        <h2>Processing report</h2>
                        <p>{{ $uploadContext['filename'] ?? 'Uploaded spreadsheet' }} · {{ $uploadContext['course'] ?? '' }} · {{ $uploadContext['session'] ?? '' }} · {{ ucfirst(strtolower($uploadContext['semester'] ?? '')) }}</p>
                    </div>
                    @if (($report['not_uploaded'] ?? 0) === 0)
                        <span class="result-upload-badge"><i class="fas fa-check-circle"></i> All rows saved</span>
                    @else
                        <span class="result-upload-badge" style="background:#fff6df;color:#946500"><i class="fas fa-eye"></i> Review rows below</span>
                    @endif
                </div>
                <div class="result-upload-stats">
                    <div class="result-upload-stat"><strong>{{ $report['total_rows'] ?? 0 }}</strong><small>Rows checked</small></div>
                    <div class="result-upload-stat success"><strong>{{ $report['successful'] ?? 0 }}</strong><small>Saved successfully</small></div>
                    <div class="result-upload-stat purple"><strong>{{ $report['inserted'] ?? 0 }}</strong><small>New results</small></div>
                    <div class="result-upload-stat warning"><strong>{{ $report['updated'] ?? 0 }}</strong><small>Updated drafts</small></div>
                    <div class="result-upload-stat danger"><strong>{{ $report['not_uploaded'] ?? 0 }}</strong><small>Not uploaded</small></div>
                </div>
                <div class="result-upload-table-wrap">
                    <div class="result-upload-notice"><i class="fas fa-lightbulb"></i> Results are saved as <strong>Lecturer submitted</strong> and still need the normal Department, Faculty, Course System, Senate, and Vice-Chancellor approvals. @if(($report['resits'] ?? 0) > 0)<strong>{{ $report['resits'] }} resit result(s)</strong> were saved as repeat attempts; an earlier approved attempt means the course units will not be counted again.@endif @if(($report['cleaned_registrations'] ?? 0) > 0)<strong>{{ $report['cleaned_registrations'] }} old course-registration {{ ($report['cleaned_registrations'] ?? 0) === 1 ? 'entry' : 'entries' }}</strong> without a result were removed automatically.@endif</div>

                    @if ($issues->isNotEmpty())
                        <div class="result-upload-table-title"><i class="fas fa-triangle-exclamation"></i> Rows not uploaded ({{ $issues->count() }})</div>
                        <table class="result-upload-table">
                            <thead><tr><th>Excel row</th><th>Matric number</th><th>Reason</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach ($issues as $detail)
                                    <tr><td>{{ $detail['row'] }}</td><td><strong>{{ $detail['username'] }}</strong></td><td>{{ $detail['message'] }}</td><td><span class="result-upload-status {{ $detail['status'] }}"><i class="fas {{ $detail['status'] === 'failed' ? 'fa-xmark' : 'fa-triangle-exclamation' }}"></i>{{ ucfirst($detail['status']) }}</span></td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="result-upload-empty"><i class="fas fa-check-circle" style="color:#16814b"></i> No rows were skipped or failed.</div>
                    @endif

                    @if ($successes->isNotEmpty())
                        <details style="margin-top:14px">
                            <summary class="result-upload-table-title" style="cursor:pointer">Successful rows ({{ $successes->count() }})</summary>
                            <table class="result-upload-table">
                                <thead><tr><th>Excel row</th><th>Matric number</th><th>Result</th></tr></thead>
                                <tbody>
                                    @foreach ($successes as $detail)
                                        <tr><td>{{ $detail['row'] }}</td><td><strong>{{ $detail['username'] }}</strong></td><td><span class="result-upload-status success"><i class="fas fa-check"></i>{{ $detail['message'] }}</span></td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </details>
                    @endif
                </div>
            </section>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('resultUploadForm');
        const submit = document.getElementById('resultUploadSubmit');
        if (!form || !submit) return;
        form.addEventListener('submit', function () {
            if (!form.checkValidity()) return;
            submit.disabled = true;
            submit.classList.add('is-loading');
            submit.querySelector('.result-upload-submit-icon')?.remove();
            const text = submit.querySelector('.result-upload-submit-text');
            if (text) text.textContent = 'Processing…';
        });
    });
</script>
