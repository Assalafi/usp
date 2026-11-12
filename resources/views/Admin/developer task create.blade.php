<style>
    .dt-form-page{background:#f4f8fb;min-height:calc(100vh - 90px);padding:24px}.dt-form-card{max-width:1020px;margin:auto;border:1px solid #e0eaf1;border-radius:20px;background:#fff;box-shadow:0 12px 28px rgba(27,65,92,.08);overflow:hidden}.dt-form-head{padding:24px 28px;background:linear-gradient(135deg,#123d60,#1c77a7);color:#fff}.dt-form-head h1{font-size:1.45rem;font-weight:800;margin:0 0 6px;color:#fff!important}.dt-form-head p{margin:0;color:#dcecf6!important}.dt-form-head a{color:#fff!important}.dt-form-body{padding:25px 28px}.dt-label{font-size:.76rem;text-transform:uppercase;letter-spacing:.05em;color:#557086;font-weight:800}.dt-input{border:1px solid #d9e4ee;border-radius:11px;padding:10px 12px;color:#253f55}.dt-input:focus{border-color:#70afd0;box-shadow:0 0 0 .18rem rgba(41,133,178,.12)}.dt-help{font-size:.76rem;color:#7f92a1}.attachment-row{border:1px dashed #b9ccda;border-radius:13px;padding:12px;background:#fbfdff}.attachment-row input{font-size:.82rem}.remove-attachment{color:#b43c45;border:0;background:transparent}.dt-submit{border-radius:11px;padding:11px 18px;font-weight:800}
</style>
<div class="dt-form-page">
    <div class="dt-form-card">
        <div class="dt-form-head"><a href="{{ route('developer.tasks.index') }}" class="text-white text-decoration-none small"><i class="fas fa-arrow-left me-1"></i> Back to task board</a><h1 class="mt-3"><i class="fas fa-clipboard-plus me-2"></i>Assign a developer task</h1><p>Give the developer a clear brief, a priority, a deadline and any supporting files.</p></div>
        <form method="POST" action="{{ route('developer.tasks.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="dt-form-body">
                @if($errors->any())<div class="alert alert-danger"><strong>Please review the form.</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                <div class="row g-3">
                    <div class="col-lg-8"><label class="dt-label" for="taskTitle">Task title *</label><input id="taskTitle" name="title" class="form-control dt-input" value="{{ old('title') }}" placeholder="e.g. Improve the student results export" required></div>
                    <div class="col-sm-6 col-lg-2"><label class="dt-label" for="taskPriority">Priority *</label><select id="taskPriority" name="priority" class="form-select dt-input" required>@foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $value=>$label)<option value="{{ $value }}" {{ old('priority','medium') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-sm-6 col-lg-2"><label class="dt-label" for="taskDueDate">Due date</label><input id="taskDueDate" type="date" name="due_date" class="form-control dt-input" value="{{ old('due_date') }}"></div>
                    <div class="col-lg-7"><label class="dt-label" for="taskAssignee">Assign to</label><select id="taskAssignee" name="assigned_to" class="form-select dt-input"><option value="">Unassigned (developer board)</option>@foreach($taskDevelopers as $developer)<option value="{{ $developer->username }}" {{ old('assigned_to') === $developer->username ? 'selected' : '' }}>{{ $developer->display_name }} · {{ $developer->username }}{{ $developer->appointment ? ' · '.$developer->appointment : '' }}</option>@endforeach</select><div class="dt-help mt-1">The assigned developer will see this task using the same portal login.</div></div>
                </div>
                <div class="mt-4">@include('Admin.partials.task-rich-editor',['editorId'=>'createTaskEditor','editorValue'=>old('description',''),'editorName'=>'description','editorLabel'=>'Task brief'])</div>
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-2"><div><label class="dt-label mb-0">Supporting files</label><div class="dt-help">Add a short description for every file.</div></div><button type="button" class="btn btn-sm btn-outline-primary" id="addTaskAttachment"><i class="fas fa-paperclip me-1"></i>Add file</button></div>
                    <div id="taskAttachments"><div class="attachment-row row g-2 align-items-center"><div class="col-md-5"><input type="file" name="attachments[]" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.png,.jpg,.jpeg,.zip,.txt"></div><div class="col-md-6"><input type="text" name="attachment_descriptions[]" class="form-control" placeholder="What should the developer know about this file?"></div><div class="col-md-1 text-end"><button type="button" class="remove-attachment d-none" title="Remove file"><i class="fas fa-trash"></i></button></div></div></div>
                </div>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 pt-3 border-top"><a href="{{ route('developer.tasks.index') }}" class="btn btn-light border">Cancel</a><button class="btn btn-primary dt-submit"><i class="fas fa-paper-plane me-1"></i>Assign task</button></div>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){const list=document.getElementById('taskAttachments'),add=document.getElementById('addTaskAttachment');add.addEventListener('click',function(){const row=list.querySelector('.attachment-row').cloneNode(true);row.querySelectorAll('input').forEach(i=>i.value='');row.querySelector('.remove-attachment').classList.remove('d-none');list.appendChild(row);});list.addEventListener('click',function(e){const button=e.target.closest('.remove-attachment');if(button)button.closest('.attachment-row').remove();});});
</script>
