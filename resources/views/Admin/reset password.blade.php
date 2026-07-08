@php
    use Illuminate\Support\Facades\DB;
    $userId = 0;
    $found = 0;
    if (isset($_GET['userId'])) {
        $userId = DB::table('users')->where('username', $_GET['userId'])->value('id');
        $getInvoice = DB::table('invoices')->where('username', $userId)->get();
    }
    if (isset($_GET['id'])) {
        DB::table('invoices')
            ->where(['username' => $_GET['id'], 'status' => 'pending'])
            ->update(['username' => $_GET['ids']]);
    }
@endphp
<!-- Start Content-->
<div class="card">
    <div class="card-body text-center">
        <div class="mb-4">
            <i class="feather icon-unlock auth-icon f-40"></i>
        </div>
        <h3 class="mb-4">Reset Password</h3>
        <form method="POST" action="reset-pass">
            @csrf
            <div class="input-group mb-3">
                <input id="username" type="text" class="form-control" name="username" required
                    placeholder="Enter ID Number or JAMB NO.">
            </div>
            <input type="submit" class="btn btn-primary shadow-2 mb-4" name="submit" value="Update">
        </form>
        <!-- Form End -->
        <br><br>

        @if (session('username') != 'SP4')

            <div class="card-block">
                <form class="needs-validation" novalidate method="GET" action="#">
                    @csrf
                    <div class="row gx-2">
                        <div class="form-group col-md-10">
                            <label for="username">ID Number</label>
                            <input type="text" name="userId" id="username" class="form-control" required>
                        </div>
                        <div class="form-group col-md-2">
                            <button type="submit" name="search" class="btn btn-info btn-filter"><i
                                    class="fas fa-search"></i> {{ 'Filter' }}</button>
                        </div>
                    </div>
                </form>
            </div>

            @isset($_GET['userId'])
                @forelse ($getInvoice as $item)
                    @php
                        $found = 1;
                    @endphp
                    <p>{{ $item->name }} | {{ $item->rrr }} | {{ number_format($item->amount, 2) }} |
                        {{ $item->status }}</p>
                    <hr>
                @empty
                    <div class="alert alert-info">
                        No Record Found!!!
                    </div>
                @endforelse
            @endisset
            @if ($found == 1)
                <center>
                    <form class="needs-validation" novalidate method="GET" action="#">
                        @csrf
                        <div class="row gx-2">
                            <div class="form-group col-md-10">
                                <input type="hidden" name="id" id="id" value="{{ $item->username }}">
                                <input type="hidden" name="ids" id="ids" value="{{ $_GET['userId'] }}">
                            </div>
                            <div class="form-group col-md-12">
                                <button type="submit" name="search" class="btn btn-danger"><i
                                        class="fas fa-delete"></i> {{ 'Delete' }}</button>
                            </div>
                        </div>
                    </form>
                </center>
            @endif
        @endif
    </div>
</div>
<!-- End Content-->
