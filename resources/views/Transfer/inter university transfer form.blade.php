<div class="main-body">
    <div class="page-wrapper">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Application for Transfer to UNIMAID</h5>
                        </div>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/dash"><i class="feather icon-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="/inter-university-transfer">Transfer</a></li>
                            <li class="breadcrumb-item">Form</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <form method="POST" action="{{ route('inter-transfer.store') }}" enctype="multipart/form-data">@csrf
                    <input type="hidden" name="transfer_type"
                        value="{{ $invoice->amount >= 200000 ? 'abroad' : 'within_nigeria' }}">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">PART I: PERSONAL DATA</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4"><label><strong>First Name *</strong></label><input type="text"
                                        class="form-control text-uppercase" name="first_name"
                                        value="{{ old('first_name', $application->first_name ?? '') }}" required></div>
                                <div class="col-md-4"><label><strong>Middle Name</strong></label><input type="text"
                                        class="form-control text-uppercase" name="middle_name"
                                        value="{{ old('middle_name', $application->middle_name ?? '') }}"></div>
                                <div class="col-md-4"><label><strong>Surname *</strong></label><input type="text"
                                        class="form-control text-uppercase" name="surname"
                                        value="{{ old('surname', $application->surname ?? '') }}" required></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4"><label><strong>Date of Birth *</strong></label><input
                                        type="date" class="form-control" name="date_of_birth"
                                        value="{{ old('date_of_birth', $application->date_of_birth ?? '') }}" required>
                                </div>
                                <div class="col-md-4"><label><strong>Nationality *</strong></label><input type="text"
                                        class="form-control text-uppercase" name="nationality"
                                        value="{{ old('nationality', $application->nationality ?? 'NIGERIAN') }}"
                                        required></div>
                                <div class="col-md-4"><label><strong>Phone</strong></label><input type="text"
                                        class="form-control" name="phone"
                                        value="{{ old('phone', $application->phone ?? '') }}"></div>
                            </div>
                            <div class="form-group mb-3"><label><strong>Postal Address *</strong></label>
                                <textarea class="form-control text-uppercase" name="postal_address" rows="2" required>{{ old('postal_address', $application->postal_address ?? '') }}</textarea>
                            </div>
                            <hr>
                            <h6 class="text-primary mb-3"><strong>JAMB Information</strong></h6>
                            <div class="row mb-3">
                                <div class="col-md-4"><label><strong>Admission Type *</strong></label>
                                    <select class="form-control" name="admission_type" id="admissionType" required>
                                        <option value="">Select</option>
                                        <option value="UTME" {{ old('admission_type', $application->admission_type ?? '') == 'UTME' ? 'selected' : '' }}>UTME</option>
                                        <option value="DE" {{ old('admission_type', $application->admission_type ?? '') == 'DE' ? 'selected' : '' }}>Direct Entry (DE)</option>
                                    </select>
                                </div>
                                <div class="col-md-4" id="jambScoreField"><label><strong>JAMB Score *</strong></label>
                                    <input type="number" class="form-control" name="jamb_score" min="0" max="400"
                                        value="{{ old('jamb_score', $application->jamb_score ?? '') }}"
                                        placeholder="Enter UTME score (0-400)">
                                </div>
                                <div class="col-md-4"><label><strong>JAMB Result/DE Slip *</strong></label>
                                    <input type="file" class="form-control" name="jamb_result_file" accept="image/*,.pdf"
                                        @if($application->jamb_result_file ?? false) disabled @endif>
                                    @if($application->jamb_result_file ?? false)
                                        <small class="text-success">File already uploaded</small>
                                    @else
                                        <small class="text-muted">Upload JAMB result (UTME) or DE slip (PDF/Image)</small>
                                    @endif
                                </div>
                            </div>
                            <hr>
                            <h6 class="text-primary mb-3"><strong>Present Institution</strong></h6>
                            <div class="row mb-3">
                                <div class="col-md-4"><label><strong>Institution *</strong></label><input type="text"
                                        class="form-control text-uppercase" name="present_institution"
                                        value="{{ old('present_institution', $application->present_institution ?? '') }}"
                                        required></div>
                                <div class="col-md-4"><label><strong>Reg. Number *</strong></label><input type="text"
                                        class="form-control text-uppercase" name="registration_number"
                                        value="{{ old('registration_number', $application->registration_number ?? '') }}"
                                        required></div>
                                <div class="col-md-4"><label><strong>Year of Study *</strong></label>
                                    <select class="form-control" name="year_of_study" required>
                                        <option value="">Select</option>
                                        @for ($i = 1; $i <= 6; $i++)
                                            <option value="Year {{ $i }}"
                                                {{ old('year_of_study', $application->year_of_study ?? '') == "Year $i" ? 'selected' : '' }}>
                                                Year {{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <hr>
                            <h6 class="text-primary mb-3"><strong>Course to Transfer To (at UNIMAID)</strong></h6>
                            <div class="row mb-3">
                                <div class="col-md-4"><label><strong>Faculty *</strong></label>
                                    <select class="form-control" name="new_faculty" id="newFaculty" required>
                                        <option value="">Select Faculty</option>
                                        @foreach ($faculties as $f)
                                            <option value="{{ $f->code }}"
                                                {{ old('new_faculty', $application->new_faculty ?? '') == $f->code ? 'selected' : '' }}>
                                                {{ $f->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4"><label><strong>Department *</strong></label><select
                                        class="form-control" name="new_department" id="newDepartment" required>
                                        <option value="">Select</option>
                                    </select></div>
                                <div class="col-md-4"><label><strong>Programme *</strong></label><select
                                        class="form-control" name="new_program" id="newProgram" required>
                                        <option value="">Select</option>
                                    </select></div>
                            </div>
                            <div class="form-group mb-3"><label><strong>Reason(s) for Transfer *</strong></label>
                                <textarea class="form-control" name="reason_for_transfer" rows="3" required minlength="20">{{ old('reason_for_transfer', $application->reason_for_transfer ?? '') }}</textarea>
                            </div>
                            <hr>
                            <h6 class="text-primary mb-3"><strong>Educational Qualifications</strong></h6>
                            <div class="row mb-3">
                                <div class="col-md-4"><label>WASC/SSCE</label><input type="text"
                                        class="form-control" name="qualifications_wasc"
                                        value="{{ old('qualifications_wasc', $application->qualifications_wasc ?? '') }}"
                                        placeholder="Grades obtained"></div>
                                <div class="col-md-4"><label>TC II</label><input type="text" class="form-control"
                                        name="qualifications_tc2"
                                        value="{{ old('qualifications_tc2', $application->qualifications_tc2 ?? '') }}">
                                </div>
                                <div class="col-md-4"><label>GCE/HSC A/L</label><input type="text"
                                        class="form-control" name="qualifications_gce"
                                        value="{{ old('qualifications_gce', $application->qualifications_gce ?? '') }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4"><label>IJMB</label><input type="text" class="form-control"
                                        name="qualifications_ijmb"
                                        value="{{ old('qualifications_ijmb', $application->qualifications_ijmb ?? '') }}">
                                </div>
                                <div class="col-md-4"><label>NCE</label><input type="text" class="form-control"
                                        name="qualifications_nce"
                                        value="{{ old('qualifications_nce', $application->qualifications_nce ?? '') }}">
                                </div>
                                <div class="col-md-4"><label>Others</label><input type="text" class="form-control"
                                        name="qualifications_others"
                                        value="{{ old('qualifications_others', $application->qualifications_others ?? '') }}">
                                </div>
                            </div>
                            <div class="alert alert-info"><small>Please attach photocopies of the
                                    statements/certificates of examinations listed above when uploading
                                    documents.</small></div>
                            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save mr-2"></i>
                                Save & Continue</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        console.log('Transfer form JS loaded');

        // Handle admission type change
        $('#admissionType').on('change', function() {
            var admissionType = $(this).val();
            if (admissionType === 'DE') {
                $('#jambScoreField').hide();
                $('input[name="jamb_score"]').prop('required', false).val('');
            } else {
                $('#jambScoreField').show();
                $('input[name="jamb_score"]').prop('required', true);
            }
        });

        // Initialize on page load
        $('#admissionType').trigger('change');

        $('#newFaculty').on('change', function() {
            var faculty = $(this).val();
            console.log('Faculty changed:', faculty);
            $('#newDepartment').html('<option value="">Loading...</option>');
            $('#newProgram').html('<option value="">Select</option>');
            if (!faculty) return;
            $.ajax({
                url: '/inter-university-transfer/get-departments',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    faculty: faculty
                },
                success: function(data) {
                    console.log('Departments loaded:', data.length);
                    var o = '<option value="">Select Department</option>';
                    data.forEach(function(d) {
                        o += '<option value="' + d.code + '">' + d.title +
                            '</option>';
                    });
                    $('#newDepartment').html(o);
                },
                error: function(xhr) {
                    console.error('Dept AJAX error:', xhr.status, xhr.responseText);
                    $('#newDepartment').html('<option value="">Error loading</option>');
                }
            });
        });

        $('#newDepartment').on('change', function() {
            var dept = $(this).val();
            console.log('Department changed:', dept);
            $('#newProgram').html('<option value="">Loading...</option>');
            if (!dept) return;
            $.ajax({
                url: '/inter-university-transfer/get-programs',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    department: dept
                },
                success: function(data) {
                    console.log('Programs loaded:', data.length);
                    var o = '<option value="">Select Programme</option>';
                    data.forEach(function(d) {
                        o += '<option value="' + d.code + '">' + d.title +
                            '</option>';
                    });
                    $('#newProgram').html(o);
                },
                error: function(xhr) {
                    console.error('Prog AJAX error:', xhr.status, xhr.responseText);
                    $('#newProgram').html('<option value="">Error loading</option>');
                }
            });
        });

        @if (old('new_faculty', $application->new_faculty ?? ''))
            $.post('/inter-university-transfer/get-departments', {
                _token: '{{ csrf_token() }}',
                faculty: '{{ old('new_faculty', $application->new_faculty ?? '') }}'
            }, function(data) {
                var o = '<option value="">Select</option>';
                data.forEach(function(d) {
                    o += '<option value="' + d.code + '" ' + (d.code ==
                        '{{ old('new_department', $application->new_department ?? '') }}' ?
                        'selected' : '') + '>' + d.title + '</option>';
                });
                $('#newDepartment').html(o);
                if ('{{ old('new_department', $application->new_department ?? '') }}') {
                    $.post('/inter-university-transfer/get-programs', {
                        _token: '{{ csrf_token() }}',
                        department: '{{ old('new_department', $application->new_department ?? '') }}'
                    }, function(data2) {
                        var o2 = '<option value="">Select</option>';
                        data2.forEach(function(d) {
                            o2 += '<option value="' + d.code + '" ' + (d.code ==
                                '{{ old('new_program', $application->new_program ?? '') }}' ?
                                'selected' : '') + '>' + d.title + '</option>';
                        });
                        $('#newProgram').html(o2);
                    });
                }
            });
        @endif

        $('form').on('submit', function(e) {
            console.log('Form submitting...');
            var dept = $('#newDepartment').val();
            var prog = $('#newProgram').val();
            console.log('Dept:', dept, 'Prog:', prog);
            if (!dept || !prog) {
                e.preventDefault();
                alert('Please select Faculty, Department, and Programme before submitting.');
                return false;
            }
        });
    });
</script>
