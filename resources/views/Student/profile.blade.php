@php
    use App\Models\Student;
    use Illuminate\Support\Facades\DB;

    $data = Student::where('user_id', session('id'))->get();
    $lgas = DB::table('locals')->where('state_id', 8)->orderBy('local_name', 'ASC')->get();
    $editMode = request('mode') === 'edit' || $errors->any();
@endphp
@foreach ($data as $row)
    <!-- Start Content-->
    <div class="main-body student-profile-page">
        <div class="page-wrapper">
            <!-- [ Main Content ] start -->
            @if (!$editMode)
            <div class="row sp-profile-grid">
                <div class="col-md-4 sp-profile-identity">
                    <div class="card user-card user-card-1 sp-identity-card">
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
                    <div class="sp-card-edit-action">
                        <a class="sp-page-edit" href="{{ url('/profile?mode=edit') }}"><i class="fas fa-pen"></i> Edit profile</a>
                    </div>
                </div>
                <div class="col-md-8 sp-profile-overview">
                    <div class="card sp-overview-card">
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
            @endif
            <div class="sp-edit-intro"><div><span class="sp-kicker">Profile centre</span><h2>{{ $editMode ? 'Keep your details up to date' : 'Your profile information' }}</h2><p>{{ $editMode ? 'Complete each section below. Required fields are marked with *.' : 'Review your biodata and contact information. Use Edit profile below the identity card to make changes.' }}</p></div><div class="sp-edit-actions"><span class="sp-secure-pill"><i class="fas fa-shield-alt"></i> Secure &amp; private</span>@if ($editMode)<a class="sp-page-edit" href="{{ url('/profile') }}"><i class="fas fa-times"></i> Close editor</a>@endif</div></div>
            @if ($editMode)
            <form class="form-group" action="update-profile" id="myform" method="POST"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $row->id }}">
                <input type="hidden" name="processed_photo_token" id="processed_photo_token" value="">
                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm" role="alert">
                        <strong>Please review the highlighted fields.</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body sp-editor-card">
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
                                                        <input type="email" class="form-control" id="home_email"
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
                                                        <input type="email" class="form-control" id="contact_email"
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
                                                        <input type="email" class="form-control" id="kin_email"
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
                                                        <input type="email" class="form-control" id="sponsor_email"
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
                                                        <input type="email" class="form-control" id="mother_email"
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
                                                        <input type="email" class="form-control" id="father_email"
                                                            name="father_email" placeholder="Father's Email"
                                                            value="{{ $row->father_email }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="pills-signature" role="tabpanel"
                                        aria-labelledby="pills-signature-tab">
                                        <div class="sp-media-grid">
                                            <section class="sp-media-card">
                                                <div class="sp-media-heading">
                                                    <span class="sp-media-icon"><i class="fas fa-camera"></i></span>
                                                    <div><h5>Profile photograph</h5><p>Use a clear, front-facing passport photograph.</p></div>
                                                </div>
                                                <div class="sp-photo-editor">
                                                    <div class="sp-photo-frame"><img id="profile-photo-preview" src="{{ asset('storage/picture/' . $row->picture) }}" alt="Profile photo preview"></div>
                                                    <div class="sp-photo-controls">
                                                        <label for="picture" class="sp-upload-label"><i class="fas fa-upload"></i> Choose photo</label>
                                                        <input type="file" class="d-none" id="picture" name="picture" accept="image/jpeg,image/png" capture="user">
                                                        <div class="sp-photo-actions">
                                                            <button type="button" class="btn btn-primary" id="process-profile-photo"><i class="fas fa-wand-magic-sparkles"></i> Process &amp; preview</button>
                                                            <button type="button" class="btn btn-light" id="process-current-photo"><i class="fas fa-rotate"></i> Reprocess current</button>
                                                        </div>
                                                        <div id="photo-processing-status" class="sp-media-status" role="status"></div>
                                                        <small class="sp-help">One face only. The background is cleaned, the image is cropped to the shoulders and saved below 200 KB. Review the preview before updating.</small>
                                                    </div>
                                                </div>
                                            </section>
                                            <section class="sp-media-card">
                                                <div class="sp-media-heading">
                                                    <span class="sp-media-icon"><i class="fas fa-signature"></i></span>
                                                    <div><h5>Digital signature</h5><p>Draw your signature or upload a transparent image.</p></div>
                                                </div>
                                                <div class="sp-signature-wrap">
                                                    <canvas id="student-signature-canvas" width="800" height="300" aria-label="Signature drawing area"></canvas>
                                                    <div class="sp-signature-actions"><button type="button" class="btn btn-light btn-sm" id="clear-student-signature"><i class="fas fa-eraser"></i> Clear</button></div>
                                                </div>
                                                <label for="signiture" class="sp-upload-label sp-upload-secondary"><i class="fas fa-file-arrow-up"></i> Upload signature image</label>
                                                <input type="file" class="d-none" id="signiture" name="signiture" accept="image/png,image/jpeg">
                                                <small class="sp-help">Sign inside the box using your finger or mouse. Your signature appears on official documents and ID cards.</small>
                                                @if ($row->signiture)
                                                    <img class="sp-existing-signature" src="{{ asset('storage/signature/' . $row->signiture) }}" alt="Current signature">
                                                @endif
                                            </section>
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
            @endif
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
            if (!profileForm || !submitButton) return;

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
                const emailFields = form.querySelectorAll('input[id$="email"]');
                validateEmailFields(emailFields, invalidFields);

                console.log('Invalid fields found:', invalidFields.length);

                // If there are invalid fields, show error message
                if (invalidFields.length > 0) {
                    console.log('Validation failed - showing errors');
                    showValidationErrors(invalidFields, form);
                    return false;
                } else {
                    const photoInput = form.querySelector('#picture');
                    const photoToken = form.querySelector('#processed_photo_token');
                    if (photoInput && photoInput.files.length > 0 && (!photoToken || !photoToken.value)) {
                        const status = document.getElementById('photo-processing-status');
                        if (status) {
                            status.className = 'sp-media-status error';
                            status.textContent = 'Process the selected photo and review the preview before saving.';
                        }
                        document.getElementById('pills-signature-tab')?.click();
                        return false;
                    }
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
    <style>
        .student-profile-page { background: #f4f8fc; padding: 1rem; }
        .student-profile-page .card { border: 0; border-radius: 18px; box-shadow: 0 10px 30px rgba(27, 67, 102, .08); overflow: hidden; }
        .student-profile-page .user-card-1 { background: linear-gradient(145deg, #0f75bc, #3ea1e4); color: #fff; }
        .student-profile-page .user-card-1 .list-group-item { background: rgba(255,255,255,.1); color: inherit; border-color: rgba(255,255,255,.12); }
        .student-profile-page .nav-pills { gap: .45rem; padding: .35rem; background: #f1f6fb; border-radius: 12px; overflow-x: auto; flex-wrap: nowrap; }
        .student-profile-page .nav-pills .nav-link { white-space: nowrap; border-radius: 9px; color: #456176; font-weight: 600; }
        .student-profile-page .nav-pills .nav-link.active { background: #3ea1e4; color: #fff; box-shadow: 0 4px 12px rgba(62,161,228,.3); }
        .student-profile-page .tab-content { padding-top: .75rem; }
        .student-profile-page .form-control, .student-profile-page .form-select, .student-profile-page .select2-container--default .select2-selection--single { min-height: 44px; border-radius: 9px; border-color: #dce7f0; }
        .student-profile-page .form-control:focus, .student-profile-page .form-select:focus { border-color: #3ea1e4; box-shadow: 0 0 0 .2rem rgba(62,161,228,.16); }
        .student-profile-page .col-form-label { color: #30485c; font-weight: 600; }
        .sp-media-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 1.25rem; }
        .sp-media-card { border: 1px solid #e1ebf3; border-radius: 16px; padding: 1.25rem; background: #fff; }
        .sp-media-heading { display:flex; align-items:center; gap:.75rem; margin-bottom:1rem; }
        .sp-media-heading h5 { margin:0; color:#203b50; font-weight:700; }
        .sp-media-heading p { margin:.2rem 0 0; color:#718596; font-size:.86rem; }
        .sp-media-icon { width:40px; height:40px; border-radius:12px; display:grid; place-items:center; background:#e8f5fd; color:#1686c9; font-size:1.1rem; }
        .sp-photo-editor { display:flex; gap:1rem; align-items:flex-start; }
        .sp-photo-frame { width:150px; aspect-ratio:3/4; flex:0 0 auto; overflow:hidden; border-radius:12px; background:#f2f6f9; border:1px solid #dce7ef; display:grid; place-items:center; }
        .sp-photo-frame img { width:100%; height:100%; object-fit:cover; }
        .sp-photo-controls { min-width:0; flex:1; }
        .sp-upload-label { display:inline-flex; align-items:center; gap:.45rem; cursor:pointer; color:#167db7; font-weight:700; font-size:.9rem; margin-bottom:.7rem; }
        .sp-photo-actions { display:flex; flex-wrap:wrap; gap:.5rem; }
        .sp-photo-actions .btn { border-radius:9px; font-size:.82rem; }
        .sp-media-status { min-height:1.4rem; margin-top:.65rem; font-size:.86rem; font-weight:600; }
        .sp-media-status.success { color:#168454; } .sp-media-status.error { color:#c23b48; }
        .sp-help { display:block; color:#7c8e9b; line-height:1.45; margin-top:.45rem; }
        .sp-signature-wrap { position:relative; border:1px dashed #b9cfdd; border-radius:12px; background:linear-gradient(180deg,#fff,#f8fbfd); overflow:hidden; }
        #student-signature-canvas { display:block; width:100%; height:auto; min-height:150px; touch-action:none; cursor:crosshair; }
        .sp-signature-actions { position:absolute; right:.55rem; top:.55rem; }
        .sp-upload-secondary { margin-top:.8rem; }
        .sp-existing-signature { max-width:170px; max-height:70px; object-fit:contain; display:block; margin-top:.5rem; border:1px solid #e1ebf3; border-radius:8px; }
        .student-profile-page #submit-profile-btn { border:0; border-radius:11px; min-height:48px; font-weight:700; background:#3ea1e4; box-shadow:0 7px 18px rgba(62,161,228,.28); }
        @media (max-width: 767.98px) {
            .student-profile-page { padding:.45rem; }
            .student-profile-page .page-wrapper { padding:0; }
            .student-profile-page .card-body { padding:1rem; }
            .sp-media-grid { grid-template-columns:1fr; gap:.85rem; }
            .sp-photo-editor { flex-direction:column; }
            .sp-photo-frame { width:125px; }
            .sp-photo-actions .btn { flex:1 1 100%; }
            .student-profile-page .form-group.row { margin-bottom:.8rem; }
            .student-profile-page .col-form-label { padding-bottom:.3rem; }
        }
    </style>
    <style>
        /* Profile workspace refresh: deliberately overrides the legacy grid for a calmer, mobile-first layout. */
        .student-profile-page { max-width: 1320px; margin: 0 auto; }
        .student-profile-page .sp-profile-grid { align-items: start; row-gap: 1rem; }
        .student-profile-page .sp-identity-card { height: auto!important; align-self:start; background: #fff; color: #18364b; border: 1px solid #e5eef5; position: relative; }
        .student-profile-page .sp-identity-card > .card-body:first-child { background: linear-gradient(135deg,#0d79be,#3ea1e4); padding: 1.35rem; color:#fff; }
        .student-profile-page .sp-identity-card .user-about-block { margin:0!important; }
        .student-profile-page .sp-identity-card .user-about-block img { width:74px; height:74px; border:3px solid rgba(255,255,255,.85); box-shadow:0 6px 18px rgba(0,0,0,.18); }
        .student-profile-page .sp-identity-card .media-body h6 { color:#fff; font-size:1.1rem; line-height:1.25; }
        .student-profile-page .sp-identity-card .media-body p { color:rgba(255,255,255,.82)!important; }
        .student-profile-page .sp-top-edit { display:inline-flex; align-items:center; justify-content:center; gap:.45rem; margin-top:.75rem; padding:.58rem .85rem; border:1px solid rgba(255,255,255,.55); border-radius:9px; color:#fff; background:rgba(255,255,255,.14); font-size:.82rem; font-weight:700; text-decoration:none; transition:.2s ease; }
        .student-profile-page .sp-top-edit:hover { background:#fff; color:#1479b5; }
        .student-profile-page .sp-identity-card .list-group-item { display:flex; gap:.65rem; align-items:flex-start; padding:.78rem 1.15rem; background:#fff; color:#587184; border-color:#edf2f6; }
        .student-profile-page .sp-identity-card .list-group-item .f-w-500 { flex:0 0 42%; color:#7c8d99; font-size:.78rem; text-transform:uppercase; letter-spacing:.03em; }
        .student-profile-page .sp-identity-card .list-group-item > .float-end { margin-left:auto; max-width:56%; color:#1f3b4e; text-align:right; overflow-wrap:anywhere; }
        .student-profile-page .sp-identity-card > .card-body:last-child { display:none; }
        .student-profile-page .sp-overview-card { height:100%; border:1px solid #e5eef5; }
        .student-profile-page .sp-overview-card .card-block { padding:1.25rem; }
        .student-profile-page .sp-overview-card fieldset { border:1px solid #e5eef5!important; border-radius:13px; padding:1rem 1.1rem!important; margin-bottom:1rem; background:#fbfdff; }
        .student-profile-page .sp-overview-card fieldset p { display:flex; justify-content:space-between; gap:1rem; margin:0; padding:.42rem 0; font-size:.88rem; color:#314d60; }
        .student-profile-page .sp-overview-card fieldset hr { display:none; }
        .student-profile-page .sp-overview-card fieldset mark { background:transparent; color:#8092a0!important; font-weight:600; }
        .student-profile-page .sp-overview-card legend { float:none; width:auto; padding:0 .45rem; margin:0 0 .4rem; font-size:.78rem; color:#197fba; font-weight:800; text-transform:uppercase; letter-spacing:.06em; }
        .student-profile-page .sp-overview-card .btn { border-radius:10px; min-height:44px; font-weight:700; }
        .student-profile-page > .page-wrapper > form { margin-top:1rem; }
        .student-profile-page .sp-edit-intro { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin:1.35rem 0 .7rem; }
        .student-profile-page .sp-edit-intro h2 { margin:.15rem 0 .25rem; color:#18364b; font-size:1.35rem; font-weight:800; }
        .student-profile-page .sp-edit-intro p { margin:0; color:#708594; font-size:.9rem; }
        .student-profile-page .sp-kicker { color:#1983bf; font-size:.72rem; text-transform:uppercase; letter-spacing:.11em; font-weight:800; }
        .student-profile-page .sp-edit-actions { display:flex; align-items:center; gap:.65rem; flex-wrap:wrap; justify-content:flex-end; }
        .student-profile-page .sp-page-edit { display:inline-flex; align-items:center; gap:.45rem; border-radius:10px; padding:.58rem .9rem; background:#3ea1e4; color:#fff; text-decoration:none; font-size:.82rem; font-weight:700; box-shadow:0 5px 14px rgba(62,161,228,.24); }
        .student-profile-page .sp-page-edit:hover { background:#1683c3; color:#fff; }
        .student-profile-page .sp-card-edit-action { margin-top:.7rem; }
        .student-profile-page .sp-card-edit-action .sp-page-edit { width:100%; justify-content:center; }
        .student-profile-page .sp-secure-pill { display:inline-flex; align-items:center; gap:.4rem; border:1px solid #ccebdc; background:#effaf4; color:#278155; border-radius:999px; padding:.5rem .8rem; white-space:nowrap; font-size:.78rem; font-weight:700; }
        .student-profile-page form > .row > .col-md-12 > .card { border:1px solid #e5eef5; }
        .student-profile-page .nav-pills { position:sticky; top:0; z-index:4; margin:-.25rem -.25rem 1rem; padding:.4rem; box-shadow:0 5px 14px rgba(35,78,105,.06); }
        .student-profile-page .nav-pills .nav-item { flex:0 0 auto; }
        .student-profile-page .tab-pane { animation:spFade .18s ease-out; }
        @keyframes spFade { from{opacity:.45;transform:translateY(3px)} to{opacity:1;transform:none} }
        .student-profile-page .form-group.row { align-items:center; padding:.28rem 0; }
        .student-profile-page .form-group.row label { font-size:.84rem; }
        .student-profile-page .form-group.row label sup { color:#d34b5a; margin-left:2px; }
        .student-profile-page .scheduler-border { border-color:#e5eef5!important; }
        .student-profile-page .sp-editor-card { display:grid; grid-template-columns:215px minmax(0,1fr); gap:1.25rem; align-items:start; }
        .student-profile-page .sp-editor-card > .nav-pills { display:flex; flex-direction:column; align-items:stretch; gap:.35rem; position:sticky; top:1rem; margin:0; overflow:visible; background:#f5f9fc; padding:.55rem; }
        .student-profile-page .sp-editor-card > .nav-pills .nav-link { display:flex; align-items:center; justify-content:flex-start; gap:.55rem; min-height:44px; padding:.65rem .75rem; font-size:.83rem; }
        .student-profile-page .sp-editor-card > .nav-pills .nav-link::after { content:'›'; margin-left:auto; font-size:1.15rem; opacity:.45; }
        .student-profile-page .sp-editor-card > .nav-pills .nav-link.active::after { content:'✓'; opacity:1; }
        .student-profile-page .sp-editor-card > .tab-content { min-width:0; padding:0; }
        .student-profile-page .sp-editor-card > .tab-content > .tab-pane { display:none; opacity:0; }
        .student-profile-page .sp-editor-card > .tab-content > .tab-pane.show { display:block; opacity:1; }
        .student-profile-page .sp-editor-card { display:block; padding:1.25rem!important; background:#fff; }
        .student-profile-page .sp-editor-card > .nav-pills { display:block!important; position:static; margin:0; padding:0; overflow:visible; background:transparent; box-shadow:none; }
        .student-profile-page .sp-editor-card > .nav-pills .sp-accordion-item { list-style:none; margin:0 0 .65rem; border:1px solid #e1ebf2; border-radius:13px; overflow:hidden; background:#fff; }
        .student-profile-page .sp-editor-card > .nav-pills .sp-accordion-item:last-child { margin-bottom:0; }
        .student-profile-page .sp-editor-card > .nav-pills .sp-accordion-item .nav-link { width:100%; border:0; border-radius:0; background:#f5f9fc; color:#345166; padding:.8rem 1rem; min-height:48px; }
        .student-profile-page .sp-editor-card > .nav-pills .sp-accordion-item .nav-link.active { background:linear-gradient(90deg,#e7f5fd,#f3faff); color:#1277b0; box-shadow:none; }
        .student-profile-page .sp-editor-card > .nav-pills .sp-accordion-item > .tab-pane { display:none; padding:1.2rem 1.05rem .85rem; border-top:1px solid #e4edf3; background:#fff; opacity:1; }
        .student-profile-page .sp-editor-card > .nav-pills .sp-accordion-item > .tab-pane.show { display:block; }
        .student-profile-page .sp-editor-card > .nav-pills .sp-accordion-item > .tab-pane > .row { margin-left:-.45rem; margin-right:-.45rem; }
        .student-profile-page .sp-editor-card > .nav-pills .sp-accordion-item > .tab-pane .form-group.row { margin-left:0; margin-right:0; border-bottom:1px solid #f0f4f7; }
        @media (max-width:767.98px) {
            .student-profile-page .sp-profile-grid { margin-left:0; margin-right:0; }
            .student-profile-page .sp-card-edit-action { margin-top:.55rem; }
            .student-profile-page .sp-edit-intro { align-items:flex-start; flex-direction:column; margin:.9rem 0 .6rem; gap:.55rem; }
            .student-profile-page .sp-edit-actions { width:100%; justify-content:space-between; }
            .student-profile-page .sp-page-edit { flex:1; justify-content:center; }
            .student-profile-page .sp-edit-intro h2 { font-size:1.15rem; }
            .student-profile-page .sp-edit-intro p { font-size:.82rem; }
            .student-profile-page .sp-profile-identity,.student-profile-page .sp-profile-overview { padding-left:0; padding-right:0; }
            .student-profile-page .sp-identity-card > .card-body:first-child { padding:1rem; }
            .student-profile-page .sp-top-edit { width:100%; margin-top:.65rem; }
            .student-profile-page .sp-identity-card .user-about-block img { width:62px; height:62px; }
            .student-profile-page .sp-identity-card .list-group-item { padding:.68rem .85rem; }
            .student-profile-page .sp-identity-card .list-group-item .f-w-500 { flex-basis:38%; }
            .student-profile-page .sp-overview-card .card-block { padding:.85rem; }
            .student-profile-page .sp-overview-card fieldset { padding:.7rem .8rem!important; margin-bottom:.7rem; }
            .student-profile-page .sp-overview-card fieldset p { font-size:.82rem; align-items:flex-start; }
            .student-profile-page form > .row > .col-md-12 > .card-body { padding:.7rem; }
            .student-profile-page .sp-editor-card { display:block; padding:.65rem!important; }
            .student-profile-page .sp-editor-card > .nav-pills { position:static; margin:0; padding:0; }
            .student-profile-page .sp-editor-card > .nav-pills .nav-link { min-height:42px; }
            .student-profile-page .sp-editor-card > .nav-pills .sp-accordion-item > .tab-pane { padding:.85rem .7rem .55rem; }
            .student-profile-page .sp-editor-card > .tab-content { padding:.1rem .1rem 0; }
            .student-profile-page .nav-pills { margin:-.1rem -.1rem .8rem; top:58px; }
            .student-profile-page .tab-content { padding:.1rem .1rem 0; }
            .student-profile-page .form-group.row { display:block; margin-bottom:.72rem; }
            .student-profile-page .form-group.row > [class*="col-"] { width:100%; max-width:100%; }
            .student-profile-page .form-group.row .col-form-label { display:block; padding:.15rem 0 .3rem; }
            .student-profile-page .form-group.row .form-control,.student-profile-page .form-group.row .select2-container { width:100%!important; }
            .student-profile-page .tab-pane > .row > [class*="col-"] { padding-left:.25rem; padding-right:.25rem; }
            .student-profile-page #submit-profile-btn { position:sticky; bottom:.55rem; z-index:5; }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('myform');
            const pictureInput = document.getElementById('picture');
            const picturePreview = document.getElementById('profile-photo-preview');
            const processButton = document.getElementById('process-profile-photo');
            const currentButton = document.getElementById('process-current-photo');
            const photoToken = document.getElementById('processed_photo_token');
            const photoStatus = document.getElementById('photo-processing-status');
            const csrf = form?.querySelector('input[name="_token"]')?.value || '';
            const processUrl = @json(route('profile.photo.preview'));

            // Edit sections behave as a stacked accordion: each section's content
            // sits directly under its heading, with only one section open at a time.
            const editorNav = document.getElementById('pills-tab');
            if (editorNav) {
                const links = Array.from(editorNav.querySelectorAll('.nav-link'));
                const panes = links.map(link => document.querySelector(link.getAttribute('href'))).filter(Boolean);
                const tabContent = document.getElementById('pills-tabContent');
                links.forEach(link => {
                    const item = link.closest('.nav-item');
                    const pane = document.querySelector(link.getAttribute('href'));
                    if (item && pane && pane.parentElement !== item) {
                        item.classList.add('sp-accordion-item');
                        item.appendChild(pane);
                    }
                });
                if (tabContent) tabContent.hidden = true;
                links.forEach(link => link.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    const pane = document.querySelector(this.getAttribute('href'));
                    const closing = this.classList.contains('active') && pane?.classList.contains('show');
                    links.forEach(item => { item.classList.remove('active'); item.setAttribute('aria-selected', 'false'); });
                    panes.forEach(item => item.classList.remove('show', 'active'));
                    if (!closing && pane) { this.classList.add('active'); this.setAttribute('aria-selected', 'true'); pane.classList.add('show', 'active'); }
                }));
            }

            const setPhotoStatus = (message, type = '') => { if (photoStatus) { photoStatus.className = 'sp-media-status ' + type; photoStatus.textContent = message; } };
            const processPhoto = async (useCurrent = false) => {
                if (!useCurrent && (!pictureInput || !pictureInput.files.length)) { setPhotoStatus('Choose a photo first.', 'error'); pictureInput?.click(); return; }
                const payload = new FormData(); payload.append('_token', csrf);
                if (!useCurrent) payload.append('picture', pictureInput.files[0]);
                processButton && (processButton.disabled = true); currentButton && (currentButton.disabled = true); setPhotoStatus('Processing photo…');
                try {
                    const response = await fetch(processUrl, { method:'POST', body:payload, headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'} });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || Object.values(data.errors || {}).flat()[0] || 'Photo processing failed.');
                    photoToken.value = data.token; picturePreview.src = data.preview; setPhotoStatus('Preview ready. Review it, then update your profile.', 'success');
                } catch (error) { photoToken.value = ''; setPhotoStatus(error.message, 'error'); }
                finally { processButton && (processButton.disabled = false); currentButton && (currentButton.disabled = false); }
            };
            pictureInput?.addEventListener('change', function () { photoToken.value = ''; setPhotoStatus('Photo selected. Click “Process & preview” before saving.'); if (this.files[0]) picturePreview.src = URL.createObjectURL(this.files[0]); });
            processButton?.addEventListener('click', () => processPhoto(false));
            currentButton?.addEventListener('click', () => processPhoto(true));

            const canvas = document.getElementById('student-signature-canvas');
            const signatureInput = document.getElementById('signiture');
            const clearSignature = document.getElementById('clear-student-signature');
            if (canvas && signatureInput) {
                const context = canvas.getContext('2d'); context.lineWidth = 4; context.lineCap = 'round'; context.lineJoin = 'round'; context.strokeStyle = '#152d42';
                let drawing = false, hasInk = false;
                const point = (event) => { const rect = canvas.getBoundingClientRect(); const source = event.touches?.[0] || event; return { x:(source.clientX-rect.left)*(canvas.width/rect.width), y:(source.clientY-rect.top)*(canvas.height/rect.height) }; };
                const start = (event) => { event.preventDefault(); drawing=true; const p=point(event); context.beginPath(); context.moveTo(p.x,p.y); };
                const move = (event) => { if(!drawing) return; event.preventDefault(); const p=point(event); context.lineTo(p.x,p.y); context.stroke(); hasInk=true; };
                const finish = () => { if(!drawing) return; drawing=false; if(hasInk) canvas.toBlob(blob => { const transfer = new DataTransfer(); transfer.items.add(new File([blob], 'signature.png', {type:'image/png'})); signatureInput.files=transfer.files; }, 'image/png'); };
                canvas.addEventListener('pointerdown',start); canvas.addEventListener('pointermove',move); canvas.addEventListener('pointerup',finish); canvas.addEventListener('pointerleave',finish);
                clearSignature?.addEventListener('click', () => { context.clearRect(0,0,canvas.width,canvas.height); hasInk=false; signatureInput.value=''; });
                signatureInput.addEventListener('change', () => { if(signatureInput.files.length) { const image = new Image(); image.onload=()=>{ context.clearRect(0,0,canvas.width,canvas.height); const scale=Math.min(canvas.width/image.width,canvas.height/image.height); const w=image.width*scale,h=image.height*scale; context.drawImage(image,(canvas.width-w)/2,(canvas.height-h)/2,w,h); hasInk=true; }; image.src=URL.createObjectURL(signatureInput.files[0]); } });
            }
        });
    </script>
@endforeach
