<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">General Settings</h5>
                        </div>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/"><i class="feather icon-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="#!">System Settings</a></li>
                            <li class="breadcrumb-item"><a href="#!">General</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Categories Sidebar -->
            <div class="col-lg-3 col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Settings Categories</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach ($categories as $catKey => $cat)
                                <a href="{{ route('settings.general', ['category' => $catKey]) }}"
                                    class="list-group-item list-group-item-action d-flex align-items-center {{ $currentCategory == $catKey ? 'active' : '' }}">
                                    <span class="badge bg-{{ $cat['color'] }} me-3">
                                        <i class="{{ $cat['icon'] }}"></i>
                                    </span>
                                    {{ $cat['title'] }}
                                    @if ($currentCategory == $catKey)
                                        <i class="fas fa-chevron-right ms-auto"></i>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-outline-warning btn-sm w-100 mb-2"
                            onclick="resetDefaults()">
                            <i class="fas fa-undo me-2"></i>Reset to Defaults
                        </button>
                        <a href="{{ route('settings.export') }}" class="btn btn-outline-info btn-sm w-100 mb-2">
                            <i class="fas fa-download me-2"></i>Export Settings
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100"
                            onclick="location.reload()">
                            <i class="fas fa-sync me-2"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Settings Form -->
            <div class="col-lg-9 col-md-8">
                <div class="card">
                    <div
                        class="card-header bg-{{ $categories[$currentCategory]['color'] }} text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="{{ $categories[$currentCategory]['icon'] }} me-2"></i>
                            {{ $categories[$currentCategory]['title'] }}
                        </h5>
                        <span class="badge bg-light text-dark">{{ count($settings) }} Settings</span>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form id="settingsForm">
                            @csrf

                            @if ($currentCategory == 'sessions')
                                <!-- Session Management -->
                                <div class="alert alert-primary mb-4">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    <strong>Session Management:</strong> Configure active sessions for different
                                    modules. Each module can have its own active session.
                                </div>

                                @php
                                    $availableSessions = DB::table('session')
                                        ->orderBy('title', 'desc')
                                        ->pluck('title')
                                        ->toArray();
                                @endphp

                                <div class="row">
                                    @foreach ($settings as $setting)
                                        <div class="col-md-6 mb-4">
                                            <div class="card border-primary h-100">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0">
                                                        <i class="fas fa-calendar me-2 text-primary"></i>
                                                        {{ $setting->label }}
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <select class="form-control form-control-lg"
                                                        name="{{ $setting->key }}">
                                                        @foreach ($availableSessions as $sess)
                                                            <option value="{{ $sess }}"
                                                                {{ $setting->value == $sess ? 'selected' : '' }}>
                                                                {{ $sess }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small
                                                        class="text-muted mt-2 d-block">{{ $setting->description }}</small>

                                                    @if ($setting->key == 'system_session')
                                                        <div class="mt-2">
                                                            <span class="badge bg-primary">Main System Session</span>
                                                        </div>
                                                    @elseif($setting->key == 'post_utme_session')
                                                        <div class="mt-2">
                                                            <span class="badge bg-info">Applicant Portal</span>
                                                        </div>
                                                    @elseif($setting->key == 'school_fees_session')
                                                        <div class="mt-2">
                                                            <span class="badge bg-success">Student Payments</span>
                                                        </div>
                                                    @elseif($setting->key == 'hostel_fees_session')
                                                        <div class="mt-2">
                                                            <span
                                                                class="badge bg-warning text-dark">Accommodation</span>
                                                        </div>
                                                    @elseif($setting->key == 'results_session')
                                                        <div class="mt-2">
                                                            <span class="badge bg-secondary">Academic Records</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="card bg-light mt-3">
                                    <div class="card-body">
                                        <h6><i class="fas fa-info-circle me-2"></i>Session Usage Guide</h6>
                                        <ul class="mb-0 small">
                                            <li><strong>System Session:</strong> General operations, course
                                                registration, student records</li>
                                            <li><strong>POST-UTME Session:</strong> Applicant registration, admission
                                                processing</li>
                                            <li><strong>School Fees Session:</strong> School fee payments and invoices
                                            </li>
                                            <li><strong>Hostel Fees Session:</strong> Hostel allocation and
                                                accommodation payments</li>
                                            <li><strong>Results Session:</strong> Result uploads and transcript
                                                generation</li>
                                        </ul>
                                    </div>
                                </div>
                            @elseif($currentCategory == 'hostel')
                                <!-- Hostel Settings -->
                                <div class="alert alert-info mb-4">
                                    <i class="fas fa-building me-2"></i>
                                    <strong>Hostel application control:</strong> Open or close new bed-space
                                    reservations without changing existing hostel allocations.
                                </div>

                                <div class="row">
                                    @foreach ($settings as $setting)
                                        <div class="col-md-{{ in_array($setting->key, ['hostel_closed_message', 'hostel_announcement']) ? '12' : '6' }} mb-4">
                                            <div class="card border-info h-100">
                                                <div class="card-body">
                                                    <label class="form-label fw-bold">
                                                        <i class="fas fa-{{ $setting->key == 'hostel_application_status' ? 'toggle-on' : ($setting->key == 'hostel_announcement' ? 'bullhorn' : 'comment-alt') }} me-1 text-info"></i>
                                                        {{ $setting->label }}
                                                    </label>
                                                    @if ($setting->type == 'boolean')
                                                        <div class="form-check form-switch mt-2">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="{{ $setting->key }}" name="{{ $setting->key }}"
                                                                value="1"
                                                                {{ $setting->value == '1' ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="{{ $setting->key }}">
                                                                <span class="badge {{ $setting->value == '1' ? 'bg-success' : 'bg-danger' }} fs-6">
                                                                    {{ $setting->value == '1' ? 'OPEN' : 'CLOSED' }}
                                                                </span>
                                                            </label>
                                                        </div>
                                                    @else
                                                        @php
                                                            $richValue = \App\Http\Controllers\SystemSettingsController::sanitizeRichText($setting->value);
                                                        @endphp
                                                        <div class="hostel-rich-editor mt-2" data-rich-editor-wrapper>
                                                            <div class="hostel-rich-toolbar" role="toolbar" aria-label="Text formatting tools">
                                                                <select class="hostel-rich-format" data-rich-format aria-label="Text style">
                                                                    <option value="p">Paragraph</option>
                                                                    <option value="h2">Heading</option>
                                                                    <option value="h3">Small heading</option>
                                                                </select>
                                                                <span class="hostel-rich-divider" aria-hidden="true"></span>
                                                                <button type="button" data-command="bold" title="Bold" aria-label="Bold"><i class="fas fa-bold"></i></button>
                                                                <button type="button" data-command="italic" title="Italic" aria-label="Italic"><i class="fas fa-italic"></i></button>
                                                                <button type="button" data-command="underline" title="Underline" aria-label="Underline"><i class="fas fa-underline"></i></button>
                                                                <button type="button" data-command="strikeThrough" title="Strikethrough" aria-label="Strikethrough"><i class="fas fa-strikethrough"></i></button>
                                                                <span class="hostel-rich-divider" aria-hidden="true"></span>
                                                                <button type="button" data-command="justifyLeft" title="Align left" aria-label="Align left"><i class="fas fa-align-left"></i></button>
                                                                <button type="button" data-command="justifyCenter" title="Align center" aria-label="Align center"><i class="fas fa-align-center"></i></button>
                                                                <button type="button" data-command="justifyRight" title="Align right" aria-label="Align right"><i class="fas fa-align-right"></i></button>
                                                                <button type="button" data-command="justifyFull" title="Justify" aria-label="Justify"><i class="fas fa-align-justify"></i></button>
                                                                <span class="hostel-rich-divider" aria-hidden="true"></span>
                                                                <button type="button" data-command="insertUnorderedList" title="Bulleted list" aria-label="Bulleted list"><i class="fas fa-list-ul"></i></button>
                                                                <button type="button" data-command="insertOrderedList" title="Numbered list" aria-label="Numbered list"><i class="fas fa-list-ol"></i></button>
                                                                <button type="button" data-command="createLink" title="Insert link" aria-label="Insert link"><i class="fas fa-link"></i></button>
                                                                <button type="button" data-command="removeFormat" title="Clear formatting" aria-label="Clear formatting"><i class="fas fa-eraser"></i></button>
                                                                <span class="hostel-rich-divider" aria-hidden="true"></span>
                                                                <button type="button" data-command="undo" title="Undo" aria-label="Undo"><i class="fas fa-undo"></i></button>
                                                                <button type="button" data-command="redo" title="Redo" aria-label="Redo"><i class="fas fa-redo"></i></button>
                                                            </div>
                                                            <div class="hostel-rich-surface" data-rich-surface contenteditable="true" role="textbox" aria-multiline="true" spellcheck="true">{!! $richValue !!}</div>
                                                            <textarea class="d-none" name="{{ $setting->key }}" data-rich-source>{{ $richValue }}</textarea>
                                                        </div>
                                                        <small class="text-muted d-block mt-2"><i class="fas fa-pen me-1"></i>Format the message with headings, emphasis, lists, links and left, centre, right or justified alignment.</small>
                                                    @endif
                                                    <small class="text-muted mt-2 d-block">{{ $setting->description }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="card bg-light mt-2">
                                    <div class="card-body">
                                        <h6><i class="fas fa-info-circle me-2"></i>What happens when applications are closed?</h6>
                                        <ul class="mb-0 small">
                                            <li>Students cannot start a new bed-space reservation.</li>
                                            <li>Existing reservations, payments and permits remain available.</li>
                                            <li>Students receive the message written above.</li>
                                        </ul>
                                    </div>
                                </div>
                            @elseif($currentCategory == 'fees')
                                <!-- Fee Settings -->
                                <div class="alert alert-info mb-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Note:</strong> All fee amounts are in Nigerian Naira (₦). Changes will
                                    affect new transactions only.
                                </div>

                                <div class="row">
                                    @foreach ($settings as $setting)
                                        <div class="col-md-6 mb-4">
                                            <div class="card border h-100">
                                                <div class="card-body">
                                                    <label class="form-label fw-bold">
                                                        <i class="fas fa-naira-sign me-1 text-success"></i>
                                                        {{ $setting->label }}
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">₦</span>
                                                        <input type="number" class="form-control form-control-lg"
                                                            name="{{ $setting->key }}" value="{{ $setting->value }}"
                                                            min="0" step="100">
                                                    </div>
                                                    <small class="text-muted">{{ $setting->description }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($currentCategory == 'security')
                                <!-- Security Settings -->
                                <div class="alert alert-warning mb-4">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Warning:</strong> Security settings affect user authentication. Please be
                                    careful when modifying these values.
                                </div>

                                @php
                                    $ninManagement = $ninVerification ?? [
                                        'enabled' => true,
                                        'cycle' => 1,
                                        'session' => null,
                                        'eligible' => 0,
                                        'confirmed' => 0,
                                        'pending' => 0,
                                        'confirmed_today' => 0,
                                        'completion' => 0,
                                        'cycle_started_at' => null,
                                    ];
                                @endphp
                                <style>
                                    .nin-control{border:1px solid #dfe7ee;border-radius:18px;overflow:hidden;background:#fff;box-shadow:0 8px 24px rgba(31,65,91,.07)}
                                    .nin-control-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;padding:22px 24px;background:linear-gradient(135deg,#f7fafc,#eef5f8)}
                                    .nin-control-kicker{display:block;margin-bottom:6px;color:#547086;font-size:.68rem;font-weight:800;letter-spacing:.09em;text-transform:uppercase}
                                    .nin-control h4{margin:0 0 7px;color:#213f56;font-weight:800}.nin-control-head p{max-width:690px;margin:0;color:#6c7f8e;font-size:.82rem;line-height:1.55}
                                    .nin-status{display:inline-flex;align-items:center;gap:7px;white-space:nowrap;border-radius:999px;padding:8px 12px;font-size:.7rem;font-weight:800}
                                    .nin-status.active{background:#e7f6ee;color:#18784a}.nin-status.paused{background:#fff1e5;color:#9b5b14}
                                    .nin-status i{font-size:.55rem}.nin-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;padding:16px 20px}
                                    .nin-stat{border:1px solid #e4ebf0;border-radius:13px;padding:13px;background:#fff}.nin-stat small,.nin-stat strong{display:block}.nin-stat small{color:#7c8e9c;font-size:.66rem;font-weight:700}.nin-stat strong{margin-top:5px;color:#213f56;font-size:1.16rem}.nin-stat span{display:block;margin-top:3px;color:#91a0ab;font-size:.62rem}
                                    .nin-progress-wrap{padding:0 20px 16px}.nin-progress-meta{display:flex;justify-content:space-between;gap:12px;margin-bottom:7px;color:#6c7f8e;font-size:.7rem}.nin-progress{height:8px;overflow:hidden;border-radius:999px;background:#edf2f5}.nin-progress span{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,#2479ad,#36a06f)}
                                    .nin-actions{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:17px 20px;border-top:1px solid #e8eef2;background:#fbfcfd}.nin-actions-note{color:#758795;font-size:.7rem;line-height:1.5}.nin-actions-buttons{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}.nin-actions .btn{font-size:.72rem;font-weight:700}
                                    @media(max-width:850px){.nin-control-head{display:block}.nin-status{margin-top:13px}.nin-stats{grid-template-columns:repeat(2,1fr)}.nin-actions{display:block}.nin-actions-buttons{justify-content:flex-start;margin-top:12px}}
                                    @media(max-width:480px){.nin-control-head{padding:18px}.nin-stats{padding:13px;gap:7px}.nin-stat{padding:11px}.nin-progress-wrap{padding:0 13px 14px}.nin-actions{padding:14px}.nin-actions-buttons .btn{width:100%}}
                                </style>
                                <section class="nin-control mb-4" aria-labelledby="ninControlTitle">
                                    <div class="nin-control-head">
                                        <div>
                                            <span class="nin-control-kicker">Student account security</span>
                                            <h4 id="ninControlTitle"><i class="fas fa-fingerprint me-2"></i>NIN verification control</h4>
                                            <p>Students enter the NIN already stored on their record. A correct match completes the current cycle; a mismatch or duplicate NIN is rejected.</p>
                                        </div>
                                        <span class="nin-status {{ $ninManagement['enabled'] ? 'active' : 'paused' }}">
                                            <i class="fas fa-circle"></i>{{ $ninManagement['enabled'] ? 'ENFORCEMENT ACTIVE' : 'ENFORCEMENT PAUSED' }}
                                        </span>
                                    </div>
                                    <div class="nin-stats" aria-label="NIN verification summary">
                                        <div class="nin-stat"><small>Current cycle</small><strong>{{ number_format($ninManagement['cycle']) }}</strong><span>{{ $ninManagement['session'] ?: 'Current session' }}</span></div>
                                        <div class="nin-stat"><small>Students covered</small><strong>{{ number_format($ninManagement['eligible']) }}</strong><span>Active, submitted profiles</span></div>
                                        <div class="nin-stat"><small>Confirmed</small><strong>{{ number_format($ninManagement['confirmed']) }}</strong><span>{{ number_format($ninManagement['confirmed_today']) }} completed today</span></div>
                                        <div class="nin-stat"><small>Still pending</small><strong>{{ number_format($ninManagement['pending']) }}</strong><span>Restricted while active</span></div>
                                    </div>
                                    <div class="nin-progress-wrap">
                                        <div class="nin-progress-meta"><span>Completion progress</span><strong>{{ number_format($ninManagement['completion'], 1) }}%</strong></div>
                                        <div class="nin-progress" role="progressbar" aria-valuenow="{{ $ninManagement['completion'] }}" aria-valuemin="0" aria-valuemax="100"><span style="width:{{ min(100, max(0, $ninManagement['completion'])) }}%"></span></div>
                                    </div>
                                    <div class="nin-actions">
                                        <div class="nin-actions-note">
                                            @if($ninManagement['cycle_started_at'])
                                                Current cycle started {{ \Carbon\Carbon::parse($ninManagement['cycle_started_at'])->format('d M Y, h:i A') }}.
                                            @endif
                                            Starting a new cycle immediately requires every covered student to confirm again.
                                        </div>
                                        <div class="nin-actions-buttons">
                                            <button type="button" class="btn {{ $ninManagement['enabled'] ? 'btn-outline-warning' : 'btn-success' }}" id="toggleStudentNinVerification" data-enabled="{{ $ninManagement['enabled'] ? '1' : '0' }}">
                                                <i class="fas {{ $ninManagement['enabled'] ? 'fa-pause' : 'fa-play' }} me-2"></i>{{ $ninManagement['enabled'] ? 'Pause verification' : 'Resume verification' }}
                                            </button>
                                            <button type="button" class="btn btn-danger" id="requestStudentNinUpdate">
                                                <i class="fas fa-rotate me-2"></i>{{ $ninManagement['enabled'] ? 'Start a new cycle' : 'Activate a new cycle' }}
                                            </button>
                                        </div>
                                    </div>
                                </section>

                                @foreach ($settings->reject(fn ($setting) => in_array($setting->key, ['student_nin_update_version', 'student_nin_verification_enabled'], true)) as $setting)
                                    <div class="mb-4 p-3 border rounded">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-key me-1 text-danger"></i>
                                            {{ $setting->label }}
                                        </label>
                                        @if ($setting->type == 'boolean')
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox"
                                                    name="{{ $setting->key }}" value="1"
                                                    {{ $setting->value == '1' ? 'checked' : '' }}>
                                                <label
                                                    class="form-check-label">{{ $setting->value == '1' ? 'Enabled' : 'Disabled' }}</label>
                                            </div>
                                        @elseif($setting->type == 'number')
                                            <input type="number" class="form-control" name="{{ $setting->key }}"
                                                value="{{ $setting->value }}" min="1">
                                        @else
                                            <input type="text" class="form-control" name="{{ $setting->key }}"
                                                value="{{ $setting->value }}">
                                        @endif
                                        <small class="text-muted">{{ $setting->description }}</small>
                                    </div>
                                @endforeach
                            @elseif($currentCategory == 'academic')
                                <!-- Academic Settings -->
                                <div class="alert alert-primary mb-4">
                                    <i class="fas fa-graduation-cap me-2"></i>
                                    <strong>Academic Configuration:</strong> These settings control academic operations.
                                </div>

                                @php
                                    $hodCourseMode = \App\Http\Controllers\SystemSettingsController::get('hod_program_course_management_mode', 'view');
                                    $hodCourseUsers = $hodManagementUsers ?? [];
                                @endphp

                                <div class="card border-primary shadow-sm mb-4">
                                    <div class="card-header bg-light d-flex align-items-center justify-content-between gap-2">
                                        <div>
                                            <h5 class="mb-1 text-primary"><i class="fas fa-user-tie me-2"></i>Programme course access for Heads of Department</h5>
                                            <p class="mb-0 text-muted">Heads of Department can always review the programmes in their department. Choose who may add, edit or remove programme courses.</p>
                                        </div>
                                        <span class="badge bg-primary">Academic records</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3 align-items-start">
                                            <div class="col-lg-5">
                                                <label class="form-label fw-bold" for="hod_program_course_management_mode">Editing access</label>
                                                <select class="form-control form-control-lg" id="hod_program_course_management_mode" name="hod_program_course_management_mode">
                                                    <option value="view" {{ $hodCourseMode === 'view' ? 'selected' : '' }}>Review only</option>
                                                    <option value="all" {{ $hodCourseMode === 'all' ? 'selected' : '' }}>All Heads of Department may manage</option>
                                                    <option value="selected" {{ $hodCourseMode === 'selected' ? 'selected' : '' }}>Only selected Heads of Department may manage</option>
                                                </select>
                                                <small class="text-muted d-block mt-2">Review only keeps the page available without allowing changes.</small>
                                            </div>
                                            <div class="col-lg-7">
                                                <label class="form-label fw-bold" for="hod_program_course_management_users">Heads of Department who may manage</label>
                                                <select class="form-control" id="hod_program_course_management_users" name="hod_program_course_management_users[]" multiple size="6" {{ $hodCourseMode === 'selected' ? '' : 'disabled' }}>
                                                    @forelse($hodStaff ?? [] as $hod)
                                                        <option value="{{ $hod->username }}" {{ in_array((string) $hod->username, array_map('strval', $hodCourseUsers), true) ? 'selected' : '' }}>
                                                            {{ $hod->name ?: $hod->username }} — {{ $hod->department_title ?: $hod->department ?: 'Department not set' }}
                                                        </option>
                                                    @empty
                                                        <option disabled>No active Head of Department records were found.</option>
                                                    @endforelse
                                                </select>
                                                <small class="text-muted d-block mt-2">Hold Ctrl (Windows) or Command (Mac) to select more than one person.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    @foreach ($settings->reject(fn ($setting) => in_array($setting->key, ['hod_program_course_management_mode', 'hod_program_course_management_users'], true)) as $setting)
                                        <div class="col-md-6 mb-4">
                                            <div class="card border h-100">
                                                <div class="card-body">
                                                    <label class="form-label fw-bold">
                                                        <i class="fas fa-cog me-1 text-primary"></i>
                                                        {{ $setting->label }}
                                                    </label>
                                                    @if ($setting->type == 'boolean')
                                                        <div class="form-check form-switch mt-2">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="{{ $setting->key }}" name="{{ $setting->key }}"
                                                                value="1"
                                                                {{ $setting->value == '1' ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="{{ $setting->key }}">
                                                                <span
                                                                    class="badge {{ $setting->value == '1' ? 'bg-success' : 'bg-danger' }}">
                                                                    {{ $setting->value == '1' ? 'OPEN' : 'CLOSED' }}
                                                                </span>
                                                            </label>
                                                        </div>
                                                    @elseif($setting->key == 'current_semester')
                                                        <select class="form-control" name="{{ $setting->key }}">
                                                            @forelse ($semesters as $sem)
                                                                <option value="{{ $sem }}" @if(strtoupper($setting->value) == strtoupper($sem)) selected @endif>
                                                                    {{ ucfirst(strtolower($sem)) }} Semester</option>
                                                            @empty
                                                                <option value="FIRST" @if(strtoupper($setting->value) == 'FIRST') selected @endif>First Semester</option>
                                                                <option value="SECOND" @if(strtoupper($setting->value) == 'SECOND') selected @endif>Second Semester</option>
                                                            @endforelse
                                                        </select>
                                                    @elseif($setting->type == 'number')
                                                        <input type="number" class="form-control"
                                                            name="{{ $setting->key }}" value="{{ $setting->value }}"
                                                            min="1">
                                                    @else
                                                        <input type="text" class="form-control"
                                                            name="{{ $setting->key }}"
                                                            value="{{ $setting->value }}">
                                                    @endif
                                                    <small
                                                        class="text-muted mt-2 d-block">{{ $setting->description }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($currentCategory == 'recruitment')
                                <!-- Recruitment Portal Settings -->
                                <div class="alert alert-info mb-4">
                                    <i class="fas fa-user-tie me-2"></i>
                                    <strong>Recruitment Portal:</strong> Control the recruitment application process. These settings are synced with the recruitment portal at <code>employee.umstad.online</code>.
                                </div>

                                <div class="row">
                                    @foreach ($settings as $setting)
                                        <div class="col-md-{{ $setting->key == 'recruitment_closed_message' ? '12' : '6' }} mb-4">
                                            <div class="card border h-100">
                                                <div class="card-body">
                                                    <label class="form-label fw-bold">
                                                        <i class="fas fa-{{ $setting->key == 'recruitment_status' ? 'toggle-on' : 'comment-alt' }} me-1 text-info"></i>
                                                        {{ $setting->label }}
                                                    </label>
                                                    @if ($setting->type == 'boolean')
                                                        <div class="form-check form-switch mt-2">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="{{ $setting->key }}" name="{{ $setting->key }}"
                                                                value="1"
                                                                {{ $setting->value == '1' ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="{{ $setting->key }}">
                                                                <span class="badge {{ $setting->value == '1' ? 'bg-success' : 'bg-danger' }} fs-6">
                                                                    {{ $setting->value == '1' ? 'OPEN' : 'CLOSED' }}
                                                                </span>
                                                            </label>
                                                        </div>
                                                    @else
                                                        <textarea class="form-control mt-2" name="{{ $setting->key }}" rows="3">{{ $setting->value }}</textarea>
                                                    @endif
                                                    <small class="text-muted mt-2 d-block">{{ $setting->description }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="card bg-light mt-3">
                                    <div class="card-body">
                                        <h6><i class="fas fa-info-circle me-2"></i>How it works</h6>
                                        <ul class="mb-0 small">
                                            <li><strong>OPEN:</strong> Applicants can register, create, save drafts, and submit applications.</li>
                                            <li><strong>CLOSED:</strong> Applicants can still log in and view their submitted applications, but cannot create new applications or continue editing drafts.</li>
                                            <li>The <strong>Closed Message</strong> is displayed to applicants on the dashboard and if they try to access the application form.</li>
                                        </ul>
                                    </div>
                                </div>

                            @elseif($currentCategory == 'institution')
                                <!-- Institution Settings -->
                                <div class="text-center mb-4">
                                    <img src="{{ asset('uploads/logo.png') }}" alt="Logo" style="height: 80px;"
                                        class="mb-2">
                                    <h4 class="text-primary">Institution Information</h4>
                                </div>

                                @foreach ($settings as $setting)
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-university me-1 text-info"></i>
                                            {{ $setting->label }}
                                        </label>
                                        @if ($setting->key == 'institution_address')
                                            <textarea class="form-control" name="{{ $setting->key }}" rows="2">{{ $setting->value }}</textarea>
                                        @else
                                            <input type="text" class="form-control" name="{{ $setting->key }}"
                                                value="{{ $setting->value }}">
                                        @endif
                                        <small class="text-muted">{{ $setting->description }}</small>
                                    </div>
                                @endforeach
                            @elseif($currentCategory == 'payment')
                                <!-- Payment Gateway Settings -->
                                <div class="alert alert-warning mb-4">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Caution:</strong> These are sensitive payment credentials. Only modify if
                                    you know what you're doing.
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border-warning mb-3">
                                            <div class="card-header bg-warning text-dark">
                                                <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Remita
                                                    Configuration</h6>
                                            </div>
                                            <div class="card-body">
                                                @foreach ($settings as $setting)
                                                    @if (str_contains($setting->key, 'remita') && !in_array($setting->key, ['remita_service_catalog', 'remita_split_settings'], true))
                                                        <div class="mb-3">
                                                            <label
                                                                class="form-label fw-bold">{{ $setting->label }}</label>
                                                            @if ($setting->key == 'remita_mode')
                                                                <select class="form-control form-control-lg"
                                                                    name="{{ $setting->key }}">
                                                                    <option value="demo"
                                                                        {{ $setting->value == 'demo' ? 'selected' : '' }}>
                                                                        🧪 Demo (Test Mode)</option>
                                                                    <option value="live"
                                                                        {{ $setting->value == 'live' ? 'selected' : '' }}>
                                                                        🟢 Live (Production)</option>
                                                                </select>
                                                            @else
                                                                <input type="text" class="form-control"
                                                                    name="{{ $setting->key }}"
                                                                    value="{{ $setting->value }}">
                                                            @endif
                                                            <small
                                                                class="text-muted">{{ $setting->description }}</small>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-info h-100">
                                            <div class="card-header bg-info text-white">
                                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Payment Info
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <p><strong>Current Environment:</strong>
                                                    @php $env = $settings->where('key', 'remita_mode')->first(); @endphp
                                                    <span
                                                        class="badge {{ ($env->value ?? 'demo') == 'demo' ? 'bg-warning text-dark' : 'bg-success' }}">
                                                        {{ ($env->value ?? 'demo') == 'demo' ? '🧪 DEMO' : '🟢 LIVE' }}
                                                    </span>
                                                </p>
                                                <hr>
                                                <p class="mb-1"><strong>Demo URL:</strong></p>
                                                <code>https://demo.remita.net/</code>
                                                <p class="mb-1 mt-2"><strong>Live URL:</strong></p>
                                                <code>https://login.remita.net/</code>
                                                <hr>
                                                <small class="text-muted">
                                                    Always test in demo mode before switching to live.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-primary mb-4">
                                    <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between gap-2">
                                        <div>
                                            <h6 class="mb-1"><i class="fas fa-list-check me-2"></i>Remita service types</h6>
                                            <small>Register payment services here. Set a base amount and choose whether it is available to UG students and/or Alumni. Programme fees remain separate.</small>
                                        </div>
                                        <button type="button" class="btn btn-light btn-sm" id="addRemitaService"><i class="fas fa-plus me-1"></i>Add service</button>
                                    </div>
                                    <div class="card-body p-0">
                                        <input type="hidden" name="remita_service_catalog" id="remitaServiceCatalogPayload">
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle mb-0" id="remitaServiceTable">
                                                <thead class="table-light"><tr><th>Service key</th><th>Display name</th><th>Remita Service Type ID</th><th>Base amount (₦)</th><th>Additional charge (₦)</th><th>Student optional</th><th>Alumni optional</th><th>Use service</th><th>Use split</th><th></th></tr></thead>
                                                <tbody>
                                                    @php($lockedRemitaServices = ['school_fees', 'hostel', 'post_utme', 'id_card', 'change_of_course', 'inter_university_transfer'])
                                                    @foreach ($remitaServiceCatalog as $service)
                                                        <tr data-remita-service-row>
                                                            <td><input type="text" class="form-control form-control-sm" data-field="key" value="{{ $service['key'] }}" {{ in_array($service['key'], $lockedRemitaServices, true) ? 'readonly' : '' }}></td>
                                                            <td><input type="text" class="form-control form-control-sm" data-field="label" value="{{ $service['label'] }}"></td>
                                                            <td><input type="text" class="form-control form-control-sm" data-field="service_type_id" value="{{ $service['service_type_id'] }}" inputmode="numeric" placeholder="e.g. 365039916"></td>
                                                            <td><input type="number" min="0" step="0.01" class="form-control form-control-sm" data-field="amount" value="{{ $service['amount'] ?? '0.00' }}" inputmode="decimal"></td>
                                                            <td><input type="number" min="0" step="0.01" class="form-control form-control-sm" data-field="surcharge" value="{{ $service['surcharge'] ?? '0.00' }}" inputmode="decimal"></td>
                                                            <td class="text-center"><input type="checkbox" class="form-check-input" data-field="optional" {{ !empty($service['optional']) ? 'checked' : '' }} title="Show this service to UG students"></td>
                                                            <td class="text-center"><input type="checkbox" class="form-check-input" data-field="alumni" {{ !empty($service['alumni']) ? 'checked' : '' }} title="Show this service to UG Alumni"></td>
                                                            <td class="text-center"><input type="checkbox" class="form-check-input" data-field="active" {{ $service['active'] ? 'checked' : '' }}></td>
                                                            <td class="text-center"><input type="checkbox" class="form-check-input" data-field="split_enabled" {{ $service['split_enabled'] ? 'checked' : '' }}></td>
                                                            <td>@if (!in_array($service['key'], $lockedRemitaServices, true))<button type="button" class="btn btn-sm btn-outline-danger" data-remove-remita-service title="Remove service"><i class="fas fa-trash"></i></button>@else<span class="text-muted small" title="Built-in service">Built-in</span>@endif</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <template id="remitaServiceTemplate"><tr data-remita-service-row>
                                            <td><input type="text" class="form-control form-control-sm" data-field="key" placeholder="e.g. transcript"></td>
                                            <td><input type="text" class="form-control form-control-sm" data-field="label" placeholder="Service name"></td>
                                            <td><input type="text" class="form-control form-control-sm" data-field="service_type_id" inputmode="numeric" placeholder="Service Type ID"></td>
                                            <td><input type="number" min="0" step="0.01" class="form-control form-control-sm" data-field="amount" value="0.00" inputmode="decimal"></td>
                                            <td><input type="number" min="0" step="0.01" class="form-control form-control-sm" data-field="surcharge" value="0.00" inputmode="decimal"></td>
                                            <td class="text-center"><input type="checkbox" class="form-check-input" data-field="optional" checked title="Show this service to UG students"></td>
                                            <td class="text-center"><input type="checkbox" class="form-check-input" data-field="alumni" title="Show this service to UG Alumni"></td>
                                            <td class="text-center"><input type="checkbox" class="form-check-input" data-field="active" checked></td>
                                            <td class="text-center"><input type="checkbox" class="form-check-input" data-field="split_enabled"></td>
                                            <td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-remita-service title="Remove service"><i class="fas fa-trash"></i></button></td>
                                        </tr></template>
                                    </div>
                                </div>

                                <div class="card border-success mb-3">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-1"><i class="fas fa-code-branch me-2"></i>Split-payment preparation</h6>
                                        <small>Configure the optional charge and three settlement beneficiaries. The split rules are applied only to services marked “Use split” after the Remita split feature is enabled.</small>
                                    </div>
                                    <div class="card-body">
                                        <input type="hidden" name="remita_split_settings" id="remitaSplitSettingsPayload">
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Split payments</label>
                                                <div class="form-check form-switch mt-2"><input class="form-check-input" type="checkbox" id="remitaSplitEnabled" data-split-field="enabled" {{ !empty($remitaSplitSettings['enabled']) ? 'checked' : '' }}><label class="form-check-label" for="remitaSplitEnabled">Enable when ready</label></div>
                                            </div>
                                            <div class="col-md-4"><label class="form-label fw-bold">Service-specific charges</label><div class="border rounded-3 bg-light p-2 small text-muted"><i class="fas fa-circle-info me-1"></i>Set the additional charge for each service in the service table above. A service may have a different amount, including zero.</div></div>
                                            <div class="col-md-4"><label class="form-label fw-bold">Fee bearer</label><input type="text" class="form-control" value="University main account" readonly><small class="text-muted">One beneficiary must bear Remita’s transaction fee.</small></div>
                                        </div>
                                        <div class="row g-3">
                                            @foreach (($remitaSplitSettings['beneficiaries'] ?? []) as $index => $beneficiary)
                                                <div class="col-lg-4"><div class="border rounded-3 p-3 h-100 bg-light"><h6 class="mb-3">{{ $index === 0 ? 'Primary account' : 'Additional account ' . $index }}</h6><label class="form-label small">Beneficiary name</label><input type="text" class="form-control form-control-sm mb-2" data-beneficiary-field="label" data-beneficiary-index="{{ $index }}" value="{{ $beneficiary['label'] ?? '' }}"><label class="form-label small">Account number</label><input type="text" class="form-control form-control-sm mb-2" data-beneficiary-field="account_number" data-beneficiary-index="{{ $index }}" value="{{ $beneficiary['account_number'] ?? '' }}" inputmode="numeric" autocomplete="off"><label class="form-label small">Bank code</label><input type="text" class="form-control form-control-sm mb-2" data-beneficiary-field="bank_code" data-beneficiary-index="{{ $index }}" value="{{ $beneficiary['bank_code'] ?? '' }}" inputmode="numeric" maxlength="12"><label class="form-label small">Share percentage</label><div class="input-group input-group-sm"><input type="number" min="0" max="100" step="0.01" class="form-control" data-beneficiary-field="percentage" data-beneficiary-index="{{ $index }}" value="{{ $beneficiary['percentage'] ?? 0 }}"><span class="input-group-text">%</span></div></div></div>
                                            @endforeach
                                        </div>
                                        <div class="alert alert-info small mt-3 mb-0"><i class="fas fa-circle-info me-1"></i>The three shares must total exactly 100%. Account details are stored securely and are never sent to the browser of a student.</div>
                                    </div>
                                </div>
                            @elseif($currentCategory == 'email')
                                <!-- Email Settings -->
                                <div class="alert alert-secondary mb-4">
                                    <i class="fas fa-envelope me-2"></i>
                                    <strong>Email Configuration:</strong> Configure SMTP settings for sending emails.
                                </div>

                                <div class="row">
                                    @foreach ($settings as $setting)
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-mail-bulk me-1 text-secondary"></i>
                                                {{ $setting->label }}
                                            </label>
                                            @if ($setting->type == 'boolean')
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="{{ $setting->key }}" value="1"
                                                        {{ $setting->value == '1' ? 'checked' : '' }}>
                                                    <label
                                                        class="form-check-label">{{ $setting->value == '1' ? 'Enabled' : 'Disabled' }}</label>
                                                </div>
                                            @elseif($setting->key == 'smtp_password')
                                                <input type="password" class="form-control"
                                                    name="{{ $setting->key }}" value="{{ $setting->value }}"
                                                    placeholder="••••••••">
                                            @elseif($setting->type == 'number')
                                                <input type="number" class="form-control"
                                                    name="{{ $setting->key }}" value="{{ $setting->value }}">
                                            @else
                                                <input type="text" class="form-control"
                                                    name="{{ $setting->key }}" value="{{ $setting->value }}">
                                            @endif
                                            <small class="text-muted">{{ $setting->description }}</small>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($currentCategory == 'maintenance')
                                <!-- Maintenance Settings -->
                                <div class="row">
                                    <div class="col-md-6">
                                        @foreach ($settings as $setting)
                                            <div
                                                class="mb-4 p-4 border rounded {{ $setting->key == 'maintenance_mode' && $setting->value == '1' ? 'border-danger bg-light' : '' }}">
                                                <label class="form-label fw-bold">
                                                    <i class="fas fa-tools me-1 text-dark"></i>
                                                    {{ $setting->label }}
                                                </label>
                                                @if ($setting->type == 'boolean')
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="{{ $setting->key }}" name="{{ $setting->key }}"
                                                            value="1"
                                                            {{ $setting->value == '1' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="{{ $setting->key }}">
                                                            @if ($setting->key == 'maintenance_mode')
                                                                <span
                                                                    class="badge {{ $setting->value == '1' ? 'bg-danger' : 'bg-success' }} fs-6">
                                                                    {{ $setting->value == '1' ? 'MAINTENANCE MODE ON' : 'System Online' }}
                                                                </span>
                                                            @else
                                                                {{ $setting->value == '1' ? 'Enabled' : 'Disabled' }}
                                                            @endif
                                                        </label>
                                                    </div>
                                                @else
                                                    <textarea class="form-control" name="{{ $setting->key }}" rows="3">{{ $setting->value }}</textarea>
                                                @endif
                                                <small class="text-muted">{{ $setting->description }}</small>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-danger">
                                            <div class="card-header bg-danger text-white">
                                                <h6 class="mb-0"><i
                                                        class="fas fa-exclamation-triangle me-2"></i>Warning</h6>
                                            </div>
                                            <div class="card-body">
                                                <p>Enabling maintenance mode will:</p>
                                                <ul>
                                                    <li>Block access for all non-admin users</li>
                                                    <li>Display maintenance message to visitors</li>
                                                    <li>Prevent new registrations and payments</li>
                                                </ul>
                                                <p class="mb-0 text-danger"><strong>Use only for critical
                                                        updates!</strong></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Generic Settings Display -->
                                @foreach ($settings as $setting)
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">{{ $setting->label }}</label>
                                        @if ($setting->type == 'boolean')
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox"
                                                    name="{{ $setting->key }}" value="1"
                                                    {{ $setting->value == '1' ? 'checked' : '' }}>
                                            </div>
                                        @elseif($setting->type == 'number')
                                            <input type="number" class="form-control" name="{{ $setting->key }}"
                                                value="{{ $setting->value }}">
                                        @else
                                            <input type="text" class="form-control" name="{{ $setting->key }}"
                                                value="{{ $setting->value }}">
                                        @endif
                                        <small class="text-muted">{{ $setting->description }}</small>
                                    </div>
                                @endforeach
                            @endif

                            <hr>
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary" onclick="location.reload()">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($currentCategory == 'hostel')
    <style>
        .hostel-rich-editor {
            overflow: hidden;
            border: 1px solid #d6e2eb;
            border-radius: .5rem;
            background: #fff;
        }

        .hostel-rich-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .2rem;
            padding: .45rem;
            border-bottom: 1px solid #d6e2eb;
            background: #f7fafc;
        }

        .hostel-rich-toolbar button,
        .hostel-rich-format {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.1rem;
            height: 2rem;
            border: 1px solid transparent;
            border-radius: .3rem;
            background: transparent;
            color: #39566b;
            cursor: pointer;
        }

        .hostel-rich-toolbar button:hover,
        .hostel-rich-toolbar button:focus,
        .hostel-rich-format:focus {
            border-color: #8fc5e3;
            background: #eaf6fd;
            color: #126d9f;
            outline: none;
        }

        .hostel-rich-format {
            min-width: 8.5rem;
            justify-content: flex-start;
            padding: 0 .45rem;
            background: #fff;
        }

        .hostel-rich-divider {
            width: 1px;
            height: 1.4rem;
            margin: 0 .2rem;
            background: #d6e2eb;
        }

        .hostel-rich-surface {
            min-height: 155px;
            padding: .85rem 1rem;
            color: #243b4d;
            line-height: 1.6;
            outline: none;
        }

        .hostel-rich-surface:focus {
            box-shadow: inset 0 0 0 2px rgba(62, 161, 228, .2);
        }

        .hostel-rich-surface p,
        .hostel-rich-surface h2,
        .hostel-rich-surface h3,
        .hostel-rich-surface blockquote {
            margin: 0 0 .55rem;
        }

        .hostel-rich-surface ul,
        .hostel-rich-surface ol {
            margin: .25rem 0 .65rem;
            padding-left: 1.4rem;
        }

        .hostel-rich-surface blockquote {
            border-left: 3px solid #9bcfe9;
            padding-left: .8rem;
            color: #587284;
        }

        @media (max-width: 576px) {
            .hostel-rich-toolbar {
                gap: .1rem;
            }

            .hostel-rich-toolbar button {
                min-width: 1.95rem;
            }
        }
    </style>
    <script>
        (function () {
            function initialiseHostelEditors() {
                document.querySelectorAll('[data-rich-editor-wrapper]').forEach(function (wrapper) {
                    var surface = wrapper.querySelector('[data-rich-surface]');
                    var source = wrapper.querySelector('[data-rich-source]');
                    if (!surface || !source) return;

                    var sync = function () { source.value = surface.innerHTML; };
                    var focusSurface = function () { surface.focus(); };

                    wrapper.querySelectorAll('[data-command]').forEach(function (button) {
                        button.addEventListener('mousedown', function (event) { event.preventDefault(); });
                        button.addEventListener('click', function () {
                            var command = button.getAttribute('data-command');
                            focusSurface();
                            if (command === 'createLink') {
                                var url = window.prompt('Enter the link address', 'https://');
                                if (url) document.execCommand(command, false, url.trim());
                            } else {
                                document.execCommand(command, false, null);
                            }
                            sync();
                        });
                    });

                    var format = wrapper.querySelector('[data-rich-format]');
                    if (format) {
                        format.addEventListener('change', function () {
                            focusSurface();
                            document.execCommand('formatBlock', false, '<' + format.value + '>');
                            sync();
                        });
                    }

                    surface.addEventListener('input', sync);
                    var form = wrapper.closest('form');
                    if (form) form.addEventListener('submit', sync, true);
                    sync();
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initialiseHostelEditors);
            } else {
                initialiseHostelEditors();
            }
        })();
    </script>
@endif

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function collectRemitaPaymentSettings() {
        var catalog = [];
        document.querySelectorAll('#remitaServiceTable [data-remita-service-row]').forEach(function (row) {
            catalog.push({
                key: row.querySelector('[data-field="key"]')?.value.trim() || '',
                label: row.querySelector('[data-field="label"]')?.value.trim() || '',
                service_type_id: row.querySelector('[data-field="service_type_id"]')?.value.trim() || '',
                description: row.querySelector('[data-field="label"]')?.value.trim() || '',
                amount: row.querySelector('[data-field="amount"]')?.value || '0',
                surcharge: row.querySelector('[data-field="surcharge"]')?.value || '0',
                optional: !!row.querySelector('[data-field="optional"]')?.checked,
                alumni: !!row.querySelector('[data-field="alumni"]')?.checked,
                active: !!row.querySelector('[data-field="active"]')?.checked,
                split_enabled: !!row.querySelector('[data-field="split_enabled"]')?.checked
            });
        });
        var catalogTarget = document.getElementById('remitaServiceCatalogPayload');
        if (catalogTarget) catalogTarget.value = JSON.stringify(catalog);

        var beneficiaries = [0, 1, 2].map(function (index) {
            var field = function (name) {
                return document.querySelector('[data-beneficiary-index="' + index + '"][data-beneficiary-field="' + name + '"]')?.value.trim() || '';
            };
            return {
                key: index === 0 ? 'primary' : 'additional_' + index,
                label: field('label'),
                account_number: field('account_number'),
                bank_code: field('bank_code'),
                percentage: parseFloat(field('percentage') || '0')
            };
        });
        var splitTarget = document.getElementById('remitaSplitSettingsPayload');
        if (splitTarget) {
            splitTarget.value = JSON.stringify({
                enabled: !!document.getElementById('remitaSplitEnabled')?.checked,
                surcharge: '0',
                fee_bearer: 'primary',
                beneficiaries: beneficiaries
            });
        }
    }

    document.addEventListener('click', function (event) {
        var removeButton = event.target.closest('[data-remove-remita-service]');
        if (removeButton) {
            removeButton.closest('[data-remita-service-row]')?.remove();
        }
        if (event.target.closest('#addRemitaService')) {
            var template = document.getElementById('remitaServiceTemplate');
            var body = document.querySelector('#remitaServiceTable tbody');
            if (template && body) body.appendChild(template.content.cloneNode(true));
        }
    });

    $(document).ready(function() {
        $('#hod_program_course_management_mode').on('change', function() {
            $('#hod_program_course_management_users').prop('disabled', $(this).val() !== 'selected');
        });

        $('#requestStudentNinUpdate').on('click', function() {
            var button = $(this);
            Swal.fire({
                title: 'Start a new NIN confirmation cycle?',
                text: 'All covered undergraduate students will be required to confirm the NIN already stored on their record before using portal services. This also activates enforcement.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Start new cycle',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (!result.isConfirmed) return;
                button.prop('disabled', true);
                var original = button.html();
                button.html('<i class="fas fa-spinner fa-spin me-2"></i>Starting cycle...');
                $.ajax({
                    url: '{{ route('settings.request-nin-update') }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        Swal.fire({ icon: 'success', title: 'Confirmation requested', text: response.message }).then(function() { location.reload(); });
                    },
                    error: function(xhr) {
                        button.prop('disabled', false);
                        button.html(original);
                        Swal.fire({ icon: 'error', title: 'Could not start request', text: xhr.responseJSON?.error || 'Please try again.' });
                    }
                });
            });
        });

        $('#toggleStudentNinVerification').on('click', function() {
            var button = $(this);
            var currentlyEnabled = button.data('enabled') == 1;
            var enabling = !currentlyEnabled;
            Swal.fire({
                title: enabling ? 'Resume NIN verification?' : 'Pause NIN verification?',
                text: enabling
                    ? 'Students who have not completed the current cycle will be redirected to the NIN confirmation screen.'
                    : 'Students will be allowed to use the portal without completing NIN confirmation. Existing confirmations will remain recorded.',
                icon: enabling ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonText: enabling ? 'Resume verification' : 'Pause verification',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (!result.isConfirmed) return;
                var original = button.html();
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...');
                $.ajax({
                    url: '{{ route('settings.student-nin-verification') }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', enabled: enabling ? 1 : 0 },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: response.enabled ? 'Verification active' : 'Verification paused',
                            text: response.message
                        }).then(function() { location.reload(); });
                    },
                    error: function(xhr) {
                        button.prop('disabled', false).html(original);
                        var message = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Please try again.';
                        Swal.fire({ icon: 'error', title: 'Setting not changed', text: message });
                    }
                });
            });
        });

        // Handle form submission
        $('#settingsForm').submit(function(e) {
            e.preventDefault();
            collectRemitaPaymentSettings();

            Swal.fire({
                title: 'Saving Settings...',
                text: 'Please wait',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Handle unchecked checkboxes
            $(this).find('input[type=checkbox]:not(:checked)').each(function() {
                $(this).after($('<input>').attr({
                    type: 'hidden',
                    name: $(this).attr('name'),
                    value: '0'
                }));
            });

            $.ajax({
                url: '{{ route('settings.update') }}',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: true
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message ||
                            'Failed to save settings',
                    });
                }
            });
        });

        // Real-time toggle update
        $('input[type=checkbox]').change(function() {
            var key = $(this).attr('name');
            var value = $(this).is(':checked') ? '1' : '0';
            var label = $(this).closest('.form-check').find('.badge');

            if (label.length) {
                if (key == 'maintenance_mode') {
                    label.removeClass('bg-success bg-danger')
                        .addClass(value == '1' ? 'bg-danger' : 'bg-success')
                        .text(value == '1' ? 'MAINTENANCE MODE ON' : 'System Online');
                } else {
                    label.removeClass('bg-success bg-danger')
                        .addClass(value == '1' ? 'bg-success' : 'bg-danger')
                        .text(value == '1' ? 'OPEN' : 'CLOSED');
                }
            }
        });
    });

    function resetDefaults() {
        Swal.fire({
            title: 'Reset to Defaults?',
            text: 'This will reset all {{ $categories[$currentCategory]['title'] }} to their default values.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, reset!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route('settings.reset') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        category: '{{ $currentCategory }}'
                    },
                    success: function(response) {
                        Swal.fire('Reset!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to reset settings', 'error');
                    }
                });
            }
        });
    }
</script>
