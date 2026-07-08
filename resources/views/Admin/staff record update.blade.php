@php
    use Illuminate\Support\Facades\DB;
@endphp
<!-- Start Content-->
@foreach ($data as $row)
    <div class="main-body">
        <div class="page-wrapper">
            <!-- [ Main Content ] start -->
            <form class="form-group" action="/update staff" method="POST" enctype="multipart/form-data">
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
                                        <input type="text" name="state" id="state" class="form-control"
                                            value="{{ $row->state }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="lga" class="form-label">LGA of Origin</label>
                                        <input type="text" name="lga" id="lga" class="form-control"
                                            value="{{ $row->lga }}" required>
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
                                        <select name="unit" id="unit" class="form-control">
                                            <option value="{{ $row->unit }}">{{ $row->unit }}</option>
                                            @foreach ($unit as $roww)
                                                <option value="{{ $roww->unit }}">{{ $roww->unit }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="current_rank" class="form-label">Designation/Rank</label>
                                        <select name="current_rank" id="current_rank" class="form-control">
                                            <option value="{{ $row->current_rank }}">{{ $row->current_rank }}
                                            </option>
                                            @foreach ($designation as $roww)
                                                <option value="{{ $roww->current_rank }}">{{ $roww->current_rank }}
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
                                        <label for="employee_status" class="form-label">Employee Status</label>
                                        <select name="employee_status" id="employee_status" class="form-control">
                                            <option value="{{ $row->employee_status }}">
                                                {{ $row->employee_status }}</option>
                                            <option value="PERMANENT">PERMANENT</option>
                                            <option value="CONTRACT">CONTRACT</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="grade" class="form-label">Grade</label>
                                        <select name="grade" id="grade" class="form-control">
                                            <option value="{{ $row->grade }}">{{ $row->grade }}</option>
                                            @foreach ($grade as $roww)
                                                <option value="{{ $roww->grade }}">{{ $roww->grade }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="step" class="form-label">Step</label>
                                        <select name="step" id="step" class="form-control">
                                            <option value="{{ $row->step }}">{{ $row->step }}</option>
                                            @foreach ($step as $roww)
                                                <option value="{{ $roww->step }}">{{ $roww->step }}
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
                                        <input type="text" name="rank_of_first_appointment"
                                            id="rank_of_first_appointment"
                                            value="{{ $row->rank_of_first_appointment }}" class="form-control"
                                            required>
                                    </div>
                                    <div class="form-group">
                                        <label for="date_of_asumption" class="form-label">Date of Assumption</label>
                                        <input type="date" name="date_of_asumption" id="date_of_asumption"
                                            value="{{ $row->date_of_asumption }}" class="form-control">
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
