@php
    use Illuminate\Support\Facades\DB;
    $activeTab = request('tab', 'personal');
    $editMode = request('mode', 'view') === 'edit';
@endphp

<style>
    :root {
        --primary: #4361ee;
        --primary-dark: #3a56d4;
        --accent: #06d6a0;
        --dark: #2c3e50;
        --gray: #6c757d;
        --light-gray: #e9ecef;
        --card-shadow: 0 4px 20px rgba(0,0,0,0.08);
        --transition: all 0.3s ease;
    }

    .sp-container * { box-sizing: border-box; }
    .sp-container { max-width: 1200px; margin: 0 auto; padding: 20px; overflow-x: hidden; }

    .sp-header {
        background: linear-gradient(135deg, var(--primary), #7209b7);
        color: white; border-radius: 16px; padding: 25px 30px;
        margin-bottom: 25px; display: flex; align-items: center; gap: 20px; flex-wrap: wrap;
    }
    .sp-header img { width: 90px; height: 90px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.4); object-fit: cover; }
    .sp-header .placeholder { width: 90px; height: 90px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 36px; border: 3px solid rgba(255,255,255,0.3); }
    .sp-header-info h4 { margin: 0 0 4px; font-weight: 700; }
    .sp-header-info span { opacity: 0.85; font-size: 14px; margin-right: 10px; }
    .sp-header-actions { margin-left: auto; }
    .sp-edit-toggle {
        background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.5);
        color: white; padding: 10px 22px; border-radius: 30px; font-weight: 600;
        font-size: 14px; cursor: pointer; transition: var(--transition); text-decoration: none;
    }
    .sp-edit-toggle:hover { background: rgba(255,255,255,0.3); color: white; text-decoration: none; }
    .sp-edit-toggle.editing { background: #dc3545; border-color: #dc3545; }

    .sp-tabs { display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 20px; background: white; border-radius: 12px; padding: 6px; box-shadow: var(--card-shadow); }
    .sp-tab { padding: 10px 18px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; color: var(--gray); border: none; background: none; transition: var(--transition); }
    .sp-tab:hover { background: #f0f4ff; color: var(--primary); }
    .sp-tab.active { background: var(--primary); color: white; }

    .sp-panel { display: none; background: white; border-radius: 14px; padding: 30px; box-shadow: var(--card-shadow); }
    .sp-panel.active { display: block; }

    .sp-section-title { font-size: 18px; font-weight: 700; color: var(--primary); margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--light-gray); display: flex; align-items: center; gap: 8px; }

    /* View Mode */
    .sp-view-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px; margin-bottom: 20px; }
    .sp-view-item { padding: 12px 16px; background: #f8fafc; border-radius: 10px; border-left: 3px solid var(--primary); }
    .sp-view-item .label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gray); font-weight: 600; margin-bottom: 3px; }
    .sp-view-item .value { font-size: 15px; color: var(--dark); font-weight: 500; word-break: break-word; }
    .sp-view-item .value.empty { color: #adb5bd; font-style: italic; }

    /* Edit Mode */
    .sp-form-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin-bottom: 16px; }
    .sp-form-group { margin-bottom: 16px; }
    .sp-form-group label { display: block; font-weight: 600; font-size: 13px; color: var(--dark); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.3px; }
    .sp-form-group input, .sp-form-group select, .sp-form-group textarea {
        width: 100%; padding: 10px 14px; border: 2px solid var(--light-gray); border-radius: 10px;
        font-size: 14px; transition: var(--transition); background: #fafbfc;
    }
    .sp-form-group input:focus, .sp-form-group select:focus, .sp-form-group textarea:focus {
        border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); background: white;
    }
    .sp-form-group input[readonly] { background: #f0f0f0; cursor: not-allowed; }

    .sp-save-btn {
        background: linear-gradient(to right, var(--primary), var(--primary-dark));
        color: white; border: none; border-radius: 10px; padding: 12px 28px;
        font-size: 15px; font-weight: 600; cursor: pointer; transition: var(--transition);
        display: inline-flex; align-items: center; gap: 8px; margin-top: 10px;
    }
    .sp-save-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(67,97,238,0.3); }

    .sp-doc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 16px; }
    .sp-doc-item { border: 2px dashed var(--light-gray); border-radius: 12px; padding: 16px; text-align: center; transition: var(--transition); }
    .sp-doc-item:hover { border-color: var(--primary); background: #f8faff; }
    .sp-doc-item label.doc-label { font-weight: 600; font-size: 13px; color: var(--dark); margin-bottom: 10px; display: block; }
    .sp-doc-item input[type="file"] { font-size: 12px; max-width: 100%; }
    .sp-doc-item .doc-status { font-size: 11px; margin-top: 6px; }
    .sp-doc-item .doc-status.uploaded { color: #28a745; }
    .sp-doc-item .doc-status.pending { color: var(--gray); }
    .sp-doc-hint { font-size: 11px; color: var(--gray); margin-top: 4px; }

    .sp-experience-entry { border: 1px solid var(--light-gray); border-radius: 10px; padding: 16px; margin-bottom: 12px; position: relative; }
    .sp-add-btn { background: none; border: 2px dashed var(--primary); color: var(--primary); border-radius: 8px; padding: 8px 16px; font-weight: 600; cursor: pointer; font-size: 13px; }
    .sp-add-btn:hover { background: #f0f4ff; }
    .sp-remove-btn { position: absolute; top: 8px; right: 8px; background: #dc3545; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; font-size: 12px; cursor: pointer; }

    .sp-doc-view-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; }
    .sp-doc-view-item { padding: 12px; border-radius: 10px; border: 1px solid var(--light-gray); text-align: center; }
    .sp-doc-view-item .doc-name { font-size: 12px; font-weight: 600; color: var(--dark); margin-bottom: 4px; }
    .sp-doc-view-item .doc-badge { font-size: 11px; padding: 3px 10px; border-radius: 20px; display: inline-block; }
    .sp-doc-view-item .doc-badge.yes { background: #d4edda; color: #155724; }
    .sp-doc-view-item .doc-badge.no { background: #f8d7da; color: #721c24; }

    /* Tablet */
    @media (max-width: 992px) {
        .sp-view-grid { grid-template-columns: repeat(2, 1fr); }
        .sp-form-row { grid-template-columns: repeat(2, 1fr); }
        .sp-doc-grid { grid-template-columns: repeat(2, 1fr); }
        .sp-doc-view-grid { grid-template-columns: repeat(2, 1fr); }
    }

    /* Mobile */
    @media (max-width: 768px) {
        .sp-container { padding: 10px; }

        .sp-header {
            flex-direction: column; text-align: center;
            padding: 18px 15px; border-radius: 12px; gap: 12px;
        }
        .sp-header img { width: 70px; height: 70px; }
        .sp-header .placeholder { width: 70px; height: 70px; font-size: 28px; }
        .sp-header-info h4 { font-size: 16px; }
        .sp-header-info span { font-size: 12px; display: block; line-height: 1.6; }
        .sp-header-actions { margin-left: 0; margin-top: 6px; width: 100%; text-align: center; }
        .sp-edit-toggle { display: block; text-align: center; padding: 8px 16px; font-size: 13px; }

        .sp-tabs {
            overflow-x: auto; flex-wrap: nowrap; gap: 2px;
            padding: 4px; margin-bottom: 14px; border-radius: 10px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .sp-tabs::-webkit-scrollbar { display: none; }
        .sp-tab {
            padding: 8px 12px; font-size: 12px; white-space: nowrap; flex-shrink: 0;
        }
        .sp-tab i { display: none; }

        .sp-panel { padding: 16px; border-radius: 10px; }

        .sp-section-title { font-size: 15px; margin-bottom: 14px; padding-bottom: 8px; }

        .sp-view-grid { grid-template-columns: 1fr; gap: 8px; }
        .sp-view-item { padding: 10px 12px; }
        .sp-view-item .label { font-size: 10px; }
        .sp-view-item .value { font-size: 14px; }

        .sp-form-row { grid-template-columns: 1fr; gap: 10px; }
        .sp-form-group { margin-bottom: 10px; }
        .sp-form-group label { font-size: 12px; margin-bottom: 4px; }
        .sp-form-group input, .sp-form-group select, .sp-form-group textarea {
            padding: 8px 10px; font-size: 13px; border-radius: 8px;
        }

        .sp-save-btn { width: 100%; justify-content: center; padding: 12px 20px; font-size: 14px; border-radius: 8px; }

        .sp-doc-grid { grid-template-columns: 1fr; gap: 10px; }
        .sp-doc-item { padding: 12px; }
        .sp-doc-item label.doc-label { font-size: 12px; margin-bottom: 6px; }

        .sp-doc-view-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
        .sp-doc-view-item { padding: 10px; }
        .sp-doc-view-item .doc-name { font-size: 11px; }

        .sp-experience-entry { padding: 12px; margin-bottom: 10px; }
        .sp-experience-entry .sp-form-row { grid-template-columns: 1fr; }
        .sp-add-btn { width: 100%; text-align: center; padding: 10px; font-size: 12px; }
        .sp-remove-btn { width: 22px; height: 22px; font-size: 11px; top: 6px; right: 6px; }
        input[type="file"] { max-width: 100%; font-size: 12px; }
    }

    /* Small phones */
    @media (max-width: 420px) {
        .sp-container { padding: 6px; }
        .sp-header { padding: 14px 12px; }
        .sp-header img { width: 60px; height: 60px; }
        .sp-header .placeholder { width: 60px; height: 60px; font-size: 24px; }
        .sp-header-info h4 { font-size: 15px; }
        .sp-header-info span { font-size: 11px; }
        .sp-panel { padding: 12px; }
        .sp-doc-view-grid { grid-template-columns: 1fr; }
        .sp-tab { padding: 7px 10px; font-size: 11px; }
    }
</style>

@foreach ($data as $row)
@php
    $facultyTitle = DB::table('faculty')->where('code', $row->faculty)->value('title') ?? $row->faculty;
    $deptTitle = DB::table('department')->where('code', $row->department)->value('title') ?? $row->department;
    $programTitle = DB::table('program')->where('code', $row->program)->value('title') ?? $row->program;
    $unitName = (isset($row->unit_id) && $row->unit_id) ? DB::table('units')->where('id', $row->unit_id)->value('name') : (isset($row->unit) ? $row->unit : '');
    $designationName = (isset($row->designation_id) && $row->designation_id) ? DB::table('designations')->where('id', $row->designation_id)->value('name') : (isset($row->current_rank) ? $row->current_rank : '');
    $gradeName = (isset($row->grade_id) && $row->grade_id) ? DB::table('grades')->where('id', $row->grade_id)->value('name') : (isset($row->grade) ? $row->grade : '');
    $stepName = (isset($row->step_id) && $row->step_id) ? DB::table('steps')->where('id', $row->step_id)->value('name') : (isset($row->step) ? $row->step : '');
    $promotions = json_decode($row->promotions ?? '[]', true) ?: [];
@endphp
<div class="sp-container">
    <!-- Header -->
    <div class="sp-header">
        @if($row->picture && $row->picture != '')
            <img src="{{ asset('storage/picture/' . $row->picture) }}" alt="Photo">
        @else
            <div class="placeholder"><i class="fas fa-user"></i></div>
        @endif
        <div class="sp-header-info">
            <h4>{{ $row->name }}</h4>
            <span><i class="fas fa-id-badge"></i> {{ $row->username }}</span>
            <span><i class="fas fa-building"></i> {{ $unitName }}</span>
            <span><i class="fas fa-star"></i> {{ $row->current_rank }}</span>
        </div>
        <div class="sp-header-actions">
            @if($editMode)
                <a href="/staff-profile?tab={{ $activeTab }}&mode=view" class="sp-edit-toggle editing"><i class="fas fa-times"></i> Cancel Editing</a>
            @else
                <a href="/staff-profile?tab={{ $activeTab }}&mode=edit" class="sp-edit-toggle"><i class="fas fa-edit"></i> Edit Profile</a>
            @endif
        </div>
    </div>

    <!-- Tabs -->
    <div class="sp-tabs">
        <button class="sp-tab {{ $activeTab == 'personal' ? 'active' : '' }}" onclick="switchTab('personal')"><i class="fas fa-user"></i> Personal Info</button>
        <button class="sp-tab {{ $activeTab == 'service' ? 'active' : '' }}" onclick="switchTab('service')"><i class="fas fa-briefcase"></i> Service Record</button>
        <button class="sp-tab {{ $activeTab == 'nextofkin' ? 'active' : '' }}" onclick="switchTab('nextofkin')"><i class="fas fa-users"></i> Next of Kin & Bank</button>
        <button class="sp-tab {{ $activeTab == 'education' ? 'active' : '' }}" onclick="switchTab('education')"><i class="fas fa-graduation-cap"></i> Education & Experience</button>
        <button class="sp-tab {{ $activeTab == 'documents' ? 'active' : '' }}" onclick="switchTab('documents')"><i class="fas fa-file-alt"></i> Documents</button>
        <button class="sp-tab {{ $activeTab == 'submit' ? 'active' : '' }}" onclick="switchTab('submit')"><i class="fas fa-paper-plane"></i> Submit</button>
    </div>

    {{-- ==================== TAB 1: PERSONAL INFO ==================== --}}
    <div class="sp-panel {{ $activeTab == 'personal' ? 'active' : '' }}" id="panel-personal">
    @if(!$editMode)
        <div class="sp-section-title"><i class="fas fa-user"></i> Personal Details</div>
        <div class="sp-view-grid">
            <div class="sp-view-item"><div class="label">Staff ID</div><div class="value">{{ $row->username }}</div></div>
            <div class="sp-view-item"><div class="label">Full Name</div><div class="value">{{ $row->name }}</div></div>
            <div class="sp-view-item"><div class="label">Gender</div><div class="value">{{ $row->gender ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Marital Status</div><div class="value">{{ $row->marital_status ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Date of Birth</div><div class="value">{{ ($row->date_of_birth && $row->date_of_birth != '1970-01-01') ? $row->date_of_birth : 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Phone</div><div class="value">{{ $row->phone ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Email</div><div class="value">{{ $row->email ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">State of Origin</div><div class="value">{{ $row->state ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">LGA</div><div class="value">{{ $row->lga ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Nationality</div><div class="value">{{ $row->nationality ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">NIN</div><div class="value">{{ $row->nin ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Address</div><div class="value">{{ $row->address ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Physically Challenged</div><div class="value">{{ $row->physically_challenged ?: 'Not set' }}</div></div>
            @if($row->physically_challenged == 'Yes')
                <div class="sp-view-item"><div class="label">Physical Challenge Type</div><div class="value">{{ $row->physical_challenge_type ?: 'Not specified' }}</div></div>
            @endif
        </div>
    @else
        <form action="/staff-profile-update" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tab" value="personal">

            <div class="sp-section-title"><i class="fas fa-camera"></i> Profile Picture</div>
            <div class="sp-form-row">
                <div class="sp-form-group">
                    <label>Upload Photo</label>
                    <input type="file" name="picture" accept="image/jpeg,image/png,image/jpg">
                    <div class="sp-doc-hint">JPG, PNG - Max 300KB</div>
                </div>
                <div class="sp-form-group">
                    <label>Current Photo</label>
                    @if($row->picture && $row->picture != '')
                        <div><img src="{{ asset('storage/picture/' . $row->picture) }}" width="80" height="80" style="border-radius:8px; object-fit:cover;"> <small class="text-success d-block mt-1"><i class="fas fa-check-circle"></i> Uploaded</small></div>
                    @else
                        <div class="text-muted"><i class="fas fa-image"></i> No photo</div>
                    @endif
                </div>
            </div>

            <div class="sp-section-title"><i class="fas fa-user"></i> Personal Details</div>
            <div class="sp-form-row">
                <div class="sp-form-group">
                    <label>Staff ID</label>
                    <input type="text" value="{{ $row->username }}" readonly>
                </div>
                <div class="sp-form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="{{ $row->name }}" required>
                </div>
                <div class="sp-form-group">
                    <label>Gender</label>
                    <select name="gender">
                        <option value="{{ $row->gender }}" selected>{{ $row->gender ?: 'Select' }}</option>
                        <option value="MALE">MALE</option>
                        <option value="FEMALE">FEMALE</option>
                    </select>
                </div>
                <div class="sp-form-group">
                    <label>Marital Status</label>
                    <select name="marital_status">
                        <option value="{{ $row->marital_status }}" selected>{{ $row->marital_status ?: 'Select' }}</option>
                        <option value="SINGLE">SINGLE</option>
                        <option value="MARRIED">MARRIED</option>
                        <option value="DIVORCED">DIVORCED</option>
                        <option value="WIDOWED">WIDOWED</option>
                    </select>
                </div>
                <div class="sp-form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ $row->date_of_birth }}">
                </div>
                <div class="sp-form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="{{ $row->phone }}" required>
                </div>
                <div class="sp-form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="{{ $row->email }}">
                </div>
                <div class="sp-form-group">
                    <label>State of Origin</label>
                    <select name="state" id="sp-state">
                        <option value="">Select</option>
                        <option value="Abia" {{ strcasecmp($row->state ?? '', 'Abia') == 0 ? 'selected' : '' }}>Abia</option>
                        <option value="Adamawa" {{ strcasecmp($row->state ?? '', 'Adamawa') == 0 ? 'selected' : '' }}>Adamawa</option>
                        <option value="Akwa Ibom" {{ strcasecmp($row->state ?? '', 'Akwa Ibom') == 0 ? 'selected' : '' }}>Akwa Ibom</option>
                        <option value="Anambra" {{ strcasecmp($row->state ?? '', 'Anambra') == 0 ? 'selected' : '' }}>Anambra</option>
                        <option value="Bauchi" {{ strcasecmp($row->state ?? '', 'Bauchi') == 0 ? 'selected' : '' }}>Bauchi</option>
                        <option value="Bayelsa" {{ strcasecmp($row->state ?? '', 'Bayelsa') == 0 ? 'selected' : '' }}>Bayelsa</option>
                        <option value="Benue" {{ strcasecmp($row->state ?? '', 'Benue') == 0 ? 'selected' : '' }}>Benue</option>
                        <option value="Borno" {{ strcasecmp($row->state ?? '', 'Borno') == 0 ? 'selected' : '' }}>Borno</option>
                        <option value="Cross River" {{ strcasecmp($row->state ?? '', 'Cross River') == 0 ? 'selected' : '' }}>Cross River</option>
                        <option value="Delta" {{ strcasecmp($row->state ?? '', 'Delta') == 0 ? 'selected' : '' }}>Delta</option>
                        <option value="Ebonyi" {{ strcasecmp($row->state ?? '', 'Ebonyi') == 0 ? 'selected' : '' }}>Ebonyi</option>
                        <option value="Edo" {{ strcasecmp($row->state ?? '', 'Edo') == 0 ? 'selected' : '' }}>Edo</option>
                        <option value="Ekiti" {{ strcasecmp($row->state ?? '', 'Ekiti') == 0 ? 'selected' : '' }}>Ekiti</option>
                        <option value="Enugu" {{ strcasecmp($row->state ?? '', 'Enugu') == 0 ? 'selected' : '' }}>Enugu</option>
                        <option value="FCT" {{ strcasecmp($row->state ?? '', 'FCT') == 0 ? 'selected' : '' }}>FCT</option>
                        <option value="Gombe" {{ strcasecmp($row->state ?? '', 'Gombe') == 0 ? 'selected' : '' }}>Gombe</option>
                        <option value="Imo" {{ strcasecmp($row->state ?? '', 'Imo') == 0 ? 'selected' : '' }}>Imo</option>
                        <option value="Jigawa" {{ strcasecmp($row->state ?? '', 'Jigawa') == 0 ? 'selected' : '' }}>Jigawa</option>
                        <option value="Kaduna" {{ strcasecmp($row->state ?? '', 'Kaduna') == 0 ? 'selected' : '' }}>Kaduna</option>
                        <option value="Kano" {{ strcasecmp($row->state ?? '', 'Kano') == 0 ? 'selected' : '' }}>Kano</option>
                        <option value="Katsina" {{ strcasecmp($row->state ?? '', 'Katsina') == 0 ? 'selected' : '' }}>Katsina</option>
                        <option value="Kebbi" {{ strcasecmp($row->state ?? '', 'Kebbi') == 0 ? 'selected' : '' }}>Kebbi</option>
                        <option value="Kogi" {{ strcasecmp($row->state ?? '', 'Kogi') == 0 ? 'selected' : '' }}>Kogi</option>
                        <option value="Kwara" {{ strcasecmp($row->state ?? '', 'Kwara') == 0 ? 'selected' : '' }}>Kwara</option>
                        <option value="Lagos" {{ strcasecmp($row->state ?? '', 'Lagos') == 0 ? 'selected' : '' }}>Lagos</option>
                        <option value="Nasarawa" {{ strcasecmp($row->state ?? '', 'Nasarawa') == 0 ? 'selected' : '' }}>Nasarawa</option>
                        <option value="Niger" {{ strcasecmp($row->state ?? '', 'Niger') == 0 ? 'selected' : '' }}>Niger</option>
                        <option value="Ogun" {{ strcasecmp($row->state ?? '', 'Ogun') == 0 ? 'selected' : '' }}>Ogun</option>
                        <option value="Ondo" {{ strcasecmp($row->state ?? '', 'Ondo') == 0 ? 'selected' : '' }}>Ondo</option>
                        <option value="Osun" {{ strcasecmp($row->state ?? '', 'Osun') == 0 ? 'selected' : '' }}>Osun</option>
                        <option value="Oyo" {{ strcasecmp($row->state ?? '', 'Oyo') == 0 ? 'selected' : '' }}>Oyo</option>
                        <option value="Plateau" {{ strcasecmp($row->state ?? '', 'Plateau') == 0 ? 'selected' : '' }}>Plateau</option>
                        <option value="Rivers" {{ strcasecmp($row->state ?? '', 'Rivers') == 0 ? 'selected' : '' }}>Rivers</option>
                        <option value="Sokoto" {{ strcasecmp($row->state ?? '', 'Sokoto') == 0 ? 'selected' : '' }}>Sokoto</option>
                        <option value="Taraba" {{ strcasecmp($row->state ?? '', 'Taraba') == 0 ? 'selected' : '' }}>Taraba</option>
                        <option value="Yobe" {{ strcasecmp($row->state ?? '', 'Yobe') == 0 ? 'selected' : '' }}>Yobe</option>
                        <option value="Zamfara" {{ strcasecmp($row->state ?? '', 'Zamfara') == 0 ? 'selected' : '' }}>Zamfara</option>
                    </select>
                </div>
                <div class="sp-form-group">
                    <label>LGA of Origin</label>
                    <select name="lga" id="sp-lga">
                        <option value="">Select State First</option>
                    </select>
                </div>
                <div class="sp-form-group">
                    <label>Nationality</label>
                    <select name="nationality" id="sp-nationality">
                        <option value="">Select</option>
                        <option value="Nigerian" {{ (isset($row->nationality) && strcasecmp($row->nationality,'Nigerian')==0) ? 'selected' : '' }}>Nigerian</option>
                        <option value="Other" {{ (isset($row->nationality) && strcasecmp($row->nationality,'Nigerian')!=0 && $row->nationality) ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="sp-form-group">
                    <label>NIN <small class="text-muted">(required if Nigerian)</small></label>
                    <input type="text" name="nin" id="sp-nin" value="{{ $row->nin }}" placeholder="e.g. 12345678901">
                </div>
                <div class="sp-form-group" style="grid-column: 1/-1;">
                    <label>Home Address</label>
                    <textarea name="address" rows="2">{{ $row->address }}</textarea>
                </div>
                <div class="sp-form-group">
                    <label>Physically Challenged</label>
                    <select name="physically_challenged" id="sp-physically-challenged">
                        <option value="{{ $row->physically_challenged }}">{{ $row->physically_challenged ?: 'Select' }}</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="sp-form-group" id="sp-physical-challenge-type-group" style="display: none;">
                    <label>Physical Challenge Type</label>
                    <input type="text" name="physical_challenge_type" id="sp-physical-challenge-type" value="{{ $row->physical_challenge_type }}" placeholder="Specify type">
                </div>
            </div>
            <button type="submit" class="sp-save-btn"><i class="fas fa-save"></i> Save Personal Info</button>
        </form>
    @endif
    </div>

    {{-- ==================== TAB 2: SERVICE RECORD ==================== --}}
    <div class="sp-panel {{ $activeTab == 'service' ? 'active' : '' }}" id="panel-service">
    @if(!$editMode)
        <div class="sp-section-title"><i class="fas fa-briefcase"></i> Service Record</div>
        <div class="sp-view-grid">
            <div class="sp-view-item"><div class="label">Faculty</div><div class="value">{{ $facultyTitle ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Department</div><div class="value">{{ $deptTitle ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Unit</div><div class="value">{{ $unitName ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Designation/Rank</div><div class="value">{{ $designationName ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Staff Category</div><div class="value">{{ $row->staff_category ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Employment Status</div><div class="value">{{ $row->employee_status ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Grade/Level</div><div class="value">{{ $gradeName ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Step</div><div class="value">{{ $stepName ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Date of First Appointment</div><div class="value">{{ ($row->date_of_first_appointment && $row->date_of_first_appointment != '1970-01-01') ? $row->date_of_first_appointment : 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Rank on First Appointment</div><div class="value">{{ $row->rank_of_first_appointment ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Date of Assumption</div><div class="value">{{ ($row->date_of_asumption && $row->date_of_asumption != '1970-01-01') ? $row->date_of_asumption : 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Current Qualification recognized by the university</div><div class="value">{{ $row->current_qualification ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Staff Status</div><div class="value">{{ $row->staff_status ?: 'Active' }}</div></div>
            @if($row->staff_status && $row->staff_status != 'Active')
                <div class="sp-view-item"><div class="label">Institution/Organization</div><div class="value">{{ $row->leave_institution ?: 'Not set' }}</div></div>
                <div class="sp-view-item"><div class="label">Leave Start Date</div><div class="value">{{ ($row->leave_start_date && $row->leave_start_date != '1970-01-01') ? $row->leave_start_date : 'Not set' }}</div></div>
                <div class="sp-view-item"><div class="label">Leave End Date</div><div class="value">{{ ($row->leave_end_date && $row->leave_end_date != '1970-01-01') ? $row->leave_end_date : 'Not set' }}</div></div>
            @endif
        </div>

        @if(!empty($promotions))
        <div class="sp-section-title" style="margin-top:25px;"><i class="fas fa-award"></i> Promotions</div>
        <div class="sp-view-grid">
            @foreach($promotions as $promo)
                <div class="sp-view-item" style="width:100%; background:#f8f9fa; padding:12px; border-radius:8px; margin-bottom:8px;">
                    <div class="label" style="font-weight:700; color:var(--primary);">{{ $promo['promotion'] ?? '' }} Promotion</div>
                    <div class="value" style="margin-top:4px;">
                        <div><strong>Date:</strong> {{ $promo['date'] ?? 'N/A' }}</div>
                        <div><strong>Designation:</strong> {{ $promo['designation'] ?? 'N/A' }}</div>
                        <div><strong>Grade:</strong> {{ $promo['grade'] ?? 'N/A' }}</div>
                        <div><strong>Step:</strong> {{ $promo['step'] ?? 'N/A' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    @else
        <form action="/staff-profile-update" method="POST">
            @csrf
            <input type="hidden" name="tab" value="service">

            <div class="sp-section-title"><i class="fas fa-briefcase"></i> Service Record</div>
            <div class="sp-form-row">
                <div class="sp-form-group">
                    <label>Faculty</label>
                    <select name="faculty" class="faculty" id="facultysp1" lang="sp1">
                        <option value="{{ $row->faculty }}">{{ $facultyTitle ?: 'Select Faculty' }}</option>
                        @foreach ($faculty as $fac)
                            <option value="{{ $fac->code }}">{{ $fac->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sp-form-group">
                    <label>Department</label>
                    <select name="department" class="department" id="departmentsp1" lang="sp1">
                        <option value="{{ $row->department }}">{{ $deptTitle ?: 'Select Faculty First' }}</option>
                    </select>
                </div>
                <div class="sp-form-group">
                    <label>Program</label>
                    <select name="program" class="program" id="programsp1" lang="sp1">
                        <option value="{{ $row->program ?? '' }}">{{ $programTitle ?: 'Select Department First' }}</option>
                    </select>
                </div>
                <div class="sp-form-group">
                    <label>Department/Unit</label>
                    <select name="unit_id">
                        <option value="">Select</option>
                        @foreach ($unit as $u)
                            <option value="{{ $u->id }}" {{ (isset($row->unit_id) && $row->unit_id == $u->id) || (isset($row->unit) && $row->unit == $u->name) ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sp-form-group">
                    <label>Designation/Rank</label>
                    <select name="designation_id">
                        <option value="">Select</option>
                        @foreach ($designation as $d)
                            <option value="{{ $d->id }}" {{ (isset($row->designation_id) && $row->designation_id == $d->id) || (isset($row->current_rank) && $row->current_rank == $d->name) ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sp-form-group">
                    <label>Staff Category</label>
                    <select name="staff_category">
                        <option value="{{ $row->staff_category }}">{{ $row->staff_category ?: 'Select' }}</option>
                        <option value="TEACHING STAFF">TEACHING STAFF</option>
                        <option value="NON TEACHING STAFF">NON TEACHING STAFF</option>
                    </select>
                </div>
                <div class="sp-form-group">
                    <label>Employee Status</label>
                    <select name="employee_status">
                        <option value="{{ $row->employee_status }}">{{ $row->employee_status ?: 'Select' }}</option>
                        <option value="PERMANENT">PERMANENT</option>
                        <option value="CONTRACT">CONTRACT</option>
                    </select>
                </div>
                <div class="sp-form-group">
                    <label>Grade/Level</label>
                    <select name="grade_id">
                        <option value="">Select</option>
                        @foreach ($grade as $g)
                            <option value="{{ $g->id }}" {{ $row->grade_id == $g->id || $row->grade == $g->name ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sp-form-group">
                    <label>Step</label>
                    <select name="step_id">
                        <option value="">Select</option>
                        @foreach ($step as $s)
                            <option value="{{ $s->id }}" {{ $row->step_id == $s->id || $row->step == $s->name ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sp-form-group">
                    <label>Date of First Appointment</label>
                    <input type="date" name="date_of_first_appointment" value="{{ $row->date_of_first_appointment }}">
                </div>
                <div class="sp-form-group">
                    <label>Rank on First Appointment</label>
                    <select name="rank_of_first_appointment_id">
                        <option value="">Select</option>
                        @foreach ($designation as $d)
                            <option value="{{ $d->id }}" {{ (isset($row->rank_of_first_appointment) && $row->rank_of_first_appointment == $d->name) ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sp-form-group">
                    <label>Date of Assumption</label>
                    <input type="date" name="date_of_asumption" value="{{ $row->date_of_asumption }}">
                </div>
                <div class="sp-form-group">
                    <label>Current Qualification recognized by the university</label>
                    <select name="current_qualification">
                        <option value="">Select</option>
                        <option value="SSCE/GCE" {{ $row->current_qualification == 'SSCE/GCE' ? 'selected' : '' }}>SSCE/GCE</option>
                        <option value="Trade Test" {{ $row->current_qualification == 'Trade Test' ? 'selected' : '' }}>Trade Test</option>
                        <option value="Diploma" {{ $row->current_qualification == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                        <option value="Degree" {{ $row->current_qualification == 'Degree' ? 'selected' : '' }}>Degree</option>
                        <option value="Masters" {{ $row->current_qualification == 'Masters' ? 'selected' : '' }}>Masters</option>
                        <option value="PhD" {{ $row->current_qualification == 'PhD' ? 'selected' : '' }}>PhD</option>
                    </select>
                </div>
                <div class="sp-form-group">
                    <label>Staff Status</label>
                    <select name="staff_status" id="staff_status" onchange="toggleLeaveFields()">
                        <option value="Active" {{ ($row->staff_status == 'Active' || !$row->staff_status) ? 'selected' : '' }}>Active</option>
                        <option value="Leave without pay" {{ $row->staff_status == 'Leave without pay' ? 'selected' : '' }}>Leave without pay</option>
                        <option value="Sabbatical Leave" {{ $row->staff_status == 'Sabbatical Leave' ? 'selected' : '' }}>Sabbatical Leave</option>
                        <option value="Study Leave" {{ $row->staff_status == 'Study Leave' ? 'selected' : '' }}>Study Leave</option>
                    </select>
                </div>
                <div id="leave_fields" style="display: none;">
                    <div class="sp-form-group">
                        <label>Institution/Organization</label>
                        <input type="text" name="leave_institution" value="{{ $row->leave_institution ?? '' }}">
                    </div>
                    <div class="sp-form-group">
                        <label>Leave Start Date</label>
                        <input type="date" name="leave_start_date" value="{{ $row->leave_start_date ?? '' }}">
                    </div>
                    <div class="sp-form-group">
                        <label>Leave End Date</label>
                        <input type="date" name="leave_end_date" value="{{ $row->leave_end_date ?? '' }}">
                    </div>
                </div>
            </div>

            <div class="sp-section-title" style="margin-top:25px;"><i class="fas fa-award"></i> Promotions</div>
            <div id="promotions-entries">
                @php if (empty($promotions)) $promotions = [[]]; @endphp
                @foreach($promotions as $idx => $promo)
                <div class="sp-experience-entry" data-index="{{ $idx }}">
                    @if($idx > 0)<button type="button" class="sp-remove-btn" onclick="this.parentElement.remove()">&times;</button>@endif
                    <div class="sp-form-row">
                        <div class="sp-form-group">
                            <label>Promotion</label>
                            <select name="promotions[{{ $idx }}][promotion]">
                                <option value="{{ $promo['promotion'] ?? '' }}">{{ $promo['promotion'] ?? 'Select' }}</option>
                                @for($i = 1; $i <= 15; $i++)
                                    @php
                                        $ordinals = [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th', 5 => '5th', 6 => '6th', 7 => '7th', 8 => '8th', 9 => '9th', 10 => '10th', 11 => '11th', 12 => '12th', 13 => '13th', 14 => '14th', 15 => '15th'];
                                    @endphp
                                    <option value="{{ $ordinals[$i] }}">{{ $ordinals[$i] }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="sp-form-group">
                            <label>Date</label>
                            <input type="date" name="promotions[{{ $idx }}][date]" value="{{ $promo['date'] ?? '' }}">
                        </div>
                        <div class="sp-form-group">
                            <label>Designation/Rank</label>
                            <select name="promotions[{{ $idx }}][designation]">
                                <option value="{{ $promo['designation'] ?? '' }}">{{ $promo['designation'] ?? 'Select' }}</option>
                                @foreach ($designation as $des)
                                    <option value="{{ $des->name }}">{{ $des->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sp-form-group">
                            <label>Grade/Level</label>
                            <select name="promotions[{{ $idx }}][grade]">
                                <option value="{{ $promo['grade'] ?? '' }}">{{ $promo['grade'] ?? 'Select' }}</option>
                                @foreach ($grade as $gr)
                                    <option value="{{ $gr->name }}">{{ $gr->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sp-form-group">
                            <label>Step</label>
                            <select name="promotions[{{ $idx }}][step]">
                                <option value="{{ $promo['step'] ?? '' }}">{{ $promo['step'] ?? 'Select' }}</option>
                                @foreach ($step as $st)
                                    <option value="{{ $st->name }}">{{ $st->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <button type="button" class="sp-add-btn mb-3" onclick="addPromotion()"><i class="fas fa-plus"></i> Add Promotion</button>
            <button type="submit" class="sp-save-btn"><i class="fas fa-save"></i> Save Service Record</button>
        </form>
    @endif
    </div>

    {{-- ==================== TAB 3: NEXT OF KIN & BANK ==================== --}}
    <div class="sp-panel {{ $activeTab == 'nextofkin' ? 'active' : '' }}" id="panel-nextofkin">
    @if(!$editMode)
        <div class="sp-section-title"><i class="fas fa-users"></i> Next of Kin</div>
        <div class="sp-view-grid">
            <div class="sp-view-item"><div class="label">Full Name</div><div class="value">{{ $row->kin_name ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Phone</div><div class="value">{{ $row->kin_phone ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Relationship</div><div class="value">{{ $row->kin_relationship ?? 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Address</div><div class="value">{{ $row->kin_address ?: 'Not set' }}</div></div>
        </div>
        <div class="sp-section-title"><i class="fas fa-university"></i> Bank Details</div>
        <div class="sp-view-grid">
            <div class="sp-view-item"><div class="label">Bank Name</div><div class="value">{{ $row->bank_name ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Account Number</div><div class="value">{{ $row->account_number ?: 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Pension Name</div><div class="value">{{ $row->pension_administrator ?? 'Not set' }}</div></div>
            <div class="sp-view-item"><div class="label">Pension PIN Number</div><div class="value">{{ $row->pension_number ?? 'Not set' }}</div></div>
        </div>
    @else
        <form action="/staff-profile-update" method="POST">
            @csrf
            <input type="hidden" name="tab" value="nextofkin">

            <div class="sp-section-title"><i class="fas fa-users"></i> Next of Kin</div>
            <div class="sp-form-row">
                <div class="sp-form-group">
                    <label>Full Name</label>
                    <input type="text" name="kin_name" value="{{ $row->kin_name }}">
                </div>
                <div class="sp-form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="kin_phone" value="{{ $row->kin_phone }}">
                </div>
                <div class="sp-form-group">
                    <label>Relationship</label>
                    <input type="text" name="kin_relationship" value="{{ $row->kin_relationship ?? '' }}">
                </div>
                <div class="sp-form-group" style="grid-column: 1/-1;">
                    <label>Address</label>
                    <textarea name="kin_address" rows="2">{{ $row->kin_address }}</textarea>
                </div>
            </div>

            <div class="sp-section-title"><i class="fas fa-university"></i> Bank Details</div>
            <div class="sp-form-row">
                <div class="sp-form-group">
                    <label>Bank Name</label>
                    <input type="text" name="bank_name" value="{{ $row->bank_name }}">
                </div>
                <div class="sp-form-group">
                    <label>Account Number</label>
                    <input type="text" name="account_number" value="{{ $row->account_number }}">
                </div>
                <div class="sp-form-group">
                    <label>Pension Name</label>
                    <input type="text" name="pension_administrator" value="{{ $row->pension_administrator ?? '' }}">
                </div>
                <div class="sp-form-group">
                    <label>Pension PIN Number</label>
                    <input type="text" name="pension_number" value="{{ $row->pension_number ?? '' }}">
                </div>
            </div>
            <button type="submit" class="sp-save-btn"><i class="fas fa-save"></i> Save Next of Kin & Bank</button>
        </form>
    @endif
    </div>

    {{-- ==================== TAB 4: EDUCATION & EXPERIENCE ==================== --}}
    <div class="sp-panel {{ $activeTab == 'education' ? 'active' : '' }}" id="panel-education">
    @php
        $institutions = json_decode($row->institutions ?? '[]', true) ?: [];
        $experiences = json_decode($row->experiences ?? '[]', true) ?: [];
        $publications = json_decode($row->publications ?? '[]', true) ?: [];
        $honours = json_decode($row->honours ?? '[]', true) ?: [];
        $memberships = json_decode($row->memberships ?? '[]', true) ?: [];
    @endphp
    @if(!$editMode)
        <div class="sp-section-title"><i class="fas fa-graduation-cap"></i> Education & Qualifications</div>
        @if(!empty($institutions))
            @foreach($institutions as $inst)
                @if(!empty($inst['name']))
                <div class="sp-view-grid">
                    <div class="sp-view-item"><div class="label">Institution</div><div class="value">{{ $inst['name'] ?? '' }}</div></div>
                    <div class="sp-view-item"><div class="label">Degree</div><div class="value">{{ $inst['degree'] ?? '' }}</div></div>
                    <div class="sp-view-item"><div class="label">Field</div><div class="value">{{ $inst['field'] ?? '' }}</div></div>
                    <div class="sp-view-item"><div class="label">Year</div><div class="value">{{ $inst['year'] ?? '' }}</div></div>
                </div>
                @endif
            @endforeach
        @else
            <p class="text-muted">No education records added yet.</p>
        @endif

        <div class="sp-section-title"><i class="fas fa-briefcase"></i> Work Experience</div>
        @if(!empty($experiences))
            @foreach($experiences as $exp)
                @if(!empty($exp['place']))
                <div class="sp-view-grid">
                    <div class="sp-view-item"><div class="label">Place</div><div class="value">{{ $exp['place'] ?? '' }}</div></div>
                    <div class="sp-view-item"><div class="label">Period</div><div class="value">{{ $exp['date'] ?? '' }}</div></div>
                    <div class="sp-view-item"><div class="label">Position</div><div class="value">{{ $exp['position'] ?? '' }}</div></div>
                </div>
                @endif
            @endforeach
        @else
            <p class="text-muted">No work experience added yet.</p>
        @endif

        <div class="sp-section-title"><i class="fas fa-book"></i> Publications</div>
        @if(!empty($publications) && $publications != [''])
            <ul>@foreach($publications as $pub)@if(!empty($pub))<li>{{ $pub }}</li>@endif @endforeach</ul>
        @else
            <p class="text-muted">No publications added yet.</p>
        @endif

        <div class="sp-section-title"><i class="fas fa-award"></i> Honours/Distinctions</div>
        @if(!empty($honours) && $honours != [''])
            <ul>@foreach($honours as $hon)@if(!empty($hon))<li>{{ $hon }}</li>@endif @endforeach</ul>
        @else
            <p class="text-muted">No honours added yet.</p>
        @endif

        <div class="sp-section-title"><i class="fas fa-certificate"></i> Professional Memberships</div>
        @if(!empty($memberships) && $memberships != [''])
            <ul>@foreach($memberships as $mem)@if(!empty($mem))<li>{{ $mem }}</li>@endif @endforeach</ul>
        @else
            <p class="text-muted">No memberships added yet.</p>
        @endif

        <div class="sp-section-title"><i class="fas fa-running"></i> Extra-curricular Activities</div>
        <p>{{ $row->extra_curricular ?: 'None added yet.' }}</p>
    @else
        <form action="/staff-profile-update" method="POST">
            @csrf
            <input type="hidden" name="tab" value="education">

            <div class="sp-section-title"><i class="fas fa-graduation-cap"></i> Education & Qualifications</div>
            <div id="education-entries">
                @php if (empty($institutions)) $institutions = [['name' => '', 'degree' => '', 'field' => '', 'year' => '']]; @endphp
                @foreach($institutions as $idx => $inst)
                <div class="sp-experience-entry" data-index="{{ $idx }}">
                    @if($idx > 0)<button type="button" class="sp-remove-btn" onclick="this.parentElement.remove()">&times;</button>@endif
                    <div class="sp-form-row">
                        <div class="sp-form-group"><label>Institution Name</label><input type="text" name="institutions[{{ $idx }}][name]" value="{{ $inst['name'] ?? '' }}" placeholder="e.g. University of Maiduguri"></div>
                        <div class="sp-form-group"><label>Degree/Certificate</label>
                            <select name="institutions[{{ $idx }}][degree]">
                                <option value="{{ $inst['degree'] ?? '' }}">{{ $inst['degree'] ?? 'Select' }}</option>
                                <option value="PhD">PhD</option><option value="Masters">Masters</option><option value="Bachelors">Bachelors</option>
                                <option value="HND">HND</option><option value="ND">ND</option><option value="NCE">NCE</option>
                                <option value="Diploma">Diploma</option><option value="SSCE">SSCE</option><option value="Primary">Primary</option><option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="sp-form-group"><label>Field of Study</label><input type="text" name="institutions[{{ $idx }}][field]" value="{{ $inst['field'] ?? '' }}" placeholder="e.g. Computer Science"></div>
                        <div class="sp-form-group"><label>Graduation Year</label><input type="text" name="institutions[{{ $idx }}][year]" value="{{ $inst['year'] ?? '' }}" placeholder="YYYY"></div>
                    </div>
                </div>
                @endforeach
            </div>
            <button type="button" class="sp-add-btn mb-3" onclick="addEducation()"><i class="fas fa-plus"></i> Add Another Institution</button>

            <div class="sp-section-title"><i class="fas fa-briefcase"></i> Work Experience</div>
            <div id="experience-entries">
                @php if (empty($experiences)) $experiences = [['place' => '', 'date' => '', 'position' => '']]; @endphp
                @foreach($experiences as $idx => $exp)
                <div class="sp-experience-entry" data-index="{{ $idx }}">
                    @if($idx > 0)<button type="button" class="sp-remove-btn" onclick="this.parentElement.remove()">&times;</button>@endif
                    <div class="sp-form-row">
                        <div class="sp-form-group"><label>Place (Institution/Company)</label><input type="text" name="experiences[{{ $idx }}][place]" value="{{ $exp['place'] ?? '' }}" placeholder="Organization name"></div>
                        <div class="sp-form-group"><label>Date (Period)</label><input type="text" name="experiences[{{ $idx }}][date]" value="{{ $exp['date'] ?? '' }}" placeholder="e.g. 2018-2022"></div>
                        <div class="sp-form-group"><label>Position</label><input type="text" name="experiences[{{ $idx }}][position]" value="{{ $exp['position'] ?? '' }}" placeholder="Position held"></div>
                    </div>
                </div>
                @endforeach
            </div>
            <button type="button" class="sp-add-btn mb-3" onclick="addExperience()"><i class="fas fa-plus"></i> Add Another Experience</button>

            <div class="sp-section-title"><i class="fas fa-book"></i> Publications</div>
            <div id="publications-entries">
                @php if (empty($publications)) $publications = ['']; @endphp
                @foreach($publications as $idx => $pub)
                <div class="sp-experience-entry" data-index="{{ $idx }}">
                    @if($idx > 0)<button type="button" class="sp-remove-btn" onclick="this.parentElement.remove()">&times;</button>@endif
                    <div class="sp-form-group"><label>Publication {{ $idx + 1 }}</label><input type="text" name="publications[]" value="{{ $pub }}" placeholder="Enter publication details"></div>
                </div>
                @endforeach
            </div>
            <button type="button" class="sp-add-btn mb-3" onclick="addPublication()"><i class="fas fa-plus"></i> Add Publication</button>

            <div class="sp-section-title"><i class="fas fa-award"></i> Honours/Distinctions</div>
            <div id="honours-entries">
                @php if (empty($honours)) $honours = ['']; @endphp
                @foreach($honours as $idx => $hon)
                <div class="sp-experience-entry" data-index="{{ $idx }}">
                    @if($idx > 0)<button type="button" class="sp-remove-btn" onclick="this.parentElement.remove()">&times;</button>@endif
                    <div class="sp-form-group"><label>Honour/Distinction {{ $idx + 1 }}</label><input type="text" name="honours[]" value="{{ $hon }}" placeholder="Enter honour or distinction"></div>
                </div>
                @endforeach
            </div>
            <button type="button" class="sp-add-btn mb-3" onclick="addHonour()"><i class="fas fa-plus"></i> Add Honour</button>

            <div class="sp-section-title"><i class="fas fa-certificate"></i> Professional Memberships</div>
            <div id="memberships-entries">
                @php if (empty($memberships)) $memberships = ['']; @endphp
                @foreach($memberships as $idx => $mem)
                <div class="sp-experience-entry" data-index="{{ $idx }}">
                    @if($idx > 0)<button type="button" class="sp-remove-btn" onclick="this.parentElement.remove()">&times;</button>@endif
                    <div class="sp-form-group"><label>Membership {{ $idx + 1 }}</label><input type="text" name="memberships[]" value="{{ $mem }}" placeholder="Enter membership details"></div>
                </div>
                @endforeach
            </div>
            <button type="button" class="sp-add-btn mb-3" onclick="addMembership()"><i class="fas fa-plus"></i> Add Membership</button>

            <div class="sp-section-title"><i class="fas fa-running"></i> Extra-curricular Activities</div>
            <div class="sp-form-group">
                <textarea name="extra_curricular" rows="3" placeholder="List extra-curricular activities">{{ $row->extra_curricular ?? '' }}</textarea>
            </div>

            <button type="submit" class="sp-save-btn"><i class="fas fa-save"></i> Save Education & Experience</button>
        </form>
    @endif
    </div>

    {{-- ==================== TAB 5: DOCUMENTS ==================== --}}
    <div class="sp-panel {{ $activeTab == 'documents' ? 'active' : '' }}" id="panel-documents">
    @php
        $documents = [
            'doc_photo' => 'Photo',
            'doc_birth_certificate' => 'Birth Certificate/Declaration of Age',
            'doc_primary_cert' => 'Primary School Certificate',
            'doc_ssce' => 'SSCE/GCE',
            'doc_diploma' => 'Diploma',
            'doc_degree' => 'Degree',
            'doc_masters' => 'Masters',
            'doc_phd' => 'PhD',
            'doc_indigine' => 'Indigine',
            'doc_workshop' => 'Workshop Cert',
            'doc_nysc' => 'NYSC/Exception',
            'doc_appointment_letter' => 'Appointment Letter',
            'doc_confirmation' => 'Letter of Confirmation',
            'doc_professional_body' => 'Certificate of Professional Body Membership',
        ];
        $docOthers = json_decode($row->doc_others ?? '[]', true) ?: [];
    @endphp
    @if(!$editMode)
        <div class="sp-section-title"><i class="fas fa-file-alt"></i> Uploaded Documents</div>
        <div class="sp-doc-view-grid">
            @foreach($documents as $field => $label)
            <div class="sp-doc-view-item">
                <div class="doc-name">{{ $label }}</div>
                @if(!empty($row->$field))
                    <span class="doc-badge yes"><i class="fas fa-check"></i> Uploaded</span>
                    <a href="{{ asset('storage/staff_documents/' . $row->$field) }}" target="_blank" style="display:block; font-size:11px; margin-top:4px; color:var(--primary);"><i class="fas fa-eye"></i> View</a>
                @else
                    <span class="doc-badge no"><i class="fas fa-times"></i> Missing</span>
                @endif
            </div>
            @endforeach
        </div>

        @if(!empty($docOthers))
        <div class="sp-section-title" style="margin-top:20px;"><i class="fas fa-paperclip"></i> Other Documents</div>
        <div class="sp-doc-view-grid">
            @foreach($docOthers as $other)
            <div class="sp-doc-view-item">
                <div class="doc-name">{{ $other['name'] ?? 'Unnamed' }}</div>
                <span class="doc-badge yes"><i class="fas fa-check"></i> Uploaded</span>
                <a href="{{ asset('storage/staff_documents/' . $other['file']) }}" target="_blank" style="display:block; font-size:11px; margin-top:4px; color:var(--primary);"><i class="fas fa-eye"></i> View</a>
            </div>
            @endforeach
        </div>
        @endif
    @else
        <form action="/staff-profile-documents" method="POST" enctype="multipart/form-data" onsubmit="return checkRequiredDocs(this)">
            @csrf

            <div class="sp-section-title"><i class="fas fa-file-upload"></i> Upload Documents</div>
            <p class="text-muted mb-3" style="font-size:13px;"><i class="fas fa-info-circle"></i> Max file size: <strong>300KB</strong> per file. Allowed: PDF, JPG, PNG</p>

            <div class="sp-doc-grid">
                @foreach($documents as $field => $label)
                <div class="sp-doc-item">
                    <label class="doc-label">{{ $label }} @if(in_array($field, ['doc_photo', 'doc_birth_certificate', 'doc_appointment_letter', 'doc_confirmation']))<span class="text-danger">*</span>@endif</label>
                    @if(!empty($row->$field))
                        <div class="doc-status uploaded"><i class="fas fa-check-circle"></i> Uploaded
                            <a href="{{ asset('storage/staff_documents/' . $row->$field) }}" target="_blank" style="font-size:11px; color:var(--primary); margin-left:5px;"><i class="fas fa-eye"></i> View</a>
                        </div>
                    @else
                        <div class="doc-status pending"><i class="fas fa-clock"></i> Not uploaded</div>
                    @endif
                    <input type="file" name="{{ $field }}" accept=".pdf,.jpg,.jpeg,.png" style="margin-top:6px;">
                    <div class="sp-doc-hint">PDF, JPG, PNG - Max 300KB</div>
                </div>
                @endforeach
            </div>

            {{-- Previously uploaded other documents --}}
            @if(!empty($docOthers))
            <div class="sp-section-title" style="margin-top:20px;"><i class="fas fa-paperclip"></i> Previously Uploaded Other Documents</div>
            <div class="sp-doc-view-grid" style="margin-bottom:16px;">
                @foreach($docOthers as $idx => $other)
                <div class="sp-doc-view-item" style="position:relative;">
                    <div class="doc-name">{{ $other['name'] ?? 'Unnamed' }}</div>
                    <a href="{{ asset('storage/staff_documents/' . $other['file']) }}" target="_blank" style="font-size:11px; color:var(--primary);"><i class="fas fa-eye"></i> View</a>
                    <form action="/staff-profile-delete-doc" method="POST" style="display:inline; margin-left:6px;" onsubmit="return confirm('Delete this document?')">
                        @csrf
                        <input type="hidden" name="index" value="{{ $idx }}">
                        <button type="submit" style="background:none; border:none; color:#dc3545; font-size:11px; cursor:pointer;"><i class="fas fa-trash"></i> Remove</button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Add new other documents --}}
            <div class="sp-section-title" style="margin-top:20px;"><i class="fas fa-plus-circle"></i> Add Other Documents</div>
            <div id="other-docs-container">
                <div class="sp-experience-entry">
                    <div class="sp-form-row">
                        <div class="sp-form-group">
                            <label>Document Name</label>
                            <input type="text" name="other_doc_names[]" placeholder="e.g. Staff ID Card">
                        </div>
                        <div class="sp-form-group">
                            <label>File</label>
                            <input type="file" name="other_doc_files[]" accept=".pdf,.jpg,.jpeg,.png">
                            <div class="sp-doc-hint">PDF, JPG, PNG - Max 300KB</div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="sp-add-btn mb-3" onclick="addOtherDoc()"><i class="fas fa-plus"></i> Add Another Document</button>

            <div style="margin-top:16px;">
                <button type="button" class="sp-save-btn" onclick="handleDocUpload()"><i class="fas fa-upload"></i> Upload Documents</button>
            </div>
        </form>
    @endif
    </div>

    {{-- ==================== TAB 6: SUBMIT ==================== --}}
    <div class="sp-panel {{ $activeTab == 'submit' ? 'active' : '' }}" id="panel-submit">
    @php
        // Calculate profile completion
        $missingFields = [];
        $totalRequired = 0;
        $filledRequired = 0;

        // Personal Info required fields
        $personalFields = [
            'name' => 'Full Name',
            'gender' => 'Gender',
            'marital_status' => 'Marital Status',
            'date_of_birth' => 'Date of Birth',
            'phone' => 'Phone',
            'email' => 'Email',
            'state' => 'State of Origin',
            'lga' => 'LGA',
            'nationality' => 'Nationality',
            'nin' => 'NIN',
            'address' => 'Home Address',
            'physically_challenged' => 'Physically Challenged',
        ];
        $personalMissing = [];
        foreach ($personalFields as $field => $label) {
            $totalRequired++;
            if ($field == 'date_of_birth') {
                if (!empty($row->$field) && $row->$field != '1970-01-01') {
                    $filledRequired++;
                } else {
                    $personalMissing[] = $label;
                }
            } else {
                if (!empty($row->$field)) {
                    $filledRequired++;
                } else {
                    $personalMissing[] = $label;
                }
            }
        }
        if ($row->physically_challenged == 'Yes' && empty($row->physical_challenge_type)) {
            $personalMissing[] = 'Physical Challenge Type';
        }
        if (!empty($personalMissing)) $missingFields['Personal Info'] = $personalMissing;

        // Service Record required fields
        $serviceFields = [
            'designation_id' => 'Designation/Rank',
            'staff_category' => 'Staff Category',
            'employee_status' => 'Employment Status',
            'grade_id' => 'Grade/Level',
            'step_id' => 'Step',
            'date_of_first_appointment' => 'Date of First Appointment',
            'rank_of_first_appointment' => 'Rank on First Appointment',
            'date_of_asumption' => 'Date of Assumption',
            'current_qualification' => 'Current Qualification recognized by the university',
            'staff_status' => 'Staff Status',
        ];
        $serviceMissing = [];
        foreach ($serviceFields as $field => $label) {
            $totalRequired++;
            if (in_array($field, ['date_of_first_appointment', 'date_of_asumption'])) {
                if (!empty($row->$field) && $row->$field != '1970-01-01') {
                    $filledRequired++;
                } else {
                    $serviceMissing[] = $label;
                }
            } else {
                if (!empty($row->$field)) {
                    $filledRequired++;
                } else {
                    $serviceMissing[] = $label;
                }
            }
        }
        if (!empty($serviceMissing)) $missingFields['Service Record'] = $serviceMissing;

        // Next of Kin & Bank required fields
        $kinBankFields = [
            'kin_name' => 'Next of Kin Name',
            'kin_phone' => 'Next of Kin Phone',
            'kin_relationship' => 'Next of Kin Relationship',
            'kin_address' => 'Next of Kin Address',
            'bank_name' => 'Bank Name',
            'account_number' => 'Account Number',
            'pension_administrator' => 'Pension Name',
            'pension_number' => 'Pension PIN Number',
        ];
        $kinBankMissing = [];
        foreach ($kinBankFields as $field => $label) {
            $totalRequired++;
            if (!empty($row->$field)) {
                $filledRequired++;
            } else {
                $kinBankMissing[] = $label;
            }
        }
        if (!empty($kinBankMissing)) $missingFields['Next of Kin & Bank'] = $kinBankMissing;

        // Education required (at least one record)
        $educationInstitutions = json_decode($row->institutions ?? '[]', true) ?: [];
        $hasEducation = false;
        foreach ($educationInstitutions as $inst) {
            if (!empty($inst['name'])) {
                $hasEducation = true;
                break;
            }
        }
        $totalRequired++;
        if ($hasEducation) {
            $filledRequired++;
        } else {
            $missingFields['Education & Experience'] = ['At least one Education & Qualification record is required'];
        }

        // Documents required
        $requiredDocs = [
            'doc_photo' => 'Photo',
            'doc_birth_certificate' => 'Birth Certificate/Declaration of Age',
            'doc_appointment_letter' => 'Appointment Letter',
            'doc_confirmation' => 'Letter of Confirmation',
        ];
        $docsMissing = [];
        foreach ($requiredDocs as $field => $label) {
            $totalRequired++;
            if (!empty($row->$field)) {
                $filledRequired++;
            } else {
                $docsMissing[] = $label;
            }
        }
        if (!empty($docsMissing)) $missingFields['Documents'] = $docsMissing;

        $completionPercent = $totalRequired > 0 ? round(($filledRequired / $totalRequired) * 100) : 0;
        $isComplete = empty($missingFields);
    @endphp

        <div class="sp-section-title"><i class="fas fa-paper-plane"></i> Profile Submission</div>

        {{-- Progress Bar --}}
        <div style="margin-bottom: 25px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <strong>Profile Completion</strong>
                <span style="font-weight:700; color:{{ $completionPercent == 100 ? '#06d6a0' : ($completionPercent >= 70 ? '#f4a261' : '#ef476f') }};">{{ $completionPercent }}%</span>
            </div>
            <div style="width:100%; background:#e9ecef; border-radius:10px; height:12px; overflow:hidden;">
                <div style="width:{{ $completionPercent }}%; background:{{ $completionPercent == 100 ? '#06d6a0' : ($completionPercent >= 70 ? '#f4a261' : '#ef476f') }}; height:100%; border-radius:10px; transition:width 0.5s;"></div>
            </div>
        </div>

        {{-- Status Badge --}}
        @if($row->profile_status == 'submitted')
            <div style="background:#d4edda; border:1px solid #c3e6cb; border-radius:10px; padding:20px; margin-bottom:20px;">
                <h4 style="color:#155724; margin:0 0 8px 0;"><i class="fas fa-check-circle"></i> Profile Submitted</h4>
                <p style="color:#155724; margin:0;">Your profile was submitted on <strong>{{ $row->profile_submitted_at ? \Carbon\Carbon::parse($row->profile_submitted_at)->format('d M, Y h:i A') : '' }}</strong>. All information has been recorded.</p>
            </div>
        @endif

        {{-- Missing Fields --}}
        @if(!$isComplete)
            <div style="background:#fff3cd; border:1px solid #ffc107; border-radius:10px; padding:20px; margin-bottom:20px;">
                <h4 style="color:#856404; margin:0 0 12px 0;"><i class="fas fa-exclamation-triangle"></i> Incomplete Profile</h4>
                <p style="color:#856404; margin:0 0 12px 0;">Please fill in the following required fields and <strong>save each section</strong> before submitting:</p>
                @foreach($missingFields as $section => $fields)
                    <div style="margin-bottom:12px;">
                        <strong style="color:#6c4c00;">{{ $section }}:</strong>
                        <ul style="margin:4px 0 0 20px; padding:0; color:#856404;">
                            @foreach($fields as $f)
                                <li>{{ $f }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @else
            <div style="background:#d1ecf1; border:1px solid #bee5eb; border-radius:10px; padding:20px; margin-bottom:20px;">
                <h4 style="color:#0c5460; margin:0 0 8px 0;"><i class="fas fa-info-circle"></i> All Sections Complete</h4>
                <p style="color:#0c5460; margin:0;">Your profile information is complete. You may now submit your profile.</p>
            </div>
        @endif

        {{-- Declaration --}}
        <div style="background:#f8f9fa; border:1px solid #dee2e6; border-radius:10px; padding:20px; margin-bottom:20px;">
            <h4 style="margin:0 0 12px 0; color:#2c3e50;"><i class="fas fa-shield-alt"></i> Declaration</h4>
            <p style="font-size:14px; color:#495057; line-height:1.7; margin:0;">
                I hereby declare that all the information provided in this profile is true, accurate, and complete to the best of my knowledge.
                I understand that any false or misleading information may lead to disciplinary action, including but not limited to termination of appointment.
                I also agree that this submission serves as my consent for the university to verify the information provided herein.
            </p>
        </div>

        {{-- Submit Button --}}
        @if($row->profile_status != 'submitted')
            <form action="/staff-profile-submit" method="POST" id="profile-submit-form">
                @csrf
                <div style="margin-bottom:15px;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:14px;">
                        <input type="checkbox" id="declaration-checkbox" required style="width:18px; height:18px;">
                        <span>I have read and agree to the declaration above</span>
                    </label>
                </div>
                <button type="submit" class="sp-save-btn" id="submit-profile-btn" style="background:linear-gradient(135deg, #06d6a0, #00b894); font-size:16px; padding:14px 32px;" {{ !$isComplete ? 'disabled' : '' }}>
                    <i class="fas fa-paper-plane"></i> Submit Profile
                </button>
                @if(!$isComplete)
                    <p style="color:#dc3545; margin-top:10px; font-size:13px;"><i class="fas fa-exclamation-circle"></i> Please complete all required fields before submitting.</p>
                @endif
            </form>
        @else
            <p style="color:#28a745; font-weight:600;"><i class="fas fa-check-circle"></i> Profile has been submitted successfully.</p>
        @endif
    </div>
</div>
@endforeach

<script src="{{ url('assets/js/plugins/sweetalert.min.js') }}"></script>

<script>
// Designation options for JavaScript
const designationOptions = @json($designation?->pluck('name', 'name')->toArray() ?? []);
const gradeOptions = @json($grade?->pluck('name', 'name')->toArray() ?? []);
const stepOptions = @json($step?->pluck('name', 'name')->toArray() ?? []);

function switchTab(tab) {
    document.querySelectorAll('.sp-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.sp-panel').forEach(p => p.classList.remove('active'));
    event.target.closest('.sp-tab').classList.add('active');
    document.getElementById('panel-' + tab).classList.add('active');
    var mode = '{{ $editMode ? "edit" : "view" }}';
    history.replaceState(null, '', '/staff-profile?tab=' + tab + '&mode=' + mode);
}

function checkRequiredDocs(form) {
    const requiredDocs = {
        'doc_photo': 'Photo',
        'doc_birth_certificate': 'Birth Certificate/Declaration of Age',
        'doc_appointment_letter': 'Appointment Letter',
        'doc_confirmation': 'Letter of Confirmation'
    };

    const uploadedDocs = [
        @if(!empty($row->doc_photo))'doc_photo',@endif
        @if(!empty($row->doc_birth_certificate))'doc_birth_certificate',@endif
        @if(!empty($row->doc_appointment_letter))'doc_appointment_letter',@endif
        @if(!empty($row->doc_confirmation))'doc_confirmation',@endif
    ];

    const missingDocs = [];

    for (const field in requiredDocs) {
        if (!uploadedDocs.includes(field)) {
            const fileInput = form.querySelector(`input[name="${field}"]`);
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                missingDocs.push(requiredDocs[field]);
            }
        }
    }

    if (missingDocs.length > 0) {
        const message = 'The following required documents are missing:\n\n' + missingDocs.map(doc => '• ' + doc).join('\n');
        swal('Missing Required Documents', message, 'warning');
        return false;
    }

    return true;
}

function handleDocUpload() {
    const form = document.querySelector('form[action="/staff-profile-documents"]');
    if (!form) {
        return;
    }

    if (checkRequiredDocs(form)) {
        form.submit();
    }
}

function toggleLeaveFields() {
    const staffStatus = document.getElementById('staff_status');
    const leaveFields = document.getElementById('leave_fields');

    if (staffStatus && leaveFields) {
        const status = staffStatus.value;
        if (status !== 'Active') {
            leaveFields.style.display = 'block';
        } else {
            leaveFields.style.display = 'none';
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleLeaveFields();
});

function addPromotion() {
    const container = document.getElementById('promotions-entries');
    const idx = container.children.length;
    const ordinals = ['1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th', '9th', '10th', '11th', '12th', '13th', '14th', '15th'];
    let promotionOptions = ordinals.map(o => `<option value="${o}">${o}</option>`).join('');

    let designationSelectOptions = '<option value="">Select</option>';
    for (const key in designationOptions) {
        designationSelectOptions += `<option value="${key}">${key}</option>`;
    }

    let gradeSelectOptions = '<option value="">Select</option>';
    for (const key in gradeOptions) {
        gradeSelectOptions += `<option value="${key}">${key}</option>`;
    }

    let stepSelectOptions = '<option value="">Select</option>';
    for (const key in stepOptions) {
        stepSelectOptions += `<option value="${key}">${key}</option>`;
    }

    container.insertAdjacentHTML('beforeend', `
        <div class="sp-experience-entry">
            <button type="button" class="sp-remove-btn" onclick="this.parentElement.remove()">&times;</button>
            <div class="sp-form-row">
                <div class="sp-form-group"><label>Promotion</label><select name="promotions[${idx}][promotion]"><option value="">Select</option>${promotionOptions}</select></div>
                <div class="sp-form-group"><label>Date</label><input type="date" name="promotions[${idx}][date]"></div>
                <div class="sp-form-group"><label>Designation/Rank</label><select name="promotions[${idx}][designation]">${designationSelectOptions}</select></div>
                <div class="sp-form-group"><label>Grade/Level</label><select name="promotions[${idx}][grade]">${gradeSelectOptions}</select></div>
                <div class="sp-form-group"><label>Step</label><select name="promotions[${idx}][step]">${stepSelectOptions}</select></div>
            </div>
        </div>
    `);
}

function addEducation() {
    const container = document.getElementById('education-entries');
    const idx = container.children.length;
    container.insertAdjacentHTML('beforeend', `
        <div class="sp-experience-entry">
            <button type="button" class="sp-remove-btn" onclick="this.parentElement.remove()">&times;</button>
            <div class="sp-form-row">
                <div class="sp-form-group"><label>Institution Name</label><input type="text" name="institutions[${idx}][name]" placeholder="e.g. University of Maiduguri"></div>
                <div class="sp-form-group"><label>Degree/Certificate</label><select name="institutions[${idx}][degree]"><option value="">Select</option><option value="PhD">PhD</option><option value="Masters">Masters</option><option value="Bachelors">Bachelors</option><option value="HND">HND</option><option value="ND">ND</option><option value="NCE">NCE</option><option value="Diploma">Diploma</option><option value="SSCE">SSCE</option><option value="Primary">Primary</option><option value="Other">Other</option></select></div>
                <div class="sp-form-group"><label>Field of Study</label><input type="text" name="institutions[${idx}][field]" placeholder="e.g. Computer Science"></div>
                <div class="sp-form-group"><label>Graduation Year</label><input type="text" name="institutions[${idx}][year]" placeholder="YYYY"></div>
            </div>
        </div>
    `);
}

function addExperience() {
    const container = document.getElementById('experience-entries');
    const idx = container.children.length;
    container.insertAdjacentHTML('beforeend', `
        <div class="sp-experience-entry">
            <button type="button" class="sp-remove-btn" onclick="this.parentElement.remove()">&times;</button>
            <div class="sp-form-row">
                <div class="sp-form-group"><label>Place</label><input type="text" name="experiences[${idx}][place]" placeholder="Organization name"></div>
                <div class="sp-form-group"><label>Date</label><input type="text" name="experiences[${idx}][date]" placeholder="e.g. 2018-2022"></div>
                <div class="sp-form-group"><label>Position</label><input type="text" name="experiences[${idx}][position]" placeholder="Position held"></div>
            </div>
        </div>
    `);
}

function addPublication() {
    const container = document.getElementById('publications-entries');
    const idx = container.children.length;
    container.insertAdjacentHTML('beforeend', `
        <div class="sp-experience-entry">
            <button type="button" class="sp-remove-btn" onclick="this.parentElement.remove()">&times;</button>
            <div class="sp-form-group"><label>Publication ${idx + 1}</label><input type="text" name="publications[]" placeholder="Enter publication details"></div>
        </div>
    `);
}

function addHonour() {
    const container = document.getElementById('honours-entries');
    const idx = container.children.length;
    container.insertAdjacentHTML('beforeend', `
        <div class="sp-experience-entry">
            <button type="button" class="sp-remove-btn" onclick="this.parentElement.remove()">&times;</button>
            <div class="sp-form-group"><label>Honour/Distinction ${idx + 1}</label><input type="text" name="honours[]" placeholder="Enter honour or distinction"></div>
        </div>
    `);
}

function addMembership() {
    const container = document.getElementById('memberships-entries');
    const idx = container.children.length;
    container.insertAdjacentHTML('beforeend', `
        <div class="sp-experience-entry">
            <button type="button" class="sp-remove-btn" onclick="this.parentElement.remove()">&times;</button>
            <div class="sp-form-group"><label>Membership ${idx + 1}</label><input type="text" name="memberships[]" placeholder="Enter membership details"></div>
        </div>
    `);
}

function addOtherDoc() {
    const container = document.getElementById('other-docs-container');
    container.insertAdjacentHTML('beforeend', `
        <div class="sp-experience-entry">
            <button type="button" class="sp-remove-btn" onclick="this.parentElement.remove()">&times;</button>
            <div class="sp-form-row">
                <div class="sp-form-group">
                    <label>Document Name</label>
                    <input type="text" name="other_doc_names[]" placeholder="e.g. Professional Certificate">
                </div>
                <div class="sp-form-group">
                    <label>File</label>
                    <input type="file" name="other_doc_files[]" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="sp-doc-hint">PDF, JPG, PNG - Max 300KB</div>
                </div>
            </div>
        </div>
    `);
}

// Staff profile cascading dropdowns (public, no rolls check)
$(document).on('change', '#facultysp1', function() {
    let _url = '/department ajax public';
    var faculty = this.value;
    var _token = $('input[name="_token"]').val();
    $.ajax({
        type: 'POST',
        url: _url,
        data: { faculty: faculty, _token: _token },
        success: function(data) {
            $("#departmentsp1").html(data);
        }
    });
});

$(document).on('change', '#departmentsp1', function() {
    let _url = '/program ajax public';
    var dept = this.value;
    var faculty = $('#facultysp1').val();
    var _token = $('input[name="_token"]').val();
    $.ajax({
        type: 'POST',
        url: _url,
        data: { faculty: faculty, dept: dept, _token: _token },
        success: function(data) {
            $("#programsp1").html(data);
        }
    });
});

// Nationality/NIN conditional requirement (staff profile personal tab)
function spSyncNinRequired() {
    var nat = $('#sp-nationality').val();
    var nin = $('#sp-nin');
    if (nat && nat.toLowerCase() === 'nigerian') {
        nin.attr('required', true);
    } else {
        nin.removeAttr('required');
    }
}
$(document).on('change', '#sp-nationality', function(){ spSyncNinRequired(); });
$(function(){ spSyncNinRequired(); });
$('#sp-nationality').trigger('change');

// Physically Challenged conditional field (staff profile personal tab)
function spSyncPhysicallyChallenged() {
    var physicallyChallenged = $('#sp-physically-challenged').val();
    var typeGroup = $('#sp-physical-challenge-type-group');
    if (physicallyChallenged === 'Yes') {
        typeGroup.show();
    } else {
        typeGroup.hide();
    }
}
$(document).on('change', '#sp-physically-challenged', function(){ spSyncPhysicallyChallenged(); });
$(function(){ spSyncPhysicallyChallenged(); });
$('#sp-physically-challenged').trigger('change');

@include('includes.nigeria-states-lgas')

// State → LGA cascading for staff profile
bindStateLGA('#sp-state', '#sp-lga');

// Set initial state and LGA values on page load for edit mode
$(function() {
    var currentState = "{{ $row->state ?? '' }}";
    var currentLga = "{{ $row->lga ?? '' }}";
    initStateLGAEdit('#sp-state', '#sp-lga', currentState, currentLga);
});

// Bind form submit event for document upload
$(document).on('submit', 'form[action="/staff-profile-documents"]', function(e) {
    console.log('Form submit event triggered');
    return checkRequiredDocs(this);
});

// Profile submit confirmation
$(document).on('submit', '#profile-submit-form', function(e) {
    e.preventDefault();
    var form = this;
    var checkbox = document.getElementById('declaration-checkbox');
    if (!checkbox.checked) {
        swal('Declaration Required', 'Please check the declaration checkbox to proceed.', 'warning');
        return false;
    }
    swal({
        title: 'Submit Profile?',
        text: 'Are you sure you want to submit your profile? Please ensure all information is correct as this action confirms the genuineness of your data.',
        icon: 'warning',
        buttons: ['Cancel', 'Yes, Submit'],
        dangerMode: true,
    }).then(function(confirmed) {
        if (confirmed) {
            form.submit();
        }
    });
});
</script>
