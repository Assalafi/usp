@php
    use Illuminate\Support\Facades\DB;
@endphp

<style>
    :root {
        --primary: #4361ee;
        --primary-dark: #3a56d4;
        --secondary: #7209b7;
        --accent: #06d6a0;
        --light: #f8f9fa;
        --dark: #2c3e50;
        --gray: #6c757d;
        --light-gray: #e9ecef;
        --card-shadow: 0 10px 30px rgba(0,0,0,0.08);
        --transition: all 0.3s ease;
    }
    
    .staff-profile-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
        min-height: 100vh;
    }
    
    .back-button {
        background: linear-gradient(to right, var(--primary), var(--secondary));
        color: white;
        border-radius: 30px;
        padding: 12px 30px;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        border: none;
        margin-bottom: 30px;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 16px;
        cursor: pointer;
    }
    
    .back-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
    }
    
    .profile-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
        border-radius: 18px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: var(--card-shadow);
        position: relative;
        overflow: hidden;
    }
    
    .profile-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
        z-index: 0;
    }
    
    .profile-header h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 5px;
        position: relative;
        z-index: 1;
    }
    
    .profile-header .subtitle {
        font-size: 16px;
        opacity: 0.9;
        position: relative;
        z-index: 1;
    }
    
    .profile-picture-section {
        position: relative;
        z-index: 1;
    }
    
    .profile-picture {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid rgba(255,255,255,0.3);
        object-fit: cover;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        transition: var(--transition);
    }
    
    .profile-picture:hover {
        transform: scale(1.05);
    }
    
    .profile-picture-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 4px solid rgba(255,255,255,0.3);
        font-size: 40px;
        color: white;
    }
    
    .staff-profile-card {
        background: white;
        border-radius: 18px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        margin-bottom: 30px;
    }
    
    .info-section {
        padding: 25px;
        border-bottom: 1px solid var(--light-gray);
    }
    
    .info-section:last-child {
        border-bottom: none;
    }
    
    .section-header {
        font-size: 20px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 3px solid var(--light-gray);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
    
    .info-item {
        padding: 15px;
        border-radius: 12px;
        background: rgba(67, 97, 238, 0.03);
        transition: var(--transition);
    }
    
    .info-item:hover {
        background: rgba(67, 97, 238, 0.08);
        transform: translateY(-3px);
    }
    
    .info-label {
        font-weight: 600;
        color: var(--gray);
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
        display: block;
    }
    
    .info-value {
        color: var(--dark);
        font-size: 16px;
        font-weight: 500;
    }
    
    .form-section {
        background: white;
        border-radius: 18px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }
    
    .form-header {
        background: linear-gradient(to right, #f8f9fa, #edf2f9);
        padding: 20px 25px;
        font-size: 20px;
        font-weight: 700;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 3px solid var(--primary);
    }
    
    .form-content {
        padding: 30px;
    }
    
    .modern-form-group {
        margin-bottom: 20px;
    }
    
    .modern-form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--dark);
    }
    
    .modern-form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid var(--light-gray);
        border-radius: 12px;
        font-size: 16px;
        transition: var(--transition);
        background: white;
    }
    
    .modern-form-control:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }
    
    .update-button {
        background: linear-gradient(to right, var(--accent), #04b48d);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 14px 30px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 15px rgba(4, 180, 141, 0.3);
    }
    
    .update-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(4, 180, 141, 0.4);
    }
    
    /* Responsive Design */
    @media (max-width: 992px) {
        .profile-header {
            text-align: center;
        }
        
        .profile-picture-section {
            margin-top: 20px;
            justify-content: center;
        }
        
        .d-flex {
            flex-direction: column;
        }
    }
    
    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .form-content {
            padding: 20px;
        }
    }
    
    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animated {
        animation: fadeIn 0.6s ease-out forwards;
    }
</style>

@foreach ($data as $row)
    <div class="staff-profile-container">
        <div class="container-fluid">
            
            <!-- Profile Header -->
            <div class="row">
                <div class="col-12">
                    <div class="profile-header animated">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div>
                                <h4>{{ $row->name }}</h4>
                            </div>
                            <div class="profile-picture-section d-flex">
                                @if($row->picture && $row->picture != '')
                                    <img src="{{ asset('storage/picture/' . $row->picture) }}" alt="Profile Picture" class="profile-picture">
                                @else
                                    <div class="profile-picture-placeholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                                <div class="ms-3 d-flex flex-column justify-content-center">
                                    <div class="mt-2">
                                        <span class="text-white">ID: {{ $row->username }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="staff-profile-card animated">
                        <div class="info-grid">
                            <!-- Personal Information -->
                            <div class="info-section">
                                <div class="section-header">
                                    <i class="fas fa-user"></i> Personal Information
                                </div>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label">Staff ID</span>
                                        <span class="info-value">{{ $row->username }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Full Name</span>
                                        <span class="info-value">{{ $row->name }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Gender</span>
                                        <span class="info-value">{{ $row->gender }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Marital Status</span>
                                        <span class="info-value">{{ $row->marital_status }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Phone</span>
                                        <span class="info-value">{{ $row->phone }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Email</span>
                                        <span class="info-value">{{ $row->email }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Date of Birth</span>
                                        <span class="info-value">{{ $row->date_of_birth == '1970-01-01' ? 'Not Provided' : $row->date_of_birth }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Address</span>
                                        <span class="info-value">{{ $row->address }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">State</span>
                                        <span class="info-value">{{ $row->state }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">LGA</span>
                                        <span class="info-value">{{ $row->lga }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Service Record -->
                            <div class="info-section">
                                <div class="section-header">
                                    <i class="fas fa-briefcase"></i> Service Record
                                </div>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label">Department</span>
                                        <span class="info-value">{{ $row->unit }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Current Rank</span>
                                        <span class="info-value">{{ $row->current_rank }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Staff Category</span>
                                        <span class="info-value">{{ $row->staff_category }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Status</span>
                                        <span class="info-value badge bg-info">{{ $row->employee_status }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Grade</span>
                                        <span class="info-value">{{ $row->grade }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Step</span>
                                        <span class="info-value">{{ $row->step }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">First Appointment</span>
                                        <span class="info-value">{{ $row->rank_of_first_appointment }} on {{ $row->date_of_first_appointment == '1970-01-01' ? 'Not Provided' : $row->date_of_first_appointment }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Date of Assumption</span>
                                        <span class="info-value">{{ $row->date_of_asumption == '1970-01-01' ? 'Not Provided' : $row->date_of_asumption }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Last Promotion</span>
                                        <span class="info-value">{{ $row->date_of_last_promotion == '1970-01-01' ? 'Not Provided' : $row->date_of_last_promotion }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Next of Kin Information -->
                            <div class="info-section">
                                <div class="section-header">
                                    <i class="fas fa-users"></i> Next of Kin
                                </div>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label">Name</span>
                                        <span class="info-value">{{ $row->kin_name ?: 'Not Provided' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Phone</span>
                                        <span class="info-value">{{ $row->kin_phone ?: 'Not Provided' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Address</span>
                                        <span class="info-value">{{ $row->kin_address ?: 'Not Provided' }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bank Details -->
                            <div class="info-section">
                                <div class="section-header">
                                    <i class="fas fa-university"></i> Bank Details
                                </div>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label">Bank</span>
                                        <span class="info-value">{{ $row->bank_name ?: 'Not Provided' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Account Number</span>
                                        <span class="info-value">{{ $row->account_number ?: 'Not Provided' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Account Name</span>
                                        <span class="info-value">{{ $row->name ?: 'Not Provided' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">BVN</span>
                                        <span class="info-value">{{ $row->bvn ?: 'Not Provided' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Pension Admin</span>
                                        <span class="info-value">{{ $row->pension_administrator ?: 'Not Provided' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Form Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-section animated">
                        <div class="form-header">
                            <i class="fas fa-edit"></i> Update Your Information
                        </div>
                        <div class="form-content">
                            <form action="/update staff" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="id" value="{{ $row->id }}">
                                
                                <!-- Profile Picture Section - First -->
                                <div class="row mb-5">
                                    <div class="col-12">
                                        <h5 class="section-header mb-4">
                                            <i class="fas fa-camera"></i> Profile Picture
                                        </h5>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="modern-form-group">
                                                    <label for="picture" class="modern-form-label">Upload New Profile Picture</label>
                                                    <input type="file" name="picture" id="picture" class="modern-form-control" accept="image/*">
                                                    <small class="text-muted">Accepted formats: JPG, PNG, GIF (Max: 2MB)</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="modern-form-group">
                                                    <label class="modern-form-label">Current Photo</label>
                                                    <div class="d-flex align-items-center">
                                                        @if($row->picture && $row->picture != '')
                                                            <img src="{{ asset('storage/picture/' . $row->picture) }}" width="100" height="100" class="rounded-circle me-3" style="border: 3px solid #4361ee; object-fit: cover;">
                                                            <div>
                                                                <span class="text-muted d-block">{{ $row->picture }}</span>
                                                                <small class="text-success"><i class="fas fa-check-circle"></i> Photo uploaded</small>
                                                            </div>
                                                        @else
                                                            <div class="text-center" style="padding: 20px; border: 2px dashed #ccc; border-radius: 12px; min-width: 200px;">
                                                                <i class="fas fa-camera text-muted" style="font-size: 24px;"></i>
                                                                <div class="text-muted mt-2">No profile photo uploaded</div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Personal Information Section -->
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <h5 class="section-header mb-4">
                                            <i class="fas fa-user"></i> Personal Information
                                        </h5>
                                        
                                        <div class="modern-form-group">
                                            <label for="marital_status" class="modern-form-label">Marital Status</label>
                                            <select name="marital_status" id="marital_status" class="modern-form-control">
                                                <option value="{{ $row->marital_status }}">{{ $row->marital_status }}</option>
                                                <option value="Single">Single</option>
                                                <option value="Married">Married</option>
                                                <option value="Divorced">Divorced</option>
                                                <option value="Widowed">Widowed</option>
                                            </select>
                                        </div>
                                        
                                        <div class="modern-form-group">
                                            <label for="phone" class="modern-form-label">Phone Number</label>
                                            <input type="tel" name="phone" id="phone" value="{{ $row->phone }}" class="modern-form-control" required>
                                        </div>
                                        
                                        <div class="modern-form-group">
                                            <label for="email" class="modern-form-label">Email Address</label>
                                            <input type="email" name="email" id="email" value="{{ $row->email }}" class="modern-form-control" required>
                                        </div>
                                        
                                        <div class="modern-form-group">
                                            <label for="address" class="modern-form-label">Home Address</label>
                                            <textarea name="address" id="address" class="modern-form-control" rows="3" required>{{ $row->address }}</textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6 col-md-12">
                                        <h5 class="section-header mb-4">
                                            <i class="fas fa-users"></i> Next of Kin Information
                                        </h5>
                                        
                                        <div class="modern-form-group">
                                            <label for="kin_name" class="modern-form-label">Full Name</label>
                                            <input type="text" name="kin_name" id="kin_name" value="{{ $row->kin_name }}" class="modern-form-control" required>
                                        </div>
                                        
                                        <div class="modern-form-group">
                                            <label for="kin_phone" class="modern-form-label">Phone Number</label>
                                            <input type="tel" name="kin_phone" id="kin_phone" value="{{ $row->kin_phone }}" class="modern-form-control" required>
                                        </div>
                                        
                                        <div class="modern-form-group">
                                            <label for="kin_address" class="modern-form-label">Home Address</label>
                                            <textarea name="kin_address" id="kin_address" class="modern-form-control" rows="3" required>{{ $row->kin_address }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="update-button">
                                        <i class="fas fa-save"></i> Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add animation to elements
        const elements = document.querySelectorAll('.animated');
        elements.forEach(el => {
            el.style.opacity = '0';
            el.style.animation = 'fadeIn 0.6s ease-out forwards';
        });
    });
</script>
