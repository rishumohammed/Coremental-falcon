<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function validateGeofence($reqLat, $reqLng)
    {
        $user = \Auth::user();
        if (!$user->geo_location) return true;

        $parts = explode(',', $user->geo_location);
        if (count($parts) != 2) return true;

        $userLat = trim($parts[0]);
        $userLng = trim($parts[1]);

        if (!$reqLat || !$reqLng) return false;

        $earthRadius = 6371000; // meters
        $lat1 = deg2rad((float)$userLat);
        $lon1 = deg2rad((float)$userLng);
        $lat2 = deg2rad((float)$reqLat);
        $lon2 = deg2rad((float)$reqLng);

        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($lonDelta / 2), 2)));
        $distance = $angle * $earthRadius;

        $radius = $user->geo_radius ?? \DB::table('settings')->where('key', 'geo_radius')->value('val') ?? 50;

        return $distance <= $radius;
    }
}
