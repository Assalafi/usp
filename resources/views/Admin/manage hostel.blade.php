@php
    function get_phrase($data)
    {
        return strtoupper($data);
    }
@endphp
<div class="row">
    <div class="col-md-6">
        <!-- Start Content-->
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-4">Delete Hostel</h3>
                <!-- Form Start -->
                <div id="mainbody">
                    <form method="POST" action="delete bed">
                        @csrf
                        <div class="input-group mb-3">
                            <select class="form-control" id="hall" name="hall" required>
                                <option value="">Select Hall</option>
                                @foreach ($hall as $hall)
                                    <option value="{{ $hall->hall }}">{{ $hall->hall }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <select class="form-control" id="block" name="block">
                                <option value="">Select Hall First</option>
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <select class="form-control" id="room" name="room">
                                <option value="">Select Block First</option>
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <select class="form-control" id="bed" name="bed">
                                <option value="">Select Room First</option>
                            </select>
                        </div>
                        <input type="submit" class="btn btn-danger shadow-2 mb-4" name="submit" value="Delete">
                    </form>
                </div>
                <div style="display: none;" id="wait">
                    <div class="spinner-grow" role="status">
                        <span class="visually-hidden"></span>
                    </div>
                </div>

                <!-- Form End -->
            </div>
        </div>
        <!-- End Content-->
    </div>
    <div class="col-md-6">
        <!-- Start Content-->
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-4">Change Hall or Block name</h3>
                <!-- Form Start -->
                <div id="mainbody">
                    <form method="POST" action="change hall">
                        @csrf
                        <div class="input-group mb-3">
                            <select class="form-control" id="hall3" name="hall">
                                <option value="">Select Current Hall</option>
                                @foreach ($hall3 as $hall)
                                    <option value="{{ $hall->hall }}">{{ $hall->hall }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control" name="newHall" id="newHall"
                                placeholder="Enter New Hall name">
                        </div>
                        <div class="input-group mb-3">
                            <select class="form-control" id="block3" name="block">
                                <option value="">Select Current Block</option>
                                @foreach ($block as $block)
                                    <option value="{{ $block->block }}">{{ $block->block }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control" name="newBlock" id="newBlock"
                                placeholder="Enter New Block name">
                        </div>
                        <input type="submit" class="btn btn-primary shadow-2 mb-4" name="submit" value="Change">
                    </form>
                </div>
                <div style="display: none;" id="wait">
                    <div class="spinner-grow" role="status">
                        <span class="visually-hidden"></span>
                    </div>
                </div>

                <!-- Form End -->
            </div>
        </div>
        <!-- End Content-->
        <div class="">

            <h5 class="modal-header text-center" style="text-align: center;" id="myModalLabel">Create Hostel</h5>
            <button style="width: 100%;" type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                data-bs-target="#createHostel"><i class="fas fa-plus"></i> Create Hostel </button>
        </div>

    </div>
    <div class="col-md-6">
        <!-- Start Content-->
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-4">Online or Reserse Hostel</h3>
                <!-- Form Start -->
                <div id="mainbody">
                    <form method="POST" action="change bed">
                        @csrf
                        <div class="input-group mb-3">
                            <select class="form-control" id="hall2" name="hall" required>
                                <option value="">Select Hall</option>
                                @foreach ($hall2 as $hall)
                                    <option value="{{ $hall->hall }}">{{ $hall->hall }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <select class="form-control" id="block2" name="block">
                                <option value="">Select Hall First</option>
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <select class="form-control" id="room2" name="room">
                                <option value="">Select Block First</option>
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <select class="form-control" id="bed2" name="bed">
                                <option value="">Select Room First</option>
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <select class="form-control" id="bedType" name="bedType" required>
                                <option value="">Select Type</option>
                                <option value="0">Online</option>
                                <option value="1">Reserve</option>
                                <option value="2">New Student</option>
                                <option value="Online">Online Payment</option>
                                <option value="Bank">Bank Payment</option>
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <select class="form-control" id="r1" name="r1">
                                <option value="">Room: Select Starting Range</option>

                                @for ($i = 1; $i <= 200; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                            <select class="form-control" id="r2" name="r2">
                                <option value="">Room: Select Ending Range</option>

                                @for ($i = 1; $i <= 200; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <select class="form-control" id="b1" name="b1">
                                <option value="">Bed: Select Starting Range</option>

                                @for ($i = 1; $i <= 20; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                            <select class="form-control" id="b2" name="b2">
                                <option value="">Bed: Select Ending Range</option>

                                @for ($i = 1; $i <= 20; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <input type="submit" class="btn btn-primary shadow-2 mb-4" name="submit" value="Exacute">
                    </form>
                </div>
                <div style="display: none;" id="wait">
                    <div class="spinner-grow" role="status">
                        <span class="visually-hidden"></span>
                    </div>
                </div>

                <!-- Form End -->
            </div>
        </div>
        <!-- End Content-->
    </div>
</div>

<div id="createHostel" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Create Hostel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form action="create-hostel" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="col-md-12" for="example-text"><?php echo get_phrase('Hall Name'); ?></label>
                        <div class="col-sm-12">
                            <input type="text" class="form-control" name="hall" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" for="example-text"><?php echo get_phrase('gender'); ?></label>
                        <div class="col-sm-12">
                            <select name="gender" class="form-control select2" style="width:100%" required>
                                <option value=""><?php echo get_phrase('select'); ?></option>
                                <option value="male"><?php echo get_phrase('male'); ?></option>
                                <option value="female"><?php echo get_phrase('female'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" for="example-text"><?php echo get_phrase('Block Name'); ?></label>
                        <div class="col-sm-12">
                            <input type="text" class="form-control" name="b_name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" for="example-text"><?php echo get_phrase('Block Number'); ?></label>
                        <div class="col-sm-12">
                            <select name="b_number" class="form-control select2" style="width:100%" required>
                                <option value=""><?php echo get_phrase('select'); ?></option>
                                <?php $x = 1; while ($x <= 5) { ?>
                                <option value="<?php echo $x; ?>"><?php echo $x; ?></option>
                                <?php $x++; } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" for="example-text"><?php echo get_phrase('Block Numbering'); ?></label>
                        <div class="col-sm-12">
                            <select name="block_numbering" class="form-control select2" style="width:100%" required>
                                <option value=""><?php echo get_phrase('select'); ?></option>
                                <option value="no"><?php echo get_phrase('No Numbering'); ?></option>
                                <option value="yes"><?php echo get_phrase('With Numbering'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" for="example-text"><?php echo get_phrase('Room No. (Start from)'); ?></label>
                        <div class="col-sm-12">
                            <input type="number" class="form-control" name="room_start" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" for="example-text"><?php echo get_phrase('Room No. (End at)'); ?></label>
                        <div class="col-sm-12">
                            <input type="number" class="form-control" name="room" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" for="example-text"><?php echo get_phrase('Room Reserve'); ?></label>
                        <div class="col-sm-12">
                            <select name="r_r" class="form-control select2" style="width:100%" required>
                                <option value=""><?php echo get_phrase('Select Reserve Room'); ?></option>
                                <option value="all"><?php echo get_phrase('All'); ?></option>
                                <?php for($i = 0; $i < 51; $i++){ ?>
                                <option value="<?php echo $i; ?>"><?php echo get_phrase($i); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" for="example-text"><?php echo get_phrase('bed (Start From)'); ?></label>
                        <div class="col-sm-12">
                            <input type="number" class="form-control" name="bed_start" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" for="example-text"><?php echo get_phrase('bed (End At)'); ?></label>
                        <div class="col-sm-12">
                            <input type="number" class="form-control" name="bed" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" for="example-text"><?php echo get_phrase('Bed Reserve 1'); ?></label>
                        <div class="col-sm-12">
                            <input type="number" class="form-control" name="b_r_1" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" for="example-text"><?php echo get_phrase('Bed Reserve 2'); ?></label>
                        <div class="col-sm-12">
                            <input type="number" class="form-control" name="b_r_2" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" for="example-text"><?php echo get_phrase('Category'); ?></label>
                        <div class="col-sm-12">
                            <select name="category" class="form-control select2" style="width:100%" required>
                                <option value=""><?php echo get_phrase('select'); ?></option>
                                <option value="conventional"><?php echo get_phrase('Conventional'); ?></option>
                                <option value="nonconventional"><?php echo get_phrase('Non Conventional'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" for="example-text"><?php echo get_phrase('Hostel Amount'); ?></label>
                        <div class="col-sm-12">
                            <input type="number" class="form-control" name="hostel_amount" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-info btn-block btn-rounded btn-sm"><i
                                class="fa fa-plus"></i>&nbsp;&nbsp;<?php echo get_phrase('add hostel'); ?></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
