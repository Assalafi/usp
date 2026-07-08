@php
    use App\Models\Student;
    use Illuminate\Support\Facades\DB;
    $course_flag = DB::table('program')
        ->where(['code' => session('program')])
        ->select('courses')
        ->value('courses');
@endphp

<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        @if ($course_flag == 1 || strpos(session('faculty'), '.PG') !== false)
            <!-- Welcome Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-gradient-primary text-white">
                        <div class="card-body text-center py-4">
                            <h2 class="mb-2"><i class="fas fa-graduation-cap me-2"></i>Course Registration</h2>
                            <p class="mb-0 fs-5">Welcome! Let's register your courses for this semester</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session Selection -->
            <div class="row mb-4">
                <div class="col-md-6 mx-auto">
                    <div class="card border-primary">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Select Academic Session</h5>
                        </div>
                        <div class="card-body">
                            <form action="/student course registration" method="GET">
                                <div class="form-group">
                                    <label class="form-label fw-bold">Choose Session:</label>
                                    <select name="session" class="form-select form-select-lg" onchange="this.form.submit()">
                                        <option value="{{ session('system_session') }}"
                                            {{ request('session') == session('system_session') ? 'selected' : '' }}>
                                            Current Session: {{ session('system_session') }}
                                        </option>
                                        <option value="2023/2024"
                                            {{ request('session') == '2023/2024' ? 'selected' : '' }}>
                                            2023/2024 Session
                                        </option>
                                        <option value="2022/2023"
                                            {{ request('session') == '2022/2023' ? 'selected' : '' }}>
                                            2022/2023 Session
                                        </option>
                                        <option value="2021/2022"
                                            {{ request('session') == '2021/2022' ? 'selected' : '' }}>
                                            2021/2022 Session
                                        </option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Registration Steps -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- Step Navigation -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-center flex-wrap">
                                        <div class="btn-group mb-2" role="group">
                                            <button type="button" class="btn btn-primary btn-lg active" id="step1-btn" onclick="showStep(1)">
                                                <i class="fas fa-list me-2"></i>Step 1: View My Courses
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-lg" id="step2-btn" onclick="showStep(2)">
                                                <i class="fas fa-plus-circle me-2"></i>Step 2: Add Elective Courses
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-lg" id="step3-btn" onclick="showStep(3)">
                                                <i class="fas fa-check-circle me-2"></i>Step 3: Complete Registration
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 1: View My Courses -->
                            <div id="step1" class="step-content">
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <h5><i class="fas fa-info-circle me-2"></i>Your Registered Courses</h5>
                                            <p class="mb-0">Below are all the courses you are currently registered for. You can change the semester for some courses if allowed.</p>
                                        </div>
                                    </div>
                                </div>

                                @if(count($data) > 0)
                                    <div class="row">
                                        @php $sn1 = 1; @endphp
                                        @foreach ($data as $row)
                                            @php
                                                $courseTitle = DB::table('course')->where(['code' => $row->code])->value('title');
                                                $change = DB::table('program_course_registration')
                                                    ->where(['program' => session('program'), 'code' => $row->code])
                                                    ->value('change_semester');
                                            @endphp
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card h-100 border-left-primary">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <span class="badge bg-primary fs-6">{{ $row->code }}</span>
                                                            <span class="badge bg-secondary">{{ $row->unit }} Units</span>
                                                        </div>
                                                        <h6 class="card-title text-truncate" title="{{ $courseTitle }}">{{ $courseTitle ?: 'Course Title' }}</h6>
                                                        <div class="row text-center mt-3">
                                                            <div class="col-6">
                                                                <small class="text-muted">Semester</small>
                                                                <div class="fw-bold">
                                                                    @if ($change == 1)
                                                                        <button class="btn btn-outline-info btn-sm" type="button"
                                                                            data-bs-toggle="modal" data-bs-target="#update{{ $row->id }}">
                                                                            {{ $row->semester }} <i class="fas fa-edit ms-1"></i>
                                                                        </button>
                                                                    @else
                                                                        <span class="badge bg-light text-dark">{{ $row->semester }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-muted">Type</small>
                                                                <div class="fw-bold">
                                                                    <span class="badge {{ $row->type == 'CORE' ? 'bg-success' : 'bg-warning' }}">
                                                                        {{ $row->type }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if ($change == 1)
                                                <!-- Change Semester Modal -->
                                                <div id="update{{ $row->id }}" class="modal fade" tabindex="-1" role="dialog">
                                                    <div class="modal-dialog modal-sm" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Change Semester</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form action="change-semester" method="POST">
                                                                <div class="modal-body">
                                                                    @csrf
                                                                    <input type="hidden" name="id" value="{{ $row->id }}">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">Select New Semester:</label>
                                                                        <select class="form-select" name="semester" required>
                                                                            <option value="{{ $row->semester }}">Current: {{ $row->semester }}</option>
                                                                            <option value="FIRST">First Semester</option>
                                                                            <option value="SECOND">Second Semester</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No courses registered yet</h5>
                                        <p class="text-muted">You haven't registered for any courses in this session.</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Step 2: Add Elective Courses -->
                            <div id="step2" class="step-content" style="display: none;">
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="alert alert-warning">
                                            <h5><i class="fas fa-plus-circle me-2"></i>Choose Your Elective Courses</h5>
                                            <p class="mb-0">Select elective courses for both semesters. You can change these selections before final submission.</p>
                                        </div>
                                    </div>
                                </div>

                                <form action="/register-elective-course" method="POST" id="electiveForm">
                                    @csrf
                                    <input type="hidden" value="{{ $ses }}" name="session">
                                    
                                    <!-- First Semester Electives -->
                                    <div class="card mb-4">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>First Semester Electives</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @php $table = 'student_course_registration'; @endphp
                                                @for($i = 1; $i <= 5; $i++)
                                                    @php 
                                                        $registeredCourse = DB::table($table)->where(['elective' => $i, 'semester' => 'FIRST', 'level' => $lvl, 'username' => session('id_number')])->value('code');
                                                        $electiveVar = 'f' . $i;
                                                    @endphp
                                                    <div class="col-md-6 mb-3">
                                                        <div class="card border-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title">Elective Course {{ $i }}</h6>
                                                                @if($registeredCourse)
                                                                    <div class="alert alert-success py-2">
                                                                        <small><i class="fas fa-check-circle me-1"></i>Currently Registered:</small>
                                                                        <strong>{{ $registeredCourse }}</strong>
                                                                    </div>
                                                                @endif
                                                                <select class="form-select form-select-lg" name="code[]" required>
                                                                    <option value="">Choose a course...</option>
                                                                    @forelse ($$electiveVar as $item)
                                                                        <option value="{{ $item->code }},{{ $i }}" 
                                                                            {{ $registeredCourse == $item->code ? 'selected' : '' }}>
                                                                            {{ $item->code }} - {{ DB::table('course')->where('code', $item->code)->value('title') }}
                                                                        </option>
                                                                    @empty
                                                                        <option value="NIL">No courses available</option>
                                                                    @endforelse
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Second Semester Electives -->
                                    <div class="card mb-4">
                                        <div class="card-header bg-info text-white">
                                            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Second Semester Electives</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @php 
                                                        $registeredCourse = DB::table($table)->where(['elective' => $i, 'semester' => 'SECOND', 'level' => $lvl, 'username' => session('id_number')])->value('code');
                                                        $electiveVar = 's' . $i;
                                                    @endphp
                                                    <div class="col-md-6 mb-3">
                                                        <div class="card border-light">
                                                            <div class="card-body">
                                                                <h6 class="card-title">Elective Course {{ $i }}</h6>
                                                                @if($registeredCourse)
                                                                    <div class="alert alert-success py-2">
                                                                        <small><i class="fas fa-check-circle me-1"></i>Currently Registered:</small>
                                                                        <strong>{{ $registeredCourse }}</strong>
                                                                    </div>
                                                                @endif
                                                                <select class="form-select form-select-lg" name="code[]" required>
                                                                    <option value="">Choose a course...</option>
                                                                    @forelse ($$electiveVar as $item)
                                                                        <option value="{{ $item->code }},{{ $i }}" 
                                                                            {{ $registeredCourse == $item->code ? 'selected' : '' }}>
                                                                            {{ $item->code }} - {{ DB::table('course')->where('code', $item->code)->value('title') }}
                                                                        </option>
                                                                    @empty
                                                                        <option value="NIL">No courses available</option>
                                                                    @endforelse
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success btn-lg px-5">
                                            <i class="fas fa-save me-2"></i>Save Elective Courses
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Step 3: Complete Registration -->
                            <div id="step3" class="step-content" style="display: none;">
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="alert alert-success">
                                            <h5><i class="fas fa-check-circle me-2"></i>Complete Your Registration</h5>
                                            <p class="mb-0">Review your course selection and complete your registration for this session.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    @if ($sn1 > 1)
                                        @if (session('student_session') == session('system_session') || strpos(session('faculty'), '.PG') !== false)
                                            <div class="col-md-6 mb-3">
                                                <div class="card border-primary">
                                                    <div class="card-body text-center">
                                                        <i class="fas fa-redo fa-3x text-primary mb-3"></i>
                                                        <h5>Regenerate Core Courses</h5>
                                                        <p class="text-muted">Generate your core courses for this session</p>
                                                        <form action="create {{ $page }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" value="new" name="register">
                                                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                                                <i class="fas fa-registered me-2"></i>Regenerate Core Courses
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-success">
                                                <div class="card-body text-center">
                                                    <i class="fas fa-print fa-3x text-success mb-3"></i>
                                                    <h5>Print Course Registration</h5>
                                                    <p class="text-muted">Download and print your registered courses</p>
                                                    <button class="btn btn-success btn-lg w-100" data-bs-toggle="modal" data-bs-target="#printModal">
                                                        <i class="fas fa-print me-2"></i>Print Courses
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Print Modal -->
            <div id="printModal" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Print Course Registration</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="/get-registered-courses" method="GET">
                            <div class="modal-body">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Select Session to Print:</label>
                                    <select class="form-select" name="session" required>
                                        <option value="">Choose session...</option>
                                        <option value="2024/2025">2024/2025</option>
                                        <option value="2023/2024">2023/2024</option>
                                        <option value="2022/2023">2022/2023</option>
                                        <option value="2021/2022">2021/2022</option>
                                        <option value="2020/2021">2020/2021</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-print me-2"></i>Generate Print
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        @else
            <div class="text-center py-5">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h4>Course Registration Not Available</h4>
                <p class="text-muted">Course registration is not enabled for your program at this time.</p>
            </div>
        @endif
    </div>
</div>

<!-- Custom CSS -->
<style>
.border-left-primary {
    border-left: 4px solid #007bff !important;
}

.step-content {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.btn-group .btn {
    margin: 2px;
}

@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-group .btn {
        margin: 2px 0;
    }
}

.card:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

<!-- JavaScript for Step Navigation -->
<script>
function showStep(stepNumber) {
    // Hide all steps
    document.querySelectorAll('.step-content').forEach(step => {
        step.style.display = 'none';
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('[id$="-btn"]').forEach(btn => {
        btn.classList.remove('btn-primary', 'active');
        btn.classList.add('btn-outline-primary');
    });
    
    // Show selected step
    document.getElementById('step' + stepNumber).style.display = 'block';
    
    // Make selected button active
    const activeBtn = document.getElementById('step' + stepNumber + '-btn');
    activeBtn.classList.remove('btn-outline-primary');
    activeBtn.classList.add('btn-primary', 'active');
    
    // Special styling for step 3
    if (stepNumber === 3) {
        activeBtn.classList.remove('btn-primary');
        activeBtn.classList.add('btn-success');
    }
}

// Initialize tooltips if using Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
