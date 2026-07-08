@php
    use App\Models\HostelPin;
    $pin = HostelPin::select('pin', 'username')->where('username', session('id_number'))->first();
    $payment = DB::table('hostel')->where('occupant', session('id_number'))->value('hostel_payment');
    $myCourses = DB::table('student_course_registration')
        ->select('code')
        ->where(['username' => session('id_number')])
        ->pluck('code');
    $myLectureTimetable = DB::table('lecture_timetable')
        ->whereIn('course', $myCourses)
        ->where(['session' => session('system_session')])
        ->get();
    $days = [
        'Mon' => 1,
        'Tue' => 2,
        'Wed' => 3,
        'Thu' => 4,
        'Fri' => 5,
        'Sat' => 6,
        'Sun' => 7,
    ];
    $days = $days[date('D')];
    if ($days == 7) {
        $tomorrow = 1;
    } else {
        $tomorrow = $days + 1;
    }
    function abbreviateMiddleName($name)
    {
        $nameParts = explode(' ', $name);
        if (count($nameParts) === 3) {
            $nameParts[1] = substr($nameParts[1], 0, 1) . '.';
            $abbreviatedName = implode(' ', $nameParts);
            return $abbreviatedName;
        }
        return $name;
    }
@endphp
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ DB::table('program')->where(['code' => session('program')])->value('title') }}
                            ({{ session('program') }}) {{ session('current_level') }} Level |
                            {{ session('system_session') }} Academic Session</h5>
                    </div>
                    <div class="card-body row">
                        <h4>Today's Lecture</h4>
                        @forelse ($myLectureTimetable->where('day_no', $days) as $row)
                            @php
                                $staffs = DB::table('course_allocation')
                                    ->where(['course' => $row->course])
                                    ->select('name')
                                    ->orderBy('type', 'ASC')
                                    ->get();
                                $lecturer = '';
                                foreach ($staffs as $staff) {
                                    $lecturer .= abbreviateMiddleName($staff->name) . ' | ';
                                }
                            @endphp
                            <div class="card-block col-md-4 shadow">
                                <p>Course: {{ $row->course }}</p>
                                <p>Hall: {{ $row->hall }} | Time: {{ date('h:i A', strtotime($row->start)) }} -
                                    {{ date('h:i A', strtotime($row->end)) }} </p>
                                <p>Lecturer: {{ $lecturer }}</p>
                                <p>{{ $row->comment }}</p>
                            </div>
                        @empty
                            <div class="card-block col-md-4">
                                <p>You don't have lecture today!!!</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="card-body row">
                        <h4>Tomorrow's Lecture</h4>
                        @forelse ($myLectureTimetable->where('day_no', $tomorrow) as $row)
                            @php
                                $staffs = DB::table('course_allocation')
                                    ->where(['course' => $row->course])
                                    ->select('name')
                                    ->orderBy('type', 'ASC')
                                    ->get();
                                $lecturer = '';
                                foreach ($staffs as $staff) {
                                    $lecturer .= abbreviateMiddleName($staff->name) . ' | ';
                                }
                            @endphp
                            <div class="card-block col-md-4 shadow">
                                <p>Course: {{ $row->course }}</p>
                                <p>Hall: {{ $row->hall }} | Time: {{ date('h:i A', strtotime($row->start)) }} -
                                    {{ date('h:i A', strtotime($row->end)) }} </p>
                                <p>Lecturer: {{ $lecturer }}</p>
                                <p>{{ $row->comment }}</p>
                            </div>
                        @empty
                            <div class="card-block col-md-4">
                                <p>You don't have lecture tomorrow!!!</p>
                            </div>
                        @endforelse

                    </div>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
{{-- Check if level_flag is 0, popup a modal to update the level --}}
@if (session('level_flag') == 0)
    <!-- Level Update Modal -->
    <div class="modal fade" id="levelUpdateModal" tabindex="-1" aria-labelledby="levelUpdateModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="levelUpdateModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Update Your Current Level
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Your current level needs to be updated. Please select your correct academic level below.
                    </div>
                    <form id="levelUpdateForm" action="{{ route('update.student.level') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="currentLevel" class="form-label fw-bold">Select Your Current Level:</label>
                            <select class="form-select" id="currentLevel" name="level" required>
                                <option value="">Choose your level...</option>
                                <option value="100">100 Level (First Year)</option>
                                <option value="200">200 Level (Second Year)</option>
                                <option value="300">300 Level (Third Year)</option>
                                <option value="400">400 Level (Fourth Year)</option>
                                <option value="500">500 Level (Fifth Year)</option>
                                <option value="600">600 Level (Sixth Year)</option>
                                <option value="700">700 Level (Seventh Year)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="fas fa-lightbulb me-1"></i>
                                Select the level you are currently in for the {{ session('system_session') }} academic
                                session.
                            </small>
                        </div>
                        <div class="mb-3">
                            <div class="alert alert-light border">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Note:</strong> You can change your level later by visiting your
                                    <strong>Profile</strong> and updating it under the <strong>Academic
                                        Section</strong>.
                                </small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="updateLevel()">
                        <i class="fas fa-save me-2"></i>Update Level
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show modal automatically when page loads
            const levelModal = new bootstrap.Modal(document.getElementById('levelUpdateModal'));
            levelModal.show();
        });

        function updateLevel() {
            const form = document.getElementById('levelUpdateForm');
            const levelSelect = document.getElementById('currentLevel');

            if (!levelSelect.value) {
                swal("Oops!!!", "Please select your current level before proceeding.", "warning");
                return;
            }

            if (confirm('Are you sure ' + levelSelect.value + ' is your correct current level?')) {
                form.submit();
            }
        }
    </script>
@endif
