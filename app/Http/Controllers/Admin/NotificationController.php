<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Otp;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->username !== 'superadmin') {
                abort(403, 'Unauthorized access to notifications.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $otps = Otp::with('user')->latest()->paginate(20);
        return view('admin.notifications.index', compact('otps'));
    }

    public function clear()
    {
        Otp::query()->delete();
        return redirect()->back()->with('success', 'All notifications cleared successfully.');
    }
}
