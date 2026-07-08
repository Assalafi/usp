<style>
    :root {
        --primary: #3da1e3;
        --primary-dark: #2b8bd6;
        --secondary: #2b8bd6;
        --success: #06d6a0;
        --warning: #ffd166;
        --danger: #ef476f;
        --light: #f8f9fa;
        --dark: #212529;
        --gray: #6c757d;
        --light-gray: #e9ecef;
        --card-shadow: 0 10px 30px rgba(0,0,0,0.08);
        --transition: all 0.3s ease;
    }
    
    .profile-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
        border-radius: 18px;
        padding: 40px 35px;
        margin-bottom: 35px;
        box-shadow: var(--card-shadow);
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    
    .profile-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
        z-index: -1;
    }
    
    .profile-avatar {
        width: 160px;
        height: 160px;
        border: 5px solid rgba(255,255,255,0.3);
        box-shadow: 0 12px 25px rgba(0,0,0,0.2);
        transition: var(--transition);
        position: relative;
    }
    
    .profile-avatar:hover {
        transform: scale(1.03);
        box-shadow: 0 15px 30px rgba(0,0,0,0.25);
    }
    
    .profile-avatar i {
        font-size: 5rem;
        color: rgba(255,255,255,0.8);
    }
    
    .status-badge {
        background: linear-gradient(45deg, var(--primary), var(--primary-dark));
        color: white;
        padding: 8px 18px;
        border-radius: 25px;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 12px rgba(61, 161, 227, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .info-card {
        border-radius: 18px;
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        border: none;
        margin-bottom: 30px;
        overflow: hidden;
        background: white;
        height: 100%;
    }
    
    .info-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.12);
    }
    
    .card-header-custom {
        background: linear-gradient(to right, rgba(61, 161, 227, 0.08), rgba(61, 161, 227, 0.15));
        border-bottom: 3px solid var(--primary);
        border-radius: 18px 18px 0 0 !important;
        padding: 22px 25px;
        backdrop-filter: blur(10px);
    }
    
    .section-title {
        color: var(--primary);
        font-weight: 700;
        font-size: 20px;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .section-title i {
        background: linear-gradient(45deg, var(--primary), var(--secondary));
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 24px;
    }
    
    .card-body {
        padding: 25px;
    }
    
    .info-item {
        padding: 16px 0;
        border-bottom: 1px solid var(--light-gray);
        display: flex;
        align-items: flex-start;
        transition: var(--transition);
    }
    
    .info-item:hover {
        background-color: rgba(61, 161, 227, 0.03);
        border-radius: 10px;
        padding: 16px 12px;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 18px;
        color: white;
        flex-shrink: 0;
        box-shadow: 0 5px 15px rgba(61, 161, 227, 0.25);
    }
    
    .info-label {
        font-weight: 600;
        color: var(--gray);
        margin-bottom: 4px;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }
    
    .info-value {
        color: var(--dark);
        font-size: 17px;
        margin: 0;
        font-weight: 500;
        line-height: 1.4;
    }
    
    .back-btn {
        background: linear-gradient(to right, var(--primary), var(--secondary));
        color: white;
        border-radius: 30px;
        padding: 14px 36px;
        font-weight: 600;
        box-shadow: 0 6px 20px rgba(61, 161, 227, 0.3);
        border: none;
        margin-bottom: 30px;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 16px;
    }
    
    .back-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(61, 161, 227, 0.4);
    }
    
    .action-btn {
        background: white;
        color: var(--primary);
        border: 2px solid var(--light-gray);
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 500;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 15px;
    }
    
    .action-btn:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(61, 161, 227, 0.2);
    }
    
    .info-badge {
        display: inline-block;
        background: rgba(6, 214, 160, 0.15);
        color: #06a37a;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-left: 10px;
    }
    
    .text-highlight {
        color: var(--primary);
        font-weight: 600;
    }
    
    /* Tablet Styles */
    @media (max-width: 992px) {
        .profile-header {
            padding: 35px 25px;
        }
        
        .profile-header .col-md-3 {
            margin-bottom: 25px;
            text-align: center !important;
        }
        
        .profile-header .col-md-6 {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .profile-header .col-md-3:last-child {
            margin-bottom: 0;
        }
        
        .back-btn {
            width: 100%;
            justify-content: center;
            margin-bottom: 25px;
        }
        
        .info-card {
            margin-bottom: 20px;
        }
    }
    
    /* Mobile Styles */
    @media (max-width: 768px) {
        .profile-header {
            padding: 25px 15px;
            margin-bottom: 25px;
        }
        
        .profile-header h1 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        
        .profile-header h4 {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        
        .profile-header .d-flex {
            flex-direction: column;
            gap: 8px !important;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            margin-bottom: 20px;
        }
        
        .profile-avatar i {
            font-size: 3.5rem;
        }
        
        .status-badge {
            font-size: 11px;
            padding: 6px 14px;
            margin-bottom: 20px;
        }
        
        .back-btn {
            padding: 12px 24px;
            font-size: 14px;
            width: 100%;
            justify-content: center;
            margin-top: 0;
        }
        
        .main-body {
            padding-top: 0;
        }
        
        .page-wrapper {
            padding-top: 0;
        }
        
        .info-card {
            margin-bottom: 20px;
            border-radius: 15px;
        }
        
        .card-header-custom {
            padding: 18px 20px;
            border-radius: 15px 15px 0 0 !important;
        }
        
        .section-title {
            font-size: 16px;
            gap: 8px;
        }
        
        .section-title i {
            font-size: 20px;
        }
        
        .card-body {
            padding: 20px 15px;
        }
        
        .info-item {
            padding: 12px 0;
            flex-direction: column;
            align-items: flex-start !important;
            text-align: left;
        }
        
        .info-item:hover {
            padding: 12px 8px;
        }
        
        .info-icon {
            width: 35px;
            height: 35px;
            margin-right: 0;
            margin-bottom: 8px;
            border-radius: 10px;
        }
        
        .info-label {
            font-size: 11px;
            margin-bottom: 3px;
        }
        
        .info-value {
            font-size: 15px;
            line-height: 1.3;
        }
        
        .action-btn {
            width: 100%;
            justify-content: center;
            padding: 12px 20px;
            font-size: 14px;
            margin-top: 20px;
        }
        
        .info-badge {
            display: block;
            margin-left: 0;
            margin-top: 5px;
            text-align: center;
        }
        
        /* Stack columns on mobile */
        .col-lg-6, .col-lg-4, .col-lg-12 {
            margin-bottom: 20px;
        }
        
        /* Profile header mobile layout */
        .profile-header .d-flex.justify-content-center {
            flex-direction: column;
            gap: 15px !important;
        }
        
        .profile-header .d-flex.justify-content-center > div {
            text-align: center;
        }
    }
    
    /* Small Mobile Styles */
    @media (max-width: 480px) {
        .profile-header {
            padding: 20px 10px;
            margin-bottom: 20px;
        }
        
        .profile-header h1 {
            font-size: 1.5rem;
        }
        
        .profile-header h4 {
            font-size: 1.1rem;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
        }
        
        .profile-avatar i {
            font-size: 3rem;
        }
        
        .back-btn {
            padding: 10px 20px;
            font-size: 13px;
        }
        
        .card-header-custom {
            padding: 15px;
        }
        
        .section-title {
            font-size: 14px;
        }
        
        .section-title i {
            font-size: 18px;
        }
        
        .card-body {
            padding: 15px 10px;
        }
        
        .info-icon {
            width: 30px;
            height: 30px;
        }
        
        .info-label {
            font-size: 10px;
        }
        
        .info-value {
            font-size: 14px;
        }
        
        .action-btn {
            padding: 10px 15px;
            font-size: 12px;
        }
        
        .status-badge {
            font-size: 10px;
            padding: 5px 12px;
        }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animated-section {
        animation: fadeIn 0.6s ease-out forwards;
    }
    
    .card-delay-1 { animation-delay: 0.1s; }
    .card-delay-2 { animation-delay: 0.2s; }
    .card-delay-3 { animation-delay: 0.3s; }
    .card-delay-4 { animation-delay: 0.4s; }
    .card-delay-5 { animation-delay: 0.5s; }
</style>

<!-- Start Content-->
@foreach ($data as $row)
    <div class="main-body">
        <div class="page-wrapper">
            <!-- [ Main Content ] start -->
            <button onclick="history.back()" class="btn btn-info back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </button>
            
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center">
                        @if($row->picture && file_exists(public_path('storage/picture/' . $row->picture)))
                            <img src="{{ asset('storage/picture/' . $row->picture) }}" alt="{{ $row->name }}" 
                                 class="profile-avatar rounded-circle img-fluid">
                        @else
                            <div class="profile-avatar rounded-circle bg-white d-flex align-items-center justify-content-center mx-auto">
                                <i class="fas fa-user fa-4x text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h1 class="mb-3">{{ $row->name }}</h1>
                        <h4 class="mb-3 opacity-85">{{ $row->current_rank }}</h4>
                        <div class="d-flex flex-wrap gap-3 mb-3">
                            <p class="mb-2"><i class="fas fa-id-card me-2"></i> SP/JP: {{ $row->username }}</p>
                            <p class="mb-2"><i class="fas fa-building me-2"></i> {{ $row->unit }}</p>
                            <p class="mb-0"><i class="fas fa-envelope me-2"></i> {{ $row->email }}</p>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="status-badge mb-4">
                            <i class="fas fa-check-circle"></i>
                            Active Staff
                        </div>
                        <div class="d-flex justify-content-center gap-4">
                            <div>
                                <p class="mb-1"><strong>Grade:</strong></p>
                                <p class="fs-5 fw-bold">{{ $row->grade }}</p>
                            </div>
                            <div>
                                <p class="mb-1"><strong>Step:</strong></p>
                                <p class="fs-5 fw-bold">{{ $row->step }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Personal Information -->
                <div class="col-lg-6 mb-4">
                    <div class="card info-card animated-section card-delay-1">
                        <div class="card-header card-header-custom">
                            <h5 class="section-title">
                                <i class="fas fa-user"></i>
                                Personal Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-venus-mars"></i>
                                </div>
                                <div>
                                    <div class="info-label">Gender</div>
                                    <div class="info-value">{{ $row->gender }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div>
                                    <div class="info-label">Marital Status</div>
                                    <div class="info-value">{{ $row->marital_status }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <div class="info-label">Phone Number</div>
                                    <div class="info-value">{{ $row->phone }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-birthday-cake"></i>
                                </div>
                                <div>
                                    <div class="info-label">Date of Birth</div>
                                    <div class="info-value">{{ $row->date_of_birth == '1970-01-01' ? 'Not Specified' : date('F j, Y', strtotime($row->date_of_birth)) }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <div class="info-label">Location</div>
                                    <div class="info-value">{{ $row->lga }}, {{ $row->state }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div>
                                    <div class="info-label">Home Address</div>
                                    <div class="info-value">{{ $row->address }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Service Record -->
                <div class="col-lg-6 mb-4">
                    <div class="card info-card animated-section card-delay-2">
                        <div class="card-header card-header-custom">
                            <h5 class="section-title">
                                <i class="fas fa-briefcase"></i>
                                Service Record
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div>
                                    <div class="info-label">Department/Unit</div>
                                    <div class="info-value">{{ $row->unit }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <div class="info-label">Current Designation</div>
                                    <div class="info-value">{{ $row->current_rank }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-tags"></i>
                                </div>
                                <div>
                                    <div class="info-label">Staff Category</div>
                                    <div class="info-value">{{ $row->staff_category }} ({{ $row->employee_status }})</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <div class="info-label">Grade & Step</div>
                                    <div class="info-value">Grade {{ $row->grade }}, Step {{ $row->step }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div>
                                    <div class="info-label">First Appointment</div>
                                    <div class="info-value">
                                        {{ $row->rank_of_first_appointment }} 
                                        @if($row->date_of_first_appointment != '1970-01-01')
                                            <br><small class="text-muted">{{ date('F j, Y', strtotime($row->date_of_first_appointment)) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <div class="info-label">Date of Assumption</div>
                                    <div class="info-value">{{ $row->date_of_asumption == '1970-01-01' ? 'Not Specified' : date('F j, Y', strtotime($row->date_of_asumption)) }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-arrow-up"></i>
                                </div>
                                <div>
                                    <div class="info-label">Last Promotion</div>
                                    <div class="info-value">{{ $row->date_of_last_promotion == '1970-01-01' ? 'Not Specified' : date('F j, Y', strtotime($row->date_of_last_promotion)) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Academic Information -->
                <div class="col-lg-4 mb-4">
                    <div class="card info-card animated-section card-delay-3">
                        <div class="card-header card-header-custom">
                            <h5 class="section-title">
                                <i class="fas fa-graduation-cap"></i>
                                Academic Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-school"></i>
                                </div>
                                <div>
                                    <div class="info-label">Primary School Certificate</div>
                                    <div class="info-value">
                                        Available 
                                        <span class="info-badge">Complete</span>
                                    </div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-certificate"></i>
                                </div>
                                <div>
                                    <div class="info-label">Secondary School Certificate</div>
                                    <div class="info-value">
                                        Available 
                                        <span class="info-badge">Complete</span>
                                    </div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-medal"></i>
                                </div>
                                <div>
                                    <div class="info-label">Degree Certificate</div>
                                    <div class="info-value">
                                        {{ $row->degree ? 'Available' : 'Not Available' }}
                                        @if($row->degree)
                                            <span class="info-badge">Verified</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Next of Kin Information -->
                <div class="col-lg-4 mb-4">
                    <div class="card info-card animated-section card-delay-4">
                        <div class="card-header card-header-custom">
                            <h5 class="section-title">
                                <i class="fas fa-users"></i>
                                Next of Kin
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="info-label">Full Name</div>
                                    <div class="info-value">{{ $row->kin_name ?: 'Not Specified' }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <div class="info-label">Phone Number</div>
                                    <div class="info-value">{{ $row->kin_phone ?: 'Not Specified' }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <div class="info-label">Address</div>
                                    <div class="info-value">{{ $row->kin_address ?: 'Not Specified' }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div>
                                    <div class="info-label">Relationship</div>
                                    <div class="info-value">Not Specified</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bank Details -->
                <div class="col-lg-4 mb-4">
                    <div class="card info-card animated-section card-delay-5">
                        <div class="card-header card-header-custom">
                            <h5 class="section-title">
                                <i class="fas fa-university"></i>
                                Financial Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-building-columns"></i>
                                </div>
                                <div>
                                    <div class="info-label">Bank Name</div>
                                    <div class="info-value">{{ $row->bank_name ?: 'Not Specified' }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div>
                                    <div class="info-label">Account Number</div>
                                    <div class="info-value">{{ $row->account_number ?: 'Not Specified' }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div>
                                    <div class="info-label">PFA Name</div>
                                    <div class="info-value">Not Specified</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-key"></i>
                                </div>
                                <div>
                                    <div class="info-label">PFA PIN</div>
                                    <div class="info-value">Not Specified</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Medical Information -->
            <div class="row">
                <div class="col-lg-12 mb-4">
                    <div class="card info-card animated-section">
                        <div class="card-header card-header-custom">
                            <h5 class="section-title">
                                <i class="fas fa-heartbeat"></i>
                                Medical Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-tint"></i>
                                        </div>
                                        <div>
                                            <div class="info-label">Blood Group</div>
                                            <div class="info-value">Not Specified</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-dna"></i>
                                        </div>
                                        <div>
                                            <div class="info-label">Genotype</div>
                                            <div class="info-value">Not Specified</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-allergies"></i>
                                        </div>
                                        <div>
                                            <div class="info-label">Allergies</div>
                                            <div class="info-value">Not Specified</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
@endforeach

<script>
    // Simple animation trigger
    document.addEventListener('DOMContentLoaded', function() {
        const animatedElements = document.querySelectorAll('.animated-section');
        
        animatedElements.forEach(el => {
            el.style.opacity = '0';
        });
        
        setTimeout(() => {
            animatedElements.forEach(el => {
                el.style.animationPlayState = 'running';
            });
        }, 300);
    });
</script>