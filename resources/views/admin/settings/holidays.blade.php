@extends('layouts.app')

@section('page-title', 'Public Holidays')

@section('content')
<div class="container-fluid pt-2">

    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            @foreach ($errors->all() as $error)
                <div><i class="fas fa-exclamation-circle mr-1"></i>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="row">

        {{-- Add Holiday Form --}}
        <div class="col-md-4 mb-4">
            <div class="card-white p-4">
                <h6 class="font-weight-bold text-dark mb-3">
                    <i class="fas fa-plus-circle mr-2 text-primary"></i>Add Public Holiday
                </h6>
                <p class="text-muted small mb-3">
                    Dates added here will be <strong>excluded</strong> from the Absent Report.
                </p>
                <form action="{{ url('admin/settings/holidays') }}" method="post">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small text-uppercase">Date</label>
                        <input type="date" name="date" class="form-control"
                               style="background-color: #f8fafc; border: 1px solid #e5e7eb; font-size: 0.95rem; padding: 0.75rem 1rem;"
                               value="{{ old('date') }}" required>
                    </div>
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark small text-uppercase">Holiday Name <span class="text-muted">(optional)</span></label>
                        <input type="text" name="name" class="form-control"
                               style="background-color: #f8fafc; border: 1px solid #e5e7eb; font-size: 0.95rem; padding: 0.75rem 1rem;"
                               value="{{ old('name') }}" placeholder="e.g. Independence Day">
                    </div>
                    <button type="submit" class="btn ui-btn ui-btn-primary w-100">
                        <i class="fas fa-plus mr-2"></i>Add Holiday
                    </button>
                </form>
            </div>

            {{-- Info box --}}
            <div class="mt-3 p-3 rounded" style="background:#fffbeb; border: 1px solid #fde68a;">
                <div class="d-flex align-items-start">
                    <i class="fas fa-shield-alt text-warning mt-1 mr-2"></i>
                    <div class="small text-dark">
                        <strong>Soft Delete Protection</strong><br>
                        Deleting a holiday <em>archives</em> it — it still protects past employee records.
                        Use <strong>Permanent Delete</strong> only if the date was added by mistake.
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Holidays --}}
        <div class="col-md-8 mb-4">
            <div class="card-white p-0 overflow-hidden mb-4">
                <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="font-weight-bold text-dark mb-0">
                        <i class="fas fa-calendar-day mr-2 text-danger"></i>Active Public Holidays
                    </h6>
                    <span class="badge badge-light border">{{ count($holidays) }} holiday(s)</span>
                </div>

                @if(count($holidays) > 0)
                <div class="table-responsive">
                    <table class="table table-ui mb-0">
                        <thead>
                            <tr>
                                <th style="width: 38%">Date</th>
                                <th style="width: 40%">Holiday Name</th>
                                <th style="width: 22%" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($holidays as $holiday)
                            @php $isPast = strtotime($holiday->date) < strtotime(date('Y-m-d')); @endphp
                            <tr>
                                <td>
                                    <div class="font-weight-bold text-dark">
                                        <i class="far fa-calendar mr-1 text-danger"></i>
                                        {{ date('d M, Y', strtotime($holiday->date)) }}
                                    </div>
                                    <div class="text-muted small">
                                        {{ date('l', strtotime($holiday->date)) }}
                                        @if($isPast)
                                            &nbsp;<span class="badge" style="background:#fef3c7;color:#92400e;font-size:0.68rem;padding:2px 7px;border-radius:10px;">Past</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($holiday->name)
                                        <span class="badge" style="background:#fef3c7; color:#92400e; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem;">
                                            {{ $holiday->name }}
                                        </span>
                                    @else
                                        <span class="text-muted small">— no name —</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ url('admin/settings/holidays/delete/' . $holiday->id) }}"
                                       class="btn btn-sm btn-light border text-warning"
                                       data-is-past="{{ $isPast ? '1' : '0' }}"
                                       data-date="{{ date('d M Y', strtotime($holiday->date)) }}"
                                       onclick="return confirmArchive(this)"
                                       title="Archive (soft delete)">
                                        <i class="fas fa-archive"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-calendar-times fa-3x mb-3 opacity-50"></i>
                    <h6>No active public holidays defined.</h6>
                    <p class="small">Add holidays using the form on the left.</p>
                </div>
                @endif
            </div>

            {{-- Archived Holidays --}}
            @if(count($archivedHolidays) > 0)
            <div class="card-white p-0 overflow-hidden">
                <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between" style="background:#fafafa;">
                    <h6 class="font-weight-bold text-muted mb-0">
                        <i class="fas fa-archive mr-2"></i>Archived Holidays
                        <span class="text-muted small font-weight-normal ml-1">(still protect past records)</span>
                    </h6>
                    <span class="badge badge-light border">{{ count($archivedHolidays) }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-ui mb-0">
                        <thead>
                            <tr>
                                <th style="width: 35%">Date</th>
                                <th style="width: 38%">Holiday Name</th>
                                <th style="width: 27%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($archivedHolidays as $holiday)
                            <tr style="opacity:0.75;">
                                <td>
                                    <div class="font-weight-bold text-muted">
                                        <i class="far fa-calendar mr-1"></i>
                                        {{ date('d M, Y', strtotime($holiday->date)) }}
                                    </div>
                                    <div class="text-muted small">{{ date('l', strtotime($holiday->date)) }}</div>
                                </td>
                                <td>
                                    @if($holiday->name)
                                        <span class="text-muted small">{{ $holiday->name }}</span>
                                    @else
                                        <span class="text-muted small">— no name —</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{-- Restore --}}
                                    <a href="{{ url('admin/settings/holidays/restore/' . $holiday->id) }}"
                                       class="btn btn-sm btn-light border text-success mr-1"
                                       title="Restore holiday">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                    {{-- Permanent Delete --}}
                                    <a href="{{ url('admin/settings/holidays/force-delete/' . $holiday->id) }}"
                                       class="btn btn-sm btn-light border text-danger"
                                       onclick="return confirm('Permanently delete this holiday? This cannot be undone and past dates will no longer be protected.')"
                                       title="Permanently delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <div class="mt-3">
                <a href="{{ url('admin/settings') }}" class="btn ui-btn btn-light border">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Settings
                </a>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function confirmArchive(el) {
    var isPast = el.getAttribute('data-is-past') === '1';
    var date   = el.getAttribute('data-date');

    if (isPast) {
        return confirm(
            '⚠️ Warning: "' + date + '" is a past date.\n\n' +
            'This will ARCHIVE the holiday (not permanently delete it).\n' +
            'The date will still protect existing employee records.\n\n' +
            'You can restore it anytime from the Archived section.\n\n' +
            'Continue?'
        );
    }

    return confirm('Archive this holiday? It can be restored later.');
}
</script>
@endpush

@endsection
