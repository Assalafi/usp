@php
    $selectedSemester = $selectedSemester ?? null;
    $includeReset = $includeReset ?? false;
@endphp
@forelse ($semesters as $sem)
    <option value="{{ $sem }}" @if((string)$selectedSemester === (string)$sem) selected @endif>{{ ucfirst(strtolower($sem)) }} Semester</option>
@empty
    <option value="FIRST" @if((string)$selectedSemester === 'FIRST') selected @endif>First Semester</option>
    <option value="SECOND" @if((string)$selectedSemester === 'SECOND') selected @endif>Second Semester</option>
    <option value="THIRD" @if((string)$selectedSemester === 'THIRD') selected @endif>Third Semester</option>
@endforelse
@if($includeReset)
    <option value="Reset" @if((string)$selectedSemester === 'Reset') selected @endif>Reset Semester</option>
@endif
