<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!-- Required Js -->
<script src="{{ asset('dashboard/plugins/jquery/js/jquery.min.js') }}"></script>
<script src="{{ asset('dashboard/plugins/popper/js/popper.min.js') }}"></script>
<script src="{{ asset('dashboard/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('dashboard/plugins/jquery-scrollbar/js/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('dashboard/js/pcoded.min.js') }}"></script>

<!-- datatable Js -->
<script src="{{ asset('dashboard/plugins/data-tables/js/datatables.min.js') }}"></script>

<!-- form-validation Js -->
<script src="{{ asset('dashboard/js/pages/form-validation.js') }}"></script>

<!-- material datetimepicker Js -->
<script src="{{ asset('dashboard/plugins/moment/js/moment-with-locales.min.js') }}"></script>
<script src="{{ asset('dashboard/plugins/material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}"></script>

<!-- toastr Js -->
<script src="{{ asset('dashboard/plugins/toastr/js/toastr.min.js') }}"></script>
<script src="{{ url('assets/js/plugins/sweetalert.min.js') }}"></script>
<script src="{{ url('assets/js/pages/ac-alert.js') }}"></script>

{{-- <script src="{{ asset('assets/sdata/dist/js/jquery-searchbox.js') }}"></script> --}}
<!-- Toastr message display -->
{{-- @toastr_render --}}

{{-- <script type="text/javascript">
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr["error"]("{{ $error }}");
            @endforeach
        @endif
    </script> --}}

<!-- page js -->
{{-- @yield('page_js') --}}
@if (session('success'))
    <script>
        swal("", "{{ session('success') }}", "success");
    </script>
@endif
@if (session('info'))
    <script>
        swal("", "{{ session('info') }}", "info");
    </script>
@endif
@if (session('error'))
    <script>
        swal("Oops!!!", "{{ session('error') }}", "error");
    </script>
@endif
<script>
    var mainbody = document.getElementById('mainbody');
    var wait = document.getElementById('wait');
    let _token = $('input[name="_token"]').val();

    $(document).on('change', '#filterHall', function() {
        let _url = 'filter hall';
        var hall = this.value;
        var type = document.getElementById('type').value;
        var page = document.getElementById('page').value;
        mainbody.style.display = "none";
        wait.style.display = "block";
        $('#export-table').DataTable().clear().destroy();
        //swal("Oops!!!", "You Must Select SHOP", "error");
        $.ajax({
            type: 'POST',
            url: _url,
            data: {
                hall: hall,
                type: type,
                page: page,
                _token: _token
            },
            success: function(data) {
                //alert('Hi');
                $("#getFilter").html(data);

                $('#export-table').DataTable({
                    dom: 'Bfrtip',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i>',
                        exportOptions: {
                            columns: ':not(:last-child)',
                        }
                    }, ]
                });
                mainbody.style.display = "block";
                wait.style.display = "none";
            }

        });

        //window.location.href='/products';
    });

    $(document).on('change', '#filterCategory', function() {
        let _url = 'filter category';
        var category = this.value;
        var type = document.getElementById('type').value;
        var page = document.getElementById('page').value;
        mainbody.style.display = "none";
        wait.style.display = "block";
        $('#export-table').DataTable().clear().destroy();
        //swal("Oops!!!", "You Must Select SHOP", "error");
        $.ajax({
            type: 'POST',
            url: _url,
            data: {
                category: category,
                type: type,
                page: page,
                _token: _token
            },
            success: function(data) {
                //alert('Hi');
                $("#getFilter").html(data);

                $('#export-table').DataTable({
                    dom: 'Bfrtip',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i>',
                        exportOptions: {
                            columns: ':not(:last-child)',
                        }
                    }, ]
                });
                mainbody.style.display = "block";
                wait.style.display = "none";
            }

        });

        //window.location.href='/products';
    });

    $(document).on('change', '#hall', function() {
        let _url = 'block';
        var hall = this.value;
        mainbody.style.display = "none";
        wait.style.display = "block";
        //swal("Oops!!!", "You Must Select SHOP", "error");
        $.ajax({
            type: 'POST',
            url: _url,
            data: {
                hall: hall,
                _token: _token
            },
            success: function(data) {
                $("#block").html(data);
                $("#room").html('<option value="">Select Block First</option>');
                $("#bed").html('<option value="">Select Room First</option>');
                mainbody.style.display = "block";
                wait.style.display = "none";
            }

        });

        //window.location.href='/products';
    });

    $(document).on('change', '#block', function() {
        let _url = 'room';
        var block = this.value;
        var hall = document.getElementById('hall').value;
        mainbody.style.display = "none";
        wait.style.display = "block";
        //swal("Oops!!!", "You Must Select SHOP", "error");
        $.ajax({
            type: 'POST',
            url: _url,
            data: {
                hall: hall,
                block: block,
                _token: _token
            },
            success: function(data) {
                $("#room").html(data);
                $("#bed").html('<option value="">Select Room First</option>');
                mainbody.style.display = "block";
                wait.style.display = "none";
            }

        });

        //window.location.href='/products';
    });

    $(document).on('change', '#room', function() {
        let _url = 'bed';
        var room = this.value;
        var block = document.getElementById('block').value;
        var hall = document.getElementById('hall').value;
        mainbody.style.display = "none";
        wait.style.display = "block";
        //swal("Oops!!!", "You Must Select SHOP", "error");
        $.ajax({
            type: 'POST',
            url: _url,
            data: {
                hall: hall,
                block: block,
                room: room,
                _token: _token
            },
            success: function(data) {
                $("#bed").html(data);
                mainbody.style.display = "block";
                wait.style.display = "none";
            }

        });

        //window.location.href='/products';
    });


    $(document).on('change', '#hall2', function() {
        let _url = 'block';
        var hall = this.value;
        mainbody.style.display = "none";
        wait.style.display = "block";
        //swal("Oops!!!", "You Must Select SHOP", "error");
        $.ajax({
            type: 'POST',
            url: _url,
            data: {
                hall: hall,
                _token: _token
            },
            success: function(data) {
                $("#block2").html(data);
                $("#room2").html('<option value="">Select Block First</option>');
                $("#bed2").html('<option value="">Select Room First</option>');
                mainbody.style.display = "block";
                wait.style.display = "none";
            }

        });

        //window.location.href='/products';
    });

    $(document).on('change', '#block2', function() {
        let _url = 'room';
        var block = this.value;
        var hall = document.getElementById('hall2').value;
        mainbody.style.display = "none";
        wait.style.display = "block";
        //swal("Oops!!!", "You Must Select SHOP", "error");
        $.ajax({
            type: 'POST',
            url: _url,
            data: {
                hall: hall,
                block: block,
                _token: _token
            },
            success: function(data) {
                $("#room2").html(data);
                $("#bed2").html('<option value="">Select Room First</option>');
                mainbody.style.display = "block";
                wait.style.display = "none";
            }

        });

        //window.location.href='/products';
    });

    $(document).on('change', '#room2', function() {
        let _url = 'bed';
        var room = this.value;
        var block = document.getElementById('block2').value;
        var hall = document.getElementById('hall2').value;
        mainbody.style.display = "none";
        wait.style.display = "block";
        //swal("Oops!!!", "You Must Select SHOP", "error");
        $.ajax({
            type: 'POST',
            url: _url,
            data: {
                hall: hall,
                block: block,
                room: room,
                _token: _token
            },
            success: function(data) {
                $("#bed2").html(data);
                mainbody.style.display = "block";
                wait.style.display = "none";
            }

        });

        //window.location.href='/products';
    });


    var fixedClass;

    $(document).on('change', '.fixedClass', function() {
        let _url = '/description-ajax';
        var clas = this.value;
        fixedClass = this.lang;
        //alert(this.lang);
        $.ajax({
            type: 'POST',
            url: _url,
            data: {
                class: clas,
                _token: _token
            },
            success: function(data) {
                //alert(data);
                $("#description" + fixedClass).html(data);
            }

        });

        //window.location.href='/products';
    });
    var faculty;
    var department;
    var program;
    var committee;
    var sub_committee;
    var members;
    $(document).on('change', '.faculty', function() {
        let _url = '/department ajax';
        var faculty1 = this.value;
        program = this.lang;
        department = this.lang;
        faculty = this.lang;
        //alert(this.lang);
        // mainbody.style.display = "none";
        // wait.style.display = "block";
        $.ajax({
            type: 'POST',
            url: _url,
            data: {
                faculty: faculty1,
                _token: _token
            },
            success: function(data) {
                //swal("Oops!!!", "You Must Select SHOP", "error");
                $("#department" + department).html(data);
                // mainbody.style.display = "block";
                // wait.style.display = "none";
            }

        });

        //window.location.href='/products';
    });

    $(document).on('change', '.department', function() {
        let _url = '/program ajax';
        var dept = this.value;
        var faculty1 = document.getElementById('faculty' + faculty).value;
        // mainbody.style.display = "none";
        // wait.style.display = "block";
        //swal("Oops!!!", dept, "error");
        $.ajax({
            type: 'POST',
            url: _url,
            data: {
                faculty: faculty1,
                dept: dept,
                _token: _token
            },
            success: function(data) {
                $("#program" + program).html(data);
            }

        });

        //window.location.href='/products';
    });

    $(document).on('change', '.program', function() {
        let _url = '/course ajax';
        var pro = this.value;
        var dept = document.getElementById('department' + department).value;
        var faculty1 = document.getElementById('faculty' + faculty).value;
        // mainbody.style.display = "none";
        // wait.style.display = "block";
        //swal("Oops!!!", dept, "error");
        $.ajax({
            type: 'POST',
            url: _url,
            data: {
                faculty: faculty1,
                dept: dept,
                program: pro,
                _token: _token
            },
            success: function(data) {
                $("#course" + program).html(data);
            }

        });

        //window.location.href='/products';
    });


    $(document).on('change', '.committee', function() {
        let _url = 'sub-committee-ajax';
        var committee1 = this.value;
        members = this.lang;
        sub_committee = this.lang;
        committee = this.lang;
        //alert(committee);
        $.ajax({
            type: 'POST',
            url: _url,
            data: {
                committee: committee1,
                _token: _token
            },
            success: function(data) {
                $("#sub_committee" + sub_committee).html(data);
            }

        });

        //window.location.href='/products';
    });

    $(document).on('change', '.sub_committee', function() {
        let _url = 'members-ajax';
        var sub_com = this.value;
        var committee1 = document.getElementById('committee' + committee).value;
        $.ajax({
            type: 'POST',
            url: _url,
            data: {
                committee: committee1,
                sub_com: sub_com,
                _token: _token
            },
            success: function(data) {
                $("#members" + members).html(data);
            }

        });

        //window.location.href='/products';
    });

    $(document).on('change', '.getDate', function() {
        let _url = '/attendance-getDate';
        var date = this.value;
        var code = document.getElementById('hiddenCode').value;
        $.ajax({
            type: 'GET',
            url: _url,
            data: {
                date: date,
                code: code,
                _token: _token
            },
            success: function(data) {
                $("#displayRecord").html(data);
            }

        });
    });

    $(document).on('change', '.getAllocationPrograms', function() {
        let _url = '/allocation-programs';
        var course = this.value;
        $.ajax({
            type: 'GET',
            url: _url,
            data: {
                course: course,
                _token: _token
            },
            success: function(data) {
                //alert('Hiii');
                $("#displayPrograms").html(data);
            }

        });
    });
</script>

<script type="text/javascript">
    //swal("Oops!!!", "You Must Select SHOP", "info");
    $(document).ready(function() {
        // Date Picker
        $('.date').bootstrapMaterialDatePicker({
            setDate: new Date(),
            weekStart: 0,
            time: false
        });

        // Time Picker
        $('.time').bootstrapMaterialDatePicker({
            date: false,
            format: 'HH:mm'
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        // [ Zero-configuration ] start
        $('#basic-table').DataTable();
        $('#basic-table2').DataTable();
        // $('.sd').selectstyle({
        // width  : 250,
        // height : 300,
        // });
        //$('.select-data').searchBox();
        // $('.select-data').searchBox({
        //     elementWidth: '100%'
        // });

        // [ HTML5-Export ] start
        $('#export-table').DataTable({
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i>',
                exportOptions: {
                    columns: ':not(:last-child)',
                }
            }, ]
        });
        $('.dtt').DataTable({
            dom: 'Bfrtip',
            buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i>',
                exportOptions: {
                    columns: ':not(:last-child)',
                }
            }, ]
        });
    });

    var rdata = $('#example').DataTable();

    rdata.clear().destroy();

    var newdata = $('#example').DataTable();
</script>
@php
    $positions = DB::table('election_positions')->orderBy('order', 'ASC')->get();
@endphp
<script>
    @foreach ($positions as $pos)
        $('#election-table{{ $pos->id }}').DataTable();
    @endforeach
</script>



<script>
    $(document).ready(function() {
        $('.searchable-select').select2({
            placeholder: "Select a lecturer",
            allowClear: true
        });
        //alert('hello');
    });
</script>
