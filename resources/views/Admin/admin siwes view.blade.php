<style>
    @media (max-width: 768px) {
        .siwes-table tbody tr {
            display: block;
            margin-bottom: 15px;
        }
        .siwes-table tbody th,
        .siwes-table tbody td {
            display: block;
            width: 100% !important;
            box-sizing: border-box;
        }
        .siwes-table tbody th {
            background-color: #f8f9fa;
            padding: 8px 12px;
            font-weight: bold;
        }
        .siwes-table tbody td {
            padding: 8px 12px;
        }
    }
</style>
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h5>SIWES Information - {{ strtoupper($siwes->first_name . ' ' . $siwes->last_name) }}</h5>
            </div>
            <div class="card-block">
                <div class="mb-3">
                    <a href="/admin/siwes" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <a href="/siwes/download/{{ $siwes->id }}" class="btn btn-success ms-2" target="_blank">
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
                
                <h6 class="mb-3" style="color: #C20707; font-weight: bold;">STUDENT DETAILS</h6>
                <div class="table-responsive">
                    <table class="table table-bordered siwes-table">
                        <tbody>
                            <tr>
                                <td colspan="2">
                                    <div class="row">
                                        <div class="col-md-3 text-center">
                                            @if($siwes->picture && file_exists(public_path('storage/picture/' . $siwes->picture)))
                                                <img src="{{ asset('storage/picture/' . $siwes->picture) }}" alt="Student Photo" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                            @else
                                                <img src="{{ asset('dashboard/assets/images/user/avatar-1.jpg') }}" alt="Student Photo" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                            @endif
                                            <h6 class="mt-2">Student Photo</h6>
                                        </div>
                                        <div class="col-md-9">
                                            <table class="table table-bordered siwes-table">
                                                <tbody>
                                                    <tr>
                                                        <th width="40%">Name</th>
                                                        <td>{{ strtoupper($siwes->first_name . ' ' . $siwes->last_name . ' ' . $siwes->other_name) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th width="40%">Matric Number</th>
                                                        <td>{{ strtoupper($siwes->matric_number) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th width="40%">Course of Study</th>
                                                        <td>{{ strtoupper($siwes->course_of_study) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th width="40%">Level of Study</th>
                                                        <td>{{ $siwes->level }} LEVEL</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <h6 class="mb-3 mt-4" style="color: #C20707; font-weight: bold;">SIWES DETAILS</h6>
                <div class="table-responsive">
                    <table class="table table-bordered siwes-table">
                        <tbody>
                            <tr>
                                <th width="40%">Period of Attachment (From)</th>
                                <td>{{ $siwes->period_of_attachment_from ? date('d F Y', strtotime($siwes->period_of_attachment_from)) : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th width="40%">Period of Attachment (To)</th>
                                <td>{{ $siwes->period_of_attachment_to ? date('d F Y', strtotime($siwes->period_of_attachment_to)) : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th width="40%">Placement Address</th>
                                <td>{{ strtoupper($siwes->placement_of_address ?? 'N/A') }}</td>
                            </tr>
                            <tr>
                                <th width="40%">SIWES Year</th>
                                <td>{{ $siwes->siwes_year }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <h6 class="mb-3 mt-4" style="color: #C20707; font-weight: bold;">BANK DETAILS</h6>
                <div class="table-responsive">
                    <table class="table table-bordered siwes-table">
                        <tbody>
                            <tr>
                                <th width="40%">Bank Name</th>
                                <td>{{ strtoupper($siwes->bank_name ?? 'N/A') }}</td>
                            </tr>
                            <tr>
                                <th width="40%">Bank Code</th>
                                <td>{{ $siwes->bank_code ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th width="40%">Account Number</th>
                                <td>{{ $siwes->account_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th width="40%">Sort Code</th>
                                <td>{{ $siwes->sort_code ?? 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <h6 class="mb-3 mt-4" style="color: #C20707; font-weight: bold;">CONTACT INFORMATION</h6>
                <div class="table-responsive">
                    <table class="table table-bordered siwes-table">
                        <tbody>
                            <tr>
                                <th width="40%">Student Email Address</th>
                                <td>{{ strtolower($siwes->student_email_address) }}</td>
                            </tr>
                            <tr>
                                <th width="40%">Remarks</th>
                                <td>{{ strtoupper($siwes->remarks ?? 'N/A') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
