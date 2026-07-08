
<!-- Start Content-->
<div class="main-body">
    <div class="page-wrapper">
        <!-- [ Main Content ] start -->
        <div class="row">
            <!-- [ Card ] start -->
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ ('Add') }} / {{ ('Edit') }}</h5>
                    </div>

                    <div class="card-block">
                        <form class="needs-validation" novalidate method="get" action="#">
                            <div class="row gx-2">
                                <div class="form-group col-md-3">
                                    <label for="faculty">Faculty <span>*</span></label>
                                    <select class="form-control" name="faculty" id="faculty" required>
                                        <option value="">Select Option</option>
                                        @foreach ($faculty as $roww)
                                            <option value="{{ $roww -> code }}">{{ $roww -> title }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"> You must select FACULTY </div>
                                </div>

                                <div class="form-group col-md-3">
                                    <button type="submit" class="btn btn-info btn-filter"><i class="fas fa-search"></i> {{ ('Filter') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                @isset ($_GET['faculty'])
                    <div class="card">
                        @php
                        $weekdays = array('1');
                        @endphp
                        <ul class="nav nav-pills mb-3 card-block" id="myTab" role="tablist">

                            @foreach($weekdays as $weekday)
                            <li class="nav-item">
                                <a class="nav-link @if($weekday == 1) active @endif text-uppercase" id="day{{ $weekday }}-tab" data-bs-toggle="tab" href="#day{{ $weekday }}" role="tab" aria-controls="day{{ $weekday }}" aria-selected="true">
                                    @if( $weekday == 1 )
                                        {{ $day[$weekday] = 'EXAM TIMETABLE' }}
                                    @endif
                                </a>
                            </li>
                            @endforeach
                            <li>
                                <a href="/exam-timetable/{{ $_GET['faculty'] }}" class="btn btn-primary btn-sm nav-link">Preview</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            @foreach($weekdays as $weekday)
                            <div class="tab-pane fade @if($weekday == 1) show active @endif" id="day{{ $weekday }}" role="tabpanel" aria-labelledby="day{{ $weekday }}-tab">
                                <div class="">
                                    <div class="row">
                                        <div class="col-md-12">
                                        @forelse($data->where('faculty', $_GET['faculty']) as $row)
<div class="card-block" id="deleteRoutine-{{ $row->id }}">
    <form class="formD" method="POST">
        @csrf
        <div class="row">
            <input type="text" name="id" value="{{ $row->id }}" hidden>
            <div class="form-group col-md-2">
                  <label for="course">Course<span>*</span></label>
                  <select class="form-control select2 must" name="course" id="course" required>
                        <option value="">Select Option</option>
                        @foreach( $course as $subject )
                        <option value="{{ $subject->code }}" @isset($row) {{ $row->course == $subject->code ? 'selected' : '' }} @endisset>{{ $subject->code }} - {{ $subject->title }}</option>
                        @endforeach
                  </select>
            </div>
            <div class="form-group col-md-2">
                  <label for="lecturer">Lecturer <span>*</span></label>
                  <input type="text" class="form-control must" name="lecturer" value="{{ $row->lecturer }}">
            </div>
            <div class="form-group col-md-2">
                  <label for="hall">Hall<span>*</span></label>
                  <select class="form-control select2 must" name="hall" id="hall" required>
                        <option value="">{{ __('select') }}</option>
                        @foreach( $hall as $room )
                        <option value="{{ $room->hall }}" @isset($row) {{ $row->hall == $room->hall ? 'selected' : '' }} @endisset>{{ $room->hall }}</option>
                        @endforeach
                  </select>
            </div>
            <div class="form-group col-md-2">
                  <label for="date">Date<span>*</span></label>
                  <input type="date" class="form-control must" name="date" id="date" value="{{ $row->date }}" required>
            </div>
            <div class="form-group col-md-2">
                  <label for="start">Start At<span>*</span></label>
                  <input type="time" class="form-control must" name="start" id="start" value="{{ $row->start }}" required>
            </div>
            <div class="form-group col-md-2">
                  <label for="end">Ending At <span>*</span></label>
                  <input type="time" class="form-control must" name="end" id="end" value="{{ $row->end }}" required>
            </div>
            
            @isset($row)
            <div class="form-group col-md-2">
                  <div class="btn btn-danger btn-filter" onclick="deleteRoutine({{ $row->id }})">
                        <span><i class="fas fa-trash-alt"></i> {{ ('Remove') }}</span>
                  </div>
            </div>
            @endisset
      </div>
    </form>
      
</div>
                                        @empty
{{-- <div class="card-block" id="inputFormField">
    <form class="formD" method="POST">
        @csrf
        <div class="row">
            <div class="form-group col-md-2">
                  <label for="course">Course<span>*</span></label>
                  <select class="form-control select2 must" name="course" id="course" required>
                        <option value="">Select Option</option>
                        @foreach( $course as $subject )
                        <option value="{{ $subject->code }}">{{ $subject->code }} - {{ $subject->title }}</option>
                        @endforeach
                  </select>
            </div>
            <div class="form-group col-md-2">
                  <label for="lecturer">Lecturer <span>*</span></label>
                  <input type="text" class="form-control must" name="lecturer">
            </div>
            <div class="form-group col-md-2">
                  <label for="hall">Hall<span>*</span></label>
                  <select class="form-control select2 must" name="hall" id="hall" required>
                        <option value="">Select Option</option>
                        @foreach( $hall as $room )
                        <option value="{{ $room->hall }}"{{ $room->hall }}</option>
                        @endforeach
                  </select>
            </div>
            <div class="form-group col-md-2">
                  <label for="start">Start At<span>*</span></label>
                  <input type="time" class="form-control time must" name="start" id="start" required>
            </div>
            <div class="form-group col-md-2">
                  <label for="end">Ending At <span>*</span></label>
                  <input type="time" class="form-control time must" name="end" id="end" required>
            </div>
            <input type="text" name="day_no" value="{{ $weekday }}" hidden><input type="text" name="day" value="{{ $day[$weekday] }}" hidden><input type="text" name="faculty" value="{{ $_GET['faculty'] }}" hidden>
            
            <div class="form-group col-md-2">
                  <button id="removeField" type="button" class="btn btn-danger btn-filter"><i class="fas fa-trash-alt"></i> Remove</button>
            </div>
      </div>
    </form>
      
</div> --}}
                                        @endforelse
                                        <div id="newField-tab-{{ $weekday }}" class="clearfix"></div>
                                        <div class="card-block">
                                            <button id="addField" type="button" value="{{ $weekday }}" name="{{ $day[$weekday] }}" class="btn btn-info" data-bs-tab="tab-{{ $weekday }}"><i class="fas fa-plus"></i> {{ ('Add New') }}</button>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            <div class="card-footer text-right">
                                <button type="button" id="submit" class="btn btn-success"><i class="fas fa-check"></i> {{ ('Save') }}</button>
                            </div>
                        </div>
                    </div>
                @endisset
            </div>
            <!-- [ Card ] end -->
        </div>
        <!-- [ Main Content ] end -->
    </div>
</div>
<!-- End Content-->
<script type="text/javascript">

    var hall = ''; 
        @foreach ($hall as $row)
            hall += '<option value="{{ $row -> hall }}">{{ $row -> hall }}</option>';
        @endforeach

    var course = ''; 
        @foreach ($course as $row)
            course += '<option value="{{ $row -> code }}">{{ $row -> code }} - {{ $row -> title }}</option>';
        @endforeach
        $(document).on('click', '#addField', function () {
            var tab = $(this).attr('data-bs-tab');
            var html = '';
            //alert(this.value);
            var day_no = this.value;
            var day = this.name;
            var faculty;
            @isset ($_GET["faculty"])
                faculty = '{{ $_GET["faculty"] }}';
            @endisset
            //alert(day+' '+faculty);
            html += '<hr/>';
            html += '<form href="create {{ $page }}" class="formD" method="POST">@csrf<div id="inputFormField" class="card-block">';
            html += '<div class="row">';
            html += '<div class="form-group col-md-2"><label for="course">Course<span>*</span></label><select class="form-control select2 must" name="course" id="course" required><option value="">Select Option</option>'+course+'</select> <div class="invalid-feedback"> {{ __('required_field') }} {{ __('field_subject') }}</div></div>';
            html += '<div class="form-group col-md-2"><label for="lecturer">Lecturer<span>*</span></label> <input class="form-control must" type="text" name="lecturer" id="lecturer"> <div class="invalid-feedback"> {{ __('required_field') }} {{ __('field_teacher') }} </div> </div>';
            html += '<div class="form-group col-md-2"> <label for="hall">Hall<span>*</span></label> <select class="form-control select2 must" name="hall" id="hall" required> <option value="">Select Option</option>'+hall+'</select> <div class="invalid-feedback"> {{ ('required_field') }} {{ __('field_room') }} {{ __('field_no') }} </div></div>';
            html += '<div class="form-group col-md-2"> <label for="date">Date<span>*</span></label><input type="date" class="form-control must" name="date" id="date" required><div class="invalid-feedback"> </div></div>';
            html += '<div class="form-group col-md-2"> <label for="start">Starting At<span>*</span></label><input type="time" class="form-control must" name="start" id="start" required><div class="invalid-feedback"> </div></div>';
            html += '<div class="form-group col-md-2"> <label for="end">Ending At<span>*</span></label> <input type="time" class="form-control must" name="end" id="end" required> <div class="invalid-feedback"> {{ __('required_field') }} {{ __('field_time') }} {{ __('field_to') }} </div> </div>';
            html += '<div class="form-group col-md-2"><button id="removeField" type="button" class="btn btn-danger btn-filter"><i class="fas fa-trash-alt"></i> Remove</button></div>';
            html += '<input type="text" name="faculty" value="'+faculty+'" hidden> </div></form>';

            $('#newField-'+tab).append(html);

            // Time Picker
            $('.time').bootstrapMaterialDatePicker({
                date: false,
                shortTime: true,
                format: 'HH:mm'
            });
        });

        // remove Field
        $(document).on('click', '#removeField', function () {
            $(this).closest('#inputFormField').remove();

            // Time Picker
            $('.time').bootstrapMaterialDatePicker({
                date: false,
                shortTime: true,
                format: 'HH:mm'
            });
        });


    // Delete Routine
    function deleteRoutine(id) {

        let _url     = '/delete {{ $page }}';
        let _token   = $('input[name="_token"]').val();
        $.ajax({
           type:'POST',
           url:_url,
           data:{
                id: id,
              _token: _token
            },
           success:function(data) {
            swal("Success", "Done!!!", "success");
           },
          error: function(error) {
            swal("Error", "Something went wrong", "error");
          }
        });

        $("#deleteRoutine-"+id).hide();
        $("#delete_routine-"+id).attr("checked", "checked");
    }

    $('#submittt').click(function() {

    $('.formD').each(function(index, form) {
      // Display the name and value of each input in an alert
      $(form).find('select,input').each(function(index, input) {
        alert('Name: ' + $(input).attr('name') + ', Value: ' + $(input).val());
      });
    });
        var formData = $('.formD').serialize();

        $.ajax({
          type: 'POST',
          url: '/create lecture timetable', // Update the URL accordingly
          data: formData,
          success: function(response) {
            alert('Success '+response);
            // Handle success if needed
          },
          error: function(error) {
            alert('Error '+error);
            // Handle error if needed
          }
        });
      });


        $(document).on('click','#submit', function() {

        let _url = '/create {{ $page }}';
        var formD = document.getElementsByClassName('formD');
        var length = formD.length;
        var must = document.getElementsByClassName('must');
        var mustLength = must.length;
        //alert(length);
        for(var i = 0; i < mustLength; i++){
            if(must[i].value == ''){
                swal("Oops!!!", "Field With * Cannot be Empty!!!", "error");
                return false;
            }
        }
        //alert(length);
        function makeAjaxRequest(j) {
            //var formData = $('.formD').serialize();
          var currentForm = $($('.formD')[j]);
          var formData = currentForm.serialize();

            // var pairs = formData.split("&");
            //   pairs.forEach(function (pair) {
            //     var keyValue = pair.split("=");
            //     var name = decodeURIComponent(keyValue[0]);
            //     var value = decodeURIComponent(keyValue[1]);
            //     alert("Name: " + name + ", Value: " + value);
            //   });

          return new Promise((resolve, reject) => {

                $.ajax({
                  type: 'POST',
                  url: _url,
                  data: formData,
                  success: function(response) {
                    alert('Success '+response);
                  },
                  error: function(error) {
                   // alert('Error '+error);
                  }
                });
            setTimeout(() => {
              //console.log("AJAX request completed for index", i);
              resolve(); // Resolve the promise when the AJAX request is completed
            }, 1000);
          });
        }

        async function performAjaxLoop() {
          for (let i = 0; i < length; i++) {
            try {
              await makeAjaxRequest(i);
            } catch (error) {
              console.error("Error occurred during AJAX request:", error);
            }
          }
            //window.location.href='/{{ $page }}';
          swal("Success", "Done!!!", "success");
        }

        performAjaxLoop();
        //alert(status);
            
        });
</script>