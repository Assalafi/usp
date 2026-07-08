
<!-- Start Content-->
<div class="card">
    <div class="card-body text-center">
        <div class="mb-4">
            <i class="feather icon-unlock auth-icon"></i>
        </div>
        <h3 class="mb-4">Change Password</h3>

        <!-- Form Start -->
        <form method="POST" action="/update password">
        @csrf
            <div class="input-group mb-3">
                <input id="p1" type="password" class="form-control" name="p1" required placeholder="Password">
            </div>
            <div class="input-group mb-4">
                <input id="p2" type="password" class="form-control" name="p2" required placeholder="Confirm Password">
            </div>
            <input type="submit" class="btn btn-primary shadow-2 mb-4" name="submit" value="Update">
        </form>
        <!-- Form End -->
    </div>
</div>
<!-- End Content-->
