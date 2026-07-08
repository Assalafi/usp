<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Bulk Upload {{ ucfirst($type) }}s</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Download the template, fill it with your data, then upload the CSV file.
            </div>

            <div class="mb-4">
                <a href="/reference-data/{{ $type }}/download-template" class="btn btn-success"><i class="fas fa-download"></i> Download Template</a>
            </div>

            <form action="/reference-data/{{ $type }}/process-bulk-upload" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>Upload CSV File</label>
                    <input type="file" name="file" class="form-control" accept=".csv" required>
                    <small class="text-muted">File should contain columns: Name, Order, Status</small>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload</button>
                <a href="/reference-data/{{ $type }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </form>
        </div>
    </div>
</div>
