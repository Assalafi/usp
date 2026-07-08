<script>
    function refreshPage() {
        setTimeout(() => {
            location.reload();
        }, 10000);
    }
    refreshPage();
</script>
@php

    //$accreditated = DB::table('students')->select('vflag')->where('vflag', '1')->count();

    use App\Models\Student;
    use App\Models\ElectionCandidate;
    use App\Models\Staff;
    use App\Models\Alumni;
    $accreditated = 0;
    $def = 150;
    $lll = DB::table('users')->select('level')->where('username', 'su')->value('level');
    $poss = ElectionCandidate::where(['category' => $category, 'acc_type' => request('acc_type')])
        ->select('position')
        ->distinct('position')
        ->pluck('position');
    if (request('acc_type') == 'Student') {
        // $accreditated = Student::where(['school_fee' => '1'])
        //     ->select('school_fee')
        //     ->count();
        $accreditated = DB::table('students')
            ->select('vflag')
            ->whereRaw('LOWER(state_origin) = ?', ['borno'])
            ->count();
    } elseif (request('acc_type') == 'Staff') {
        $accreditated = Staff::where(['degree' => '1'])
            ->select('degree')
            ->count();
    } elseif (request('acc_type') == 'Alumni') {
        $accreditated = Alumni::count();
    }
    $cast = DB::table('election_votes')
        ->select('username')
        ->where('category', $category)
        ->where('acc_type', request('acc_type'))
        ->distinct('username')
        ->count();
    // 150 - 5 = 145
    //$cast = $cast + $def - $lll;
@endphp
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-1">
                <div class="card-block">
                    <form method="GET" action="#">
                        <select name="acc_type" id="acc_type" class="form-control" onchange="this.form.submit()">
                            <option value="Hide"
                                {{ old('acc_type', request('acc_type')) == 'Hide' ? 'selected' : '' }}>Hide</option>
                            <option value="Staff"
                                {{ old('acc_type', request('acc_type')) == 'Staff' ? 'selected' : '' }}>Staff</option>
                            <option value="Student"
                                {{ old('acc_type', request('acc_type')) == 'Student' ? 'selected' : '' }}>Student
                            </option>
                            <option value="Alumni"
                                {{ old('acc_type', request('acc_type')) == 'Alumni' ? 'selected' : '' }}>Alumni
                            </option>
                        </select>
                    </form>
                </div>
            </div>
            <div class="col-sm-9">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ strtoupper($page) }} | Accreditated: {{ number_format($accreditated) }} | Vote Cast:
                            {{ number_format($cast) }} | Yet to vote: {{ number_format($accreditated - $cast) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="card">
                    <a href="/election general" class="btn btn-light">Back to List</a>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-block">
                        <div class="row">
                            @foreach ($positions->where('category', $category)->whereIn('position', $poss) as $pos)
                                <!-- [ Data table ] start -->

                                <div class="card col-md-4 shadow" style="font-size: 10px">
                                    <div class="card-header">
                                        <h5 class="card-title">{{ $pos->position }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive" style="width: 100%">
                                            <table id=""
                                                class="display table nowrap table-striped table-hover table-responsive"
                                                style="width: 100%; font-size: 10px">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ 'Photo' }}</th>
                                                        <th>{{ 'Name' }}</th>
                                                        <th>{{ 'Vote' }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $sn = 1;
                                                    @endphp
                                                    @foreach ($data->where('category', $category)->where('position', $pos->position)->where('acc_type', request('acc_type')) as $row)
                                                        <tr>
                                                            <td>{{ $sn++ }}</td>
                                                            <td><a
                                                                    href="{{ asset('storage/picture/' . $row->picture) }}"><img
                                                                        src="{{ asset('storage/picture/' . $row->picture) }}"
                                                                        class="card-img-top img-radius img-fluid"
                                                                        style="width: 50px; height: 50px"
                                                                        alt="{{ $row->picture }}"></a></td>
                                                            <td>{{ $row->name }}</td>
                                                            <td>{{ $row->vote }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- [ Data table ] end -->
                                @php
                                    $active = 2;
                                @endphp
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->
