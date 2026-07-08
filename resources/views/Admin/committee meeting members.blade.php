<!-- Start Content-->
@php
    use Illuminate\Support\Facades\DB;
@endphp
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-header">
                        <h5>MEETING MEMBERS</h5>
                    </div>
                    <div class="card-header">
                        <h6>{{ $sub_committee }}</h6>
                    </div>
                    <div class="card-block">
                        <a href="#" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#create"><i
                                class="fas fa-upload"></i> {{ 'Upload Update' }}</a>
                        {{-- <button href="#" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#import"><i class="fas fa-upload"></i> {{ ('Import') }}</button> --}}
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-header">
                        <b>Start at:</b> {{ date(' D d M, Y h:i A', strtotime($start)) }}
                        <br>
                        <b>Ends at:</b> {{ date(' D d M, Y h:i A', strtotime($end)) }}
                        <br>
                        <hr>
                        <br>
                        <br>
                        <b>Meeting Agenda:</b> <a style="margin-left: 8px"
                            href="https://mozilla.github.io/pdf.js/web/viewer.html?file=https://umstad.online{{ $agenda1 }}">
                            <i style="font-size: 20px" class="fa fa-eye"></i></a>
                        <br>
                        <b>Last Minutes:</b> <a style="margin-left: 30px"
                            href="https://mozilla.github.io/pdf.js/web/viewer.html?file=https://umstad.online{{ $agenda2 }}">
                            <i style="font-size: 20px" class="fa fa-eye"></i></a>
                        <br>
                        <b>Papers:</b> <a style="margin-left: 70px" data-bs-toggle="modal"
                            data-bs-target="#displayPapers"> <i style="font-size: 20px" class="fa fa-eye"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="card">
                    <div class="card-block">
                        <!-- [ Data table ] start -->
                        <div class="table-responsive">
                            <table id="export-table" class="display table nowrap table-striped table-hover"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ 'Member' }}</th>
                                        <th>{{ 'Role' }}</th>
                                        <th>{{ 'Agenda' }}</th>
                                        <th>{{ 'Last Minute' }}</th>
                                        <th>{{ 'Paper' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($data as $row)
                                        @php
                                            $access = 'no';

                                            if(session('username') == $row->username || $role == 'CHAIRMAN' || $role == 'SECRETARY' || session('acc_type') == 'su') {
                                                $access = 'yes';
                                            }

                                            $agenda1 = '';
                                            $agenda2 = '';
                                            $act_id = '';
                                            $acts = DB::table('committee_meeting_activities')
                                                ->where(['meeting_id' => $meeting_id, 'username' => $row->username])
                                                ->get();
                                            foreach ($acts as $act) {
                                                $agenda1 = $act->agenda1;
                                                $agenda2 = $act->agenda2;
                                                $act_id = $act->id;
                                            }
                                            $paperr = DB::table('committee_uploads')
                                                ->where([
                                                    'row_id' => $act_id,
                                                    'table_name' => 'committee_meeting_activities',
                                                ])
                                                ->get();

                                        @endphp
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>
                                                <strong>{{ $row->username }}</strong>
                                                <br>
                                                <i style="font-size: 10px">{{ $row->name }}</i>
                                            </td>

                                            <td>{{ $row->role }}</td>
                                            @if ($access == 'no')
                                                <td>NO ACCESS</td>
                                            @else
                                                @if ($agenda1 == 'none' || $agenda1 == '' || $agenda1 == null)
                                                    <td>NO UPDATE</td>
                                                @else
                                                    <td><a
                                                            href="https://mozilla.github.io/pdf.js/web/viewer.html?file=https://umstad.online{{ $agenda1 }}">{{ $sub_committee }}</a>
                                                    </td>
                                                @endif
                                            @endif

                                            @if ($access == 'no')
                                                <td>NO ACCESS</td>
                                            @else
                                                @if ($agenda2 == 'none' || $agenda2 == '' || $agenda2 == null)
                                                    <td>NO UPDATE</td>
                                                @else
                                                    <td><a
                                                            href="https://mozilla.github.io/pdf.js/web/viewer.html?file=https://umstad.online{{ $agenda2 }}">LAST
                                                            MINUTES</a></td>
                                                @endif
                                            @endif

                                            @if ($access == 'no')
                                                <td>NO ACCESS</td>
                                            @else
                                                <td>
                                                    <a style="margin-left: 70px" data-bs-toggle="modal"
                                                        data-bs-target="#displayPaper{{ $row->id }}"> <i
                                                            style="font-size: 20px" class="fa fa-eye"></i><sup>
                                                            {{ count($paperr) }}</sup></a>
                                                </td>
                                            @endif


                                        </tr>
                                        <div id="displayPaper{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-md" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Papers Review by
                                                            {{ $row->username }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"><span
                                                                aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="card">
                                                        <div class="card-body">
                                                            @forelse ($paperr as $item)
                                                                <p>{{ $loop->iteration }}. <a style="margin-left: 30px"
                                                                        href="https://mozilla.github.io/pdf.js/web/viewer.html?file=https://umstad.online{{ $item->file_path }}">
                                                                        {{ $item->title }}</a></p>
                                                            @empty
                                                                <p>No Papers Uploaded!!!</p>
                                                            @endforelse
                                                        </div>
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

<!-- Show modal content -->
<div id="create" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Upload Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <form class="form-group" action="/create committee meetings" method="POST"
                    enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <input type="hidden" name="page" value="member">
                        <input type="hidden" name="meeting_id" value="{{ $meeting_id }}">
                        <div class="form-group">
                            <label for="agenda1">Agenda</label>
                            <input type="file" name="agenda1" id="agenda1" accept=".pdf" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="agenda2">Last Minutes</label>
                            <input type="file" name="agenda2" id="agenda2" accept=".pdf"
                                class="form-control">
                        </div>

                        <!-- Dynamic Papers Section -->
                        <div id="papers-container">
                            <!-- Dynamic Paper Inputs Will Be Added Here -->
                        </div>
                        <button type="button" id="add-paper-btn" class="btn btn-primary">
                            Add Paper
                        </button>
                        <!-- Details View End -->
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                        {{-- <button type="submit" class="btn btn-success">Create</button> --}}

                        <button type="submit" id="submitt" onclick="submittt()"
                            class="btn btn-primary">Create</button>
                        <button id="loadingg" style="display: none" class="btn btn-primary" type="button"
                            disabled>
                            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                            <span role="status">Creating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Show modal content -->
<div id="displayPapers" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Papers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="card">
                <div class="card-body">
                    @forelse ($papers as $item)
                        <p style="margin-top: 10px">{{ $loop->iteration }}. <a style="margin-left: 30px"
                                href="https://mozilla.github.io/pdf.js/web/viewer.html?file=https://umstad.online{{ $item->file_path }}">
                                {{ $item->title }}</a></p>
                    @empty
                        <p>No Papers Uploaded!!!</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function submittt() {
        // var name = document.getElementById('sub_committee').value;
        // alert(name);
        //if (name != '') {
        document.getElementById('submitt').style.display = 'none';
        document.getElementById('loadingg').style.display = 'inline';
        //}
    }

    document.addEventListener("DOMContentLoaded", function() {
        const papersContainer = document.getElementById("papers-container");
        const addPaperBtn = document.getElementById("add-paper-btn");

        // PHP array passed as a JavaScript variable
        const papersTitles = <?php echo json_encode($papersTitles); ?>;
        let paperIndex = 0;

        addPaperBtn.addEventListener("click", function() {
            paperIndex++;
            const paperDiv = document.createElement("div");
            paperDiv.className = "form-group mt-2";
            paperDiv.innerHTML = `
                <div class="d-flex align-items-center">
                    <select name="papers[${paperIndex}][title]" class="form-control me-2" required>
                        <option value="" disabled selected>Select Paper Title</option>
                        ${papersTitles.map(title => `<option value="${title}">${title}</option>`).join('')}
                    </select>
                    <input type="file" name="papers[${paperIndex}][file]" accept=".pdf" class="form-control me-2" required>
                    <button type="button" class="btn btn-danger btn-sm remove-paper-btn">Remove</button>
                </div>
            `;
            papersContainer.appendChild(paperDiv);

            // Add event listener for the remove button
            paperDiv.querySelector(".remove-paper-btn").addEventListener("click", function() {
                papersContainer.removeChild(paperDiv);
            });
        });
    });
</script>
