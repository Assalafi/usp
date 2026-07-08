@php
    use App\Models\Student;
    use Illuminate\Support\Facades\DB;

    $student = Student::where('user_id', session('id'))->first();
    $siwesData = DB::table('siwes')->where('username', $student->username ?? session('id_number'))->first();
    $editMode = false; // Default to view mode
@endphp
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
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>SIWES Information</h5>
                    </div>
                    <div class="card-block">
                        @if($siwesData)
                            <div id="viewMode">
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Your SIWES information has been recorded.
                                </div>
                                
                                <div class="mb-3">
                                    <button type="button" class="btn btn-primary" onclick="toggleEditMode()">
                                        <i class="fas fa-edit me-2"></i>Edit Information
                                    </button>
                                    <a href="/siwes/download" class="btn btn-success ms-2" target="_blank">
                                        <i class="fas fa-download me-2"></i>Download
                                    </a>
                                </div>
                                
                                <h6 class="mb-3" style="color: #C20707; font-weight: bold;">STUDENT DETAILS</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered siwes-table">
                                        <tbody>
                                            <tr>
                                                <th width="40%">Name</th>
                                                <td>{{ strtoupper($student->first_name . ' ' . $student->last_name . ' ' . $student->other_name) }}</td>
                                            </tr>
                                            <tr>
                                                <th width="40%">Matric Number</th>
                                                <td>{{ strtoupper($student->username) }}</td>
                                            </tr>
                                            <tr>
                                                <th width="40%">Course of Study</th>
                                                <td>{{ strtoupper(DB::table('program')->where('code', $student->program)->value('title')) }}</td>
                                            </tr>
                                            <tr>
                                                <th width="40%">Level of Study</th>
                                                <td>{{ $student->level }} LEVEL</td>
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
                                                <td>{{ date('d F Y', strtotime($siwesData->period_of_attachment_from)) }}</td>
                                            </tr>
                                            <tr>
                                                <th width="40%">Period of Attachment (To)</th>
                                                <td>{{ date('d F Y', strtotime($siwesData->period_of_attachment_to)) }}</td>
                                            </tr>
                                            <tr>
                                                <th width="40%">Placement Address</th>
                                                <td>{{ strtoupper($siwesData->placement_of_address) }}</td>
                                            </tr>
                                            <tr>
                                                <th width="40%">SIWES Year</th>
                                                <td>{{ $siwesData->siwes_year }}</td>
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
                                                <td>{{ strtoupper($siwesData->bank_name) }}</td>
                                            </tr>
                                            <tr>
                                                <th width="40%">Bank Code</th>
                                                <td>{{ $siwesData->bank_code }}</td>
                                            </tr>
                                            <tr>
                                                <th width="40%">Account Number</th>
                                                <td>{{ $siwesData->account_number }}</td>
                                            </tr>
                                            <tr>
                                                <th width="40%">Sort Code</th>
                                                <td>{{ $siwesData->sort_code ?? 'N/A' }}</td>
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
                                                <td>{{ strtolower($siwesData->student_email_address) }}</td>
                                            </tr>
                                            <tr>
                                                <th width="40%">Remarks</th>
                                                <td>{{ strtoupper($siwesData->remarks ?? 'N/A') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div id="editMode" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Edit your SIWES information below.
                                </div>
                                
                                <div class="mb-3">
                                    <button type="button" class="btn btn-secondary" onclick="toggleEditMode()">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </button>
                                </div>
                                
                                <form action="/siwes/save" method="POST">
                                    @csrf
                                    
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0" style="color: #C20707; font-weight: bold;">STUDENT DETAILS</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label text-muted small">Name</label>
                                                    <input type="text" class="form-control bg-light" value="{{ strtoupper($student->first_name . ' ' . $student->last_name . ' ' . $student->other_name) }}" readonly>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label text-muted small">Matric Number</label>
                                                    <input type="text" class="form-control bg-light" value="{{ strtoupper($student->username) }}" readonly>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label text-muted small">Course of Study</label>
                                                    <input type="text" class="form-control bg-light" value="{{ strtoupper(DB::table('program')->where('code', $student->program)->value('title')) }}" readonly>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label text-muted small">Level of Study</label>
                                                    <input type="text" class="form-control bg-light" value="{{ $student->level }} LEVEL" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0" style="color: #C20707; font-weight: bold;">SIWES DETAILS</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Period of Attachment (From) <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" name="period_of_attachment_from" value="{{ $siwesData->period_of_attachment_from }}" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Period of Attachment (To) <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" name="period_of_attachment_to" value="{{ $siwesData->period_of_attachment_to }}" required>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Placement Address <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="placement_of_address" rows="3" required>{{ $siwesData->placement_of_address }}</textarea>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">SIWES Year <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="siwes_year" required>
                                                        <option value="">Select Year</option>
                                                        @php
                                                            $currentYear = date('Y');
                                                            for($year = $currentYear; $year >= 2010; $year--):
                                                                $selected = ($siwesData->siwes_year == $year) ? 'selected' : '';
                                                                echo "<option value='$year' $selected>$year</option>";
                                                            endfor;
                                                        @endphp
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0" style="color: #C20707; font-weight: bold;">BANK DETAILS</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Select Bank <span class="text-danger">*</span></label>
                                                    <select class="form-select bank-select" name="bank_name" required onchange="updateBankCode()">
                                                        <option value="">Select your bank</option>
                                                        <option value="ACCESS BANK PLC" data-code="044" @if($siwesData->bank_name == 'ACCESS BANK PLC') selected @endif>ACCESS BANK PLC</option>
                                                        <option value="CITIBANK NIGERIA LIMITED" data-code="023" @if($siwesData->bank_name == 'CITIBANK NIGERIA LIMITED') selected @endif>CITIBANK NIGERIA LIMITED</option>
                                                        <option value="ECOBANK NIGERIA PLC" data-code="050" @if($siwesData->bank_name == 'ECOBANK NIGERIA PLC') selected @endif>ECOBANK NIGERIA PLC</option>
                                                        <option value="FIDELITY BANK PLC" data-code="070" @if($siwesData->bank_name == 'FIDELITY BANK PLC') selected @endif>FIDELITY BANK PLC</option>
                                                        <option value="FIRST BANK OF NIGERIA LIMITED" data-code="011" @if($siwesData->bank_name == 'FIRST BANK OF NIGERIA LIMITED') selected @endif>FIRST BANK OF NIGERIA LIMITED</option>
                                                        <option value="FIRST CITY MONUMENT BANK PLC" data-code="214" @if($siwesData->bank_name == 'FIRST CITY MONUMENT BANK PLC') selected @endif>FIRST CITY MONUMENT BANK PLC</option>
                                                        <option value="GUARANTY TRUST BANK PLC" data-code="058" @if($siwesData->bank_name == 'GUARANTY TRUST BANK PLC') selected @endif>GUARANTY TRUST BANK PLC</option>
                                                        <option value="HERITAGE BANK PLC" data-code="030" @if($siwesData->bank_name == 'HERITAGE BANK PLC') selected @endif>HERITAGE BANK PLC</option>
                                                        <option value="JAIZ BANK PLC" data-code="301" @if($siwesData->bank_name == 'JAIZ BANK PLC') selected @endif>JAIZ BANK PLC</option>
                                                        <option value="KEYSTONE BANK LIMITED" data-code="082" @if($siwesData->bank_name == 'KEYSTONE BANK LIMITED') selected @endif>KEYSTONE BANK LIMITED</option>
                                                        <option value="KUDA MICROFINANCE BANK" data-code="50211" @if($siwesData->bank_name == 'KUDA MICROFINANCE BANK') selected @endif>KUDA MICROFINANCE BANK</option>
                                                        <option value="POLARIS BANK PLC" data-code="076" @if($siwesData->bank_name == 'POLARIS BANK PLC') selected @endif>POLARIS BANK PLC</option>
                                                        <option value="PROVIDUS BANK PLC" data-code="101" @if($siwesData->bank_name == 'PROVIDUS BANK PLC') selected @endif>PROVIDUS BANK PLC</option>
                                                        <option value="STANBIC IBTC BANK PLC" data-code="221" @if($siwesData->bank_name == 'STANBIC IBTC BANK PLC') selected @endif>STANBIC IBTC BANK PLC</option>
                                                        <option value="STANDARD CHARTERED BANK NIGERIA LIMITED" data-code="068" @if($siwesData->bank_name == 'STANDARD CHARTERED BANK NIGERIA LIMITED') selected @endif>STANDARD CHARTERED BANK NIGERIA LIMITED</option>
                                                        <option value="STERLING BANK PLC" data-code="232" @if($siwesData->bank_name == 'STERLING BANK PLC') selected @endif>STERLING BANK PLC</option>
                                                        <option value="TAJ BANK LIMITED" data-code="302" @if($siwesData->bank_name == 'TAJ BANK LIMITED') selected @endif>TAJ BANK LIMITED</option>
                                                        <option value="UNION BANK OF NIGERIA PLC" data-code="032" @if($siwesData->bank_name == 'UNION BANK OF NIGERIA PLC') selected @endif>UNION BANK OF NIGERIA PLC</option>
                                                        <option value="UNITED BANK FOR AFRICA PLC" data-code="033" @if($siwesData->bank_name == 'UNITED BANK FOR AFRICA PLC') selected @endif>UNITED BANK FOR AFRICA PLC</option>
                                                        <option value="WEMA BANK PLC" data-code="035" @if($siwesData->bank_name == 'WEMA BANK PLC') selected @endif>WEMA BANK PLC</option>
                                                        <option value="ZENITH BANK PLC" data-code="057" @if($siwesData->bank_name == 'ZENITH BANK PLC') selected @endif>ZENITH BANK PLC</option>
                                                        <option value="GLOBUS BANK LIMITED" data-code="103" @if($siwesData->bank_name == 'GLOBUS BANK LIMITED') selected @endif>GLOBUS BANK LIMITED</option>
                                                        <option value="OPTIMUS BANK" data-code="526" @if($siwesData->bank_name == 'OPTIMUS BANK') selected @endif>OPTIMUS BANK</option>
                                                        <option value="MONIEPOINT MFB" data-code="50515" @if($siwesData->bank_name == 'MONIEPOINT MFB') selected @endif>MONIEPOINT MFB</option>
                                                        <option value="PALMPAY" data-code="999991" @if($siwesData->bank_name == 'PALMPAY') selected @endif>PALMPAY</option>
                                                        <option value="OPAY" data-code="999992" @if($siwesData->bank_name == 'OPAY') selected @endif>OPAY</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Bank Code</label>
                                                    <input type="text" class="form-control bg-light" name="bank_code" id="bank_code" value="{{ $siwesData->bank_code }}" readonly>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Account Number <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="account_number" value="{{ $siwesData->account_number }}" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Sort Code</label>
                                                    <input type="text" class="form-control" name="sort_code" value="{{ $siwesData->sort_code }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0" style="color: #C20707; font-weight: bold;">CONTACT INFORMATION</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Student Email Address <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control" name="student_email_address" value="{{ $siwesData->student_email_address }}" required>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Remarks</label>
                                                    <textarea class="form-control" name="remarks" rows="3">{{ $siwesData->remarks }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-save me-2"></i>Update SIWES Information
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Please fill in your SIWES information below.
                            </div>

                            <form action="/siwes/save" method="POST" enctype="multipart/form-data">
                                @csrf
                                
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0" style="color: #C20707; font-weight: bold;">STUDENT DETAILS</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label text-muted small">Name</label>
                                                <input type="text" class="form-control bg-light" value="{{ strtoupper($student->first_name . ' ' . $student->last_name . ' ' . $student->other_name) }}" readonly>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label text-muted small">Matric Number</label>
                                                <input type="text" class="form-control bg-light" value="{{ strtoupper($student->username) }}" readonly>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label text-muted small">Course of Study</label>
                                                <input type="text" class="form-control bg-light" value="{{ strtoupper(DB::table('program')->where('code', $student->program)->value('title')) }}" readonly>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label text-muted small">Level of Study</label>
                                                <input type="text" class="form-control bg-light" value="{{ $student->level }} LEVEL" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0" style="color: #C20707; font-weight: bold;">SIWES DETAILS</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Period of Attachment (From) <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="period_of_attachment_from" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Period of Attachment (To) <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="period_of_attachment_to" required>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Placement Address <span class="text-danger">*</span></label>
                                                <textarea class="form-control" name="placement_of_address" rows="3" required></textarea>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">SIWES Year <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="siwes_year" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0" style="color: #C20707; font-weight: bold;">BANK DETAILS</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Select Bank <span class="text-danger">*</span></label>
                                                <select class="form-select bank-select" name="bank_name" required onchange="updateBankCode()">
                                                    <option value="">Select your bank</option>
                                                    <option value="ACCESS BANK PLC" data-code="044">ACCESS BANK PLC</option>
                                                    <option value="CITIBANK NIGERIA LIMITED" data-code="023">CITIBANK NIGERIA LIMITED</option>
                                                    <option value="ECOBANK NIGERIA PLC" data-code="050">ECOBANK NIGERIA PLC</option>
                                                    <option value="FIDELITY BANK PLC" data-code="070">FIDELITY BANK PLC</option>
                                                    <option value="FIRST BANK OF NIGERIA LIMITED" data-code="011">FIRST BANK OF NIGERIA LIMITED</option>
                                                    <option value="FIRST CITY MONUMENT BANK PLC" data-code="214">FIRST CITY MONUMENT BANK PLC</option>
                                                    <option value="GUARANTY TRUST BANK PLC" data-code="058">GUARANTY TRUST BANK PLC</option>
                                                    <option value="HERITAGE BANK PLC" data-code="030">HERITAGE BANK PLC</option>
                                                    <option value="JAIZ BANK PLC" data-code="301">JAIZ BANK PLC</option>
                                                    <option value="KEYSTONE BANK LIMITED" data-code="082">KEYSTONE BANK LIMITED</option>
                                                    <option value="KUDA MICROFINANCE BANK" data-code="50211">KUDA MICROFINANCE BANK</option>
                                                    <option value="POLARIS BANK PLC" data-code="076">POLARIS BANK PLC</option>
                                                    <option value="PROVIDUS BANK PLC" data-code="101">PROVIDUS BANK PLC</option>
                                                    <option value="STANBIC IBTC BANK PLC" data-code="221">STANBIC IBTC BANK PLC</option>
                                                    <option value="STANDARD CHARTERED BANK NIGERIA LIMITED" data-code="068">STANDARD CHARTERED BANK NIGERIA LIMITED</option>
                                                    <option value="STERLING BANK PLC" data-code="232">STERLING BANK PLC</option>
                                                    <option value="TAJ BANK LIMITED" data-code="302">TAJ BANK LIMITED</option>
                                                    <option value="UNION BANK OF NIGERIA PLC" data-code="032">UNION BANK OF NIGERIA PLC</option>
                                                    <option value="UNITED BANK FOR AFRICA PLC" data-code="033">UNITED BANK FOR AFRICA PLC</option>
                                                    <option value="WEMA BANK PLC" data-code="035">WEMA BANK PLC</option>
                                                    <option value="ZENITH BANK PLC" data-code="057">ZENITH BANK PLC</option>
                                                    <option value="GLOBUS BANK LIMITED" data-code="103">GLOBUS BANK LIMITED</option>
                                                    <option value="OPTIMUS BANK" data-code="526">OPTIMUS BANK</option>
                                                    <option value="MONIEPOINT MFB" data-code="50515">MONIEPOINT MFB</option>
                                                    <option value="PALMPAY" data-code="999991">PALMPAY</option>
                                                    <option value="OPAY" data-code="999992">OPAY</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Bank Code</label>
                                                <input type="text" class="form-control bg-light" name="bank_code" id="bank_code" readonly>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Account Number <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="account_number" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Sort Code</label>
                                                <input type="text" class="form-control" name="sort_code">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0" style="color: #C20707; font-weight: bold;">CONTACT INFORMATION</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Student Email Address <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" name="student_email_address" value="{{ $student->contact_email }}" required>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Remarks</label>
                                                <textarea class="form-control" name="remarks" rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>Save SIWES Information
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<script>
function toggleEditMode() {
    const viewMode = document.getElementById('viewMode');
    const editMode = document.getElementById('editMode');
    
    if (viewMode.style.display === 'none') {
        viewMode.style.display = 'block';
        editMode.style.display = 'none';
    } else {
        viewMode.style.display = 'none';
        editMode.style.display = 'block';
    }
}

function updateBankCode() {
    const select = document.querySelector('.bank-select');
    const selectedOption = select.options[select.selectedIndex];
    const bankCode = selectedOption.getAttribute('data-code');
    document.getElementById('bank_code').value = bankCode || '';
}
</script>
