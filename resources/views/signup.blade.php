<div class="row">
<!-- subscribe start -->
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h5 onclick="testts()">Staffs List </h5>
        </div>
        <div class="card-body">
            <div class="row align-items-center m-l-0">
                <div class="col-sm-6">
                </div>
                <div class="col-sm-6 text-right">
                    <button class="btn btn-success btn-sm mb-3 btn-round has-ripple" data-toggle="modal" data-target="#modal-report"><i class="feather icon-plus"></i> Add Staff</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="report-table" class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Roll</th>
                            <th>Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $row)
                            <tr>
                                <td>{{ $row->name }}</td>
                                <td>{{ $row->phone }}</td>
                                <td>{{ $row->email }}</td>
                                <td>{{ $row->position }}</td>
                                <td>
                                    <a href="#!" class="btn btn-info btn-sm" data-toggle="modal" data-target="#update{{ $row->id }}">Update</a>
                                    <a href="#!" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>

                            <div class="modal fade" id="update{{ $row->id }}" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Add</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="/update{{ $page }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $row->id }}">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <h5>Staff Information</h5>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label class="floating-label" for="Name">Name</label>
                                                            <input type="text" class="form-control" id="Name" name="name" value="{{ $row->name }}" placeholder="" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label class="floating-label" for="phone">Phone</label>
                                                            <input type="number" class="form-control" id="phone" name="phone" value="{{ $row->phone }}" placeholder="" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group fill">
                                                            <label class="floating-label" for="Email">Email</label>
                                                            <input type="email" class="form-control" id="Email" name="email" value="{{ $row->email }}" placeholder="" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group fill">
                                                            <label class="floating-label" for="Password">Password</label>
                                                            <input type="text" class="form-control" id="Password" name="password" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="form-group">
                                                            <label class="floating-label" for="Address">Address</label>
                                                            <textarea class="form-control" id="Address" name="address" rows="3" required>{{ $row->address }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label class="floating-label" for="shop">Select Shop</label>
                                                            <select class="form-control" id="shop" name="shop" required>
                                                                <option value="{{ $row->shop }}">Selected Shop:{{ $row->shop }}</option>
                                                                <option value="drinks">Drinks</option>
                                                                <option value="food">Foods</option>
                                                                <option value="shisha">Shisha</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label class="floating-label" for="roll">Select Roll</label>
                                                            <select class="form-control" id="roll" name="roll" required>
                                                                <option value="{{ $row->position }}">Selected Roll:{{ $row->position }}</option>
                                                                <option value="manager">Manager</option>
                                                                <option value="cashier">Cashier</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <button style="width: 100%;" class="btn btn-primary">Update</button>
                                                    </div>
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
        </div>
    </div>
</div>
<!-- subscribe end -->
</div>

<div class="modal fade" id="modal-report" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="/add{{ $page }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <h5>Staff Information</h5>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="floating-label" for="Name">Name</label>
                                <input type="text" class="form-control" id="Name" name="name" placeholder="" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="floating-label" for="phone">Phone</label>
                                <input type="number" class="form-control" id="phone" name="phone" placeholder="" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group fill">
                                <label class="floating-label" for="Email">Email</label>
                                <input type="email" class="form-control" id="Email" name="email" placeholder="" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group fill">
                                <label class="floating-label" for="Password">Password</label>
                                <input type="text" class="form-control" id="Password" name="password" placeholder="" required>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="floating-label" for="Address">Address</label>
                                <textarea class="form-control" id="Address" name="address" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="floating-label" for="shop">Select Shop</label>
                                <select class="form-control" id="shop" name="shop" required>
                                    <option value="">Select Shop</option>
                                    <option value="drinks">Drinks</option>
                                    <option value="food">Foods</option>
                                    <option value="shisha">Shisha</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="floating-label" for="roll">Select Roll</label>
                                <select class="form-control" id="roll" name="roll" required>
                                    <option value="">Select Roll</option>
                                    <option value="manager">Manager</option>
                                    <option value="cashier">Cashier</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <button style="width: 100%;" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>