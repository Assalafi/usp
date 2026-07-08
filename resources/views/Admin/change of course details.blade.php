<style>
.approval-timeline{position:relative;padding-left:30px;margin:0}
.approval-timeline::before{content:'';position:absolute;left:14px;top:0;bottom:0;width:3px;background:#e9ecef;border-radius:2px}
.approval-step{position:relative;margin-bottom:24px}
.approval-step:last-child{margin-bottom:0}
.approval-step .step-icon{position:absolute;left:-30px;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;z-index:1;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.15)}
.step-icon.pending{background:#f8f9fa;color:#6c757d;border-color:#dee2e6}
.step-icon.active{background:#0d6efd;color:#fff;animation:pulse 2s infinite}
.step-icon.done{background:#198754;color:#fff}
.step-icon.rejected{background:#dc3545;color:#fff}
.step-icon.skipped{background:#adb5bd;color:#fff}
@keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(13,110,253,.4)}70%{box-shadow:0 0 0 10px rgba(13,110,253,0)}}
.approval-card{border:none;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);transition:all .3s ease;overflow:hidden}
.approval-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.1);transform:translateY(-1px)}
.approval-card .card-header{border:none;padding:14px 20px;font-weight:600}
.approval-card .card-body{padding:20px}
.approval-card.active-step{border-left:4px solid #0d6efd}
.approval-card.done-step{border-left:4px solid #198754}
.approval-card.skipped-step{border-left:4px solid #adb5bd;opacity:.7}
.info-card{border:none;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06)}
.info-card .card-header{border:none;border-radius:12px 12px 0 0;padding:16px 20px}
.info-card .card-header.bg-dark{background:#212529!important;color:#fff!important}
.info-card .card-header.bg-primary{background:#0d6efd!important;color:#fff!important}
.info-card .card-header.bg-info{background:#0dcaf0!important;color:#fff!important}
.info-card .card-body{padding:20px}
.status-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-weight:600;font-size:13px}
.officer-info th{font-weight:500;color:#6c757d;width:30%;font-size:13px;padding:8px 12px!important}
.officer-info td{font-size:13px;padding:8px 12px!important}
.form-control,.form-select{border-radius:8px;border:1.5px solid #dee2e6;padding:10px 14px;transition:border-color .2s}
.form-control:focus,.form-select:focus{border-color:#0d6efd;box-shadow:0 0 0 3px rgba(13,110,253,.1)}
.btn-approve{background:linear-gradient(135deg,#198754,#20c997);color:#fff;border:none;border-radius:8px;padding:10px 24px;font-weight:600;transition:all .2s}
.btn-approve:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(25,135,84,.3);color:#fff}
.btn-reject{background:linear-gradient(135deg,#dc3545,#e85d04);color:#fff;border:none;border-radius:8px;padding:10px 24px;font-weight:600}
.btn-provost{background:linear-gradient(135deg,#6f42c1,#9461fb);color:#fff;border:none;border-radius:8px;padding:10px 24px;font-weight:600}
.btn-provost:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(111,66,193,.3);color:#fff}
.transfer-badge{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:8px;font-weight:600;font-size:13px}
.bg-purple{background:#6f42c1!important}
</style>
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Change of Course Application Details</h5>
                        </div>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/"><i class="feather icon-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('change-of-course.admin') }}">Change of
                                    Course</a></li>
                            <li class="breadcrumb-item"><a href="#!">View Details</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                @php
                // Helper function to check if program is MBBS/DBS
                $isMedicalOrDentalProgram = function($programCode) {
                    $medicalPrograms = ['MBBS', 'DBS'];
                    return in_array($programCode, $medicalPrograms);
                };
                
                $isNewProgramMedical = $isMedicalOrDentalProgram($application->new_program);
                $isCurrentProgramMedical = $isMedicalOrDentalProgram($application->current_program);
                @endphp
                
                <!-- Application Information -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">PART A: STUDENT'S PERSONAL DATA</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Application No:</strong></p>
                                <h6 class="text-primary">{{ $application->application_no }}</h6>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Application Date:</strong></p>
                                <h6>{{ date('d M Y', strtotime($application->student_applied_date)) }}</h6>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <tr>
                                <th width="5%">1.</th>
                                <th width="35%">Name of Student</th>
                                <td>{{ $application->student_name }}</td>
                            </tr>
                            <tr>
                                <th>2.</th>
                                <th>Identity Number</th>
                                <td>{{ $application->username }}</td>
                            </tr>
                            <tr>
                                <th>3.</th>
                                <th>Present Department</th>
                                <td>{{ $currentDepartmentTitle }} ({{ $application->current_department }})</td>
                            </tr>
                            <tr>
                                <th>4.</th>
                                <th>Faculty of</th>
                                <td>{{ $currentFacultyTitle }} ({{ $application->current_faculty }})</td>
                            </tr>
                            <tr>
                                <th>5.</th>
                                <th>Department to transfer to</th>
                                <td class="text-primary"><strong>{{ $newDepartmentTitle }}
                                        ({{ $application->new_department }})</strong></td>
                            </tr>
                            <tr>
                                <th>6.</th>
                                <th>Faculty</th>
                                <td class="text-primary"><strong>{{ $newFacultyTitle }}
                                        ({{ $application->new_faculty }})</strong></td>
                            </tr>
                            <tr>
                                <th>7.</th>
                                <th>Reason(s) for changing course</th>
                                <td>{{ $application->reason_for_change }}</td>
                            </tr>
                        </table>

                        <!-- JAMB Information -->
                        <h6 class="text-primary mt-3"><strong>JAMB Information</strong></h6>
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th width="30%">Admission Type</th>
                                <td><span class="badge bg-info">{{ $application->admission_type ?? 'UTME' }}</span></td>
                            </tr>
                            @if($application->admission_type == 'UTME')
                                <tr>
                                    <th>JAMB Score</th>
                                    <td><strong>{{ $application->jamb_score ?? '-' }}</strong> / 400</td>
                                </tr>
                            @endif
                            <tr>
                                <th>JAMB Result/DE Slip</th>
                                <td>
                                    @if($application->jamb_result_file)
                                        <a href="{{ asset('uploads/transfer_documents/' . $application->jamb_result_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file-alt"></i> View JAMB Result
                                        </a>
                                    @else
                                        <span class="badge bg-warning">Not Uploaded</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                @php
                    $newFaculty = DB::table('faculty')->where('code', $application->new_faculty)->first();
                    $currentFaculty = DB::table('faculty')->where('code', $application->current_faculty)->first();
                    $showProvost = $isNewProgramMedical || $isCurrentProgramMedical || ($newFaculty && $newFaculty->college == 1) || ($currentFaculty && $currentFaculty->college == 1);
                    
                    // Step status helpers
                    $hodDone = $application->new_hod_willing != 'Pending';
                    $deanDone = $application->new_dean_recommendation != 'Pending';
                    $provostDone = $application->provost_recommendation != 'Pending';
                    $cHodDone = $application->current_hod_willing != 'Pending';
                    $cDeanDone = $application->current_dean_recommendation != 'Pending';
                    $regDone = $application->registrar_decision != 'Pending';
                    $vcDone = $application->vc_decision != 'Pending';
                @endphp

                <!-- Approval Workflow -->
                <div class="card info-card mb-4">
                    <div class="card-header bg-dark text-white d-flex align-items-center">
                        <i class="fas fa-tasks me-2"></i>
                        <h5 style="color: #fff" class="mb-0">Approval Workflow</h5>
                        @if($isNewProgramMedical || $isCurrentProgramMedical)
                        <span class="badge bg-warning text-dark ms-auto"><i class="fas fa-stethoscope me-1"></i>MBBS/DBS Special Workflow</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="approval-timeline">

                        <!-- ====== STEP 1: New HOD ====== -->
                        <div class="approval-step">
                            @if($isNewProgramMedical)
                            <div class="step-icon skipped"><i class="fas fa-forward"></i></div>
                            <div class="approval-card card skipped-step">
                                <div class="card-header bg-light"><i class="fas fa-user-tie me-2 text-muted"></i>New HOD's Recommendation <span class="badge bg-secondary ms-2">Handled by Provost</span></div>
                                <div class="card-body py-2"><small class="text-muted"><i class="fas fa-info-circle me-1"></i>MBBS/DBS program - this step is handled by the Provost.</small></div>
                            </div>
                            @else
                            <div class="step-icon {{ $application->status == 'Awaiting New HOD' ? 'active' : ($hodDone ? ($application->new_hod_willing=='Yes' ? 'done' : 'rejected') : 'pending') }}">
                                <i class="fas {{ $hodDone ? ($application->new_hod_willing=='Yes' ? 'fa-check' : 'fa-times') : ($application->status == 'Awaiting New HOD' ? 'fa-spinner fa-spin' : 'fa-clock') }}"></i>
                            </div>
                            <div class="approval-card card {{ $application->status == 'Awaiting New HOD' ? 'active-step' : ($hodDone ? 'done-step' : '') }}">
                                <div class="card-header {{ $application->status == 'Awaiting New HOD' ? 'bg-primary text-white' : ($hodDone ? 'bg-light' : 'bg-light') }}">
                                    <i class="fas fa-user-tie me-2"></i>New HOD's Recommendation
                                    @if($hodDone)<span class="status-pill {{ $application->new_hod_willing=='Yes' ? 'bg-success text-white' : 'bg-danger text-white' }} ms-2" style="font-size:11px"><i class="fas {{ $application->new_hod_willing=='Yes' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>{{ $application->new_hod_willing=='Yes' ? 'Accepted' : 'Rejected' }}</span>@endif
                                </div>
                                <div class="card-body">
                                    @if ($application->new_hod_willing == 'Pending' && $application->status == 'Awaiting New HOD' && ($canApproveNewHOD ?? false))
                                    <form id="newHodForm">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">8. Are you willing to accept the candidate?</label>
                                                <select class="form-control" name="decision" required>
                                                    <option value="">Select Decision</option>
                                                    <option value="Yes">Yes - Accept</option>
                                                    <option value="No">No - Reject</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">9. Recommended Level</label>
                                                <select class="form-control" name="recommended_level" required>
                                                    <option value="">Select Level</option>
                                                    <option value="100">100 Level</option>
                                                    <option value="200">200 Level</option>
                                                    <option value="300">300 Level</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">10. Remarks</label>
                                            <textarea class="form-control" name="remarks" rows="2" placeholder="Optional remarks..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-approve"><i class="fas fa-paper-plane me-2"></i>Submit Recommendation</button>
                                    </form>
                                    @elseif($hodDone)
                                    <table class="table table-sm officer-info mb-0">
                                        @if($application->new_hod_recommended_level)<tr><th>Recommended Level</th><td><strong>{{ $application->new_hod_recommended_level }} Level</strong></td></tr>@endif
                                        @if($application->new_hod_remarks)<tr><th>Remarks</th><td>{{ $application->new_hod_remarks }}</td></tr>@endif
                                        <tr><th>Officer</th><td>{{ $application->new_hod_name }}</td></tr>
                                        <tr><th>Date</th><td>{{ $application->new_hod_date ? date('d M Y', strtotime($application->new_hod_date)) : '-' }}</td></tr>
                                    </table>
                                    @else
                                    <p class="text-muted mb-0"><i class="fas fa-hourglass-half me-1"></i>Awaiting action...</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- ====== STEP 2: New Dean ====== -->
                        <div class="approval-step">
                            @if($isNewProgramMedical)
                            <div class="step-icon skipped"><i class="fas fa-forward"></i></div>
                            <div class="approval-card card skipped-step">
                                <div class="card-header bg-light"><i class="fas fa-user-graduate me-2 text-muted"></i>New Dean's Recommendation <span class="badge bg-secondary ms-2">Handled by Provost</span></div>
                                <div class="card-body py-2"><small class="text-muted"><i class="fas fa-info-circle me-1"></i>MBBS/DBS program - this step is handled by the Provost.</small></div>
                            </div>
                            @else
                            <div class="step-icon {{ $application->status == 'Awaiting New Dean' ? 'active' : ($deanDone ? ($application->new_dean_recommendation=='Yes' ? 'done' : 'rejected') : 'pending') }}">
                                <i class="fas {{ $deanDone ? ($application->new_dean_recommendation=='Yes' ? 'fa-check' : 'fa-times') : ($application->status == 'Awaiting New Dean' ? 'fa-spinner fa-spin' : 'fa-clock') }}"></i>
                            </div>
                            <div class="approval-card card {{ $application->status == 'Awaiting New Dean' ? 'active-step' : ($deanDone ? 'done-step' : '') }}">
                                <div class="card-header {{ $application->status == 'Awaiting New Dean' ? 'bg-primary text-white' : 'bg-light' }}">
                                    <i class="fas fa-user-graduate me-2"></i>New Dean's Recommendation
                                    @if($deanDone)<span class="status-pill {{ $application->new_dean_recommendation=='Yes' ? 'bg-success text-white' : 'bg-danger text-white' }} ms-2" style="font-size:11px"><i class="fas {{ $application->new_dean_recommendation=='Yes' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>{{ $application->new_dean_recommendation=='Yes' ? 'Approved' : 'Rejected' }}</span>@endif
                                </div>
                                <div class="card-body">
                                    @if ($application->new_dean_recommendation == 'Pending' && $application->status == 'Awaiting New Dean' && ($canApproveNewDean ?? false))
                                    <form id="newDeanForm">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">12. Recommendation</label>
                                                <select class="form-control" name="decision" required>
                                                    <option value="">Select Decision</option>
                                                    <option value="Yes">Yes - Approve</option>
                                                    <option value="No">No - Reject</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">13. Remarks</label>
                                            <textarea class="form-control" name="remarks" rows="2" placeholder="Optional remarks..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-approve"><i class="fas fa-paper-plane me-2"></i>Submit Recommendation</button>
                                    </form>
                                    @elseif($deanDone)
                                    <table class="table table-sm officer-info mb-0">
                                        @if($application->new_dean_remarks)<tr><th>Remarks</th><td>{{ $application->new_dean_remarks }}</td></tr>@endif
                                        <tr><th>Officer</th><td>{{ $application->new_dean_name }}</td></tr>
                                        <tr><th>Date</th><td>{{ $application->new_dean_date ? date('d M Y', strtotime($application->new_dean_date)) : '-' }}</td></tr>
                                    </table>
                                    @else
                                    <p class="text-muted mb-0"><i class="fas fa-hourglass-half me-1"></i>Awaiting action...</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- ====== STEP 3: Provost ====== -->
                        @if($showProvost)
                        <div class="approval-step">
                            <div class="step-icon {{ $application->status == 'Awaiting Provost' ? 'active' : ($provostDone ? ($application->provost_recommendation=='Yes' ? 'done' : 'rejected') : 'pending') }}">
                                <i class="fas {{ $provostDone ? ($application->provost_recommendation=='Yes' ? 'fa-check' : 'fa-times') : ($application->status == 'Awaiting Provost' ? 'fa-spinner fa-spin' : 'fa-clock') }}"></i>
                            </div>
                            <div class="approval-card card {{ $application->status == 'Awaiting Provost' ? 'active-step' : ($provostDone ? 'done-step' : '') }}" style="border-left-color:#6f42c1!important">
                                <div class="card-header {{ $application->status == 'Awaiting Provost' ? 'bg-purple text-white' : 'bg-light' }}">
                                    <i class="fas fa-university me-2"></i>Provost's Recommendation
                                    @if($isNewProgramMedical || $isCurrentProgramMedical)<span class="badge bg-warning text-dark ms-2" style="font-size:10px"><i class="fas fa-stethoscope me-1"></i>Combined Decision</span>@endif
                                    @if($provostDone)<span class="status-pill {{ $application->provost_recommendation=='Yes' ? 'bg-success text-white' : 'bg-danger text-white' }} ms-2" style="font-size:11px"><i class="fas {{ $application->provost_recommendation=='Yes' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>{{ $application->provost_recommendation=='Yes' ? 'Approved' : 'Rejected' }}</span>@endif
                                </div>
                                <div class="card-body">
                                    @if ($application->provost_recommendation == 'Pending' && $application->status == 'Awaiting Provost' && ($canApproveProvost ?? false))
                                    <form id="provostForm">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">18. Recommendation of Provost</label>
                                                <select class="form-control" name="decision" required>
                                                    <option value="">Select Decision</option>
                                                    <option value="Yes">Yes - Approve</option>
                                                    <option value="No">No - Reject</option>
                                                </select>
                                            </div>
                                            @if($isNewProgramMedical)
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Recommended Level</label>
                                                <select class="form-control" name="recommended_level">
                                                    <option value="">Select Level</option>
                                                    <option value="100">100 Level</option>
                                                    <option value="200">200 Level</option>
                                                    <option value="300">300 Level</option>
                                                </select>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Remarks</label>
                                            <textarea class="form-control" name="remarks" rows="2" placeholder="Optional remarks..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-provost"><i class="fas fa-paper-plane me-2"></i>Submit Recommendation</button>
                                    </form>
                                    @elseif($provostDone)
                                    <table class="table table-sm officer-info mb-0">
                                        @if($application->provost_remarks)<tr><th>Remarks</th><td>{{ $application->provost_remarks }}</td></tr>@endif
                                        <tr><th>Officer</th><td>{{ $application->provost_name }}</td></tr>
                                        <tr><th>Date</th><td>{{ $application->provost_date ? date('d M Y', strtotime($application->provost_date)) : '-' }}</td></tr>
                                    </table>
                                    @else
                                    <p class="text-muted mb-0"><i class="fas fa-hourglass-half me-1"></i>Awaiting action...</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- ====== STEP 4: Current HOD ====== -->
                        <div class="approval-step">
                            @if($isCurrentProgramMedical)
                            <div class="step-icon skipped"><i class="fas fa-forward"></i></div>
                            <div class="approval-card card skipped-step">
                                <div class="card-header bg-light"><i class="fas fa-user-tie me-2 text-muted"></i>Current HOD's Release <span class="badge bg-secondary ms-2">Handled by Provost</span></div>
                                <div class="card-body py-2"><small class="text-muted"><i class="fas fa-info-circle me-1"></i>MBBS/DBS program - this step is handled by the Provost.</small></div>
                            </div>
                            @else
                            <div class="step-icon {{ $application->status == 'Awaiting Current HOD' ? 'active' : ($cHodDone ? ($application->current_hod_willing=='Yes' ? 'done' : 'rejected') : 'pending') }}">
                                <i class="fas {{ $cHodDone ? ($application->current_hod_willing=='Yes' ? 'fa-check' : 'fa-times') : ($application->status == 'Awaiting Current HOD' ? 'fa-spinner fa-spin' : 'fa-clock') }}"></i>
                            </div>
                            <div class="approval-card card {{ $application->status == 'Awaiting Current HOD' ? 'active-step' : ($cHodDone ? 'done-step' : '') }}">
                                <div class="card-header {{ $application->status == 'Awaiting Current HOD' ? 'bg-warning' : 'bg-light' }}">
                                    <i class="fas fa-user-tie me-2"></i>Current HOD's Release
                                    @if($cHodDone)<span class="status-pill {{ $application->current_hod_willing=='Yes' ? 'bg-success text-white' : 'bg-danger text-white' }} ms-2" style="font-size:11px"><i class="fas {{ $application->current_hod_willing=='Yes' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>{{ $application->current_hod_willing=='Yes' ? 'Released' : 'Not Released' }}</span>@endif
                                </div>
                                <div class="card-body">
                                    @if ($application->current_hod_willing == 'Pending' && $application->status == 'Awaiting Current HOD' && ($canApproveCurrentHOD ?? false))
                                    <form id="currentHodForm">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Are you willing to release the student?</label>
                                                <select class="form-control" name="decision" required>
                                                    <option value="">Select Decision</option>
                                                    <option value="Yes">Yes - Willing to Release</option>
                                                    <option value="No">No - Not Willing</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Reason (if not willing)</label>
                                            <textarea class="form-control" name="reason" rows="2" placeholder="Provide reason..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-approve"><i class="fas fa-paper-plane me-2"></i>Submit Decision</button>
                                    </form>
                                    @elseif($cHodDone)
                                    <table class="table table-sm officer-info mb-0">
                                        @if($application->current_hod_reason)<tr><th>Reason</th><td>{{ $application->current_hod_reason }}</td></tr>@endif
                                        @if($application->current_hod_name)<tr><th>Officer</th><td>{{ $application->current_hod_name }}</td></tr>@endif
                                        @if($application->current_hod_date)<tr><th>Date</th><td>{{ date('d M Y', strtotime($application->current_hod_date)) }}</td></tr>@endif
                                    </table>
                                    @else
                                    <p class="text-muted mb-0"><i class="fas fa-hourglass-half me-1"></i>Awaiting action...</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- ====== STEP 5: Current Dean ====== -->
                        <div class="approval-step">
                            @if($isCurrentProgramMedical)
                            <div class="step-icon skipped"><i class="fas fa-forward"></i></div>
                            <div class="approval-card card skipped-step">
                                <div class="card-header bg-light"><i class="fas fa-user-graduate me-2 text-muted"></i>Current Dean's Recommendation <span class="badge bg-secondary ms-2">Handled by Provost</span></div>
                                <div class="card-body py-2"><small class="text-muted"><i class="fas fa-info-circle me-1"></i>MBBS/DBS program - this step is handled by the Provost.</small></div>
                            </div>
                            @else
                            <div class="step-icon {{ $application->status == 'Awaiting Current Dean' ? 'active' : ($cDeanDone ? ($application->current_dean_recommendation=='Yes' ? 'done' : 'rejected') : 'pending') }}">
                                <i class="fas {{ $cDeanDone ? ($application->current_dean_recommendation=='Yes' ? 'fa-check' : 'fa-times') : ($application->status == 'Awaiting Current Dean' ? 'fa-spinner fa-spin' : 'fa-clock') }}"></i>
                            </div>
                            <div class="approval-card card {{ $application->status == 'Awaiting Current Dean' ? 'active-step' : ($cDeanDone ? 'done-step' : '') }}">
                                <div class="card-header {{ $application->status == 'Awaiting Current Dean' ? 'bg-warning' : 'bg-light' }}">
                                    <i class="fas fa-user-graduate me-2"></i>Current Dean's Recommendation
                                    @if($cDeanDone)<span class="status-pill {{ $application->current_dean_recommendation=='Yes' ? 'bg-success text-white' : 'bg-danger text-white' }} ms-2" style="font-size:11px"><i class="fas {{ $application->current_dean_recommendation=='Yes' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>{{ $application->current_dean_recommendation=='Yes' ? 'Approved' : 'Rejected' }}</span>@endif
                                </div>
                                <div class="card-body">
                                    @if ($application->current_dean_recommendation == 'Pending' && $application->status == 'Awaiting Current Dean' && ($canApproveCurrentDean ?? false))
                                    <form id="currentDeanForm">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Recommendation</label>
                                                <select class="form-control" name="decision" required>
                                                    <option value="">Select Decision</option>
                                                    <option value="Yes">Yes - Approve Release</option>
                                                    <option value="No">No - Reject</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Remarks</label>
                                            <textarea class="form-control" name="remarks" rows="2" placeholder="Optional remarks..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-approve"><i class="fas fa-paper-plane me-2"></i>Submit Recommendation</button>
                                    </form>
                                    @elseif($cDeanDone)
                                    <table class="table table-sm officer-info mb-0">
                                        @if($application->current_dean_remarks)<tr><th>Remarks</th><td>{{ $application->current_dean_remarks }}</td></tr>@endif
                                        <tr><th>Officer</th><td>{{ $application->current_dean_name ?? '-' }}</td></tr>
                                        <tr><th>Date</th><td>{{ $application->current_dean_date ? date('d M Y', strtotime($application->current_dean_date)) : '-' }}</td></tr>
                                    </table>
                                    @else
                                    <p class="text-muted mb-0"><i class="fas fa-hourglass-half me-1"></i>Awaiting action...</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- ====== STEP 6: Registrar ====== -->
                        <div class="approval-step">
                            <div class="step-icon {{ $application->status == 'Awaiting Registrar' ? 'active' : ($regDone ? ($application->registrar_decision=='Approved' ? 'done' : 'rejected') : 'pending') }}">
                                <i class="fas {{ $regDone ? ($application->registrar_decision=='Approved' ? 'fa-check' : 'fa-times') : ($application->status == 'Awaiting Registrar' ? 'fa-spinner fa-spin' : 'fa-clock') }}"></i>
                            </div>
                            <div class="approval-card card {{ $application->status == 'Awaiting Registrar' ? 'active-step' : ($regDone ? 'done-step' : '') }}">
                                <div class="card-header {{ $application->status == 'Awaiting Registrar' ? 'bg-danger text-white' : 'bg-light' }}">
                                    <i class="fas fa-gavel me-2"></i>Registrar's Decision
                                    @if($regDone)<span class="status-pill {{ $application->registrar_decision=='Approved' ? 'bg-success text-white' : 'bg-danger text-white' }} ms-2" style="font-size:11px"><i class="fas {{ $application->registrar_decision=='Approved' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>{{ $application->registrar_decision }}</span>@endif
                                </div>
                                <div class="card-body">
                                    @if ($application->registrar_decision == 'Pending' && $application->status == 'Awaiting Registrar' && ($canApproveRegistrar ?? false))
                                    <form id="registrarForm">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">19. Decision</label>
                                                <select class="form-control" name="decision" id="registrarDecision" required>
                                                    <option value="">Select Final Decision</option>
                                                    <option value="Approved">Approved</option>
                                                    <option value="Rejected">Rejected</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6" id="levelSelectionDiv" style="display: none;">
                                                <label class="form-label fw-semibold">20. Approved Level</label>
                                                <select class="form-control" name="recommended_level" id="approvedLevel">
                                                    <option value="">Select Level</option>
                                                    <option value="100">100 Level</option>
                                                    <option value="200">200 Level</option>
                                                    <option value="300">300 Level</option>
                                                </select>
                                                <small class="text-muted">Registrar can override HOD's level recommendation</small>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Remarks</label>
                                            <textarea class="form-control" name="remarks" rows="2" placeholder="Optional remarks..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-approve"><i class="fas fa-gavel me-2"></i>Submit Decision</button>
                                    </form>
                                    @elseif($regDone)
                                    <table class="table table-sm officer-info mb-0">
                                        @if($application->registrar_decision == 'Approved' && $application->new_hod_recommended_level)<tr><th>Approved Level</th><td><strong>{{ $application->new_hod_recommended_level }} Level</strong></td></tr>@endif
                                        @if($application->registrar_remarks)<tr><th>Remarks</th><td>{{ $application->registrar_remarks }}</td></tr>@endif
                                        <tr><th>Officer</th><td>{{ $application->registrar_name ?? '-' }}</td></tr>
                                        <tr><th>Date</th><td>{{ $application->registrar_date ? date('d M Y', strtotime($application->registrar_date)) : '-' }}</td></tr>
                                    </table>
                                    @else
                                    <p class="text-muted mb-0"><i class="fas fa-hourglass-half me-1"></i>Awaiting action...</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- ====== STEP 7: VC ====== -->
                        <div class="approval-step">
                            <div class="step-icon {{ $application->status == 'Awaiting VC' ? 'active' : ($vcDone ? ($application->vc_decision=='Approved' ? 'done' : 'rejected') : 'pending') }}">
                                <i class="fas {{ $vcDone ? ($application->vc_decision=='Approved' ? 'fa-check' : 'fa-times') : ($application->status == 'Awaiting VC' ? 'fa-spinner fa-spin' : 'fa-clock') }}"></i>
                            </div>
                            <div class="approval-card card {{ $application->status == 'Awaiting VC' ? 'active-step' : ($vcDone ? 'done-step' : '') }}">
                                <div class="card-header {{ $application->status == 'Awaiting VC' ? 'bg-dark text-white' : 'bg-light' }}">
                                    <i class="fas fa-stamp me-2"></i>Vice-Chancellor's Approval
                                    @if($vcDone)<span class="status-pill {{ $application->vc_decision=='Approved' ? 'bg-success text-white' : 'bg-danger text-white' }} ms-2" style="font-size:11px"><i class="fas {{ $application->vc_decision=='Approved' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>{{ $application->vc_decision }}</span>@endif
                                </div>
                                <div class="card-body">
                                    @if($application->vc_decision == 'Pending' && $application->status == 'Awaiting VC' && ($canApproveVC ?? false))
                                    <form id="vcForm">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Decision</label>
                                                <select class="form-control" name="decision" required>
                                                    <option value="">Select Final Decision</option>
                                                    <option value="Approved">Approved</option>
                                                    <option value="Rejected">Rejected</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Remarks</label>
                                            <textarea class="form-control" name="remarks" rows="2" placeholder="Optional remarks..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-approve"><i class="fas fa-stamp me-2"></i>Submit VC Decision</button>
                                    </form>
                                    @elseif($vcDone)
                                    <table class="table table-sm officer-info mb-0">
                                        @if($application->vc_remarks)<tr><th>Remarks</th><td>{{ $application->vc_remarks }}</td></tr>@endif
                                        @if($application->vc_name)<tr><th>Officer</th><td>{{ $application->vc_name }}</td></tr>@endif
                                        @if($application->vc_date)<tr><th>Date</th><td>{{ date('d M Y', strtotime($application->vc_date)) }}</td></tr>@endif
                                    </table>
                                    @else
                                    <p class="text-muted mb-0"><i class="fas fa-hourglass-half me-1"></i>Awaiting action...</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        </div><!-- end approval-timeline -->
                    </div>
                </div>

                <!-- Part F: Student's Results -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">PART F: STUDENT'S RESULTS</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>20. List of all courses and units taken so far:</strong></p>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Session</th>
                                        <th>Semester</th>
                                        <th>Course Code</th>
                                        <th>Units</th>
                                        <th>CA</th>
                                        <th>Exam</th>
                                        <th>Total</th>
                                        <th>Grade</th>
                                        <th>Point</th>
                                        <th>UGP</th>
                                        <th>Remark</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($results as $result)
                                        <tr>
                                            <td>{{ $result->session }}</td>
                                            <td>{{ $result->semester }}</td>
                                            <td>{{ $result->code }}</td>
                                            <td>{{ $result->unit }}</td>
                                            <td>{{ $result->ca ?? 'N/A' }}</td>
                                            <td>{{ $result->exam ?? 'N/A' }}</td>
                                            <td>{{ $result->total ?? 'N/A' }}</td>
                                            <td>{{ $result->grade }}</td>
                                            <td>{{ $result->point ?? 'N/A' }}</td>
                                            <td>{{ $result->ugp ?? 'N/A' }}</td>
                                            <td>{{ $result->remark ?? 'N/A' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center">No results found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="col-lg-4">
                <!-- Status Card -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Application Status</h5>
                    </div>
                    <div class="card-body">
                        <h5 class="mb-3">
                            @if ($application->status == 'Approved')
                                <span class="badge bg-success w-100">{{ $application->status }}</span>
                            @elseif($application->status == 'Rejected')
                                <span class="badge bg-danger w-100">{{ $application->status }}</span>
                            @elseif($application->status == 'Payment Pending')
                                <span class="badge bg-warning w-100">{{ $application->status }}</span>
                            @else
                                <span class="badge bg-info w-100">{{ $application->status }}</span>
                            @endif
                        </h5>
                        <p><strong>Payment Status:</strong>
                            @if ($application->payment_status == 'Paid')
                                <span class="badge bg-success">Paid</span>
                            @else
                                <span class="badge bg-warning">Pending</span>
                            @endif
                        </p>
                        @if ($application->payment_status == 'Paid')
                            <p><strong>RRR:</strong> {{ $application->rrr }}</p>
                            <p><strong>Amount:</strong> ₦{{ number_format($application->amount, 2) }}</p>
                        @endif
                    </div>
                </div>

                {{-- New Account Details Card (After Approval) --}}
                @if ($application->status == 'Approved')
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>New Account Created</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <p class="mb-1 text-muted">New Username</p>
                                <h5 class="text-primary">{{ $application->application_no }}</h5>
                            </div>
                            <div class="mb-3">
                                <p class="mb-1 text-muted">Default Password</p>
                                <h5 class="text-danger">
                                    {{ \App\Http\Controllers\SystemSettingsController::get('default_student_password', 'umstad@2026') }}
                                </h5>
                            </div>
                            <hr>
                            <div class="mb-2">
                                <p class="mb-1 text-muted">New Faculty</p>
                                <p class="text-success mb-0"><strong>{{ $newFacultyTitle }}</strong></p>
                            </div>
                            <div class="mb-2">
                                <p class="mb-1 text-muted">New Department</p>
                                <p class="text-success mb-0"><strong>{{ $newDepartmentTitle }}</strong></p>
                            </div>
                            <div class="mb-2">
                                <p class="mb-1 text-muted">New Program</p>
                                <p class="text-success mb-0"><strong>{{ $newProgramTitle }}</strong></p>
                            </div>
                            <hr>
                            <div class="mb-0">
                                <p class="mb-1 text-muted">Approved By</p>
                                <p class="mb-0">{{ session('id') }}</p>
                                <small
                                    class="text-muted">{{ $application->registrar_date ? date('d M Y', strtotime($application->registrar_date)) : 'N/A' }}</small>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Actions Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('change-of-course.admin') }}" class="btn btn-secondary w-100 mb-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                        <button onclick="window.print()" class="btn btn-info w-100 mb-2">
                            <i class="fas fa-print me-2"></i>Print Application
                        </button>
                        @if($application->status == 'Approved')
                        <a href="{{ route('change-of-course.admission-letter', $application->id) }}" target="_blank" class="btn btn-success w-100">
                            <i class="fas fa-file-pdf me-2"></i>Download Admission Letter
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Show/hide level selection based on registrar decision
        $('#registrarDecision').change(function() {
            if ($(this).val() === 'Approved') {
                $('#levelSelectionDiv').show();
                $('#approvedLevel').prop('required', true);
            } else {
                $('#levelSelectionDiv').hide();
                $('#approvedLevel').prop('required', false);
            }
        });

        // New HOD Form
        $('#newHodForm').submit(function(e) {
            e.preventDefault();
            submitDecision('{{ route('change-of-course.new-hod-action', $application->id) }}', $(this)
                .serialize());
        });

        // New Dean Form
        $('#newDeanForm').submit(function(e) {
            e.preventDefault();
            submitDecision('{{ route('change-of-course.new-dean-action', $application->id) }}', $(this)
                .serialize());
        });

        // Provost Form
        $('#provostForm').submit(function(e) {
            e.preventDefault();
            submitDecision('{{ route('change-of-course.provost-action', $application->id) }}', $(this)
                .serialize());
        });

        // Current HOD Form
        $('#currentHodForm').submit(function(e) {
            e.preventDefault();
            submitDecision('{{ route('change-of-course.current-hod-action', $application->id) }}', $(
                this).serialize());
        });

        // Current Dean Form
        $('#currentDeanForm').submit(function(e) {
            e.preventDefault();
            submitDecision('{{ route('change-of-course.current-dean-action', $application->id) }}', $(
                this).serialize());
        });

        // Registrar Form
        $('#registrarForm').submit(function(e) {
            e.preventDefault();
            submitDecision('{{ route('change-of-course.registrar-action', $application->id) }}', $(
                this).serialize());
        });

        // VC Form
        $('#vcForm').submit(function(e) {
            e.preventDefault();
            submitDecision('{{ route('change-of-course.vc-action', $application->id) }}', $(
                this).serialize());
        });

        function submitDecision(url, formData) {
            $.ajax({
                url: url,
                type: 'POST',
                data: formData + '&_token={{ csrf_token() }}',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('========== AJAX ERROR DEBUG ==========');
                    console.error('URL:', url);
                    console.error('Status:', status);
                    console.error('Error:', error);
                    console.error('HTTP Status Code:', xhr.status);
                    console.error('Status Text:', xhr.statusText);
                    console.error('Response Text:', xhr.responseText);
                    try {
                        var responseJSON = JSON.parse(xhr.responseText);
                        console.error('Parsed Response:', JSON.stringify(responseJSON, null, 2));
                        if (responseJSON.errors) console.error('Validation Errors:', JSON.stringify(
                            responseJSON.errors, null, 2));
                        if (responseJSON.message) console.error('Message:', responseJSON.message);
                        if (responseJSON.exception) console.error('Exception:', responseJSON
                            .exception);
                        if (responseJSON.file) console.error('File:', responseJSON.file);
                        if (responseJSON.line) console.error('Line:', responseJSON.line);
                        if (responseJSON.trace) console.error('Trace:', responseJSON.trace);
                    } catch (e) {
                        console.error('Raw (non-JSON) response:', xhr.responseText);
                    }
                    console.error('========== END AJAX ERROR DEBUG ==========');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred. Please try again. (Check console)',
                    });
                }
            });
        }
    });
</script>
