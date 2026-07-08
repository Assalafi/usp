<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Manage {{ ucfirst($type) }}s</h5>
            <div>
                <a href="/reference-data/{{ $type }}/bulk-upload" class="btn btn-info btn-sm"><i class="fas fa-upload"></i> Bulk Upload</a>
                <a href="/reference-data/{{ $type }}/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add New</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Name</th>
                            <th width="80">Order</th>
                            <th width="80">Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->order }}</td>
                            <td>
                                @if($item->status == '1')
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="/reference-data/{{ $type }}/{{ $item->id }}/edit" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                <form action="/reference-data/{{ $type }}/{{ $item->id }}/delete" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        @if($data->count() == 0)
                        <tr>
                            <td colspan="5" class="text-center">No records found</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
