<!DOCTYPE html>
<html lang="en">
<head>
    @include('css')
</head>
<body class="">
<!-- Start Content-->
@foreach ($data as $row)
    <div class="card border-success mb-3 col-md-4">
    <center>
      <div class="card-header bg-transparent border-success text-center">
        <h3>
            UNIVERSITY OF MAIDUGURI
        </h3>
        <h3>
            {{ session('description') }}
        </h3>
            
        PAYMENT DETAILS <br>
        ID No: {{ session('username') }}
        <br>
        <span class="card-text text-center">RRR:{{ $row -> rrr }}</span></div>
    </center>
      <div class="card-body text-info">
            
        <p class="card-text">Name: <span style="float: right;">{{ $row -> name }}</span></p>
        <hr>
        <p class="card-text">Phone: <span style="float: right;">{{ $row -> phone }}</span></p>
        <hr>
        <p class="card-text">Email: <span style="float: right;">{{ $row -> email }}</span></p>
        <hr>
        <p class="card-text">Program: <span style="float: right;">{{ $row -> program }}</span></p>
        <hr>
      </div>
      <div class="card-footer bg-transparent border-success text-center"></div>
    </div>
@endforeach
<!-- End Content-->
@include('js')

</body>

</html>

        