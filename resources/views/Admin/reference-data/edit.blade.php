<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit {{ ucfirst($type) }}</h5>
        </div>
        <div class="card-body">
            <form action="/reference-data/{{ $type }}/{{ $item->id }}/update" method="POST">
                @csrf
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>
                </div>
                <div class="form-group">
                    <label>Order</label>
                    <input type="number" name="order" class="form-control" value="{{ $item->order }}">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="1" {{ $item->status == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ $item->status == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                <a href="/reference-data/{{ $type }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </form>
        </div>
    </div>
</div>
