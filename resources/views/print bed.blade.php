<!DOCTYPE html>
<html lang="en">
<head>
    @include('css')
</head>
<body class="">

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <!-- Start Content-->
                    @foreach ($data as $row)
                    <div class="card border-success mb-3 col-md-4">
                    <center>
                        
                      <div class="card-header bg-transparent border-success text-center">
                        <h2>
                            UNIVERSITY OF MAIDUGURI
                        </h2>
                        <h3>
                            STUDENT AFFAIRS DIVISION
                        </h3>
                            
                        BED SPACE RESERVATION DETAILS <br>
                        ID No: {{ $row -> occupant }}
                        <br>
                        <span class="card-text text-center">Ref ID:00{{ $row -> id }}</span></div>
                    </center>
                      <div class="card-body text-info">
                            
                        <p class="card-text">Hall: <span style="float: right;">{{ $row -> hall }}</span></p>
                        <hr>
                        <p class="card-text">Block: <span style="float: right;">{{ $row -> block }}</span></p>
                        <hr>
                        <p class="card-text">Room: <span style="float: right;">{{ $row -> room }}</span></p>
                        <hr>
                        <p class="card-text">Bed: <span style="float: right;">{{ $row -> bed }}</span></p>
                        <hr>
                      </div>
                      <div class="card-footer bg-transparent border-success text-center"></div>
                    </div>
                    @endforeach
                    <!-- End Content-->
                </div>
            </div>
        </div>
    </div>

@include('js')

</body>

</html>

        