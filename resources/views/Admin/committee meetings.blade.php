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
                        <a href="#" class="btn btn-primary createAction" data-bs-toggle="modal"
                            data-bs-target="#create"><i class="fas fa-plus"></i> {{ 'Add New' }}</a>
                        {{-- <button href="#" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#import"><i class="fas fa-upload"></i> {{ ('Import') }}</button> --}}
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
                                        {{-- <th>{{ 'Committee' }}</th> --}}
                                        <th>{{ 'Agenda' }}</th>
                                        <th>{{ 'Last Minute' }}</th>
                                        <th>{{ 'Start At' }}</th>
                                        <th>{{ 'End At' }}</th>
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
                                            {{-- <td>{{ $row->committee }}</td> --}}

                                            <td><a
                                                    href="https://mozilla.github.io/pdf.js/web/viewer.html?file=https://umstad.online{{ $row->agenda1 }}">{{ $row->sub_committee }}</a>
                                            </td>
                                            <td><a
                                                    href="https://mozilla.github.io/pdf.js/web/viewer.html?file=https://umstad.online{{ $row->agenda2 }}">Last
                                                    Minutes</a></td>

                                            {{-- <td><a href="{{ url('/pdf-viewer/pdf-viewers/web/viewer.html?file=' . urlencode($row->agenda1)) }}">
                                                    {{ $row->sub_committee }}
                                                </a></td>
                                            <td><a href="{{ url('/pdf-viewer/pdf-viewers/web/viewer.html?file=' . urlencode($row->agenda2)) }}">Last Minutes</a></td> --}}

                                            <td>{{ date(' D d M, Y h:i A', strtotime($row->start_at)) }}</td>
                                            <td>{{ date(' D d M, Y h:i A', strtotime($row->end_at)) }}</td>
                                            <td>
                                                <a href="/committee-meeting-members/{{ $row->id }}"
                                                    class="btn btn-icon btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button"
                                                    class="btn btn-icon btn-danger btn-sm deleteAction"
                                                    data-bs-toggle="modal" data-bs-target="#delete{{ $row->id }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Show modal content -->
                                        <div id="delete{{ $row->id }}" class="modal fade" tabindex="-1"
                                            role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-sm" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myModalLabel">Warning...</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"><span
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

<!-- Show modal content -->
<div id="create" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Create</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="card">
                <form class="form-group" action="create {{ $page }}" method="POST"
                    enctype="multipart/form-data">
                    <div class="card-body">
                        <!-- Details View Start -->
                        @csrf
                        <input type="hidden" name="committee" value="none">
                        <div class="form-group">
                            <label for="sub_committee">Committee</label>
                            <select name="sub_committee" id="sub_committee" lang="1"
                                class="form-control committee" required>
                                <option value="">Select Option</option>
                                @foreach ($sub_committee as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="start_at">Start at</label>
                            <input type="datetime-local" name="start_at" id="start_at" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="end_at">End at</label>
                            <input type="datetime-local" name="end_at" id="end_at" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="agenda1">Agenda</label>
                            <input type="file" name="agenda1" id="agenda1" accept=".doc,.docx" required
                                class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="agenda2">Last Minutes</label>
                            <input type="file" name="agenda2" id="agenda2" accept=".doc,.docx" required
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

<script>
    function submittt() {
        var name = document.getElementById('sub_committee').value;
        //alert(name);
        if (name != '') {
            document.getElementById('submitt').style.display = 'none';
            document.getElementById('loadingg').style.display = 'inline';
        }

    }

    document.addEventListener("DOMContentLoaded", function() {
        const papersContainer = document.getElementById("papers-container");
        const addPaperBtn = document.getElementById("add-paper-btn");

        let paperIndex = 0;

        addPaperBtn.addEventListener("click", function() {
            paperIndex++;
            const paperDiv = document.createElement("div");
            paperDiv.className = "form-group mt-2";
            paperDiv.innerHTML = `
                <div class="d-flex align-items-center">
                    <input type="text" name="papers[${paperIndex}][title]" placeholder="Paper Title" class="form-control me-2" required>
                    <input type="file" name="papers[${paperIndex}][file]" accept=".doc,.docx" class="form-control me-2" required>
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
