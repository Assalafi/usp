
                <!-- Start Content-->
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-bed f-40"></i>
                        </div>
                        <h3 class="mb-4">Validate Hostel Pin</h3>
                        @if(session('id_number') != null && session('id_number') != '')
                        <!-- Form Start -->
                        <form method="POST" action="V-H-Pin">
                        @csrf
                            <input id="email" type="hidden" class="form-control" name="email" value="{{ session('id_number') }}" required autocomplete="email" placeholder="Enter ID Number">

                            <div class="input-group mb-4">
                                <input id="password" type="text" class="form-control" name="password" required autocomplete="current-password" placeholder="Enter PIN" autofocus>
                            </div>
                            <select class="form-control" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="MALE">Male</option>
                                <option value="FEMALE">Female</option>
                            </select>
                            <br>
                            <hr>

                            <input type="submit" class="btn btn-primary shadow-2 mb-4" name="submit" value="Validate">
                        </form>
                        <!-- Form End -->
                        @else
                            <div class="alert alert-info">
                                You need to have ID NO. before validating your PIN.
                            </div>
                        @endif
                    </div>
                </div>
                <!-- End Content-->
