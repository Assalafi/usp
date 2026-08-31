@php
    $selectedSemester = $selectedSemester ?? null;
    $includeResit = $includeResit ?? false;
@endphp
@forelse ($semesters as $sem)
    <option value="{{ $sem }}" @if((string)$selectedSemester === (string)$sem) selected @endif>{{ ucfirst(strtolower($sem)) }} Semester</option>
@empty
    <option value="FIRST" @if((string)$selectedSemester === 'FIRST') selected @endif>First Semester</option>
    <option value="SECOND" @if((string)$selectedSemester === 'SECOND') selected @endif>Second Semester</option>
    <option value="THIRD" @if((string)$selectedSemester === 'THIRD') selected @endif>Third Semester</option>
@endforelse
@if($includeResit)
    <option value="RESIT" @if((string)$selectedSemester === 'RESIT') selected @endif>Resit Semester</option>
@endif
