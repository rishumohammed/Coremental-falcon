@extends('layouts.app')

@section('page-title', 'Weekend Days')

@section('content')
<div class="container-fluid pt-2">

    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-8 col-lg-6">

            <div class="card-white p-4">

                <p class="text-muted small mb-4">
                    Select the days that are considered <strong>non-working (weekend) days</strong>.
                    Employees will <strong>not</strong> appear as absent on these days in the Absent Report.
                </p>

                <form action="{{ url('admin/settings/weekend') }}" method="post">
                    @csrf

                    @php
                        $dayNames = [
                            0 => ['label' => 'Sunday',    'short' => 'Sun', 'icon' => 'fas fa-sun'],
                            1 => ['label' => 'Monday',    'short' => 'Mon', 'icon' => 'fas fa-briefcase'],
                            2 => ['label' => 'Tuesday',   'short' => 'Tue', 'icon' => 'fas fa-briefcase'],
                            3 => ['label' => 'Wednesday', 'short' => 'Wed', 'icon' => 'fas fa-briefcase'],
                            4 => ['label' => 'Thursday',  'short' => 'Thu', 'icon' => 'fas fa-briefcase'],
                            5 => ['label' => 'Friday',    'short' => 'Fri', 'icon' => 'fas fa-briefcase'],
                            6 => ['label' => 'Saturday',  'short' => 'Sat', 'icon' => 'fas fa-umbrella-beach'],
                        ];
                    @endphp

                    <div class="d-flex flex-wrap" style="gap: 12px;">
                        @foreach($dayNames as $num => $day)
                        @php $isSelected = in_array($num, $selectedDays); @endphp
                        <label class="day-toggle {{ $isSelected ? 'active' : '' }}" for="day_{{ $num }}">
                            <input type="checkbox"
                                   id="day_{{ $num }}"
                                   name="days[]"
                                   value="{{ $num }}"
                                   {{ $isSelected ? 'checked' : '' }}
                                   style="display: none;"
                                   onchange="toggleDay(this)">
                            <div class="day-toggle-inner">
                                <i class="{{ $day['icon'] }} mb-2" style="font-size: 1.4rem;"></i>
                                <div class="font-weight-bold" style="font-size: 0.9rem;">{{ $day['short'] }}</div>
                                <div style="font-size: 0.7rem; opacity: 0.7;">{{ $day['label'] }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <div class="mt-4 border-top pt-4 d-flex align-items-center justify-content-between">
                        <a href="{{ url('admin/settings') }}" class="btn ui-btn btn-light border">
                            <i class="fas fa-arrow-left mr-1"></i> Back
                        </a>
                        <button type="submit" class="btn ui-btn ui-btn-primary px-4">
                            <i class="fas fa-save mr-2"></i> Save Weekend Days
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<style>
.day-toggle {
    cursor: pointer;
    display: inline-block;
}
.day-toggle-inner {
    width: 90px;
    padding: 16px 8px;
    border-radius: 12px;
    border: 2px solid #e5e7eb;
    background: #f8fafc;
    text-align: center;
    transition: all 0.2s ease;
    color: #6b7280;
}
.day-toggle:hover .day-toggle-inner {
    border-color: #a5b4fc;
    background: #eef2ff;
    color: #4f46e5;
}
.day-toggle.active .day-toggle-inner {
    border-color: #6366f1;
    background: #eef2ff;
    color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
}
</style>

@push('scripts')
<script>
function toggleDay(checkbox) {
    var label = checkbox.closest('label');
    if (checkbox.checked) {
        label.classList.add('active');
    } else {
        label.classList.remove('active');
    }
}
</script>
@endpush

@endsection
