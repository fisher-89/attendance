<?php

namespace App\Services;

use App\Models\Clock;

class ClockService
{

    public function clock($clockData, $checkDistance = true)
    {
        $staffSn = app('CurrentUser')->staff_sn;
        $shopSn = array_has($clockData, 'shop_sn') ? $clockData['shop_sn'] : app('CurrentUser')->shop_sn;
        $distance = $checkDistance ? $this->getDistanceToShop($clockData['lng'], $clockData['lat']) : 0;
        if ($distance > config('options.invalid_distance')) {
            return returnErr('hints.111');
        } else {
            $clockData['staff_sn'] = $staffSn;
            $clockData['shop_sn'] = $shopSn;
            $clockData['distance'] = $distance;
            $res = Clock::create($clockData);
            return returnRes($res->id, 'hints.112', 'hints.113');
        }
    }

    public function getDistanceToShop($lng, $lat)
    {
        $earthRadius = 6371393;
        $shopLng = session()->get('staff.shop.lng') * pi() / 180;
        $shopLat = session()->get('staff.shop.lat') * pi() / 180;
        $staffLng = $lng * pi() / 180;
        $staffLat = $lat * pi() / 180;
        $distance = 2 * asin(min(1, sqrt(pow(sin(($staffLat - $shopLat) / 2), 2) + cos($shopLat) * cos($staffLat) * pow(sin(($staffLng - $shopLng) / 2), 2)))) * $earthRadius;
        return $distance;
    }

    public function getAttendanceDate()
    {
        $timestamp = date('H:i') >= config('options.attendance_midnight') ? time() : strtotime('-1 day');
        return date('Y-m-d', $timestamp);
    }

    public function getAttendanceDay($date = null)
    {
        if (empty($date)) {
            $date = $this->getAttendanceDate();
        }
        $startTime = $date . ' ' . config('options.attendance_midnight');
        $endTime = date('Y-m-d H:i:s', strtotime($startTime) + 60 * 60 * 24 - 1);
        return [$startTime, $endTime];
    }

}
