@php
    use Illuminate\Support\Facades\DB;
@endphp
<!-- Start Content-->
@foreach ($data as $row)
    <div class="main-body">
        <div class="page-wrapper">
            <!-- [ Main Content ] start -->
            <form class="form-group" action="/update-staff" method="POST" enctype="multipart/form-data" id="staff-record-update-form" onsubmit="return validateStaffNin(this);">
                <div class="">
                    <!-- Details View Start -->
                    @csrf
                    <input type="hidden" name="id" value="{{ $row->id }}">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Staff Information</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-block">
                                    <h4 class="text-center">Personal Information</h4>
                                    <div class="form-group">
                                        <label for="picture" class="form-label">Profile Picture</label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                @if ($row->picture)
                                                    <div class="mb-2">
                                                        <img src="{{ asset('storage/picture/' . $row->picture) }}"
                                                            alt="Current Picture"
                                                            style="width: 200px; height: 200px; object-fit: cover; border-radius: 8px;">
                                                        <small class="d-block text-muted">Current picture</small>
                                                    </div>
                                                @else
                                                    <div class="mb-2">
                                                        <img src="{{ asset('storage/picture/default.jpg') }}"
                                                            alt="Current Picture"
                                                            style="width: 200px; height: 200px; object-fit: cover; border-radius: 8px;">
                                                        <small class="d-block text-muted">Current picture</small>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <input type="file" name="picture" id="picture" class="form-control"
                                                    accept="image/jpeg,image/png,image/jpg,image/gif">
                                                <small class="text-muted">Allowed formats: JPEG, PNG, JPG, GIF. Leave
                                                    empty to keep current picture.</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="username" class="form-label">SP/JP</label>
                                        <input type="text" name="username" id="username"
                                            value="{{ $row->username }}" class="form-control" required readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="name" class="form-label">Fullname</label>
                                        <input type="text" name="name" id="name" value="{{ $row->name }}"
                                            class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="gender" class="form-label">Gender</label>
                                        <select name="gender" id="gender" class="form-control">
                                            <option value="{{ $row->gender }}">{{ $row->gender }}</option>
                                            <option value="MALE">MALE</option>
                                            <option value="FEMALE">FEMALE</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="marital_status" class="form-label">Marital Status</label>
                                        <select name="marital_status" id="marital_status" class="form-control">
                                            <option value="{{ $row->marital_status }}">{{ $row->marital_status }}
                                            </option>
                                            <option value="Single">Single</option>
                                            <option value="Married">Married</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="number" name="phone" id="phone" value="{{ $row->phone }}"
                                            class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" name="email" id="email" value="{{ $row->email }}"
                                            class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="address" class="form-label">Home Address</label>
                                        <input type="text" name="address" id="address" value="{{ $row->address }}"
                                            class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                                        <input type="date" name="date_of_birth" id="date_of_birth"
                                            value="{{ $row->date_of_birth }}" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="state" class="form-label">State of Origin</label>
                                        <select name="state" id="state-update" class="form-control" required>
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
                                    <div class="form-group">
                                        <label for="lga" class="form-label">LGA of Origin</label>
                                        <select name="lga" id="lga-update" class="form-control" required>
                                            <option value="">Select State First</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="nationality" class="form-label">Nationality</label>
                                        <select name="nationality" id="nationality" class="form-control">
                                            <option value="{{ $row->nationality }}">{{ $row->nationality ?: 'Select' }}</option>
                                            <option value="Nigerian">Nigerian</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="nin" class="form-label">NIN <small class="text-muted">(required if Nigerian)</small></label>
                                        <input type="text" name="nin" id="nin" value="{{ $row->nin }}" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-block">
                                    <h4 class="text-center">Academic Information</h4>
                                    <div class="form-group">
                                        <label for="facultyff">Faculty</label>
                                        <select class="form-control faculty" id="facultyff" name="faculty"
                                            lang="ff" required>
                                            <option value="{{ $row->faculty }}">Select Option</option>
                                            @foreach ($faculty as $rows)
                                                <option value="{{ $rows->code }}">{{ $rows->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="departmentff">Department</label>
                                        <select class="form-control department" id="departmentff" name="department"
                                            lang="ff" required>
                                            <option value="{{ $row->department }}">Select Faculty First</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="programff">Program</label>
                                        <select class="form-control" id="programff" name="program" lang="ff"
                                            required>
                                            <option value="{{ $row->program }}">Select Department First</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="unit" class="form-label">Department/Unit</label>
                                        <select name="unit_id" id="unit" class="form-control">
                                            <option value="{{ $row->unit_id ?? '' }}">{{ isset($row->unit) ? $row->unit : '' }}</option>
                                            @foreach ($unit as $roww)
                                                <option value="{{ $roww->id }}">{{ $roww->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="current_rank" class="form-label">Designation/Rank</label>
                                        <select name="designation_id" id="current_rank" class="form-control">
                                            <option value="{{ $row->designation_id ?? '' }}">{{ isset($row->current_rank) ? $row->current_rank : '' }}
                                            </option>
                                            @foreach ($designation as $roww)
                                                <option value="{{ $roww->id }}">{{ $roww->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="appointment" class="form-label">Appointment</label>
                                        <select name="appointment" id="appointment" class="form-control">
                                            <option value="{{ $row->appointment }}">{{ $row->appointment }}
                                            </option>
                                            <option value="Staff">NONE</option>
                                            <option value="DSO">DSO</option>
                                            <option value="HOD">HOD</option>
                                            <option value="DEAN">DEAN</option>
                                            <option value="PROVOST">PROVOST</option>
                                            <option value="DIRECTOR">DIRECTOR</option>
                                            <option value="SIWES">SIWES</option>
                                            <option value="SIWES DEPT">SIWES DEPT</option>
                                            <option value="COURSE SYSTEM">COURSE SYSTEM</option>
                                            <option value="COC">COC</option>
                                            <option value="REGISTRAR">REGISTRAR</option>
                                            <option value="DVC">DVC</option>
                                            <option value="VC">VC</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="staff_category" class="form-label">Staff Category</label>
                                        <select name="staff_category" id="staff_category" class="form-control">
                                            <option value="{{ $row->staff_category }}">{{ $row->staff_category }}
                                            </option>
                                            <option value="TEACHING STAFF">TEACHING STAFF</option>
                                            <option value="NON TEACHING STAFF">NON TEACHING STAFF</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="employee_status" class="form-label">Employment Status</label>
                                        <select name="employee_status" id="employee_status" class="form-control">
                                            <option value="{{ $row->employee_status }}">
                                                {{ $row->employee_status }}</option>
                                            <option value="PERMANENT">PERMANENT</option>
                                            <option value="CONTRACT">CONTRACT</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="grade" class="form-label">Grade/Level</label>
                                        <select name="grade_id" id="grade" class="form-control">
                                            <option value="{{ $row->grade_id ?? '' }}">{{ isset($row->grade) ? $row->grade : '' }}</option>
                                            @foreach ($grade as $roww)
                                                <option value="{{ $roww->id }}">{{ $roww->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="step" class="form-label">Step</label>
                                        <select name="step_id" id="step" class="form-control">
                                            <option value="{{ $row->step_id ?? '' }}">{{ isset($row->step) ? $row->step : '' }}</option>
                                            @foreach ($step as $roww)
                                                <option value="{{ $roww->id }}">{{ $roww->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="date_of_first_appointment" class="form-label">Date of First
                                            Appointment</label>
                                        <input type="date" name="date_of_first_appointment"
                                            id="date_of_first_appointment"
                                            value="{{ $row->date_of_first_appointment }}" class="form-control"
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <label for="rank_of_first_appointment" class="form-label">Rank on First
                                            Appointment</label>
                                        <select name="rank_of_first_appointment_id" id="rank_of_first_appointment" class="form-control">
                                            <option value="{{ $row->rank_of_first_appointment_id ?? '' }}">{{ isset($row->rank_of_first_appointment) ? $row->rank_of_first_appointment : 'Select' }}</option>
                                            @foreach ($designation as $roww)
                                                <option value="{{ $roww->id }}">{{ $roww->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="date_of_asumption" class="form-label">Date of Assumption</label>
                                        <input type="date" name="date_of_asumption" id="date_of_asumption"
                                            value="{{ $row->date_of_asumption }}" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="current_qualification" class="form-label">Current Qualification recognized by the university</label>
                                        <select name="current_qualification" id="current_qualification" class="form-control">
                                            <option value="">{{ $row->current_qualification ?: 'Select' }}</option>
                                            <option value="SSCE/GCE" {{ $row->current_qualification == 'SSCE/GCE' ? 'selected' : '' }}>SSCE/GCE</option>
                                            <option value="Trade Test" {{ $row->current_qualification == 'Trade Test' ? 'selected' : '' }}>Trade Test</option>
                                            <option value="Diploma" {{ $row->current_qualification == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                                            <option value="Degree" {{ $row->current_qualification == 'Degree' ? 'selected' : '' }}>Degree</option>
                                            <option value="Masters" {{ $row->current_qualification == 'Masters' ? 'selected' : '' }}>Masters</option>
                                            <option value="PhD" {{ $row->current_qualification == 'PhD' ? 'selected' : '' }}>PhD</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="date_of_last_promotion" class="form-label">Date of Last
                                            Promotion</label>
                                        <input type="date" name="date_of_last_promotion"
                                            id="date_of_last_promotion" value="{{ $row->date_of_last_promotion }}"
                                            class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="degree" class="form-label">Degree</label>
                                        <select name="degree" id="degree" class="form-control">
                                            <option value="{{ $row->degree }}">
                                                {{ $row->degree == 0 ? 'No' : 'Yes' }}</option>
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-block">
                                    <h4 class="text-center">Next of Kin Information</h4>
                                    <div class="form-group">
                                        <label for="kin_name" class="form-label">Name</label>
                                        <input type="text" name="kin_name" id="kin_name"
                                            value="{{ $row->kin_name }}" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="kin_phone" class="form-label">Phone</label>
                                        <input type="number" name="kin_phone" id="kin_phone"
                                            value="{{ $row->kin_phone }}" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="kin_address" class="form-label">Home Address</label>
                                        <input type="text" name="kin_address" id="kin_address"
                                            value="{{ $row->kin_address }}" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-block">
                                    <h4 class="text-center">Bank Details</h4>
                                    <div class="form-group">
                                        <label for="bank_name" class="form-label">Bank Name</label>
                                        <input type="text" name="bank_name" id="bank_name"
                                            value="{{ $row->bank_name }}" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="account_number" class="form-label">Account Number</label>
                                        <input type="number" name="account_number" id="account_number"
                                            value="{{ $row->account_number }}" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-block">
                                    <h4 class="text-center">Action</h4>
                                    <div class="form-group">
                                        <label for="" class="form-label">.</label>
                                        <br>
                                        <button style="width: 100%" type="submit"
                                            class="btn btn-success">Update</button>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="form-label">.</label>
                                        <br>
                                        <button onclick="history.back()" style="width: 100%" type="button"
                                            class="btn btn-info" data-bs-dismiss="modal">Go Back</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Details View End -->
                </div>
            </form>
            <!-- [ Main Content ] end -->
        </div>
    </div>
@endforeach
<script>
/* NIN required validation for staff record update (admin) */
function validateStaffNin(form) {
    try {
        var nat = (form.querySelector('#nationality') || {}).value || '';
        var nin = (form.querySelector('#nin') || {}).value || '';
        if (nat.toString().toLowerCase() === 'nigerian' && !nin.trim()) {
            alert('NIN is required for Nigerian staff.');
            return false;
        }
    } catch(e){}
    return true;
}
(function initNinRequired(){
    var sel = document.getElementById('nationality');
    var inp = document.getElementById('nin');
    if (!sel || !inp) return;
    function sync(){
        if ((sel.value || '').toString().toLowerCase() === 'nigerian') {
            inp.setAttribute('required', 'required');
        } else {
            inp.removeAttribute('required');
        }
    }
    sel.addEventListener('change', sync);
    sync();
})();

@include('includes.nigeria-states-lgas')

// State → LGA cascading for staff record update
bindStateLGA('#state-update', '#lga-update');

// Set initial state and LGA values on page load for edit mode
$(function() {
    var currentState = "{{ $row->state ?? '' }}";
    var currentLga = "{{ $row->lga ?? '' }}";
    initStateLGAEdit('#state-update', '#lga-update', currentState, currentLga);
});

</script>

