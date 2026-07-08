@php
    $totalCount = $totalCount ?? 0;
    $submittedCount = $submittedCount ?? 0;
    $draftCount = $draftCount ?? 0;
@endphp
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Recruitment Management</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard"><i class="feather icon-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Human Resource</a></li>
                        <li class="breadcrumb-item active">Recruitment</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="main-body">
        <div class="page-wrapper">
            <div class="row">
                <div class="col-sm-12">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif
                    @if(isset($error))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            API Error: {{ $error }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif

                    {{-- ── Stats Cards ── --}}
                    <div class="row mb-3">
                        <div class="col-6 col-md-4">
                            <div class="card mb-0" style="border-left:4px solid #4680ff;">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="mr-3"><i class="fas fa-users fa-2x" style="color:#4680ff;"></i></div>
                                    <div><h4 class="mb-0">{{ $submittedCount + $draftCount }}</h4><small class="text-muted">Total Applicants</small></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="card mb-0" style="border-left:4px solid #28a745;">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="mr-3"><i class="fas fa-check-circle fa-2x" style="color:#28a745;"></i></div>
                                    <div><h4 class="mb-0">{{ $submittedCount }}</h4><small class="text-muted">Submitted</small></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="card mb-0" style="border-left:4px solid #ffa21d;">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="mr-3"><i class="fas fa-edit fa-2x" style="color:#ffa21d;"></i></div>
                                    <div><h4 class="mb-0">{{ $draftCount }}</h4><small class="text-muted">Draft</small></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Action Bar ── --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="collapse" data-target="#filterPanel">
                                <i class="fas fa-filter"></i> Filters
                            </button>
                            <button type="button" class="btn btn-success btn-sm" onclick="$('#exportModal').modal('show')">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <button type="button" class="btn btn-default btn-sm" onclick="location.reload()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                        @if(isset($retrievedAt))
                        <small class="text-muted">Updated: {{ \Carbon\Carbon::parse($retrievedAt)->format('M d, Y h:i A') }}</small>
                        @endif
                    </div>

                    {{-- ── Inline Filter Panel ── --}}
                    <div class="collapse show" id="filterPanel">
                        <div class="card mb-3">
                            <div class="card-body py-3">
                                <form id="filterForm">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Search</label>
                                                <input type="text" class="form-control form-control-sm" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="App no, name, phone, email...">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Status</label>
                                                <select class="form-control form-control-sm" name="status">
                                                    <option value="">All</option>
                                                    <option value="NEW" {{ ($filters['status'] ?? '') == 'NEW' ? 'selected' : '' }}>Submitted</option>
                                                    <option value="DRAFT" {{ ($filters['status'] ?? '') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                                                    <option value="SCREENING" {{ ($filters['status'] ?? '') == 'SCREENING' ? 'selected' : '' }}>Screening</option>
                                                    <option value="SHORTLISTED" {{ ($filters['status'] ?? '') == 'SHORTLISTED' ? 'selected' : '' }}>Shortlisted</option>
                                                    <option value="REJECTED" {{ ($filters['status'] ?? '') == 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                                                </select>
                                            </div>
                                        </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Department</label>
                                            <select class="form-control form-control-sm" name="department" id="filterDepartment">
                                                <option value="">All Departments</option>
                                                <optgroup label="College of Medical Sciences">
                                                    <option>Nursing Science</option><option>Medical Laboratory Science</option><option>Radiography</option>
                                                    <option>Chemical Pathology</option><option>Medical Microbiology</option><option>Haematology</option>
                                                    <option>Pharmacology &amp; Therapeutics</option><option>Anaesthesia</option><option>Ear, Nose and Throat (ENT)</option>
                                                    <option>Ophthalmology</option><option>Paediatrics</option><option>Community Medicine</option>
                                                    <option>Mental Health</option><option>Radiology</option><option>Obstetrics &amp; Gynaecology (O&amp;G)</option>
                                                    <option>Orthopaedics</option><option>Medicine</option><option>Surgery</option>
                                                    <option>Child Dental Health</option><option>Oral &amp; Maxillofacial Surgery</option><option>Restorative Dentistry</option>
                                                    <option>Human Physiology</option><option>Nutrition and Dietetics</option><option>Human Anatomy</option>
                                                    <option>Medical Rehabilitation (Physiotherapy)</option>
                                                </optgroup>
                                                <optgroup label="Faculty of Agriculture">
                                                    <option>Animal Science</option><option>Soil Science</option><option>Crop Protection</option>
                                                    <option>Agric Extension</option><option>Agric Economics</option>
                                                </optgroup>
                                                <optgroup label="Faculty of Arts">
                                                    <option>Arabic Studies</option><option>English and Literary Studies</option><option>Islamic Studies</option>
                                                    <option>History and Strategic Studies</option><option>Languages and Linguistics</option>
                                                </optgroup>
                                                <optgroup label="Faculty of Communication">
                                                    <option>Broadcasting</option><option>Journalism</option><option>Mass Communication</option>
                                                </optgroup>
                                                <optgroup label="Faculty of Education">
                                                    <option>Arts Education</option><option>Continuing Education</option><option>Social Science Education</option>
                                                    <option>Science Education</option><option>Physical and Health Education</option><option>Library and Information Science</option>
                                                </optgroup>
                                                <optgroup label="Faculty of Engineering">
                                                    <option>Agricultural &amp; Environmental Resources Engineering</option><option>Chemical Engineering</option>
                                                    <option>Civil and Water Resources Engineering</option><option>Electrical and Electronics Engineering</option>
                                                    <option>Food Science Technology</option><option>Mechanical Engineering</option>
                                                    <option>Petroleum and Gas Engineering</option><option>Computer Engineering</option>
                                                </optgroup>
                                                <optgroup label="Faculty of Environmental Studies">
                                                    <option>Architecture</option><option>Building</option><option>Geomatics/Survey</option>
                                                    <option>Industrial Design</option><option>Urban and Regional Planning</option>
                                                </optgroup>
                                                <optgroup label="Faculty of Management Sciences">
                                                    <option>Accounting</option><option>Banking and Finance</option><option>Business Administration</option>
                                                    <option>Marketing</option><option>Public Administration</option>
                                                </optgroup>
                                                <optgroup label="Faculty of Pharmacy">
                                                    <option>Pharmaceutical Microbiology and Biotechnology</option><option>Pharmacognosy</option>
                                                    <option>Pharmaceutics and Pharmaceutical Technology</option>
                                                </optgroup>
                                                <optgroup label="Faculty of Physical Sciences">
                                                    <option>Computer Science</option><option>Geology</option><option>Mathematics</option>
                                                    <option>Physics</option><option>Petroleum Chemistry</option><option>Statistics</option>
                                                </optgroup>
                                                <optgroup label="Faculty of Life Sciences">
                                                    <option>Botany</option><option>Biochemistry</option><option>Biology</option>
                                                    <option>Biotechnology</option><option>Microbiology</option><option>Zoology</option>
                                                </optgroup>
                                                <optgroup label="Faculty of Law">
                                                    <option>Private Law</option><option>Public Law</option><option>Sharia Law</option>
                                                </optgroup>
                                                <optgroup label="Faculty of Social Sciences">
                                                    <option>Economics</option><option>Geography</option><option>Political Science</option>
                                                    <option>Sociology and Anthropology</option>
                                                </optgroup>
                                                <optgroup label="Faculty of Veterinary Medicine">
                                                    <option>Veterinary Microbiology</option>
                                                </optgroup>
                                                <optgroup label="Other Units">
                                                    <option>General Studies</option><option>Research Office</option><option>University Medical Centre</option>
                                                    <option>Registry</option><option>Bursary</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Post Applied</label>
                                            <select class="form-control form-control-sm" name="post" id="filterPost">
                                                <option value="">All Posts</option>
                                                <optgroup label="Academic">
                                                    <option>SENIOR LECTURER</option><option>LECTURER I</option><option>LECTURER II</option>
                                                    <option>ASSISTANT LECTURER</option><option>GRADUATE ASSISTANT</option>
                                                </optgroup>
                                                <optgroup label="Non-Academic">
                                                    <option>RESEARCH OFFICER II</option><option>MEDICAL LABORATORY TECHNICIAN</option>
                                                    <option>HEALTH INFORMATION MANAGEMENT TECHNICIAN</option><option>INSTRUCTOR</option>
                                                    <option>EDUCATION OFFICER II</option><option>PROCUREMENT OFFICER</option>
                                                    <option>ARCHIVIST II</option><option>ACCOUNTANT II</option><option>TECHNOLOGIST</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Gender</label>
                                            <select class="form-control form-control-sm" name="gender" id="filterGender">
                                                <option value="">All</option>
                                                <option value="MALE">Male</option>
                                                <option value="FEMALE">Female</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">State of Origin</label>
                                            <select class="form-control form-control-sm" name="state" id="filterState">
                                                <option value="">All States</option>
                                                <option>Abia</option><option>Adamawa</option><option>Akwa Ibom</option><option>Anambra</option>
                                                <option>Bauchi</option><option>Bayelsa</option><option>Benue</option><option>Borno</option>
                                                <option>Cross River</option><option>Delta</option><option>Ebonyi</option><option>Edo</option>
                                                <option>Ekiti</option><option>Enugu</option><option>FCT</option><option>Gombe</option>
                                                <option>Imo</option><option>Jigawa</option><option>Kaduna</option><option>Kano</option>
                                                <option>Katsina</option><option>Kebbi</option><option>Kogi</option><option>Kwara</option>
                                                <option>Lagos</option><option>Nasarawa</option><option>Niger</option><option>Ogun</option>
                                                <option>Ondo</option><option>Osun</option><option>Oyo</option><option>Plateau</option>
                                                <option>Rivers</option><option>Sokoto</option><option>Taraba</option><option>Yobe</option>
                                                <option>Zamfara</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">LGA</label>
                                            <select class="form-control form-control-sm" name="lga" id="filterLGA" disabled>
                                                <option value="">Select State First</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold">Staff Type</label>
                                            <select class="form-control form-control-sm" name="staff_type" id="filterStaffType">
                                                <option value="">All</option>
                                                <option value="ACADEMIC">Academic</option>
                                                <option value="NON_ACADEMIC">Non-Academic</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end mb-2">
                                        <button class="btn btn-primary btn-sm mr-2" id="applyFilters"><i class="fas fa-search"></i> Apply</button>
                                        <button class="btn btn-light btn-sm" id="resetFilters"><i class="fas fa-times"></i> Reset</button>
                                        <span class="ml-auto small text-muted" id="filterCount"></span>
                                    </div>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- ── Main Table ── --}}
                    <div class="card">
                        <div class="card-header py-2">
                            <h5 class="mb-0">Job Applicants</h5>
                        </div>
                        <div class="card-block">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-sm" id="applicantsTable">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th style="width:5%">#</th>
                                            <th style="width:22%">Applicant</th>
                                            <th style="width:18%">Contact</th>
                                            <th style="width:20%">Post / Department</th>
                                            <th style="width:10%">State / LGA</th>
                                            <th style="width:8%">Gender</th>
                                            <th style="width:8%">Status</th>
                                            <th style="width:9%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Table content will be populated by server-side DataTables -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Export Modal ── --}}
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-download"></i> Export Applicants</h5>
                <button type="button" class="close" data-dismiss="modal" onclick="$('#exportModal').modal('hide')"><span>&times;</span></button>
            </div>
            <form id="exportForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control form-control-sm" name="status">
                                    <option value="">All</option>
                                    <option value="NEW" selected>Submitted</option>
                                    <option value="DRAFT">Draft</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Department</label>
                                <select class="form-control form-control-sm export-dept-select" name="department">
                                    <option value="">All Departments</option>
                                    <optgroup label="College of Medical Sciences"><option>Nursing Science</option><option>Medical Laboratory Science</option><option>Radiography</option><option>Chemical Pathology</option><option>Medical Microbiology</option><option>Haematology</option><option>Pharmacology &amp; Therapeutics</option><option>Anaesthesia</option><option>Ear, Nose and Throat (ENT)</option><option>Ophthalmology</option><option>Paediatrics</option><option>Community Medicine</option><option>Mental Health</option><option>Radiology</option><option>Obstetrics &amp; Gynaecology (O&amp;G)</option><option>Orthopaedics</option><option>Medicine</option><option>Surgery</option><option>Child Dental Health</option><option>Oral &amp; Maxillofacial Surgery</option><option>Restorative Dentistry</option><option>Human Physiology</option><option>Nutrition and Dietetics</option><option>Human Anatomy</option><option>Medical Rehabilitation (Physiotherapy)</option></optgroup>
                                    <optgroup label="Faculty of Agriculture"><option>Animal Science</option><option>Soil Science</option><option>Crop Protection</option><option>Agric Extension</option><option>Agric Economics</option></optgroup>
                                    <optgroup label="Faculty of Arts"><option>Arabic Studies</option><option>English and Literary Studies</option><option>Islamic Studies</option><option>History and Strategic Studies</option><option>Languages and Linguistics</option></optgroup>
                                    <optgroup label="Faculty of Communication"><option>Broadcasting</option><option>Journalism</option><option>Mass Communication</option></optgroup>
                                    <optgroup label="Faculty of Education"><option>Arts Education</option><option>Continuing Education</option><option>Social Science Education</option><option>Science Education</option><option>Physical and Health Education</option><option>Library and Information Science</option></optgroup>
                                    <optgroup label="Faculty of Engineering"><option>Agricultural &amp; Environmental Resources Engineering</option><option>Chemical Engineering</option><option>Civil and Water Resources Engineering</option><option>Electrical and Electronics Engineering</option><option>Food Science Technology</option><option>Mechanical Engineering</option><option>Petroleum and Gas Engineering</option><option>Computer Engineering</option></optgroup>
                                    <optgroup label="Faculty of Environmental Studies"><option>Architecture</option><option>Building</option><option>Geomatics/Survey</option><option>Industrial Design</option><option>Urban and Regional Planning</option></optgroup>
                                    <optgroup label="Faculty of Management Sciences"><option>Accounting</option><option>Banking and Finance</option><option>Business Administration</option><option>Marketing</option><option>Public Administration</option></optgroup>
                                    <optgroup label="Faculty of Pharmacy"><option>Pharmaceutical Microbiology and Biotechnology</option><option>Pharmacognosy</option><option>Pharmaceutics and Pharmaceutical Technology</option></optgroup>
                                    <optgroup label="Faculty of Physical Sciences"><option>Computer Science</option><option>Geology</option><option>Mathematics</option><option>Physics</option><option>Petroleum Chemistry</option><option>Statistics</option></optgroup>
                                    <optgroup label="Faculty of Life Sciences"><option>Botany</option><option>Biochemistry</option><option>Biology</option><option>Biotechnology</option><option>Microbiology</option><option>Zoology</option></optgroup>
                                    <optgroup label="Faculty of Law"><option>Private Law</option><option>Public Law</option><option>Sharia Law</option></optgroup>
                                    <optgroup label="Faculty of Social Sciences"><option>Economics</option><option>Geography</option><option>Political Science</option><option>Sociology and Anthropology</option></optgroup>
                                    <optgroup label="Faculty of Veterinary Medicine"><option>Veterinary Microbiology</option></optgroup>
                                    <optgroup label="Other Units"><option>General Studies</option><option>Research Office</option><option>University Medical Centre</option><option>Registry</option><option>Bursary</option></optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Post Applied</label>
                                <select class="form-control form-control-sm export-post-select" name="post_applied">
                                    <option value="">All Posts</option>
                                    <optgroup label="Academic"><option>SENIOR LECTURER</option><option>LECTURER I</option><option>LECTURER II</option><option>ASSISTANT LECTURER</option><option>GRADUATE ASSISTANT</option></optgroup>
                                    <optgroup label="Non-Academic"><option>RESEARCH OFFICER II</option><option>MEDICAL LABORATORY TECHNICIAN</option><option>HEALTH INFORMATION MANAGEMENT TECHNICIAN</option><option>INSTRUCTOR</option><option>EDUCATION OFFICER II</option><option>PROCUREMENT OFFICER</option><option>ARCHIVIST II</option><option>ACCOUNTANT II</option><option>TECHNOLOGIST</option></optgroup>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>State</label>
                                <select class="form-control form-control-sm export-state-select" name="state">
                                    <option value="">All</option>
                                    <option>Abia</option><option>Adamawa</option><option>Akwa Ibom</option><option>Anambra</option>
                                    <option>Bauchi</option><option>Bayelsa</option><option>Benue</option><option>Borno</option>
                                    <option>Cross River</option><option>Delta</option><option>Ebonyi</option><option>Edo</option>
                                    <option>Ekiti</option><option>Enugu</option><option>FCT</option><option>Gombe</option>
                                    <option>Imo</option><option>Jigawa</option><option>Kaduna</option><option>Kano</option>
                                    <option>Katsina</option><option>Kebbi</option><option>Kogi</option><option>Kwara</option>
                                    <option>Lagos</option><option>Nasarawa</option><option>Niger</option><option>Ogun</option>
                                    <option>Ondo</option><option>Osun</option><option>Oyo</option><option>Plateau</option>
                                    <option>Rivers</option><option>Sokoto</option><option>Taraba</option><option>Yobe</option>
                                    <option>Zamfara</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>LGA</label>
                                <select class="form-control form-control-sm export-lga-select" name="lga" disabled>
                                    <option value="">Select State First</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Gender</label>
                                <select class="form-control form-control-sm" name="gender">
                                    <option value="">All</option>
                                    <option value="MALE">Male</option>
                                    <option value="FEMALE">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Staff Type</label>
                                <select class="form-control form-control-sm" name="staff_type">
                                    <option value="">All</option>
                                    <option value="ACADEMIC">Academic</option>
                                    <option value="NON_ACADEMIC">Non-Academic</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label>Export Format</label>
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-primary btn-sm active">
                                    <input type="radio" name="export_format" value="pdf" checked> PDF
                                </label>
                                <label class="btn btn-outline-primary btn-sm">
                                    <input type="radio" name="export_format" value="excel"> CSV
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" onclick="$('#exportModal').modal('hide')">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm" id="exportBtn"><i class="fas fa-download"></i> Export</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // ── Nigeria States & LGAs ──
    var nigeriaLGAs = {
        "Abia":["Aba North","Aba South","Arochukwu","Bende","Ikwuano","Isiala Ngwa North","Isiala Ngwa South","Isuikwuato","Obi Ngwa","Ohafia","Osisioma","Ugwunagbo","Ukwa East","Ukwa West","Umuahia North","Umuahia South","Umu Nneochi"],
        "Adamawa":["Demsa","Fufure","Ganye","Gayuk","Girei","Gombi","Grie","Hong","Jada","Lamurde","Madagali","Maiha","Mayo Belwa","Michika","Mubi North","Mubi South","Numan","Shelleng","Song","Toungo","Yola North","Yola South"],
        "Akwa Ibom":["Abak","Eastern Obolo","Eket","Esit Eket","Essien Udim","Etim Ekpo","Etinan","Ibeno","Ibesikpo Asutan","Ibiono-Ibom","Ika","Ikono","Ikot Abasi","Ikot Ekpene","Ini","Itu","Mbo","Mkpat-Enin","Nsit-Atai","Nsit-Ibom","Nsit-Ubium","Obot Akara","Okobo","Onna","Oron","Oruk Anam","Udung-Uko","Ukanafun","Uruan","Urue-Offong/Oruko","Uyo"],
        "Anambra":["Aguata","Anambra East","Anambra West","Anaocha","Awka North","Awka South","Ayamelum","Dunukofia","Ekwusigo","Idemili North","Idemili South","Ihiala","Njikoka","Nnewi North","Nnewi South","Ogbaru","Onitsha North","Onitsha South","Orumba North","Orumba South","Oyi"],
        "Bauchi":["Alkaleri","Bauchi","Bogoro","Damban","Darazo","Dass","Gamawa","Ganjuwa","Giade","Itas/Gadau","Jama'are","Katagum","Kirfi","Misau","Ningi","Shira","Tafawa Balewa","Toro","Warji","Zaki"],
        "Bayelsa":["Brass","Ekeremor","Kolokuma/Opokuma","Nembe","Ogbia","Sagbama","Southern Ijaw","Yenagoa"],
        "Benue":["Ado","Agatu","Apa","Buruku","Gboko","Guma","Gwer East","Gwer West","Katsina-Ala","Konshisha","Kwande","Logo","Makurdi","Obi","Ogbadibo","Ohimini","Oju","Okpokwu","Otukpo","Tarka","Ukum","Ushongo","Vandeikya"],
        "Borno":["Abadam","Askira/Uba","Bama","Bayo","Biu","Chibok","Damboa","Dikwa","Gubio","Guzamala","Gwoza","Hawul","Jere","Kaga","Kala/Balge","Konduga","Kukawa","Kwaya Kusar","Mafa","Magumeri","Maiduguri","Marte","Mobbar","Monguno","Ngala","Nganzai","Shani"],
        "Cross River":["Abi","Akamkpa","Akpabuyo","Bakassi","Bekwarra","Biase","Boki","Calabar Municipal","Calabar South","Etung","Ikom","Obanliku","Obubra","Obudu","Odukpani","Ogoja","Yakuur","Yala"],
        "Delta":["Aniocha North","Aniocha South","Bomadi","Burutu","Ethiope East","Ethiope West","Ika North East","Ika South","Isoko North","Isoko South","Ndokwa East","Ndokwa West","Okpe","Oshimili North","Oshimili South","Patani","Sapele","Udu","Ughelli North","Ughelli South","Ukwuani","Uvwie","Warri North","Warri South","Warri South West"],
        "Ebonyi":["Abakaliki","Afikpo North","Afikpo South","Ebonyi","Ezza North","Ezza South","Ikwo","Ishielu","Ivo","Izzi","Ohaozara","Ohaukwu","Onicha"],
        "Edo":["Akoko-Edo","Egor","Esan Central","Esan North-East","Esan South-East","Esan West","Etsako Central","Etsako East","Etsako West","Igueben","Ikpoba-Okha","Oredo","Orhionmwon","Ovia North-East","Ovia South-West","Owan East","Owan West","Uhunmwonde"],
        "Ekiti":["Ado Ekiti","Efon","Ekiti East","Ekiti South-West","Ekiti West","Emure","Gbonyin","Ido Osi","Ijero","Ikere","Ikole","Ilejemeje","Irepodun/Ifelodun","Ise/Orun","Moba","Oye"],
        "Enugu":["Aninri","Awgu","Enugu East","Enugu North","Enugu South","Ezeagu","Igbo Etiti","Igbo Eze North","Igbo Eze South","Isi Uzo","Nkanu East","Nkanu West","Nsukka","Oji River","Udenu","Udi","Uzo-Uwani"],
        "FCT":["Abaji","Bwari","Gwagwalada","Kuje","Kwali","Municipal Area Council"],
        "Gombe":["Akko","Balanga","Billiri","Dukku","Funakaye","Gombe","Kaltungo","Kwami","Nafada","Shongom","Yamaltu/Deba"],
        "Imo":["Aboh Mbaise","Ahiazu Mbaise","Ehime Mbano","Ezinihitte","Ideato North","Ideato South","Ihitte/Uboma","Ikeduru","Isiala Mbano","Isu","Mbaitoli","Ngor Okpala","Njaba","Nkwerre","Nwangele","Obowo","Oguta","Ohaji/Egbema","Okigwe","Onuimo","Orlu","Orsu","Oru East","Oru West","Owerri Municipal","Owerri North","Owerri West"],
        "Jigawa":["Auyo","Babura","Biriniwa","Birnin Kudu","Buji","Dutse","Gagarawa","Garki","Gumel","Guri","Gwaram","Gwiwa","Hadejia","Jahun","Kafin Hausa","Kaugama","Kazaure","Kiri Kasama","Kiyawa","Maigatari","Malam Madori","Miga","Ringim","Roni","Sule Tankarkar","Taura","Yankwashi"],
        "Kaduna":["Birnin Gwari","Chikun","Giwa","Igabi","Ikara","Jaba","Jema'a","Kachia","Kaduna North","Kaduna South","Kagarko","Kajuru","Kaura","Kauru","Kubau","Kudan","Lere","Makarfi","Sabon Gari","Sanga","Soba","Zangon Kataf","Zaria"],
        "Kano":["Ajingi","Albasu","Bagwai","Bebeji","Bichi","Bunkure","Dala","Dambatta","Dawakin Kudu","Dawakin Tofa","Doguwa","Fagge","Gabasawa","Garko","Garun Mallam","Gaya","Gezawa","Gwale","Gwarzo","Kabo","Kano Municipal","Karaye","Kibiya","Kiru","Kumbotso","Kunchi","Kura","Madobi","Makoda","Minjibir","Nassarawa","Rano","Rimin Gado","Rogo","Shanono","Sumaila","Takai","Tarauni","Tofa","Tsanyawa","Tudun Wada","Ungogo","Warawa","Wudil"],
        "Katsina":["Bakori","Batagarawa","Batsari","Baure","Bindawa","Charanchi","Dandume","Danja","Dan Musa","Daura","Dutsi","Dutsin Ma","Faskari","Funtua","Ingawa","Jibia","Kafur","Kaita","Kankara","Kankia","Katsina","Kurfi","Kusada","Mai'Adua","Malumfashi","Mani","Mashi","Matazu","Musawa","Rimi","Sabuwa","Safana","Sandamu","Zango"],
        "Kebbi":["Aleiro","Arewa Dandi","Argungu","Augie","Bagudo","Birnin Kebbi","Bunza","Dandi","Fakai","Gwandu","Jega","Kalgo","Koko/Besse","Maiyama","Ngaski","Sakaba","Shanga","Suru","Wasagu/Danko","Yauri","Zuru"],
        "Kogi":["Adavi","Ajaokuta","Ankpa","Bassa","Dekina","Ibaji","Idah","Igalamela Odolu","Ijumu","Kabba/Bunu","Kogi","Lokoja","Mopa Muro","Ofu","Ogori/Magongo","Okehi","Okene","Olamaboro","Omala","Yagba East","Yagba West"],
        "Kwara":["Asa","Baruten","Edu","Ekiti","Ifelodun","Ilorin East","Ilorin South","Ilorin West","Irepodun","Isin","Kaiama","Moro","Offa","Oke Ero","Oyun","Pategi"],
        "Lagos":["Agege","Ajeromi-Ifelodun","Alimosho","Amuwo-Odofin","Apapa","Badagry","Epe","Eti Osa","Ibeju-Lekki","Ifako-Ijaiye","Ikeja","Ikorodu","Kosofe","Lagos Island","Lagos Mainland","Mushin","Ojo","Oshodi-Isolo","Shomolu","Surulere"],
        "Nasarawa":["Akwanga","Awe","Doma","Karu","Keana","Keffi","Kokona","Lafia","Nasarawa","Nasarawa Egon","Obi","Toto","Wamba"],
        "Niger":["Agaie","Agwara","Bida","Borgu","Bosso","Chanchaga","Edatti","Gbako","Gurara","Katcha","Kontagora","Lapai","Lavun","Magama","Mariga","Mashegu","Mokwa","Moya","Paikoro","Rafi","Rijau","Shiroro","Suleja","Tafa","Wushishi"],
        "Ogun":["Abeokuta North","Abeokuta South","Ado-Odo/Ota","Ewekoro","Ifo","Ijebu East","Ijebu North","Ijebu North East","Ijebu Ode","Ikenne","Imeko Afon","Ipokia","Obafemi Owode","Odeda","Odogbolu","Ogun Waterside","Remo North","Sagamu","Yewa North","Yewa South"],
        "Ondo":["Akoko North-East","Akoko North-West","Akoko South-East","Akoko South-West","Akure North","Akure South","Ese Odo","Idanre","Ifedore","Ilaje","Ile Oluji/Okeigbo","Irele","Odigbo","Okitipupa","Ondo East","Ondo West","Ose","Owo"],
        "Osun":["Aiyedade","Aiyedire","Atakunmosa East","Atakunmosa West","Boluwaduro","Boripe","Ede North","Ede South","Egbedore","Ejigbo","Ife Central","Ife East","Ife North","Ife South","Ifedayo","Ifelodun","Ila","Ilesa East","Ilesa West","Irepodun","Irewole","Isokan","Iwo","Obokun","Odo Otin","Ola Oluwa","Olorunda","Oriade","Orolu","Osogbo"],
        "Oyo":["Afijio","Akinyele","Atiba","Atisbo","Egbeda","Ibadan North","Ibadan North-East","Ibadan North-West","Ibadan South-East","Ibadan South-West","Ibarapa Central","Ibarapa East","Ibarapa North","Ido","Irepo","Iseyin","Itesiwaju","Iwajowa","Kajola","Lagelu","Ogbomosho North","Ogbomosho South","Ogo Oluwa","Olorunsogo","Oluyole","Ona Ara","Orelope","Ori Ire","Oyo East","Oyo West","Saki East","Saki West","Surulere"],
        "Plateau":["Barkin Ladi","Bassa","Bokkos","Jos East","Jos North","Jos South","Kanam","Kanke","Langtang North","Langtang South","Mangu","Mikang","Pankshin","Qua'an Pan","Riyom","Shendam","Wase"],
        "Rivers":["Abua/Odual","Ahoada East","Ahoada West","Akuku-Toru","Andoni","Asari-Toru","Bonny","Degema","Eleme","Emohua","Etche","Gokana","Ikwerre","Khana","Obio/Akpor","Ogba/Egbema/Ndoni","Ogu/Bolo","Okrika","Omuma","Opobo/Nkoro","Oyigbo","Port Harcourt","Tai"],
        "Sokoto":["Binji","Bodinga","Dange Shuni","Gada","Goronyo","Gudu","Gwadabawa","Illela","Isa","Kebbe","Kware","Rabah","Sabon Birni","Shagari","Silame","Sokoto North","Sokoto South","Tambuwal","Tangaza","Tureta","Wamako","Wurno","Yabo"],
        "Taraba":["Ardo Kola","Bali","Donga","Gashaka","Gassol","Ibi","Jalingo","Karim Lamido","Kumi","Lau","Sardauna","Takum","Ussa","Wukari","Yorro","Zing"],
        "Yobe":["Bade","Bursari","Damaturu","Fika","Fune","Geidam","Gujba","Gulani","Jakusko","Karasuwa","Machina","Nangere","Nguru","Potiskum","Tarmuwa","Yunusari","Yusufari"],
        "Zamfara":["Anka","Bakura","Birnin Magaji/Kiyaw","Bukkuyum","Bungudu","Gummi","Gusau","Kaura Namoda","Maradun","Maru","Shinkafi","Talata Mafara","Tsafe","Zurmi"]
    };

    // ── State → LGA dependency ──
    function bindStateLGA(stateSelect, lgaSelect) {
        $(stateSelect).on('change', function() {
            var state = $(this).val();
            var $lga = $(lgaSelect);
            if (state && nigeriaLGAs[state]) {
                var html = '<option value="">All LGAs</option>';
                nigeriaLGAs[state].forEach(function(l) { html += '<option value="'+l+'">'+l+'</option>'; });
                $lga.html(html).prop('disabled', false);
            } else {
                $lga.html('<option value="">Select State First</option>').prop('disabled', true);
            }
        });
    }
    bindStateLGA('#filterState', '#filterLGA');
    bindStateLGA('.export-state-select', '.export-lga-select');

    // ── Server-side DataTable ──
    var table = $('#applicantsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "searching": false,
        "ajax": {
            "url": "/recruitment/data",
            "type": "GET",
            "data": function(d) {
                // Override DataTables default search - use our filter form instead
                delete d.search;
                delete d.order;
                delete d.columns;
                // Add filter parameters
                d.search = $('#filterForm input[name="search"]').val() || '';
                d.status = $('#filterForm select[name="status"]').val() || '';
                d.department = $('#filterForm select[name="department"]').val() || '';
                d.post = $('#filterForm select[name="post"]').val() || '';
                d.state = $('#filterForm select[name="state"]').val() || '';
                d.lga = $('#filterForm select[name="lga"]').val() || '';
                d.gender = $('#filterForm select[name="gender"]').val() || '';
                d.staff_type = $('#filterForm select[name="staff_type"]').val() || '';
            },
            "error": function(xhr, error, thrown) {
                console.error('DataTables AJAX error:', error, thrown);
            }
        },
        "pageLength": 100,
        "lengthMenu": [[50, 100, 200, -1], [50, 100, 200, "All"]],
        "ordering": false,
        "responsive": true,
        "dom": '<"d-flex justify-content-between"l<"small text-muted"i>>rt<"d-flex justify-content-between"ip>',
        "language": { 
            "processing": "<i class='fas fa-spinner fa-spin'></i> Loading...",
            "emptyTable": "No applicants found",
            "zeroRecords": "No matching records found"
        },
        "columns": [
            { "data": "row_number", "orderable": false },
            { "data": "applicant" },
            { "data": "contact" },
            { "data": "position" },
            { "data": "location" },
            { "data": "gender" },
            { "data": "status" },
            { "data": "actions", "orderable": false }
        ]
    });

    // ── Apply filters via AJAX ──
    function applyFilters() {
        table.ajax.reload();
    }

    // Filter form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        applyFilters();
    });

    $('#applyFilters').on('click', applyFilters);
    
    // Auto-apply filters on change
    $('#filterForm input, #filterForm select').on('change', function() {
        // Don't auto-apply on search input to avoid too many requests
        if ($(this).attr('name') !== 'search') {
            applyFilters();
        }
    });
    
    // Apply search on Enter key
    $('#filterForm input[name="search"]').on('keyup', function(e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });

    $('#resetFilters').on('click', function() {
        $('#filterForm')[0].reset();
        $('#filterLGA').prop('disabled', true).html('<option value="">Select State First</option>');
        table.ajax.reload();
    });

    // ── Export form ──
    $('#exportForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var fmt = $('input[name="export_format"]:checked').val();
        var url = fmt === 'pdf' ? '/recruitment/export/pdf' : '/recruitment/export/excel';
        var btn = $('#exportBtn');
        var orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> Exporting...').prop('disabled', true);

        $.ajax({
            url: url, type: 'POST', data: formData, processData: false, contentType: false,
            xhr: function() { var x = new XMLHttpRequest(); x.responseType = 'blob'; return x; },
            success: function(data, s, xhr) {
                var fn = ''; var d = xhr.getResponseHeader('Content-Disposition');
                if (d) { var m = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(d); if (m) fn = m[1].replace(/['"]/g,''); }
                var blob = new Blob([data], {type: xhr.getResponseHeader('Content-Type')});
                var a = document.createElement('a'); a.href = URL.createObjectURL(blob);
                a.download = fn || 'applicants_export.' + (fmt==='pdf'?'pdf':'csv');
                document.body.appendChild(a); a.click(); URL.revokeObjectURL(a.href); a.remove();
                $('#exportModal').modal('hide'); btn.html(orig).prop('disabled', false);
            },
            error: function() { btn.html(orig).prop('disabled', false); alert('Export failed.'); }
        });
    });
});
</script>
