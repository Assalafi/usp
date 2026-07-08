<!-- Start Content-->
@php
    $days = [
        'Mon' => 1,
        'Tue' => 2,
        'Wed' => 3,
        'Thu' => 4,
        'Fri' => 5,
        'Sat' => 6,
    ];
    if(date('D') == 'Sun'){
        $days = 1;
    }else{
        $days = $days[date('D')];
    }

    function abbreviateMiddleName($name)
    {
        $nameParts = explode(' ', $name);
        if (count($nameParts) === 3) {
            $nameParts[1] = substr($nameParts[1], 0, 1) . '.';
            $abbreviatedName = implode(' ', $nameParts);
            return $abbreviatedName;
        }
        return $name;
    }
@endphp
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    @php
                        $weekdays = ['1', '2', '3', '4', '5', '6'];
                    @endphp
                    <ul class="nav nav-pills mb-3 card-block" id="myTab" role="tablist">
                        @foreach ($weekdays as $weekday)
                            <li class="nav-item">
                                <a class="nav-link @if ($weekday == $days) active @endif text-uppercase"
                                    id="day{{ $weekday }}-tab" data-bs-toggle="tab" href="#day{{ $weekday }}"
                                    role="tab" aria-controls="day{{ $weekday }}" aria-selected="true">
                                    @if ($weekday == 1)
                                        {{ $day[$weekday] = 'Monday' }}
                                    @elseif($weekday == 2)
                                        {{ $day[$weekday] = 'Tuesday' }}
                                    @elseif($weekday == 3)
                                        {{ $day[$weekday] = 'Wednesday' }}
                                    @elseif($weekday == 4)
                                        {{ $day[$weekday] = 'Thursday' }}
                                    @elseif($weekday == 5)
                                        {{ $day[$weekday] = 'Friday' }}
                                    @elseif($weekday == 6)
                                        {{ $day[$weekday] = 'Saturday' }}
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        @foreach ($weekdays as $weekday)
                            <div class="tab-pane fade @if ($weekday == $days) show active @endif"
                                id="day{{ $weekday }}" role="tabpanel"
                                aria-labelledby="day{{ $weekday }}-tab">
                                <div class="">
                                    <div class="row">
                                        <div class="col-md-12 card-body">
                                            <!-- [ Data table ] start -->
                                            <div class="table-responsive">
                                                <table id="export-table"
                                                    class="display table nowrap table-striped table-hover"
                                                    style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>{{ 'Course' }}</th>
                                                            <th>{{ 'Hall' }}</th>
                                                            <th>{{ 'Time' }}</th>
                                                            <th>{{ 'Lecturer' }}</th>
                                                            <th>{{ 'Comment' }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $sn = 1;
                                                        @endphp
                                                        @foreach ($myLectureTimetable->where('day_no', $weekday) as $row)
                                                            @php

                                                                $staffs = DB::table('course_allocation')
                                                                    ->where(['course' => $row->course])
                                                                    ->select('name')
                                                                    ->orderBy('type', 'ASC')
                                                                    ->get();
                                                                $lecturer = '';
                                                                foreach ($staffs as $staff) {
                                                                    $lecturer .=
                                                                        abbreviateMiddleName($staff->name) . " | ";
                                                                }
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $sn++ }}</td>
                                                                <td>{{ $row->course }}</td>
                                                                <td>{{ $row->hall }}</td>
                                                                <td>{{ date('h:i A', strtotime($row->start)) }} -
                                                                    {{ date('h:i A', strtotime($row->end)) }}
                                                                </td>
                                                                <td>{{ $lecturer }}</td>
                                                                <td>{{ $row->comment }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <!-- [ Data table ] end -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <!-- [ Card ] end -->
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
