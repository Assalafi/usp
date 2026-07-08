@php
    if(isset($_GET['committee'])){
        $data = $data->where('sub_committee', $_GET['committee']);
    }
@endphp
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
                            @if (isset($_GET['committee']))
                                <table id="export-table" class="display table nowrap table-striped table-hover"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ 'SP' }}</th>
                                            <th>{{ 'Name' }}</th>
                                            <th>{{ 'Committee' }}</th>
                                            <th>{{ 'Role' }}</th>
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
                                                <td>{{ $row->sub_committee }}</td>
                                                <td>{{ $row->role }}</td>
                                                <td>
                                                    <a href="#"
                                                        class="btn btn-icon btn-primary btn-sm updateAction"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#updateStudent{{ $row->id }}">
                                                        <i class="far fa-edit"></i>
                                                    </a>

                                                    <button type="button"
                                                        class="btn btn-icon btn-danger btn-sm deleteAction"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#delete{{ $row->id }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Show modal content -->
                                            <div id="updateStudent{{ $row->id }}" class="modal fade"
                                                tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                                                aria-hidden="true">
                                                <div class="modal-dialog modal-sm" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="myModalLabel">Update</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"><span
                                                                    aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <div class="card">
                                                            <form class="form-group"
                                                                action="update {{ $page }}" method="POST"
                                                                enctype="multipart/form-data">
                                                                <div class="card-body">
                                                                    <!-- Details View Start -->
                                                                    @csrf
                                                                    <input type="hidden" name="id"
                                                                        value="{{ $row->id }}">
                                                                    <div class="form-group">
                                                                        <label for="role">Role</label>
                                                                        <select name="role" id="role"
                                                                            class="form-control" required>
                                                                            <option value="{{ $row->role }}">
                                                                                Current: {{ $row->role }}</option>
                                                                            @foreach ($role as $item)
                                                                                <option value="{{ $item->name }}">
                                                                                    {{ $item->name }}</option>
                                                                            @endforeach
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
                                                            <form class="form-group"
                                                                action="delete {{ $page }}" method="POST"
                                                                enctype="multipart/form-data">
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
                            @else
                            <table id="export-table" class="display table nowrap table-striped table-hover"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ 'Committee' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp
                                    @foreach ($sub_committee as $row)
                                        <tr>
                                            <td>{{ $sn++ }}</td>
                                            <td>
                                                <a href="/committee membership?committee={{ $row->name }}">
                                                    {{ $row->name }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @endif

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

<style>
    .custom-select {
        position: relative;
        display: inline-block;
        width: 100%;
        /* Set the width as needed */
    }

    .custom-select .select-selected {
        background-color: white;
        padding: 10px;
        border: 1px solid #ccc;
        cursor: pointer;
    }

    .select-items {
        display: none;
        position: absolute;
        background-color: white;
        border: 1px solid #ccc;
        z-index: 99;
        max-height: 450px;
        overflow-y: auto;
        width: 100%;
        /* Match the width of the selected item */
    }

    .select-items label {
        display: block;
        /* Ensure labels take full width */
        padding: 8px;
        /* Padding for the entire label */
        cursor: pointer;
        /* Pointer cursor on hover */
    }

    .select-items label:hover {
        background-color: #f1f1f1;
        /* Hover effect */
    }

    .search-input {
        padding: 8px;
        width: calc(100% - 16px);
        /* Adjust for padding */
        box-sizing: border-box;
    }
</style>

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
                        <input type="hidden" name="committee" value="none">
                        <div class="form-group">
                            <label for="sub_committee">Committee</label>
                            <select name="sub_committee" id="sub_committee" lang="1" class="form-control committee"
                                required>
                                <option value="">Select Option</option>
                                @foreach ($sub_committee as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select name="role" id="role" class="form-control" required>
                                <option value="">Select Option</option>
                                @foreach ($role as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="custom-select">
                                <div class="select-selected">Select Staff</div>
                                <div class="select-items">
                                    <input type="text" class="search-input  form-control" placeholder="Search...">
                                    @foreach ($staff as $item)
                                        <label><input type="checkbox" name="staff[]"
                                                value="{{ $item->username }}"> {{ $item->username }}:
                                            {{ $item->name }}</label>
                                    @endforeach
                                </div>
                            </div>
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

<script>
    const selected = document.querySelector('.select-selected');
    const items = document.querySelector('.select-items');
    const searchInput = document.querySelector('.search-input');
    const checkboxes = items.querySelectorAll('label');

    selected.addEventListener('click', function() {
        items.style.display = items.style.display === 'block' ? 'none' : 'block';
        searchInput.value = ''; // Clear search on opening
        filterItems(); // Reset filter on toggle
    });

    document.addEventListener('click', function(e) {
        if (!e.target.matches('.select-selected') && !e.target.matches('.select-items *')) {
            items.style.display = 'none';
        }
    });

    searchInput.addEventListener('keyup', function() {
        filterItems();
    });

    function filterItems() {
        const filter = searchInput.value.toLowerCase();
        let hasVisibleItems = false;

        checkboxes.forEach(label => {
            const text = label.textContent || label.innerText;
            if (text.toLowerCase().includes(filter)) {
                label.style.display = ''; // Show item
                hasVisibleItems = true; // Mark that we have at least one visible item
            } else {
                label.style.display = 'none'; // Hide item
            }
        });
    }
</script>
