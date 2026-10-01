@php
    use Illuminate\Support\Facades\DB;
    use App\Models\DocumentUpload;
    use App\Models\Student;
    use App\Models\StudentDocument;

    $row = Student::where('id', $id)->first();
    $sessions = DB::table('session')->orderByDesc('title')->get();
@endphp

@if (!$row)
    <div class="admin-student-page">
        <div class="student-empty-page">
            <div class="empty-icon"><i class="fas fa-user-slash"></i></div>
            <h3>Student record not found</h3>
            <p>The student record may have been removed or the link is no longer valid.</p>
            <a href="{{ url('/students list') }}" class="student-btn student-btn-light"><i class="fas fa-arrow-left"></i> Back to students</a>
        </div>
    </div>
@else
    @php
        $programRow = DB::table('program')->where('code', $row->program)->first();
        $departmentRow = DB::table('department')->where('code', $row->department)->first();
        $facultyCode = $programRow->faculty ?? $row->faculty;
        $facultyRow = DB::table('faculty')->where('code', $facultyCode)->first();
        $programTitle = $programRow->title ?? $row->program;
        $departmentTitle = $departmentRow->title ?? $row->department;
        $facultyTitle = $facultyRow->title ?? $facultyCode;
        $fullName = trim(implode(' ', array_filter([$row->first_name, $row->last_name, $row->other_name])));
        $fullName = $fullName !== '' ? $fullName : ($row->fullname ?: 'Unnamed student');
        $photoUrl = $row->picture ? asset('storage/picture/' . $row->picture) : asset('storage/picture/default.jpg');
        $isActive = (string) $row->status === '1';
        $profileSubmitted = !empty($row->profile_submitted_at) || (($row->profile_status ?? '') === 'submitted');
        $currentSession = DB::table('session')->where('status', '1')->value('title') ?: ($row->session_of_entry ?: 'Current session');

        $hostel = DB::table('hostel')->where('occupant', $row->username)
            ->select('hall', 'block', 'room', 'bed')->first();

        $registeredCourses = DB::table('student_course_registration')
            ->where('username', $row->username)
            ->orderByDesc('session')->orderBy('semester')->orderBy('code')->get();
        $courseCodes = $registeredCourses->pluck('code')->filter()->unique()->values();
        $courseTitles = $courseCodes->isEmpty() ? collect() : DB::table('course')->whereIn('code', $courseCodes)->pluck('title', 'code');
        $addCourses = DB::table('program_course_registration')->where('program', $row->program)->orderBy('code')->get();

        $invoices = DB::table('invoices')->where('username', $row->user_id)->orderByDesc('updated_at')->orderByDesc('id')->get();
        $paidInvoices = $invoices->filter(fn ($invoice) => (string) ($invoice->status ?? '') === 'Paid');
        $pendingInvoices = $invoices->filter(fn ($invoice) => (string) ($invoice->status ?? '') !== 'Paid');
        $paidAmount = (float) $paidInvoices->sum(fn ($invoice) => (float) ($invoice->amount ?? 0));
        $pendingAmount = (float) $pendingInvoices->sum(fn ($invoice) => (float) ($invoice->amount ?? 0));

        $studentDocuments = StudentDocument::where('user_id', $row->user_id)->latest('uploaded_at')->get();
        $applicantDocuments = DocumentUpload::where('user_id', $row->user_id)->latest('uploaded_at')->get();
        $documents = $studentDocuments->concat($applicantDocuments)->unique(fn ($document) => ($document->doc_type ?? '') . '|' . ($document->file_path ?? ''));
        $documentLabels = [
            'picture' => 'Profile picture', 'signature' => 'Signature', 'signiture' => 'Signature',
            'ssce' => 'SSCE certificate', 'birth' => 'Birth certificate', 'nin' => 'NIN',
            'jamb' => 'JAMB admission', 'primary' => 'Primary certificate',
            'indigene' => 'Indigene certificate', 'indigene_certificate' => 'Indigene certificate',
        ];
        $value = static fn ($item, $fallback = 'Not provided') => trim((string) ($item ?? '')) !== '' ? $item : $fallback;
        $money = static fn ($amount) => '₦' . number_format((float) $amount, 2);
        $statusLabel = static fn ($status) => match (strtolower((string) $status)) {
            'paid', 'successful', 'success' => 'Paid',
            'pending' => 'Pending',
            'failed', 'cancelled', 'canceled' => ucfirst((string) $status),
            default => $value($status, 'Unknown'),
        };
    @endphp

    <div class="admin-student-page">
        <div class="student-toolbar">
            <div class="toolbar-title">
                <a href="{{ url('/students list') }}" class="back-link"><i class="fas fa-arrow-left"></i><span>Students</span></a>
                <span class="toolbar-divider"></span>
                <span>Student profile</span>
            </div>
            <div class="toolbar-actions">
                <a href="{{ url('/student details pdf/' . $row->id) }}" class="student-btn student-btn-light" target="_blank"><i class="fas fa-file-pdf"></i><span>Student PDF</span></a>
                <a href="{{ url('/id card/' . $row->id) }}" class="student-btn student-btn-primary" target="_blank"><i class="fas fa-id-card"></i><span>ID card</span></a>
            </div>
        </div>

        <section class="student-hero">
            <div class="hero-identity">
                <div class="hero-photo-wrap">
                    <img src="{{ $photoUrl }}" alt="{{ $fullName }}" class="hero-photo" onerror="this.onerror=null;this.src='{{ asset('storage/picture/default.jpg') }}';">
                    <span class="status-dot {{ $isActive ? 'active' : 'inactive' }}" title="{{ $isActive ? 'Active student' : 'Inactive student' }}"></span>
                </div>
                <div class="hero-copy">
                    <div class="eyebrow">POSTGRADUATE STUDENT</div>
                    <h1>{{ $fullName }}</h1>
                    <div class="matric-number">{{ $value($row->username) }}</div>
                    <div class="hero-tags">
                        <span class="hero-tag"><i class="fas fa-circle"></i>{{ $isActive ? 'Active' : 'Inactive' }}</span>
                        <span class="hero-tag"><i class="fas fa-{{ $profileSubmitted ? 'check-circle' : 'clock' }}"></i>{{ $profileSubmitted ? 'Profile submitted' : 'Profile pending' }}</span>
                        @if ($row->mode_of_entry)
                            <span class="hero-tag"><i class="fas fa-route"></i>{{ $row->mode_of_entry }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="hero-academic">
                <div><span>Programme</span><strong>{{ $programTitle }}</strong><small>{{ $value($row->program) }}</small></div>
                <div><span>Department</span><strong>{{ $departmentTitle }}</strong><small>{{ $value($row->department) }}</small></div>
                <div><span>Level</span><strong>{{ $value($row->level, '—') }}</strong><small>{{ $value($row->session_of_entry, 'Entry session not set') }}</small></div>
            </div>
        </section>

        <section class="student-stat-grid">
            <div class="student-stat"><span class="stat-icon blue"><i class="fas fa-book-open"></i></span><div><strong>{{ $registeredCourses->count() }}</strong><span>Registered courses</span></div></div>
            <div class="student-stat"><span class="stat-icon green"><i class="fas fa-check-circle"></i></span><div><strong>{{ $money($paidAmount) }}</strong><span>Paid invoices</span></div></div>
            <div class="student-stat"><span class="stat-icon amber"><i class="fas fa-clock"></i></span><div><strong>{{ $money($pendingAmount) }}</strong><span>Pending balance</span></div></div>
            <div class="student-stat"><span class="stat-icon purple"><i class="fas fa-folder-open"></i></span><div><strong>{{ $documents->count() }}</strong><span>Uploaded documents</span></div></div>
        </section>

        <div class="student-layout">
            <aside class="student-nav" aria-label="Student record sections">
                <div class="nav-caption">RECORD</div>
                <button type="button" class="student-nav-link active" data-student-tab="overview"><i class="fas fa-user"></i><span>Overview</span></button>
                <button type="button" class="student-nav-link" data-student-tab="academic"><i class="fas fa-graduation-cap"></i><span>Academic</span></button>
                <button type="button" class="student-nav-link" data-student-tab="courses"><i class="fas fa-book"></i><span>Courses</span><b>{{ $registeredCourses->count() }}</b></button>
                <button type="button" class="student-nav-link" data-student-tab="payments"><i class="fas fa-wallet"></i><span>Payments</span><b>{{ $invoices->count() }}</b></button>
                <button type="button" class="student-nav-link" data-student-tab="documents"><i class="fas fa-folder"></i><span>Documents</span><b>{{ $documents->count() }}</b></button>
            </aside>

            <main class="student-content">
                <section class="student-tab-panel active" data-student-panel="overview">
                    <div class="section-heading"><div><span class="section-kicker">PERSONAL RECORD</span><h2>Overview</h2><p>Key identity and contact information for this student.</p></div><span class="record-chip"><i class="fas fa-shield-alt"></i> Admin view</span></div>
                    <div class="detail-grid">
                        <article class="record-card"><div class="record-card-title"><i class="fas fa-user"></i><h3>Personal information</h3></div><dl class="detail-list">
                            <div><dt>Full name</dt><dd>{{ $fullName }}</dd></div>
                            <div><dt>Gender</dt><dd>{{ $value($row->gender) }}</dd></div>
                            <div><dt>Date of birth</dt><dd>{{ $value($row->date_of_birth) }}</dd></div>
                            <div><dt>Place of birth</dt><dd>{{ $value($row->place_of_birth) }}</dd></div>
                            <div><dt>Marital status</dt><dd>{{ $value($row->marital_status) }}</dd></div>
                            <div><dt>Religion</dt><dd>{{ $value($row->religion) }}</dd></div>
                            <div><dt>Nationality</dt><dd>{{ $value($row->country) }}</dd></div>
                            <div><dt>NIN</dt><dd>{{ $value($row->nin) }}</dd></div>
                        </dl></article>
                        <article class="record-card"><div class="record-card-title"><i class="fas fa-address-book"></i><h3>Contact information</h3></div><dl class="detail-list">
                            <div><dt>Email</dt><dd>{{ $value($row->contact_email) }}</dd></div>
                            <div><dt>Phone</dt><dd>{{ $value($row->contact_phone) }}</dd></div>
                            <div><dt>Address</dt><dd>{{ $value($row->contact_address) }}</dd></div>
                            <div><dt>State of origin</dt><dd>{{ $value($row->state_origin) }}</dd></div>
                            <div><dt>LGA of origin</dt><dd>{{ $value($row->lga_origin) }}</dd></div>
                            <div><dt>Emergency contact</dt><dd>{{ $value($row->emergency_name) }}{{ $row->emergency_phone ? ' · ' . $row->emergency_phone : '' }}</dd></div>
                        </dl></article>
                        <article class="record-card"><div class="record-card-title"><i class="fas fa-users"></i><h3>Family and sponsor</h3></div><dl class="detail-list">
                            <div><dt>Father</dt><dd>{{ $value($row->father_name) }}</dd></div>
                            <div><dt>Mother</dt><dd>{{ $value($row->mother_name) }}</dd></div>
                            <div><dt>Maiden name</dt><dd>{{ $value($row->maiden_name) }}</dd></div>
                            <div><dt>Sponsor name</dt><dd>{{ $value($row->sponsor_name ?? $row->sponsor) }}</dd></div>
                            <div><dt>Sponsor phone</dt><dd>{{ $value($row->sponsor_phone) }}</dd></div>
                        </dl></article>
                        <article class="record-card"><div class="record-card-title"><i class="fas fa-bed"></i><h3>Hostel allocation</h3></div><dl class="detail-list">
                            <div><dt>Hall</dt><dd>{{ $value($hostel->hall ?? $row->hall) }}</dd></div>
                            <div><dt>Block</dt><dd>{{ $value($hostel->block ?? $row->block) }}</dd></div>
                            <div><dt>Room</dt><dd>{{ $value($hostel->room ?? $row->room) }}</dd></div>
                            <div><dt>Bed</dt><dd>{{ $value($hostel->bed ?? $row->bed) }}</dd></div>
                        </dl></article>
                    </div>
                </section>
                <section class="student-tab-panel" data-student-panel="academic">
                    <div class="section-heading"><div><span class="section-kicker">ACADEMIC RECORD</span><h2>Academic details</h2><p>Programme, admission and profile status from the student record.</p></div></div>
                    <div class="academic-grid">
                        <article class="record-card accent-card"><div class="record-card-title"><i class="fas fa-university"></i><h3>Programme placement</h3></div><div class="academic-path">
                            <div><span>Faculty</span><strong>{{ $facultyTitle }}</strong><small>{{ $value($facultyCode) }}</small></div>
                            <i class="fas fa-chevron-right"></i>
                            <div><span>Department</span><strong>{{ $departmentTitle }}</strong><small>{{ $value($row->department) }}</small></div>
                            <i class="fas fa-chevron-right"></i>
                            <div><span>Programme</span><strong>{{ $programTitle }}</strong><small>{{ $value($row->program) }}</small></div>
                        </div></article>
                        <article class="record-card"><div class="record-card-title"><i class="fas fa-file-signature"></i><h3>Admission details</h3></div><dl class="detail-list">
                            <div><dt>Matric number</dt><dd>{{ $value($row->username) }}</dd></div>
                            <div><dt>JAMB number</dt><dd>{{ $value($row->jamb_no) }}</dd></div>
                            <div><dt>Mode of entry</dt><dd>{{ $value($row->mode_of_entry) }}</dd></div>
                            <div><dt>Entry session</dt><dd>{{ $value($row->session_of_entry) }}</dd></div>
                            <div><dt>Entry level</dt><dd>{{ $value($row->level_of_entry) }}</dd></div>
                            <div><dt>Programme award</dt><dd>{{ $value($programRow->award ?? null) }}</dd></div>
                            <div><dt>Programme duration</dt><dd>{{ $value($programRow->duration ?? $row->duration, '—') }} years</dd></div>
                        </dl></article>
                        <article class="record-card"><div class="record-card-title"><i class="fas fa-layer-group"></i><h3>Current standing</h3></div><dl class="detail-list">
                            <div><dt>Current level</dt><dd>{{ $value($row->level) }}</dd></div>
                            <div><dt>Current session</dt><dd>{{ $currentSession }}</dd></div>
                            <div><dt>Account status</dt><dd><span class="inline-status {{ $isActive ? 'success' : 'danger' }}">{{ $isActive ? 'Active' : 'Inactive' }}</span></dd></div>
                            <div><dt>Profile status</dt><dd><span class="inline-status {{ $profileSubmitted ? 'success' : 'warning' }}">{{ $profileSubmitted ? 'Submitted' : 'Pending update' }}</span></dd></div>
                            <div><dt>Last profile update</dt><dd>{{ $value($row->profile_last_updated_at ?? null) }}</dd></div>
                        </dl></article>
                    </div>
                </section>

                <section class="student-tab-panel" data-student-panel="courses">
                    <div class="section-heading"><div><span class="section-kicker">ACADEMIC ACTIVITY</span><h2>Course registration</h2><p>Courses registered by {{ $value($row->username) }}, grouped by session.</p></div><button class="student-btn student-btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#studentAddCourse"><i class="fas fa-plus"></i> Add course</button></div>
                    @if ($registeredCourses->isEmpty())
                        <div class="student-empty"><div class="empty-icon"><i class="fas fa-book-open"></i></div><h3>No course registrations yet</h3><p>There are no registered courses for this student.</p></div>
                    @else
                        <div class="responsive-table">
                            <table class="record-table">
                                <thead><tr><th>Course</th><th>Session</th><th>Semester</th><th>Level</th><th>Units</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                                <tbody>
                                    @foreach ($registeredCourses as $course)
                                        @php
                                            $courseTitle = $courseTitles[$course->code] ?? 'Course title not found';
                                            $courseStatus = strtolower((string) ($course->status ?? 'registered'));
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $course->code }}</strong><small>{{ $courseTitle }}</small></td>
                                            <td>{{ $value($course->session) }}</td>
                                            <td>{{ $value($course->semester) }}</td>
                                            <td>{{ $value($course->level) }}</td>
                                            <td>{{ $value($course->unit, '—') }}</td>
                                            <td><span class="table-pill {{ in_array($courseStatus, ['approved', 'registered', 'completed']) ? 'success' : 'muted' }}">{{ ucfirst($courseStatus) }}</span></td>
                                            <td class="text-end"><button class="table-action danger" type="button" data-bs-toggle="modal" data-bs-target="#studentDropCourse{{ $course->id }}"><i class="fas fa-minus-circle"></i> Drop</button></td>
                                        </tr>
                                        <div class="modal fade" id="studentDropCourse{{ $course->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered"><div class="modal-content student-modal">
                                                <div class="modal-header"><div><span class="section-kicker">COURSE ACTION</span><h5>Drop {{ $course->code }}?</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                                <div class="modal-body"><p>This will remove <strong>{{ $course->code }}</strong> from the student's registration. You can add it again later if needed.</p></div>
                                                <form action="{{ url('/drop-student-course') }}" method="POST">@csrf<input type="hidden" name="id" value="{{ $course->id }}"><div class="modal-footer"><button type="button" class="student-btn student-btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="student-btn student-btn-danger">Drop course</button></div></form>
                                            </div></div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>

                <section class="student-tab-panel" data-student-panel="payments">
                    <div class="section-heading"><div><span class="section-kicker">FINANCIAL RECORD</span><h2>Payments</h2><p>Invoice history and payment status linked to this student account.</p></div></div>
                    <div class="payment-summary">
                        <div><span><i class="fas fa-check"></i> Paid</span><strong>{{ $money($paidAmount) }}</strong><small>{{ $paidInvoices->count() }} invoice{{ $paidInvoices->count() === 1 ? '' : 's' }}</small></div>
                        <div><span><i class="fas fa-hourglass-half"></i> Pending</span><strong>{{ $money($pendingAmount) }}</strong><small>{{ $pendingInvoices->count() }} invoice{{ $pendingInvoices->count() === 1 ? '' : 's' }}</small></div>
                    </div>
                    @if ($invoices->isEmpty())
                        <div class="student-empty"><div class="empty-icon"><i class="fas fa-wallet"></i></div><h3>No payment records</h3><p>No invoice has been linked to this student account.</p></div>
                    @else
                        <div class="responsive-table">
                            <table class="record-table">
                                <thead><tr><th>Description</th><th>Session</th><th>Reference</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                                <tbody>
                                    @foreach ($invoices as $invoice)
                                        @php $invoiceStatus = strtolower((string) ($invoice->status ?? '')); @endphp
                                        <tr>
                                            <td><strong>{{ $value($invoice->description) }}</strong><small>{{ $value($invoice->fees_type ?? null, 'Student payment') }}</small></td>
                                            <td>{{ $value($invoice->session) }}</td>
                                            <td><span class="mono">{{ $value($invoice->rrr ?? $invoice->reference ?? null) }}</span></td>
                                            <td><strong>{{ $money($invoice->amount ?? 0) }}</strong></td>
                                            <td><span class="table-pill {{ $invoiceStatus === 'paid' ? 'success' : ($invoiceStatus === 'pending' ? 'warning' : 'muted') }}">{{ $statusLabel($invoice->status) }}</span></td>
                                            <td>{{ $value($invoice->updated_at ?? $invoice->created_at) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
                <section class="student-tab-panel" data-student-panel="documents">
                    <div class="section-heading"><div><span class="section-kicker">VERIFICATION RECORD</span><h2>Documents</h2><p>Documents uploaded for the application and student profile.</p></div></div>
                    @if ($documents->isEmpty())
                        <div class="student-empty"><div class="empty-icon"><i class="fas fa-folder-open"></i></div><h3>No documents uploaded</h3><p>This student has not uploaded any supporting documents.</p></div>
                    @else
                        <div class="document-grid">
                            @foreach ($documents as $document)
                                @php
                                    $docType = strtolower((string) ($document->doc_type ?? 'document'));
                                    $docName = $documentLabels[$docType] ?? ucwords(str_replace(['_', '-'], ' ', $docType));
                                    $fileName = $document->original_name ?: basename((string) $document->file_path);
                                    $fileUrl = $document->file_path ? (str_starts_with($document->file_path, 'http') ? $document->file_path : asset('storage/' . ltrim($document->file_path, '/'))) : null;
                                    $isPdf = str_contains(strtolower((string) ($document->mime_type ?? '')), 'pdf') || str_ends_with(strtolower((string) $fileName), '.pdf');
                                @endphp
                                <article class="document-card">
                                    <div class="document-icon {{ $isPdf ? 'pdf' : 'image' }}"><i class="fas fa-{{ $isPdf ? 'file-pdf' : 'file-image' }}"></i></div>
                                    <div class="document-copy"><strong>{{ $docName }}</strong><span>{{ $fileName ?: 'Uploaded document' }}</span><small>{{ $document->uploaded_at ? \Illuminate\Support\Carbon::parse($document->uploaded_at)->format('d M Y, g:i A') : 'Date not available' }}</small></div>
                                    @if ($fileUrl)<a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="document-view" title="Open document"><i class="fas fa-external-link-alt"></i></a>@endif
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            </main>
        </div>
    </div>

    <div class="modal fade" id="studentAddCourse" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content student-modal">
            <div class="modal-header"><div><span class="section-kicker">COURSE ACTION</span><h5>Add a course</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="{{ url('/add-student-course') }}" method="POST">@csrf
                <input type="hidden" name="program" value="{{ $row->program }}">
                <input type="hidden" name="username" value="{{ $row->username }}">
                <div class="modal-body">
                    <label class="student-field-label" for="studentCourseCode">Course code</label>
                    <select name="code" id="studentCourseCode" class="student-field" required>
                        <option value="">Select a course</option>
                        @foreach ($addCourses as $add)<option value="{{ $add->code }}">{{ $add->code }}</option>@endforeach
                    </select>
                    <label class="student-field-label" for="studentCourseSession">Session</label>
                    <select name="session" id="studentCourseSession" class="student-field" required>
                        <option value="">Select a session</option>
                        @foreach ($sessions as $session)<option value="{{ $session->title }}" {{ $session->title === $currentSession ? 'selected' : '' }}>{{ $session->title }}</option>@endforeach
                    </select>
                </div>
                <div class="modal-footer"><button type="button" class="student-btn student-btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="student-btn student-btn-primary"><i class="fas fa-plus"></i> Add course</button></div>
            </form>
        </div></div>
    </div>

    <style>
        .admin-student-page{--student-blue:#2563eb;--student-deep:#172554;--student-ink:#172033;--student-muted:#718096;--student-border:#e6ebf2;--student-bg:#f5f7fb;color:var(--student-ink);padding:clamp(14px,2.6vw,30px);background:var(--student-bg);min-height:calc(100vh - 70px)}
        .admin-student-page *{box-sizing:border-box}.admin-student-page h1,.admin-student-page h2,.admin-student-page h3,.admin-student-page h5,.admin-student-page p{margin-top:0}
        .student-toolbar{display:flex;justify-content:space-between;align-items:center;gap:16px;margin:0 auto 18px;max-width:1440px}.toolbar-title{display:flex;align-items:center;gap:12px;color:#667085;font-size:.88rem}.back-link{display:inline-flex;gap:8px;align-items:center;color:var(--student-blue);font-weight:700;text-decoration:none}.back-link:hover{color:#1d4ed8}.toolbar-divider{height:20px;width:1px;background:#d5dce7}.toolbar-actions{display:flex;gap:8px;flex-wrap:wrap}
        .student-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:0;border-radius:10px;padding:10px 14px;font-size:.82rem;font-weight:700;line-height:1;text-decoration:none;cursor:pointer;transition:.18s ease}.student-btn:hover{transform:translateY(-1px)}.student-btn-primary{background:var(--student-blue);color:#fff;box-shadow:0 6px 15px rgba(37,99,235,.18)}.student-btn-primary:hover{background:#1d4ed8;color:#fff}.student-btn-light{background:#fff;color:#40516b;border:1px solid var(--student-border)}.student-btn-light:hover{color:var(--student-blue);border-color:#bfd0ef}.student-btn-danger{background:#dc2626;color:#fff}.student-btn-danger:hover{background:#b91c1c;color:#fff}
        .student-hero{max-width:1440px;margin:0 auto 16px;background:linear-gradient(135deg,#172554 0%,#1d4ed8 60%,#2563eb 100%);color:#fff;border-radius:18px;padding:clamp(20px,3vw,32px);display:flex;justify-content:space-between;gap:28px;align-items:stretch;box-shadow:0 12px 30px rgba(30,64,175,.17)}.hero-identity{display:flex;align-items:center;gap:20px;min-width:0}.hero-photo-wrap{position:relative;flex:0 0 auto}.hero-photo{width:92px;height:92px;border-radius:18px;object-fit:cover;border:4px solid rgba(255,255,255,.7);background:#fff;display:block}.status-dot{position:absolute;right:2px;bottom:2px;width:18px;height:18px;border:3px solid #fff;border-radius:50%;background:#22c55e}.status-dot.inactive{background:#f97316}.hero-copy{min-width:0}.eyebrow,.section-kicker{letter-spacing:.11em;font-size:.68rem;font-weight:800;color:rgba(255,255,255,.7)}.hero-copy h1{font-size:clamp(1.35rem,3vw,2rem);margin:5px 0 4px;line-height:1.15;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.matric-number{font-size:.92rem;color:#dbeafe;font-weight:600}.hero-tags{display:flex;gap:8px;flex-wrap:wrap;margin-top:15px}.hero-tag{display:inline-flex;align-items:center;gap:6px;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.1);border-radius:50px;padding:6px 9px;font-size:.71rem;color:#eff6ff}.hero-tag i{font-size:.55rem;color:#93c5fd}.hero-academic{display:grid;grid-template-columns:repeat(3,minmax(130px,1fr));gap:1px;background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.17);border-radius:13px;overflow:hidden;min-width:min(520px,46%)}.hero-academic>div{padding:14px 16px;background:rgba(255,255,255,.08);display:flex;flex-direction:column;justify-content:center}.hero-academic span{font-size:.67rem;text-transform:uppercase;letter-spacing:.08em;color:#bfdbfe;margin-bottom:5px}.hero-academic strong{font-size:.88rem;line-height:1.35;color:#fff}.hero-academic small{margin-top:3px;color:#dbeafe;font-size:.72rem}
        .student-stat-grid{max-width:1440px;margin:0 auto 22px;display:grid;grid-template-columns:repeat(4,1fr);gap:12px}.student-stat{background:#fff;border:1px solid var(--student-border);border-radius:14px;padding:15px 16px;display:flex;align-items:center;gap:12px;box-shadow:0 4px 14px rgba(15,23,42,.035)}.stat-icon{width:40px;height:40px;display:grid;place-items:center;border-radius:11px;font-size:1rem}.stat-icon.blue{background:#eff6ff;color:#2563eb}.stat-icon.green{background:#ecfdf3;color:#16a34a}.stat-icon.amber{background:#fff7ed;color:#ea580c}.stat-icon.purple{background:#f5f3ff;color:#7c3aed}.student-stat strong{display:block;font-size:1.07rem;line-height:1.1}.student-stat div span{display:block;font-size:.73rem;color:var(--student-muted);margin-top:4px}
        .student-layout{max-width:1440px;margin:0 auto;display:grid;grid-template-columns:215px minmax(0,1fr);gap:20px;align-items:start}.student-nav{position:sticky;top:15px;background:#fff;border:1px solid var(--student-border);border-radius:15px;padding:10px;box-shadow:0 4px 14px rgba(15,23,42,.035)}.nav-caption{font-size:.65rem;font-weight:800;letter-spacing:.12em;color:#98a2b3;padding:8px 11px 7px}.student-nav-link{width:100%;display:flex;align-items:center;gap:10px;border:0;background:transparent;color:#667085;border-radius:9px;padding:11px;font-weight:600;font-size:.82rem;text-align:left;cursor:pointer;transition:.18s ease}.student-nav-link i{width:17px;text-align:center;color:#98a2b3}.student-nav-link b{margin-left:auto;border-radius:99px;min-width:21px;padding:3px 6px;background:#f1f5f9;color:#64748b;font-size:.66rem;text-align:center}.student-nav-link:hover{background:#f8fafc;color:var(--student-blue)}.student-nav-link.active{background:#eff6ff;color:var(--student-blue)}.student-nav-link.active i{color:var(--student-blue)}.student-nav-link.active b{background:#dbeafe;color:#1d4ed8}.student-content{min-width:0}.student-tab-panel{display:none;animation:studentPanelIn .2s ease}.student-tab-panel.active{display:block}@keyframes studentPanelIn{from{opacity:0;transform:translateY(3px)}to{opacity:1;transform:none}}
        .section-heading{display:flex;justify-content:space-between;align-items:flex-end;gap:14px;margin:0 0 16px}.section-kicker{display:block;color:#64748b}.section-heading h2{font-size:1.35rem;margin:4px 0 5px;color:#14213d}.section-heading p{color:var(--student-muted);font-size:.82rem;margin:0}.record-chip{display:inline-flex;align-items:center;gap:7px;color:#2563eb;background:#eff6ff;border-radius:50px;padding:7px 10px;font-size:.72rem;font-weight:700;white-space:nowrap}.detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.record-card{background:#fff;border:1px solid var(--student-border);border-radius:14px;padding:19px;box-shadow:0 4px 14px rgba(15,23,42,.028);min-width:0}.record-card-title{display:flex;align-items:center;gap:9px;padding-bottom:13px;border-bottom:1px solid #eef2f6;margin-bottom:3px}.record-card-title i{color:var(--student-blue);width:20px;text-align:center}.record-card-title h3{font-size:.93rem;margin:0;color:#26344d}.detail-list{margin:0}.detail-list>div{display:flex;justify-content:space-between;gap:18px;padding:10px 0;border-bottom:1px dashed #edf0f4}.detail-list>div:last-child{border-bottom:0}.detail-list dt{color:#7a879a;font-size:.76rem}.detail-list dd{margin:0;text-align:right;font-size:.79rem;font-weight:600;color:#26344d;max-width:64%;overflow-wrap:anywhere}.inline-status,.table-pill{display:inline-flex;align-items:center;border-radius:99px;padding:5px 9px;font-size:.7rem;font-weight:700}.inline-status.success,.table-pill.success{background:#ecfdf3;color:#15803d}.inline-status.warning,.table-pill.warning{background:#fff7ed;color:#c2410c}.inline-status.danger{background:#fef2f2;color:#b91c1c}.inline-status,.table-pill.muted{background:#f1f5f9;color:#64748b}.academic-grid{display:grid;grid-template-columns:1.2fr 1fr;gap:14px}.academic-grid .record-card:last-child{grid-column:span 2}.accent-card{grid-column:span 2}.academic-path{display:flex;align-items:center;gap:14px}.academic-path>div{flex:1;min-width:0;padding:12px;background:#f8fafc;border-radius:10px}.academic-path span{display:block;color:#7a879a;font-size:.69rem;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px}.academic-path strong{display:block;font-size:.84rem;line-height:1.3}.academic-path small{display:block;color:#64748b;font-size:.7rem;margin-top:3px}.academic-path>i{color:#b1bbca;font-size:.75rem}
        .payment-summary{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:14px}.payment-summary>div{background:#fff;border:1px solid var(--student-border);border-radius:13px;padding:15px 17px}.payment-summary span{display:block;color:#64748b;font-size:.75rem;font-weight:700}.payment-summary span i{margin-right:5px;color:#16a34a}.payment-summary>div:nth-child(2) span i{color:#ea580c}.payment-summary strong{display:block;font-size:1.2rem;margin:6px 0 2px}.payment-summary small{color:#98a2b3;font-size:.72rem}.responsive-table{background:#fff;border:1px solid var(--student-border);border-radius:14px;overflow:auto}.record-table{width:100%;min-width:710px;border-collapse:collapse}.record-table th{padding:12px 15px;text-align:left;background:#f8fafc;color:#7b8798;text-transform:uppercase;letter-spacing:.06em;font-size:.65rem;white-space:nowrap}.record-table td{padding:13px 15px;border-top:1px solid #eef2f6;color:#475467;font-size:.77rem;vertical-align:middle}.record-table td strong{display:block;color:#26344d;font-size:.79rem}.record-table td small{display:block;color:#98a2b3;font-size:.69rem;margin-top:3px}.text-end{text-align:right!important}.table-action{border:0;background:transparent;font-size:.72rem;font-weight:700;cursor:pointer;padding:6px 0}.table-action.danger{color:#dc2626}.table-action:hover{text-decoration:underline}.mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.71rem;color:#475467}.student-empty,.student-empty-page{background:#fff;border:1px dashed #d7deea;border-radius:14px;text-align:center;padding:45px 20px;color:#748096}.student-empty-page{max-width:540px;margin:50px auto;padding:70px 25px}.empty-icon{width:50px;height:50px;border-radius:50%;display:grid;place-items:center;background:#eff6ff;color:#2563eb;margin:0 auto 13px;font-size:1.2rem}.student-empty h3,.student-empty-page h3{margin:0 0 6px;font-size:1rem;color:#26344d}.student-empty p,.student-empty-page p{margin:0 0 17px;font-size:.79rem}.document-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.document-card{display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--student-border);border-radius:13px;padding:13px 14px;min-width:0}.document-icon{width:39px;height:39px;border-radius:10px;display:grid;place-items:center;flex:0 0 auto}.document-icon.pdf{background:#fef2f2;color:#dc2626}.document-icon.image{background:#eff6ff;color:#2563eb}.document-copy{min-width:0;flex:1}.document-copy strong,.document-copy span,.document-copy small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.document-copy strong{font-size:.8rem;color:#26344d}.document-copy span{font-size:.71rem;color:#64748b;margin-top:3px}.document-copy small{font-size:.67rem;color:#98a2b3;margin-top:4px}.document-view{color:#2563eb;background:#eff6ff;border-radius:8px;width:31px;height:31px;display:grid;place-items:center;text-decoration:none;flex:0 0 auto}.student-modal{border:0;border-radius:16px;overflow:hidden}.student-modal .modal-header{padding:18px 20px;border-bottom:1px solid #eef2f6}.student-modal .modal-header h5{margin:4px 0 0;color:#172033}.student-modal .modal-body{padding:20px;color:#667085;font-size:.83rem}.student-modal .modal-footer{border-top:1px solid #eef2f6;padding:13px 20px;display:flex;justify-content:flex-end;gap:8px}.student-field-label{display:block;color:#526074;font-size:.76rem;font-weight:700;margin:0 0 6px}.student-field{display:block;width:100%;padding:10px 12px;border:1px solid #d7deea;border-radius:9px;color:#26344d;background:#fff;margin:0 0 14px;font-size:.8rem}.student-field:focus{outline:0;border-color:#60a5fa;box-shadow:0 0 0 3px #dbeafe}.student-modal .section-kicker{color:#64748b}
        @media (max-width:1050px){.student-hero{flex-direction:column}.hero-academic{min-width:0;width:100%}.student-layout{grid-template-columns:1fr}.student-nav{position:static;display:flex;align-items:center;gap:5px;overflow-x:auto;padding:7px}.nav-caption{display:none}.student-nav-link{width:auto;white-space:nowrap;padding:10px 12px}.student-nav-link b{display:none}}
        @media (max-width:700px){.admin-student-page{padding:12px 10px;min-height:calc(100vh - 55px)}.student-toolbar{align-items:flex-start;flex-direction:column;margin-bottom:12px}.toolbar-title{font-size:.78rem}.toolbar-actions{width:100%;display:grid;grid-template-columns:1fr 1fr}.toolbar-actions .student-btn{width:100%;padding:10px 8px;font-size:.74rem}.student-hero{border-radius:14px;padding:18px 15px;gap:18px}.hero-identity{gap:13px}.hero-photo{width:70px;height:70px;border-radius:14px}.hero-copy h1{font-size:1.18rem}.matric-number{font-size:.78rem}.hero-tags{margin-top:10px;gap:5px}.hero-tag{font-size:.65rem;padding:5px 7px}.hero-academic{grid-template-columns:1fr;gap:0}.hero-academic>div{padding:11px 13px}.student-stat-grid{grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:13px}.student-stat{padding:11px 10px;gap:8px}.stat-icon{width:34px;height:34px;font-size:.82rem}.student-stat strong{font-size:.9rem}.student-stat div span{font-size:.64rem}.student-nav{border-radius:11px;margin-bottom:14px}.student-nav-link{font-size:.71rem;padding:9px 10px;gap:7px}.student-nav-link i{font-size:.72rem}.section-heading{align-items:flex-start;flex-direction:column;margin-bottom:12px}.section-heading h2{font-size:1.16rem}.section-heading p{font-size:.74rem}.section-heading>.student-btn{align-self:stretch}.detail-grid,.academic-grid,.document-grid,.payment-summary{grid-template-columns:1fr}.academic-grid .record-card:last-child,.accent-card{grid-column:auto}.academic-path{display:grid;grid-template-columns:1fr;gap:6px}.academic-path>i{display:none}.record-card{padding:14px}.record-card-title{padding-bottom:10px}.detail-list>div{gap:10px;padding:8px 0}.detail-list dt,.detail-list dd{font-size:.72rem}.detail-list dd{max-width:60%}.responsive-table{margin-left:-2px;margin-right:-2px}.record-table{min-width:650px}.record-table th,.record-table td{padding:11px 10px}.student-empty{padding:35px 14px}}
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const page = document.querySelector('.admin-student-page');
            if (!page) return;
            const links = page.querySelectorAll('[data-student-tab]');
            const panels = page.querySelectorAll('[data-student-panel]');
            links.forEach(function (link) {
                link.addEventListener('click', function () {
                    const tab = link.getAttribute('data-student-tab');
                    links.forEach(function (item) { item.classList.toggle('active', item === link); });
                    panels.forEach(function (panel) { panel.classList.toggle('active', panel.getAttribute('data-student-panel') === tab); });
                    if (window.history && window.history.replaceState) {
                        window.history.replaceState(null, '', window.location.pathname + '#' + tab);
                    }
                });
            });
            const hash = window.location.hash.replace('#', '');
            if (hash && page.querySelector('[data-student-tab="' + hash + '"]')) {
                page.querySelector('[data-student-tab="' + hash + '"]').click();
            }
        });
    </script>
@endif
