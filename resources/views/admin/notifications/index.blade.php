@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="m-0">Notifications</h3>
        @if($otps->count() > 0)
        <form action="{{ route('notifications.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear all notifications?');">
            @csrf
            <button type="submit" class="btn btn-danger">Clear All Notifications</button>
        </form>
        @endif
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">Date</th>
                            <th class="border-0">User</th>
                            <th class="border-0">Message</th>
                            <th class="border-0">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($otps as $otp)
                        <tr>
                            <td>
                                <div class="font-weight-bold text-dark">{{ $otp->created_at->timezone(timezone())->format('M d, Y') }}</div>
                                <div class="text-muted small"><i class="far fa-clock"></i> {{ $otp->created_at->timezone(timezone())->format('h:i:s A') }}</div>
                            </td>
                            <td>{{ optional($otp->user)->name ?? 'Unknown' }}</td>
                            <td>
                                <strong>OTP for user {{ optional($otp->user)->name ?? 'Unknown' }} is {{ $otp->otp }}</strong>
                            </td>
                            <td>
                                @if($otp->is_used)
                                    <span class="badge badge-success px-2 py-1">Used</span>
                                @elseif($otp->expires_at < now())
                                    <span class="badge badge-danger px-2 py-1">Expired</span>
                                @else
                                    <span class="badge badge-warning px-2 py-1">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No notifications found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $otps->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
