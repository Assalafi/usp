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

                                @foreach ($settings as $setting)
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

                                <div class="row">
                                    @foreach ($settings as $setting)
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
                                                            <option value="First"
                                                                {{ $setting->value == 'First' ? 'selected' : '' }}>
                                                                First Semester</option>
                                                            <option value="Second"
                                                                {{ $setting->value == 'Second' ? 'selected' : '' }}>
                                                                Second Semester</option>
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
                                                    @if (str_contains($setting->key, 'remita'))
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Handle form submission
        $('#settingsForm').submit(function(e) {
            e.preventDefault();

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
