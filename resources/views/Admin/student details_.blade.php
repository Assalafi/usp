@php
    use App\Models\Student;
    $data = Student::where('id', $id) -> get();
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
                                <img src="{{ asset('dashboard/images/user/avatar-2.jpg') }}" class="img-radius img-fluid wid-80" alt="{{ __('field_photo') }}">
                                <div class="certificated-badge">
                                    <i class="fas fa-certificate text-primary bg-icon"></i>
                                    <i class="fas fa-check front-icon text-white"></i>
                                </div>
                            </div>
                            <div class="media-body ms-3">
                                <h6 class="mb-1">{{ $row -> fullname }}</h6>
                                <p class="mb-0 text-muted">#{{ $row -> jamb_no }}</p>
                            </div>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <span class="f-w-500"><i class="far fa-envelope m-r-10"></i>{{ ('Email') }} : </span>
                            <span class="float-end">{{ $row->email }}</span>
                        </li>
                        <li class="list-group-item">
                            <span class="f-w-500"><i class="fas fa-phone-alt m-r-10"></i>{{ ('Phone') }} : </span>
                            <span class="float-end">{{ $row->phone }}</span>
                        </li>
                        <li class="list-group-item">
                            <span class="f-w-500"><i class="fas fa-graduation-cap m-r-10"></i>{{ ('Program') }} : </span>
                            <span class="float-end">{{ $row->program }}</span>
                        </li>
                        <li class="list-group-item">
                            <span class="f-w-500"><i class="far fa-calendar-alt m-r-10"></i>{{ ('Admission Date') }} : </span>
                            <span class="float-end">
                                {{ date('d m Y', strtotime($row->created_at)) }}
                            </span>
                        </li>
                        <li class="list-group-item border-bottom-0">
                            <span class="f-w-500"><i class="far fa-question-circle m-r-10"></i>{{ ('Jamb No') }} : </span>
                            <span class="float-end">#{{ $row->jamb_no }}</span>
                        </li>
                    </ul>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col">
                                <h6 class="mb-1"></h6>
                                <p class="mb-0"></p>
                            </div>
                            <div class="col border-start">
                                <h6 class="mb-1">
                                    2
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
                                    <p><mark class="text-primary">{{ ('Father Name') }}:</mark> {{ $row->father_name }}</p><hr/>
                                    <p><mark class="text-primary">{{ ('Mother') }}:</mark> {{ $row->mother_name }}</p><hr/>

                                    <p><mark class="text-primary">{{ ('Gender') }}:</mark> 
                                        @if( $row->gender == 'M' )
                                        {{ ('Male') }}
                                        @elseif( $row->gender == 'F' )
                                        {{ ('Female') }}
                                        @endif
                                    </p><hr/>

                                    <p><mark class="text-primary">{{ ('Date of Birth') }}:</mark>
                                        {{ $row -> date_of_birth }}
                                    </p><hr/>

                                    <p><mark class="text-primary">{{ ('Emergency Phone') }}:</mark> {{ $row -> contact_phone }}</p><hr/>
                                    <p><mark class="text-primary">{{ ('Religion') }}:</mark> {{ $row->religion }}</p><hr/>
                                    <p><mark class="text-primary">{{ ('Nationality') }}:</mark> {{ $row->nationality }}</p><hr/>

                                    <p><mark class="text-primary">{{ ('Marital Status') }}:</mark>
                                        {{ $row -> marital_status }}
                                    </p><hr/>

                                    <p><mark class="text-primary">{{ ('Blood Group') }}:</mark> 
                                        {{ $row->blood_group }}
                                    </p><hr/>

                                    <p><mark class="text-primary">{{ ('NIN') }}:</mark> {{ $row->nin }}</p><hr/>
                                    </fieldset>
                                </div>
                                <div class="col-md-6">
                                    <fieldset class="row gx-2 scheduler-border">
                                    <legend>{{ ('Present') }} {{ ('Address') }}</legend>
                                    <p><mark class="text-primary">{{ ('Address') }}:</mark> {{ $row->home_address }}</p><hr/>
                                    <p><mark class="text-primary">{{ ('Phone') }}:</mark> {{ $row->home_phone }}</p><hr/>
                                    <p><mark class="text-primary">{{ ('Email') }}:</mark> {{ $row->home_email }}</p>
                                    </fieldset>

                                    <fieldset class="row gx-2 scheduler-border">
                                    <legend>{{ ('Permanent') }} {{ ('Address') }}</legend>
                                    <p><mark class="text-primary">{{ ('Address') }}:</mark> {{ $row->contact_address }}</p><hr/>
                                    <p><mark class="text-primary">{{ ('Phone') }}:</mark> {{ $row->contact_phone }}</p><hr/>
                                    <p><mark class="text-primary">{{ ('Email') }}:</mark> {{ $row->contact_email }}</p>
                                    </fieldset>

                                    <fieldset class="row gx-2 scheduler-border">
                                    <p><mark class="text-primary">{{ ('Hall') }}:</mark> {{ $row->hall }}</p><hr/>
                                    <p><mark class="text-primary">{{ ('Block') }}:</mark> {{ $row->room }}</p><hr/>
                                    <p><mark class="text-primary">{{ ('Room') }}:</mark> {{ $row->room }}</p><hr/>
                                    <p><mark class="text-primary">{{ ('Bed') }}:</mark> {{ $row->room }}</p><hr/>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="pills-bio_data-tab" data-bs-toggle="pill" href="#pills-bio_data" role="tab" aria-controls="pills-bio_data" aria-selected="true">{{ __('bio-data') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pills-admission-tab" data-bs-toggle="pill" href="#pills-admission" role="tab" aria-controls="pills-admission" aria-selected="true">{{ __('Admission') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pills-address-tab" data-bs-toggle="pill" href="#pills-address" role="tab" aria-controls="pills-address" aria-selected="true">{{ __('address') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pills-sponsor-tab" data-bs-toggle="pill" href="#pills-sponsor" role="tab" aria-controls="pills-sponsor" aria-selected="true">{{ __('next of kin & sponsor') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pills-parent-tab" data-bs-toggle="pill" href="#pills-parent" role="tab" aria-controls="pills-parent" aria-selected="true">{{ __('parent') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pills-signature-tab" data-bs-toggle="pill" href="#pills-signature" role="tab" aria-controls="pills-signature" aria-selected="true">{{ __('signature') }}</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-bio_data" role="tabpanel" aria-labelledby="pills-bio_data-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="username" class="col-sm-3 col-form-label">Student ID.<sup>*</sup></label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="username" name="username" placeholder="Student ID" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="surname" class="col-sm-3 col-form-label">Surname<sup>*</sup></label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="surname" name="surname" placeholder="Surname" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="first_name" class="col-sm-3 col-form-label">First Name<sup>*</sup></label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="other_name" class="col-sm-3 col-form-label">Other Name<sup>*</sup></label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="other_name" name="other_name" placeholder="other Name" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="gender" class="col-sm-3 col-form-label">Gender</label>
                                            <div class="col-sm-9">
                                                <select class="form-control" id="gender" name="gender" required>
                                                    <option value="">Select Gender</option>
                                                    <option value="MALE">Male</option>
                                                    <option value="FEMALE">Female</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="date_of_birth" class="col-sm-3 col-form-label">Date of Birth</label>
                                            <div class="col-sm-9">
                                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="place_of_birth" class="col-sm-3 col-form-label">Place of Birth<sup>*</sup></label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="place_of_birth" name="place_of_birth" placeholder="Place of Birth" required>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="country" class="col-sm-3 col-form-label">Country</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="country" name="country" placeholder="Country">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="state_origin" class="col-sm-3 col-form-label">State of Origin</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="state_origin" name="state_origin" placeholder="State of Origin">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="lga_origin" class="col-sm-3 col-form-label">LGA of Origin</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="lga_origin" name="lga_origin" placeholder="LGA of Origin">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="marital_status" class="col-sm-3 col-form-label">Marital Status</label>
                                            <div class="col-sm-9">
                                                <select class="form-control" id="marital_status" name="marital_status" required>
                                                    <option value="">Select Status</option>
                                                    <option value="SINGLE">SINGLE</option>
                                                    <option value="MARRIED">MARRIED</option>
                                                    <option value="DIVORCED">DIVORCED</option>
                                                    <option value="WIDOWED">WIDOWED</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="maiden_name" class="col-sm-3 col-form-label">Maiden Name</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="maiden_name" name="maiden_name" placeholder="Maiden name">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="religion" class="col-sm-3 col-form-label">Religion</label>
                                            <div class="col-sm-9">
                                                <select class="form-control" id="religion" name="religion">
                                                    <option value="">Select Religion</option>
                                                    <option value="Christianity">Christianity</option>
                                                    <option value="Islam">Islam</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="nin" class="col-sm-3 col-form-label">NIN</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="nin" name="nin" placeholder="NIN">
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="health_status" class="col-sm-3 col-form-label">Health Status</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="health_status" name="health_status" placeholder="Health Status">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="physical_challenge" class="col-sm-3 col-form-label">Physically Challenge Status</label>
                                            <div class="col-sm-9">
                                                <select class="form-control" id="physical_challenge" name="physical_challenge">
                                                    <option value="">Select Option</option>
                                                    <option value="Yes">Yes</option>
                                                    <option value="No">No</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="hobbies" class="col-sm-3 col-form-label">Hobbies</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="hobbies" name="hobbies">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="blood_group" class="col-sm-3 col-form-label">Blood Group</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="blood_group" name="blood_group" placeholder="Place of Birth">
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="room" class="col-sm-3 col-form-label">Room</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="room" name="room" placeholder="Room">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="Hall" class="col-sm-3 col-form-label">Hall</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="Hall" name="Hall" placeholder="Hall">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="games" class="col-sm-3 col-form-label">Sport</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="games" name="games" placeholder="Sport">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="genotype" class="col-sm-3 col-form-label">Genotype</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="genotype" name="genotype" placeholder="Genotype">
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pills-admission" role="tabpanel" aria-labelledby="pills-admission-tab">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label for="highest_qualification" class="col-sm-3 col-form-label">Highest Qualification</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="highest_qualification" name="highest_qualification" placeholder="Highest Qualification" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="transfer_from" class="col-sm-3 col-form-label">Transfer From</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="transfer_from" name="transfer_from" placeholder="Transfer From" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="faculty" class="col-sm-3 col-form-label">Faculty<sup>*</sup></label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="faculty" name="faculty" placeholder="Faculty" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="department" class="col-sm-3 col-form-label">Department<sup>*</sup></label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="department" name="department" placeholder="Department" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="program" class="col-sm-3 col-form-label">Program<sup>*</sup></label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="program" name="program" placeholder="Program" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="mode_of_entry" class="col-sm-3 col-form-label">Mode of Entry<sup>*</sup></label>
                                            <div class="col-sm-9">
                                                <select class="form-control" id="mode_of_entry" name="mode_of_entry" required>
                                                    <option value="">Select Option</option>
                                                    <option value="UTME">UTME</option>
                                                    <option value="DE">DE</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="session_of_entry" class="col-sm-3 col-form-label">Session of Entry<sup>*</sup></label>
                                            <div class="col-sm-9">
                                                <select class="form-control" id="session_of_entry" name="session_of_entry" required>
                                                    <option value="">Select Option</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="level_of_entry" class="col-sm-3 col-form-label">Level of Admission<sup>*</sup></label>
                                            <div class="col-sm-9">
                                                <select class="form-control" id="level_of_entry" name="level_of_entry" required>
                                                    <option value="">Select Option</option>
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
                            <div class="tab-pane fade" id="pills-address" role="tabpanel" aria-labelledby="pills-address-tab">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label for="home_address" class="col-sm-3 col-form-label">Permanent Home Address</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="home_address" name="home_address" placeholder="Permanent Home Address">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="home_phone" class="col-sm-3 col-form-label">Home Telephone</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="home_phone" name="home_phone" placeholder="Home Phone">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="home_email" class="col-sm-3 col-form-label">Home Email</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="home_email" name="home_email" placeholder="Home Email">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="contact_address" class="col-sm-3 col-form-label">Contact Address</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="contact_address" name="contact_address" placeholder="Contact Address">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="contact_phone" class="col-sm-3 col-form-label">Contact Telephone</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="contact_phone" name="contact_phone" placeholder="Contact Telephone">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="contact_email" class="col-sm-3 col-form-label">Contact Email</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="contact_email" name="contact_email" placeholder="Contact Email">
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
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->
@endforeach