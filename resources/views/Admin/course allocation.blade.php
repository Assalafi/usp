<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ strtoupper($page) }}</h5>
                    </div>
                    <div class="card-block">

                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="card">
                    @if (session('accType') == 'Admin' || session('username') == 'SP11913')
                        <div class="card-block">
                            <form class="" method="GET" action="#">
                                @csrf
                                <div class="row gx-2">
                                    <div class="form-group col-md-3">
                                        <label for="facultyf">Faculty <span>*</span></label>
                                        <select class="form-control faculty" lang="f" name="faculty"
                                            id="facultyf">
                                            {{-- get GET value  --}}

                                            <option value="">Select Option</option>
                                            @foreach ($faculty as $roww)
                                                <option value="{{ $roww->code }}">{{ $roww->title }} ({{ $roww->code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="departmentf">Department</label>
                                        <select class="form-control department" lang="f" id="departmentf"
                                            name="department" required>
                                            <option value="">Select Faculty First</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="programf">Program</label>
                                        <select class="form-control" id="programf" lang="f" name="program">
                                            <option value="">Select Department First</option>
                                        </select>
                                    </div>
                                    {{-- session --}}
                        <div class="form-group col-md-3">
                            <label for="session">Session</label>
                            <select name="session" id="session" class="form-control" required>
                                <option value="">Select Option</option>
                                <option value="2025/2026">2025/2026</option>
                                <option value="2024/2025">2024/2025</option>
                                <option value="2023/2024">2023/2024</option>
                                <option value="2022/2023">2022/2023</option>
                                <option value="2021/2022">2021/2022</option>
                                <option value="2020/2021">2020/2021</option>
                                <option value="2019/2020">2019/2020</option>
                                <option value="2018/2019">2018/2019</option>
                                <option value="2017/2018">2017/2018</option>
                                <option value="2016/2017">2016/2017</option>
                                <option value="2015/2016">2015/2016</option>
                                <option value="2014/2015">2014/2015</option>
                                <option value="2013/2014">2013/2014</option>
                            </select>
                        </div>


                                    <div class="form-group col-md-3">
                                        <button type="submit" class="btn btn-info btn-filter"><i
                                                class="fas fa-search"></i>
                                            {{ 'Filter' }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @else
                    <div class="card-block">
                        <form class="" method="GET" action="#">
                            @csrf
                            <input type="hidden" name="username" value="staff">
                            <div class="row gx-2">
                                {{-- session --}}
                    <div class="form-group col-md-3">
                        <label for="session">Session</label>
                        <select name="session" id="session" class="form-control" required>
                            <option value="">Select Option</option>
                                <option value="2025/2026">2025/2026</option>
                            <option value="2024/2025">2024/2025</option>
                            <option value="2023/2024">2023/2024</option>
                            <option value="2022/2023">2022/2023</option>
                            <option value="2021/2022">2021/2022</option>
                            <option value="2020/2021">2020/2021</option>
                            <option value="2019/2020">2019/2020</option>
                            <option value="2018/2019">2018/2019</option>
                            <option value="2017/2018">2017/2018</option>
                            <option value="2016/2017">2016/2017</option>
                            <option value="2015/2016">2015/2016</option>
                            <option value="2014/2015">2014/2015</option>
                            <option value="2013/2014">2013/2014</option>
                        </select>
                    </div>


                                <div class="form-group col-md-3">
                                    <button type="submit" class="btn btn-info btn-filter"><i
                                            class="fas fa-search"></i>
                                        {{ 'Filter' }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif
                    <div class="card-block">
                        <!-- [ Data table ] start -->
                        <a href="#" class="btn btn-primary createAction" data-bs-toggle="modal"
                            data-bs-target="#create"><i class="fas fa-plus"></i> {{ 'Add New' }}</a>
                        <div class="table-responsive">
                            <table id="export-table" class="display table nowrap table-striped table-hover"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ 'SP NO' }}</th>
                                        <th>{{ 'Name' }}</th>
                                        <th>{{ 'Course' }}</th>
                                        <th>{{ 'Type' }}</th>
                                        <th>{{ 'Session' }}</th>
                                        <th>{{ 'Action' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>{{ $row->username }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->course }}</td>
                                            <td>{{ $row->type == 'MAIN' ? 'Course Coordinator' : 'Course Lecturer' }}
                                            </td>
                                            <td>{{ $row->session }}</td>
                                            <td>
                                                <a href="#" class="btn btn-icon btn-primary btn-sm updateAction"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#updateStudent{{ $row->id }}">
                                                    <i class="far fa-edit"></i>
                                                </a>
                                                <button type="button"
                                                    class="btn btn-icon btn-danger btn-sm deleteAction"
                                                    data-bs-toggle="modal" data-bs-target="#delete{{ $row->id }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Show modal content -->
                                        <div id="updateStudent{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Update</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card">
                                                        <form class="form-group" action="update {{ $page }}"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->id }}">
                                                                <input type="hidden" name="username"
                                                                    value="{{ $row->username }}">
                                                                <input type="hidden" name="course"
                                                                    value="{{ $row->course }}">
                                                                <div class="form-group">
                                                                    <label for="username">Lecturer</label>
                                                                    <input type="text" name="username"
                                                                        value="{{ $row->username }}" id="username"
                                                                        class="form-control"
                                                                        placeholder="Enter SP/JP of the Course Lecturer"
                                                                        required>
                                                                    {{-- <select class="form-control" name="username"
                                                                        id="username" required>
                                                                        <option value="{{ $row->username }}">
                                                                            Selected: {{ $row->username }}</option>
                                                                        @foreach ($staff as $staffs)
                                                                            <option value="{{ $staffs->username }}">
                                                                                {{ $staffs->username }}:
                                                                                {{ $staffs->name }}</option>
                                                                        @endforeach
                                                                    </select> --}}
                                                                </div>
                                                                {{-- <div class="form-group">
                                                                    <label for="course">Courses</label>
                                                                    <select class="form-control" name="course"
                                                                        id="course" required>
                                                                        <option value="{{ $row->course }}">Selected:
                                                                            {{ $row->course }}</option>
                                                                        @foreach ($courses as $course)
                                                                            <option value="{{ $course->code }}">
                                                                                {{ $course->code }}:
                                                                                {{ $course->title }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div> --}}
                                                                <div class="form-group">
                                                                    <label for="type">Type</label>
                                                                    <select name="type" id="type"
                                                                        class="form-control" required>
                                                                        <option value="{{ $row->type }}">Selected:
                                                                            {{ $row->type == 'Main' ? 'Course Coordinator' : 'Course Lecturer' }}
                                                                        </option>
                                                                        <option value="Main">Course Coordinator
                                                                        </option>
                                                                        <option value="Support">Course Lecturer
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="submit"
                                                                    class="btn btn-success">Update</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="delete{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal" aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card text-center">
                                                        <div class="card-body">
                                                            <h4>Are You Sure</h4>
                                                        </div>
                                                        <form class="form-group" action="delete {{ $page }}"
                                                            method="POST" enctype="multipart/form-data">
                                                            <div class="card-body">
                                                                <!-- Details View Start -->
                                                                @csrf
                                                                <input type="hidden" name="id"
                                                                    value="{{ $row->id }}">
                                                                <!-- Details View End -->
                                                                <button type="button" class="btn btn-info"
                                                                    data-bs-dismiss="modal">No</button>
                                                                <button type="submit"
                                                                    class="btn btn-danger">Yes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- [ Data table ] end -->
                    </div>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->
<!-- Show modal content -->
<div id="import" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Upload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <div class="card-body">
                    <a href="#"><i class="fas fa-download"></i> Download Template</a>
                </div>

                <form class="form-group" action="upload {{ $page }}" method="POST"
                    enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="file"></label>
                            <input type="file" name="file" id="file" accept=".xlsx, .xls"
                                class="form-control">
                        </div>
                        <!-- Details View End -->
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Assign</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@php
    if (isset($_GET['department'])) {
        $dept = $_GET['department'];
    } else {
        $dept = session('department');
    }
@endphp
<!-- Show modal content -->
<div id="create" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Create</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group" action="create {{ $page }}" method="POST"
                    enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <div class="form-group">
                            <label for="username">Lecturer</label>
                            <input type="text" name="username" id="username" class="form-control"
                                placeholder="Enter SP/JP of the Course Lecturer" required>
                            {{-- <select class="form-control searchable-select" name="username" id="username" required>
                                <option value="">Select Option</option>
                                @foreach ($staff->where('department', $dept) as $staff)
                                    <option value="{{ $staff->username }}">{{ $staff->username }}:
                                        {{ $staff->name }}</option>
                                @endforeach
                            </select> --}}
                        <div class="form-group">
                            <label for="courseSearch">Course</label>
                            <input type="text" class="form-control" id="courseSearch" placeholder="Type to search courses...">
                        </div>
                        <div class="form-group" style="position: relative;">
                            <select class="form-control getAllocationPrograms" name="course" id="course" required style="opacity: 0; height: 0; position: absolute;">
                                <option value="">Select Option</option>
                                @foreach ($courses->where('department', $dept) as $course)
                                    <option value="{{ $course->code }}">{{ $course->code }}: {{ $course->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="list-group" id="courseResults" style="max-height: 200px; overflow-y: auto; display: none;"></div>
                        </div>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var courseSearch = document.getElementById('courseSearch');
                            var courseSelect = document.getElementById('course');
                            var courseResults = document.getElementById('courseResults');
                            var options = courseSelect.getElementsByTagName('option');
                            
                            // Store original options for reference
                            var originalOptions = [];
                            for (var i = 0; i < options.length; i++) {
                                originalOptions.push({
                                    value: options[i].value,
                                    text: options[i].text
                                });
                            }
                            
                            // Function to update results
                            function updateResults(filter) {
                                // Clear previous results
                                courseResults.innerHTML = '';
                                
                                // Convert filter to lowercase for case-insensitive matching
                                filter = filter.toLowerCase().trim();
                                
                                // If empty filter, hide results
                                if (filter === '') {
                                    courseResults.style.display = 'none';
                                    return;
                                }
                                
                                // Count matches
                                var matchCount = 0;
                                
                                // Add filtered options to results
                                for (var i = 0; i < originalOptions.length; i++) {
                                    // Skip the empty option
                                    if (originalOptions[i].value === "") continue;
                                    
                                    // Check if the text contains the search term
                                    if (originalOptions[i].text.toLowerCase().indexOf(filter) > -1) {
                                        var item = document.createElement('a');
                                        item.href = '#';
                                        item.className = 'list-group-item list-group-item-action';
                                        item.textContent = originalOptions[i].text;
                                        item.dataset.value = originalOptions[i].value;
                                        
                                        item.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            
                                            // Set the visible text
                                            courseSearch.value = this.textContent;
                                            
                                            // Update the hidden select value
                                            courseSelect.value = this.dataset.value;
                                            
                                            // Hide results
                                            courseResults.style.display = 'none';
                                            
                                            // Trigger change event for jQuery event handlers
                                            if (typeof jQuery !== 'undefined') {
                                                jQuery(courseSelect).trigger('change');
                                            } else {
                                                // Fallback to standard event
                                                var event = new Event('change');
                                                courseSelect.dispatchEvent(event);
                                            }
                                        });
                                        
                                        courseResults.appendChild(item);
                                        matchCount++;
                                    }
                                }
                                
                                // Show results if we have matches
                                if (matchCount > 0) {
                                    courseResults.style.display = 'block';
                                } else {
                                    courseResults.style.display = 'none';
                                }
                            }
                            
                            // Handle input events
                            courseSearch.addEventListener('input', function() {
                                updateResults(this.value);
                            });
                            
                            // Hide results when clicking outside
                            document.addEventListener('click', function(e) {
                                if (e.target !== courseSearch && e.target !== courseResults) {
                                    courseResults.style.display = 'none';
                                }
                            });
                            
                            // Show results when focusing on search
                            courseSearch.addEventListener('focus', function() {
                                if (this.value.trim() !== '') {
                                    updateResults(this.value);
                                }
                            });
                        });
                        </script>
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select name="type" id="type" class="form-control" required>
                                <option value="">Select Option</option>
                                <option value="Main">Course Coordinator</option>
                                <option value="Support">Course Lecturer</option>
                            </select>
                        </div>
                        {{-- session --}}
                        <div class="form-group">
                            <label for="session">Session</label>
                            <select name="session" id="session" class="form-control" required>
                                <option value="">Select Option</option>
                                <option value="2025/2026">2025/2026</option>
                                <option value="2024/2025">2024/2025</option>
                                <option value="2023/2024">2023/2024</option>
                                <option value="2022/2023">2022/2023</option>
                                <option value="2021/2022">2021/2022</option>
                                <option value="2020/2021">2020/2021</option>
                                <option value="2019/2020">2019/2020</option>
                                <option value="2018/2019">2018/2019</option>
                                <option value="2017/2018">2017/2018</option>
                                <option value="2016/2017">2016/2017</option>
                                <option value="2015/2016">2015/2016</option>
                                <option value="2014/2015">2014/2015</option>
                                <option value="2013/2014">2013/2014</option>
                                
                                
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="">Programs</label>
                            <div id="displayPrograms"></div>
                        </div>
                        <!-- Details View End -->
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
