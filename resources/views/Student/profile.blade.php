@php
    use App\Models\Student;
    use Illuminate\Support\Facades\DB;

    $data = Student::where('user_id', session('id'))->get();
    $lgas = DB::table('locals')->where('state_id', 8)->orderBy('local_name', 'ASC')->get();
@endphp
@foreach ($data as $row)
    <!-- Start Content-->
    <div class="main-body">
        <div class="page-wrapper">
            <!-- [ Main Content ] start -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card user-card user-card-1">
                        <div class="card-body pb-0">
                            <div class="media user-about-block align-items-center mt-0 mb-3">
                                <div class="position-relative d-inline-block">
                                    <img src="{{ asset('storage/picture/' . $row->picture) }}"
                                        class="img-radius img-fluid wid-80" alt="{{ __('field_photo') }}">
                                    @if (session('activeProfile') == 1)
                                        <div class="certificated-badge">
                                            <i class="fas fa-certificate text-primary bg-icon"></i>
                                            <i class="fas fa-check front-icon text-white"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="media-body ms-3">
                                    <h6 class="mb-1">
                                        {{ $row->first_name . ' ' . $row->last_name . ' ' . $row->other_name }}</h6>
                                    <p class="mb-0 text-muted">{{ $row->username }}</p>
                                </div>
                            </div>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <span class="f-w-500"><i class="far fa-envelope m-r-10"></i>{{ 'Email' }} :
                                </span>
                                <span class="float-end">{{ $row->contact_email }}</span>
                            </li>
                            <li class="list-group-item">
                                <span class="f-w-500"><i class="fas fa-phone-alt m-r-10"></i>{{ 'Phone' }} :
                                </span>
                                <span class="float-end">{{ $row->contact_phone }}</span>
                            </li>
                            <li class="list-group-item">
                                <span class="f-w-500"><i class="fas fa-graduation-cap m-r-10"></i>{{ 'Program' }} :
                                </span>
                                <span
                                    class="float-end">{{ DB::table('program')->where('code', $row->program)->value('title') }}</span>
                            </li>
                            <li class="list-group-item">
                                <span class="f-w-500"><i
                                        class="far fa-calendar-alt m-r-10"></i>{{ 'Registration Date' }} : </span>
                                <span class="float-end">
                                    {{ date('d m Y', strtotime($row->created_at)) }}
                                </span>
                            </li>
                            <li class="list-group-item border-bottom-0">
                                <span class="f-w-500"><i class="far fa-question-circle m-r-10"></i>{{ 'Jamb No' }}
                                    : </span>
                                <span class="float-end">{{ $row->jamb_no }}</span>
                            </li>
                        </ul>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col">
                                    <h6 class="mb-1">0.0</h6>
                                    <p class="mb-0"></p>
                                </div>
                                <div class="col border-start">
                                    <h6 class="mb-1">
                                        2.7
                                    </h6>
                                    <p class="mb-0"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-block">
                            <div class="">
                                <div class="row">
                                    <div class="col-md-6">
                                        <fieldset class="row gx-2 scheduler-border">
                                            <p><mark class="text-primary">{{ 'Father Name' }}:</mark>
                                                {{ $row->father_name }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Mother' }}:</mark>
                                                {{ $row->mother_name }}</p>
                                            <hr />

                                            <p><mark class="text-primary">{{ 'Gender' }}:</mark>
                                                @if ($row->gender == 'M')
                                                    {{ 'Male' }}
                                                @elseif($row->gender == 'F')
                                                    {{ 'Female' }}
                                                @endif
                                            </p>
                                            <hr />

                                            <p><mark class="text-primary">{{ 'Date of Birth' }}:</mark>
                                                {{ $row->date_of_birth }}
                                            </p>
                                            <hr />

                                            <p><mark class="text-primary">{{ 'Emergency Phone' }}:</mark>
                                                {{ $row->contact_phone }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Religion' }}:</mark>
                                                {{ $row->religion }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Nationality' }}:</mark>
                                                {{ $row->country }}</p>
                                            <hr />

                                            <p><mark class="text-primary">{{ 'Marital Status' }}:</mark>
                                                {{ $row->marital_status }}
                                            </p>
                                            <hr />

                                            <p><mark class="text-primary">{{ 'Blood Group' }}:</mark>
                                                {{ $row->blood_group }}
                                            </p>
                                            <hr />

                                            <p><mark class="text-primary">{{ 'NIN' }}:</mark>
                                                {{ $row->nin }}</p>
                                            <hr />
                                        </fieldset>
                                    </div>
                                    <div class="col-md-6">
                                        <fieldset class="row gx-2 scheduler-border">
                                            <legend>{{ 'Present' }} {{ 'Address' }}</legend>
                                            <p><mark class="text-primary">{{ 'Address' }}:</mark>
                                                {{ $row->home_address }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Phone' }}:</mark>
                                                {{ $row->home_phone }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Email' }}:</mark>
                                                {{ $row->home_email }}</p>
                                        </fieldset>

                                        <fieldset class="row gx-2 scheduler-border">
                                            <legend>{{ 'Permanent' }} {{ 'Address' }}</legend>
                                            <p><mark class="text-primary">{{ 'Address' }}:</mark>
                                                {{ $row->contact_address }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Phone' }}:</mark>
                                                {{ $row->contact_phone }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Email' }}:</mark>
                                                {{ $row->contact_email }}</p>
                                        </fieldset>

                                        <fieldset class="row gx-2 scheduler-border">
                                            <p><mark class="text-primary">{{ 'Hall' }}:</mark>
                                                {{ $row->hall }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Block' }}:</mark>
                                                {{ $row->room }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Room' }}:</mark>
                                                {{ $row->room }}</p>
                                            <hr />
                                            <p><mark class="text-primary">{{ 'Bed' }}:</mark>
                                                {{ $row->room }}</p>
                                            <hr />
                                        </fieldset>
                                    </div>
                                </div>
                                {{-- Print BIO Data --}}
                                @if (session('activeProfile') == 1)
                                    <a href="/student-details-pdf" class="btn btn-primary" style="width: 100%"><i
                                            class="fas fa-print"></i> Print BIO Data</a>
                                @else
                                    <a href="#" class="btn btn-warning" style="width: 100%"><i
                                            class="fas fa-exclamation-triangle"></i> Update your profile to print your
                                        BIO data</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <form class="form-group" action="update-profile" id="myform" method="POST"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $row->id }}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="pills-bio_data-tab" data-bs-toggle="pill"
                                            href="#pills-bio_data" role="tab" aria-controls="pills-bio_data"
                                            aria-selected="true">{{ __('bio-data') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-admission-tab" data-bs-toggle="pill"
                                            href="#pills-admission" role="tab" aria-controls="pills-admission"
                                            aria-selected="true">{{ __('Admission') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-address-tab" data-bs-toggle="pill"
                                            href="#pills-address" role="tab" aria-controls="pills-address"
                                            aria-selected="true">{{ __('address') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-sponsor-tab" data-bs-toggle="pill"
                                            href="#pills-sponsor" role="tab" aria-controls="pills-sponsor"
                                            aria-selected="true">{{ __('next of kin & sponsor') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-parent-tab" data-bs-toggle="pill"
                                            href="#pills-parent" role="tab" aria-controls="pills-parent"
                                            aria-selected="true">{{ __('parent') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-signature-tab" data-bs-toggle="pill"
                                            href="#pills-signature" role="tab" aria-controls="pills-signature"
                                            aria-selected="true">{{ __('Picture/signature') }}</a>
                                    </li>
                                </ul>
                                <div class="tab-content" id="pills-tabContent">
                                    <div class="tab-pane fade show active" id="pills-bio_data" role="tabpanel"
                                        aria-labelledby="pills-bio_data-tab">
                                        @if ($row->username)
                                            @php
                                                $username = $row->username;
                                            @endphp
                                        @else
                                            @php
                                                $username = $row->jamb_no;
                                            @endphp
                                        @endif
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label for="username" class="col-sm-3 col-form-label">Student
                                                        ID.<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="username"
                                                            name="username" placeholder="Student ID"
                                                            value="{{ $username }}" disabled required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="jamb_no" class="col-sm-3 col-form-label">Jamb
                                                        No.<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="jamb_no"
                                                            name="jamb_no" placeholder="Jamb No"
                                                            value="{{ $row->jamb_no }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="surname"
                                                        class="col-sm-3 col-form-label">Surname<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="surname"
                                                            name="surname" placeholder="Surname"
                                                            value="{{ $row->last_name }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="first_name" class="col-sm-3 col-form-label">First
                                                        Name<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="first_name"
                                                            name="first_name" placeholder="First Name"
                                                            value="{{ $row->first_name }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="other_name" class="col-sm-3 col-form-label">Other
                                                        Name</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="other_name"
                                                            name="other_name" placeholder="other Name"
                                                            value="{{ $row->other_name }}">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="gender"
                                                        class="col-sm-3 col-form-label">Gender<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control" id="gender" name="gender"
                                                            required>
                                                            <option value="{{ $row->gender }}">{{ $row->gender }}
                                                            </option>
                                                            <option value="MALE">Male</option>
                                                            <option value="FEMALE">Female</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="date_of_birth" class="col-sm-3 col-form-label">Date of
                                                        Birth<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="date" class="form-control" id="date_of_birth"
                                                            name="date_of_birth" value="{{ $row->date_of_birth }}"
                                                            required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="place_of_birth" class="col-sm-3 col-form-label">Place
                                                        of Birth<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control"
                                                            id="place_of_birth" name="place_of_birth"
                                                            placeholder="Place of Birth"
                                                            value="{{ $row->place_of_birth }}" required>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label for="country"
                                                        class="col-sm-3 col-form-label">Country<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="country"
                                                            name="country" placeholder="Country"
                                                            value="{{ $row->country }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="state_origin" class="col-sm-3 col-form-label">State of
                                                        Origin<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="state_origin"
                                                            name="state_origin" placeholder="State of Origin"
                                                            value="{{ $row->state_origin }}" required>
                                                    </div>
                                                </div>
                                                @if (session('state') == 'BORNO')
                                                    <div class="form-group row">
                                                        <label for="lga_origin" class="col-sm-3 col-form-label">LGA
                                                            of Origin<sup>*</sup></label>
                                                        <div class="col-sm-9">
                                                            <select class="form-control" id="lga_origin"
                                                                name="lga_origin" required>
                                                                <option value="{{ $row->lga_origin }}">
                                                                    {{ $row->lga_origin }}</option>
                                                                @foreach ($lgas as $item)
                                                                    <option value="{{ $item->local_name }}">
                                                                        {{ $item->local_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="form-group row">
                                                        <label for="lga_origin" class="col-sm-3 col-form-label">LGA of
                                                            Origin<sup>*</sup></label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control"
                                                                id="lga_origin" name="lga_origin"
                                                                placeholder="LGA of Origin"
                                                                value="{{ $row->lga_origin }}" required>
                                                        </div>
                                                    </div>
                                                @endif




                                                <div class="form-group row">
                                                    <label for="marital_status"
                                                        class="col-sm-3 col-form-label">Marital
                                                        Status<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control" id="marital_status"
                                                            name="marital_status" required>
                                                            <option value="{{ $row->marital_status }}">
                                                                {{ $row->marital_status }}</option>
                                                            <option value="SINGLE">SINGLE</option>
                                                            <option value="MARRIED">MARRIED</option>
                                                            <option value="DIVORCED">DIVORCED</option>
                                                            <option value="WIDOWED">WIDOWED</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="maiden_name" class="col-sm-3 col-form-label">Maiden
                                                        Name<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="maiden_name"
                                                            name="maiden_name" placeholder="Maiden name"
                                                            value="{{ $row->maiden_name }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="religion"
                                                        class="col-sm-3 col-form-label">Religion<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control" id="religion" name="religion"
                                                            required>
                                                            <option value="{{ $row->religion }}">
                                                                {{ $row->religion }}</option>
                                                            <option value="Christianity">Christianity</option>
                                                            <option value="Islam">Islam</option>
                                                            <option value="Other">Other</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="nin"
                                                        class="col-sm-3 col-form-label">NIN<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="number" class="form-control" id="nin"
                                                            name="nin" value="{{ $row->nin }}"
                                                            placeholder="NIN" required>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label for="health_status" class="col-sm-3 col-form-label">Health
                                                        Status</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="health_status"
                                                            name="health_status" placeholder="Health Status"
                                                            value="{{ $row->health_status }}">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="physical_challenge"
                                                        class="col-sm-3 col-form-label">Physically Challenge
                                                        Status</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control" id="physical_challenge"
                                                            name="physical_challenge">
                                                            <option value="{{ $row->physical_challenge }}">Select
                                                                Option</option>
                                                            <option value="Yes">Yes</option>
                                                            <option value="No">No</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="hobbies"
                                                        class="col-sm-3 col-form-label">Hobbies</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="hobbies"
                                                            name="hobbies" value="{{ $row->hobbies }}">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="blood_group" class="col-sm-3 col-form-label">Blood
                                                        Group</label>
                                                    <div class="col-sm-9">
                                                        {{-- <input type="text" class="form-control" id="blood_group"
                                                            name="blood_group" placeholder="Blood Group"
                                                            value="{{ $row->blood_group }}"> --}}
                                                        <select name="blood_group" id="blood_group"
                                                            class="form-control">
                                                            <option value="{{ $row->blood_group }}">Selected:
                                                                {{ $row->blood_group }}</option>
                                                            <option value="A+">A+</option>
                                                            <option value="A-">A-</option>
                                                            <option value="B+">B+</option>
                                                            <option value="B-">B-</option>
                                                            <option value="AB+">AB+</option>
                                                            <option value="AB-">AB-</option>
                                                            <option value="O+">O+</option>
                                                            <option value="O-">O-</option>
                                                        </select>

                                                    </div>
                                                </div>

                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label for="room" class="col-sm-3 col-form-label">Room</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="room"
                                                            name="room" placeholder="Room"
                                                            value="{{ $row->room }}" disabled>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="Hall" class="col-sm-3 col-form-label">Hall</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="Hall"
                                                            name="Hall" placeholder="Hall"
                                                            value="{{ $row->hall }}" disabled>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="games"
                                                        class="col-sm-3 col-form-label">Sport</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="games"
                                                            name="games" placeholder="Sport"
                                                            value="{{ $row->games }}">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="genotype"
                                                        class="col-sm-3 col-form-label">Genotype</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="genotype"
                                                            name="genotype" placeholder="Genotype"
                                                            value="{{ $row->genotype }}">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="pills-admission" role="tabpanel"
                                        aria-labelledby="pills-admission-tab">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label for="highest_qualification"
                                                        class="col-sm-3 col-form-label">Highest Qualification</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control"
                                                            id="highest_qualification" name="highest_qualification"
                                                            placeholder="Highest Qualification"
                                                            value="{{ $row->highest_qualification }}" disabled>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="transfer_from"
                                                        class="col-sm-3 col-form-label">Transfer From</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="transfer_from"
                                                            name="transfer_from" placeholder="Transfer From"
                                                            value="{{ $row->transfer_from }}" disabled>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="faculty"
                                                        class="col-sm-3 col-form-label">Faculty<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="faculty"
                                                            name="faculty" placeholder="Faculty"
                                                            value="{{ DB::table('faculty')->where('code', $row->faculty)->value('title') }}"
                                                            disabled required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="department"
                                                        class="col-sm-3 col-form-label">Department<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="department"
                                                            name="department" placeholder="Department"
                                                            value="{{ DB::table('department')->where('code', $row->department)->value('title') }}"
                                                            disabled required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="program"
                                                        class="col-sm-3 col-form-label">Program<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="program"
                                                            name="program" placeholder="Program"
                                                            value="{{ DB::table('program')->where('code', $row->program)->value('title') }}"
                                                            disabled required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="mode_of_entry" class="col-sm-3 col-form-label">Mode of
                                                        Entry<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control" id="mode_of_entry"
                                                            name="mode_of_entry" disabled>
                                                            <option value="{{ $row->mode_of_entry }}">
                                                                {{ $row->mode_of_entry }}</option>
                                                            <option value="UTME">UTME</option>
                                                            <option value="DE">DE</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="session_of_entry"
                                                        class="col-sm-3 col-form-label">Session of
                                                        Entry<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control" id="session_of_entry"
                                                            name="session_of_entry" disabled required>
                                                            <option value="{{ $row->session_of_entry }}">
                                                                {{ $row->session_of_entry }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="level_of_entry"
                                                        class="col-sm-3 col-form-label">Level<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control" id="level" name="level"
                                                            required>
                                                            <option value="{{ $row->level }}">
                                                                {{ $row->level }}</option>
                                                            <option value="100">100 Level</option>
                                                            <option value="200">200 Level</option>
                                                            <option value="300">300 Level</option>
                                                            <option value="400">400 Level</option>
                                                            <option value="500">500 Level</option>
                                                            <option value="600">600 Level</option>
                                                        </select>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="pills-address" role="tabpanel"
                                        aria-labelledby="pills-address-tab">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label for="home_address"
                                                        class="col-sm-3 col-form-label">Permanent Home
                                                        Address<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="home_address"
                                                            name="home_address" value="{{ $row->home_address }}"
                                                            placeholder="Permanent Home Address" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="home_phone" class="col-sm-3 col-form-label">Home
                                                        Telephone<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="home_phone"
                                                            name="home_phone" placeholder="Home Phone"
                                                            value="{{ $row->home_phone }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="home_email" class="col-sm-3 col-form-label">Home
                                                        Email<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="home_email"
                                                            name="home_email" placeholder="Home Email"
                                                            value="{{ $row->home_email }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="contact_address"
                                                        class="col-sm-3 col-form-label">Contact
                                                        Address<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control"
                                                            id="contact_address" name="contact_address"
                                                            placeholder="Contact Address"
                                                            value="{{ $row->contact_address }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="contact_phone" class="col-sm-3 col-form-label">Contact
                                                        Telephone<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="contact_phone"
                                                            name="contact_phone" placeholder="Contact Telephone"
                                                            value="{{ $row->contact_phone }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="contact_email" class="col-sm-3 col-form-label">Contact
                                                        Email</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="contact_email"
                                                            name="contact_email" placeholder="Contact Email"
                                                            value="{{ $row->contact_email }}">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="pills-sponsor" role="tabpanel"
                                        aria-labelledby="pills-sponsor-tab">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label for="kin_name" class="col-sm-3 col-form-label">Next of Kin
                                                        Name<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="kin_name"
                                                            name="kin_name" placeholder="Next Kin Name"
                                                            value="{{ $row->kin_name }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="kin_address" class="col-sm-3 col-form-label">Next of
                                                        Kin Address<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="kin_address"
                                                            name="kin_address" placeholder="Next Kin Address"
                                                            value="{{ $row->kin_address }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="kin_phone" class="col-sm-3 col-form-label">Next of Kin
                                                        Telephone<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="kin_phone"
                                                            name="kin_phone" placeholder="Next of Kin Phone"
                                                            value="{{ $row->kin_phone }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="kin_email" class="col-sm-3 col-form-label">Next of Kin
                                                        Email<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="kin_email"
                                                            name="kin_email" placeholder="Next of Kin Email"
                                                            value="{{ $row->kin_email }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="sponsor_type" class="col-sm-3 col-form-label">Sponsor
                                                        Type<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control" id="sponsor_type"
                                                            name="sponsor_type" required>
                                                            <option value="{{ $row->sponsor_type }}">Select Option
                                                            </option>
                                                            <option value="SELF/FAMILY">SELF/FAMILY</option>
                                                            <option value="SCHOLARSHIP">SCHOLARSHIP</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="sponsor_name" class="col-sm-3 col-form-label">Sponsor
                                                        Name<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="sponsor_name"
                                                            name="sponsor_name" placeholder="Sponsor Name"
                                                            value="{{ $row->sponsor_name }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="sponsor_address" class="col-sm-3 col-form-label">Next
                                                        of Kin Address<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control"
                                                            id="sponsor_address" name="sponsor_address"
                                                            placeholder="Sponsor Address"
                                                            value="{{ $row->sponsor_address }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="sponsor_phone" class="col-sm-3 col-form-label">Next of
                                                        Kin Telephone<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="sponsor_phone"
                                                            name="sponsor_phone" placeholder="Sponsor Phone"
                                                            value="{{ $row->sponsor_phone }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="sponsor_email" class="col-sm-3 col-form-label">Next of
                                                        Kin Email</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="sponsor_email"
                                                            name="sponsor_email" placeholder="Sponsor Email"
                                                            value="{{ $row->sponsor_email }}">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                    <div class="tab-pane fade" id="pills-parent" role="tabpanel"
                                        aria-labelledby="pills-parent-tab">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label for="mother_name" class="col-sm-3 col-form-label">Mother's
                                                        Name<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="mother_name"
                                                            name="mother_name" placeholder="Mother's Name"
                                                            value="{{ $row->mother_name }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="mother_address"
                                                        class="col-sm-3 col-form-label">Mother's
                                                        Address<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control"
                                                            id="mother_address" name="mother_address"
                                                            placeholder="Mother's Address"
                                                            value="{{ $row->mother_address }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="mother_phone" class="col-sm-3 col-form-label">Mother's
                                                        Telephone<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="mother_phone"
                                                            name="mother_phone" placeholder="Mother's Phone"
                                                            value="{{ $row->mother_phone }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="mother_email" class="col-sm-3 col-form-label">Mother's
                                                        Email</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="mother_email"
                                                            name="mother_email" placeholder="Mother's Email"
                                                            value="{{ $row->mother_email }}">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="father_name" class="col-sm-3 col-form-label">Father's
                                                        Name<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="father_name"
                                                            name="father_name" placeholder="Father's Name"
                                                            value="{{ $row->father_name }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="father_address"
                                                        class="col-sm-3 col-form-label">Father's
                                                        Address<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control"
                                                            id="father_address" name="father_address"
                                                            placeholder="Father's Address"
                                                            value="{{ $row->father_address }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="father_phone" class="col-sm-3 col-form-label">Father's
                                                        Telephone<sup>*</sup></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="father_phone"
                                                            name="father_phone" placeholder="Father's Phone"
                                                            value="{{ $row->father_phone }}" required>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="father_email" class="col-sm-3 col-form-label">Father's
                                                        Email</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="father_email"
                                                            name="father_email" placeholder="Father's Email"
                                                            value="{{ $row->father_email }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="pills-signature" role="tabpanel"
                                        aria-labelledby="pills-signature-tab">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label for="picture"
                                                        class="col-sm-3 col-form-label">Picture</label>
                                                    <div class="col-sm-9">
                                                        <input type="file" class="form-control" id="picture"
                                                            name="picture">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="signiture"
                                                        class="col-sm-3 col-form-label">Signiture</label>
                                                    <div class="col-sm-9">
                                                        <input type="file" class="form-control" id="signiture"
                                                            name="signiture">
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" id="submit-profile-btn" class="btn btn-primary"
                            style="width: 100%"><i class="fa fa-edit" aria-hidden="true"></i> Update Profile</button>
                    </div>
                </div>


            </form>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <!-- End Content-->
    <!-- Include Select2 CSS and JS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Include validation script directly -->
    <script>
        /**
         * Profile form validation script
         * Validates all required fields before form submission
         */
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Form validation script loaded');

            // Add custom styles for validation feedback
            addCustomStyles();

            // Initialize select2 on select elements
            try {
                if ($.fn.select2) {
                    $('select').select2({
                        width: '100%',
                        placeholder: 'Select an option'
                    });
                    console.log('Select2 initialized successfully');
                }
            } catch (e) {
                console.error('Error initializing select2:', e);
            }

            // Get the form element
            const profileForm = document.getElementById('myform');
            const submitButton = document.getElementById('submit-profile-btn');
            console.log('Form found:', profileForm !== null);
            console.log('Submit button found:', submitButton !== null);

            // Add click event handler directly to the submit button
            submitButton.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default button action
                console.log('Submit button clicked - performing validation');
                validateAndSubmitForm(profileForm);
            });

            // Also keep the form submit handler as backup
            profileForm.addEventListener('submit', function(event) {
                console.log('Form submission attempted');
                // Prevent default form submission
                event.preventDefault();

                // Use the shared validation function
                validateAndSubmitForm(profileForm);
            });

            /**
             * Validates the form and submits it if valid
             */
            function validateAndSubmitForm(form) {
                console.log('Running form validation');

                // Array to store names of unfilled or invalid fields
                const invalidFields = [];

                // Validate all fields with required attribute
                const requiredFields = form.querySelectorAll('[required]');
                console.log('Fields with required attribute found:', requiredFields.length);
                validateRequiredFields(requiredFields, invalidFields);

                // Also validate fields with asterisk in label (may not have required attribute)
                validateFieldsWithAsteriskLabels(form, invalidFields);

                // Log the complete validation summary
                console.table(invalidFields);

                // Validate email fields (even non-required ones that have content)
                const emailFields = form.querySelectorAll('input[type="text"][id$="email"]');
                validateEmailFields(emailFields, invalidFields);

                console.log('Invalid fields found:', invalidFields.length);

                // If there are invalid fields, show error message
                if (invalidFields.length > 0) {
                    console.log('Validation failed - showing errors');
                    showValidationErrors(invalidFields, form);
                    return false;
                } else {
                    // If all validations pass, submit the form
                    console.log('Form valid, submitting');
                    form.submit();
                    return true;
                }
            }

            // Add input event listeners to clear validation errors when user edits fields
            addInputListeners(profileForm);
        });

        /**
         * Validates all required fields
         */
        function validateRequiredFields(requiredFields, invalidFields) {
            requiredFields.forEach(function(field) {
                // Skip disabled fields as they can't be edited
                if (field.disabled) return;

                // Check if field is empty
                if (!field.value.trim()) {
                    // Add field to invalid fields list with reason
                    addInvalidField(field, invalidFields, 'required');

                    // Add visual indication
                    markFieldAsInvalid(field, 'This field is required');
                    console.log('Required field is empty:', field.id || field.name);
                } else {
                    // Remove error indication if field has value
                    markFieldAsValid(field);
                }
            });
        }

        /**
         * Validates fields that have asterisk in their labels (required fields)
         * This catches fields that should be required but don't have the required attribute
         */
        function validateFieldsWithAsteriskLabels(form, invalidFields) {
            // Get all form groups
            const formGroups = form.querySelectorAll('.form-group');

            formGroups.forEach(function(group) {
                // Look for labels with asterisk
                const label = group.querySelector('label');
                if (label && label.innerHTML.includes('*')) {
                    // Find the input in this group
                    const input = group.querySelector('input, select, textarea');

                    // If input exists, is not disabled, not already required, and is empty
                    if (input && !input.disabled && !input.hasAttribute('required') && !input.value.trim()) {
                        // Add to invalid fields
                        addInvalidField(input, invalidFields, 'required');

                        // Mark as invalid
                        markFieldAsInvalid(input, 'This field is required');
                        console.log('Field with asterisk label is empty:', input.id || input.name);
                    }
                }
            });

            console.log('Checked fields with asterisk labels');
        }

        /**
         * Validates email format for email fields
         */
        function validateEmailFields(emailFields, invalidFields) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            emailFields.forEach(function(field) {
                // Skip empty non-required fields
                if (!field.value.trim() && !field.hasAttribute('required')) {
                    return;
                }

                // Check if email format is valid
                if (field.value.trim() && !emailRegex.test(field.value.trim())) {
                    // Add field to invalid fields list with reason
                    addInvalidField(field, invalidFields, 'invalid email');

                    // Add visual indication
                    markFieldAsInvalid(field, 'Please enter a valid email address');
                }
            });
        }

        /**
         * Adds a field to the invalid fields list
         */
        function addInvalidField(field, invalidFields, reason) {
            // Get the field label
            let fieldLabel = '';
            const labelElement = field.closest('.form-group').querySelector('label');
            if (labelElement) {
                // Extract clean label text: remove asterisk, normalize spaces
                fieldLabel = labelElement.textContent
                    .replace(/\s*\*\s*$/, '') // Remove asterisk at end
                    .replace(/\s+/g, ' ') // Normalize multiple spaces
                    .replace(/\s*:\s*$/, '') // Remove any trailing colon
                    .trim();
            } else {
                // Use field name or ID if label not found, make it human readable
                fieldLabel = (field.name || field.id || '')
                    .replace(/_/g, ' ') // Convert underscore to space
                    .replace(/([A-Z])/g, ' $1') // Add space before capital letters
                    .replace(/\s+/g, ' ') // Normalize multiple spaces
                    .trim();
                // Capitalize the first letter
                fieldLabel = fieldLabel.charAt(0).toUpperCase() + fieldLabel.slice(1);
            }

            invalidFields.push({
                label: fieldLabel,
                id: field.id,
                reason: reason
            });
        }

        /**
         * Marks a field as invalid with custom styling and feedback message
         */
        function markFieldAsInvalid(field, message) {
            field.classList.add('is-invalid');

            // Add or update feedback message
            let feedbackDiv = field.parentNode.querySelector('.invalid-feedback');
            if (!feedbackDiv) {
                feedbackDiv = document.createElement('div');
                feedbackDiv.className = 'invalid-feedback';
                field.parentNode.appendChild(feedbackDiv);
            }
            feedbackDiv.textContent = message;
        }

        /**
         * Marks a field as valid by removing validation styling
         */
        function markFieldAsValid(field) {
            field.classList.remove('is-invalid');
            const feedbackDiv = field.parentNode.querySelector('.invalid-feedback');
            if (feedbackDiv) {
                feedbackDiv.remove();
            }
        }

        /**
         * Displays validation errors to the user
         */
        function showValidationErrors(invalidFields, form) {
            // Create compact alert message with list of unfilled fields
            let alertMessage = 'Please fix the following issues:\n';

            invalidFields.forEach(function(field, index) {
                // Clean up field label - ensure it's concise and remove any excess whitespace
                let cleanLabel = field.label.replace(/\s+/g, ' ').trim();
                let reasonText = field.reason === 'required' ? 'required' : 'invalid format';
                alertMessage += `${index + 1}. ${cleanLabel} (${reasonText})\n`;
            });

            // Show alert to user - using multiple methods to ensure it displays
            console.log('VALIDATION ERRORS:', alertMessage);

            // Create and show a compact, styled validation alert
            createCompactValidationAlert(alertMessage, invalidFields, form);
        }

        /**
         * Creates a styled, compact validation alert
         */
        function createCompactValidationAlert(message, invalidFields, form) {
            try {
                // Try standard alert first
                // window.alert(message);

                // Remove any existing alert
                const existingAlert = document.getElementById('validation-alert-modal');
                if (existingAlert) {
                    document.body.removeChild(existingAlert);
                }

                // Create modal backdrop
                const backdrop = document.createElement('div');
                backdrop.style.position = 'fixed';
                backdrop.style.top = '0';
                backdrop.style.left = '0';
                backdrop.style.width = '100%';
                backdrop.style.height = '100%';
                backdrop.style.backgroundColor = 'rgba(0,0,0,0.5)';
                backdrop.style.zIndex = '9998';
                backdrop.id = 'validation-backdrop';

                // Create alert container
                const alertDiv = document.createElement('div');
                alertDiv.id = 'validation-alert-modal';
                alertDiv.style.position = 'fixed';
                alertDiv.style.top = '20%';
                alertDiv.style.left = '50%';
                alertDiv.style.transform = 'translateX(-50%)';
                alertDiv.style.backgroundColor = 'white';
                alertDiv.style.color = '#333';
                alertDiv.style.borderRadius = '5px';
                alertDiv.style.boxShadow = '0 0 20px rgba(0,0,0,0.5)';
                alertDiv.style.zIndex = '9999';
                alertDiv.style.width = '450px';
                alertDiv.style.maxWidth = '90%';
                alertDiv.style.maxHeight = '80vh';
                alertDiv.style.overflowY = 'auto';

                // Create header
                const header = document.createElement('div');
                header.style.backgroundColor = '#dc3545';
                header.style.color = 'white';
                header.style.padding = '10px 15px';
                header.style.borderTopLeftRadius = '5px';
                header.style.borderTopRightRadius = '5px';
                header.style.fontSize = '18px';
                header.style.fontWeight = 'bold';
                header.style.display = 'flex';
                header.style.justifyContent = 'space-between';
                header.style.alignItems = 'center';

                const title = document.createElement('span');
                title.textContent = 'Validation Error';

                const closeX = document.createElement('span');
                closeX.textContent = '×';
                closeX.style.cursor = 'pointer';
                closeX.style.fontSize = '24px';
                closeX.style.fontWeight = 'bold';
                closeX.onclick = function() {
                    document.body.removeChild(alertDiv);
                    document.body.removeChild(backdrop);
                };

                header.appendChild(title);
                header.appendChild(closeX);
                alertDiv.appendChild(header);

                // Create content area
                const content = document.createElement('div');
                content.style.padding = '15px';

                // Add validation message
                const messageBox = document.createElement('div');
                messageBox.textContent = 'The form contains incomplete or invalid fields:';
                messageBox.style.marginBottom = '10px';
                content.appendChild(messageBox);

                // Create error list
                const errorList = document.createElement('ul');
                errorList.style.marginLeft = '0';
                errorList.style.paddingLeft = '20px';
                errorList.style.color = '#721c24';

                invalidFields.forEach(function(field) {
                    const item = document.createElement('li');
                    item.textContent = field.label + (field.reason === 'required' ? ' is required' :
                        ' has invalid format');
                    item.style.marginBottom = '3px';
                    item.style.cursor = 'pointer';
                    item.onclick = function() {
                        // Find and focus this field
                        const inputField = document.getElementById(field.id);
                        if (inputField) {
                            // Close modal
                            document.body.removeChild(alertDiv);
                            document.body.removeChild(backdrop);

                            // Focus and navigate to field
                            navigateToField(inputField);
                        }
                    };
                    errorList.appendChild(item);
                });

                content.appendChild(errorList);

                // Add footer note
                const note = document.createElement('div');
                note.textContent = 'Click on any field to navigate directly to it.';
                note.style.fontSize = '12px';
                note.style.fontStyle = 'italic';
                note.style.marginTop = '10px';
                content.appendChild(note);

                alertDiv.appendChild(content);

                // Add button area
                const footer = document.createElement('div');
                footer.style.padding = '10px 15px';
                footer.style.borderTop = '1px solid #dee2e6';
                footer.style.textAlign = 'right';

                const closeButton = document.createElement('button');
                closeButton.textContent = 'Close';
                closeButton.style.backgroundColor = '#6c757d';
                closeButton.style.color = 'white';
                closeButton.style.border = 'none';
                closeButton.style.padding = '6px 12px';
                closeButton.style.borderRadius = '4px';
                closeButton.style.cursor = 'pointer';
                closeButton.onclick = function() {
                    document.body.removeChild(alertDiv);
                    document.body.removeChild(backdrop);

                    // Focus on first invalid field
                    navigateToFirstInvalidField(form);
                };

                footer.appendChild(closeButton);
                alertDiv.appendChild(footer);

                // Add to page
                document.body.appendChild(backdrop);
                document.body.appendChild(alertDiv);
            } catch (e) {
                console.error('Error showing validation alert:', e);
                // Fallback to standard alert
                window.alert(message);
            }

        }

        /**
         * Navigates to a specific field and activates its tab
         */
        function navigateToField(field) {
            if (field) {
                // Focus the field
                field.focus();

                // Scroll to the tab containing the field
                const tabPane = field.closest('.tab-pane');
                if (tabPane) {
                    const tabId = tabPane.id;
                    const tabLink = document.querySelector(`[href="#${tabId}"]`);
                    if (tabLink) {
                        tabLink.click();
                    }
                }

                // Scroll the field into view with some margin
                field.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }

        /**
         * Navigates to the first invalid field and activates its tab
         */
        function navigateToFirstInvalidField(form) {
            const firstInvalidField = form.querySelector('.is-invalid');
            if (firstInvalidField) {
                firstInvalidField.focus();

                // Scroll to the tab containing the first invalid field
                const tabPane = firstInvalidField.closest('.tab-pane');
                if (tabPane) {
                    const tabId = tabPane.id;
                    const tabLink = document.querySelector(`[href="#${tabId}"]`);
                    if (tabLink) {
                        tabLink.click();
                    }
                }
            }
        }

        /**
         * Adds input event listeners to form fields
         */
        function addInputListeners(form) {
            // Listen to all inputs to clear validation errors on edit
            const allFields = form.querySelectorAll('input, select, textarea');
            allFields.forEach(function(field) {
                field.addEventListener('input', function() {
                    if (field.value.trim()) {
                        markFieldAsValid(field);
                    }
                });
            });
        }

        /**
         * Adds custom CSS styles for validation
         */
        function addCustomStyles() {
            const styleEl = document.createElement('style');
            styleEl.textContent = `
            .is-invalid {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
            }
            .invalid-feedback {
                display: block !important;
                width: 100%;
                margin-top: 0.25rem;
                font-size: 0.875em;
                color: #dc3545;
            }
            
            /* Tab styling for sections with errors */
            .has-error-tab {
                position: relative;
            }
            .has-error-tab::after {
                content: '!';
                position: absolute;
                top: -5px;
                right: -5px;
                width: 16px;
                height: 16px;
                background-color: #dc3545;
                color: white;
                border-radius: 50%;
                font-size: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
            }
        `;
            document.head.appendChild(styleEl);
        }
    </script>
@endforeach
