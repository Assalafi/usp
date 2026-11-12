@php
    $editorId = $editorId ?? 'taskEditor';
    $editorValue = $editorValue ?? '';
    $editorName = $editorName ?? 'description';
    $editorLabel = $editorLabel ?? 'Details';
    $editorRequired = $editorRequired ?? true;
@endphp
<style>
    .task-editor{border:1px solid #d9e4ee;border-radius:16px;background:#fff;overflow:hidden}
    .task-editor-toolbar{display:flex;align-items:center;gap:5px;flex-wrap:wrap;padding:9px 10px;background:#f5f8fb;border-bottom:1px solid #e5edf3}
    .task-editor-toolbar button,.task-editor-toolbar select{height:34px;border:1px solid #d4e0ea;background:#fff;border-radius:8px;color:#36546c;font-size:.78rem;padding:0 9px}
    .task-editor-toolbar button:hover{background:#eaf3f9;border-color:#8fb5cd}.task-editor-toolbar .divider{width:1px;height:22px;background:#d7e2eb;margin:0 3px}
    .task-editor-surface{min-height:180px;padding:15px 16px;outline:0;color:#243b53;font-size:.92rem;line-height:1.7}.task-editor-surface:empty:before{content:'Write the task details here…';color:#94a6b4}
    .task-editor-surface a{color:#1769aa;text-decoration:underline}
</style>
<div class="mb-3">
    <label class="form-label fw-bold" for="{{ $editorId }}Surface">{{ $editorLabel }} @if($editorRequired)<span class="text-danger">*</span>@endif</label>
    <div class="task-editor" data-task-editor data-editor-id="{{ $editorId }}">
        <div class="task-editor-toolbar" role="toolbar" aria-label="Formatting tools">
            <select data-editor-format aria-label="Text style"><option value="p">Paragraph</option><option value="h2">Heading</option><option value="h3">Subheading</option></select>
            <span class="divider"></span>
            <button type="button" data-editor-command="bold" title="Bold"><i class="fas fa-bold"></i></button>
            <button type="button" data-editor-command="italic" title="Italic"><i class="fas fa-italic"></i></button>
            <button type="button" data-editor-command="underline" title="Underline"><i class="fas fa-underline"></i></button>
            <span class="divider"></span>
            <button type="button" data-editor-command="justifyLeft" title="Align left"><i class="fas fa-align-left"></i></button>
            <button type="button" data-editor-command="justifyCenter" title="Align center"><i class="fas fa-align-center"></i></button>
            <button type="button" data-editor-command="justifyRight" title="Align right"><i class="fas fa-align-right"></i></button>
            <button type="button" data-editor-command="justifyFull" title="Justify"><i class="fas fa-align-justify"></i></button>
            <span class="divider"></span>
            <button type="button" data-editor-command="insertUnorderedList" title="Bulleted list"><i class="fas fa-list-ul"></i></button>
            <button type="button" data-editor-command="insertOrderedList" title="Numbered list"><i class="fas fa-list-ol"></i></button>
            <button type="button" data-editor-command="createLink" title="Insert link"><i class="fas fa-link"></i></button>
            <button type="button" data-editor-command="removeFormat" title="Clear formatting"><i class="fas fa-eraser"></i></button>
        </div>
        <div id="{{ $editorId }}Surface" class="task-editor-surface" contenteditable="true" role="textbox" aria-multiline="true" spellcheck="true">{!! $editorValue !!}</div>
        <textarea id="{{ $editorId }}Source" name="{{ $editorName }}" class="d-none" @if($editorRequired) required @endif>{{ $editorValue }}</textarea>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){
    document.querySelectorAll('[data-task-editor]').forEach(function(wrapper){
        if(wrapper.dataset.ready==='1') return; wrapper.dataset.ready='1';
        const surface=wrapper.querySelector('.task-editor-surface'), source=wrapper.querySelector('textarea'), format=wrapper.querySelector('[data-editor-format]');
        wrapper.querySelectorAll('[data-editor-command]').forEach(function(button){
            button.addEventListener('click',function(){
                surface.focus();
                if(button.dataset.editorCommand==='createLink'){const url=window.prompt('Enter the link address'); if(url) document.execCommand('createLink',false,url);}
                else document.execCommand(button.dataset.editorCommand,false,null);
                source.value=surface.innerHTML;
            });
        });
        format.addEventListener('change',function(){surface.focus();document.execCommand('formatBlock',false,format.value);source.value=surface.innerHTML;});
        surface.addEventListener('input',function(){source.value=surface.innerHTML;});
        const form=wrapper.closest('form'); if(form) form.addEventListener('submit',function(){source.value=surface.innerHTML;});
    });
});
</script>
