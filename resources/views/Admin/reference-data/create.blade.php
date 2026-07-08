<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add New {{ ucfirst($type) }}</h5>
        </div>
        <div class="card-body">
            <form action="/reference-data/{{ $type }}/store" method="POST">
                @csrf
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Order</label>
                    <input type="number" name="order" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                <a href="/reference-data/{{ $type }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </form>
        </div>
    </div>
</div>
