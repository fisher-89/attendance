<?php

namespace App\Services;

use App\Models\Clock;
use Image;

class ClockService
{

    public function clock($clockData, $checkDistance = true)
    {
        $staffSn = array_has($clockData, 'staff_sn') ? $clockData['staff_sn'] : app('CurrentUser')->staff_sn;
        $shopSn = array_has($clockData, 'shop_sn') ? $clockData['shop_sn'] : app('CurrentUser')->shop_sn;
        $distance = $checkDistance ? $this->getDistanceToShop($clockData['lng'], $clockData['lat']) : 0;
        $clockData['operator_sn'] = app('CurrentUser')->staff_sn;
        if ($distance > config('options.invalid_distance')) {
            return returnErr('hints.111');
        } else {
            $clockData['staff_sn'] = $staffSn;
            $clockData['shop_sn'] = $shopSn;
            $clockData['clock_at'] = isset($clockData['clock_at']) ? $clockData['clock_at'] : date('Y-m-d H:i:s');
            $clockData['distance'] = $distance;
            if (!empty($clockData['photo'])) {
                $picPath = $clockData['photo'];
                $saveDir = '/uploads/photo/' . preg_replace('/^(\d{4})-(\d{2})-(\d{2}).*$/', '$1/$2/$3', $clockData['clock_at']) . '/' . $clockData['shop_sn']
                    . '/';
                $fileName = $staffSn . '-' . preg_replace('/^.*(\d{2}):(\d{2}):(\d{2})$/', '$1$2$3', $clockData['clock_at']) . '.png';
                $photoPath = $saveDir . $fileName;
                $thumbPath = $saveDir . 'thumb_' . $fileName;
                if (!file_exists(public_path($saveDir))) {
                    mkdir(public_path($saveDir), 0755, true);
                }
                $img = Image::make($picPath);
                $img->fit(300, 400);
                $img->save(public_path($photoPath));
                $img->fit(100, 100);
                $img->save(public_path($thumbPath));
                $clockData['photo'] = $photoPath;
                $clockData['thumb'] = $thumbPath;
            }
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

    public function getAttendanceDate($format = 'Y-m-d')
    {
        $timestamp = date('H:i') >= config('options.attendance_midnight') ? time() : strtotime('-1 day');
        return date($format, $timestamp);
    }

    public function getAttendanceDay($date = null)
    {
        if (empty($date)) {
            $date = $this->getAttendanceDate();
        }
        $startTime = $date . ' ' . config('options.attendance_midnight');
        $endTimestamp = min(strtotime($startTime) + 60 * 60 * 24 - 1, time());
        $endTime = date('Y-m-d H:i:s', $endTimestamp);
        return [$startTime, $endTime];
    }

    /**
     * 获取最近一条打卡记录（本店）
     * @param null $shopSn
     * @param null $staffSn
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getLatestClock($shopSn = null, $staffSn = null)
    {
        $shopSn = empty($shopSn) ? app('CurrentUser')->shop_sn : $shopSn;
        $staffSn = empty($staffSn) ? app('CurrentUser')->staff_sn : $staffSn;

        list($startTime,
            $endTime) = app('Clock')->getAttendanceDay();
        $prevClockRecord = Clock::where('clock_at', '>', $startTime)
            ->where(['staff_sn' => $staffSn, 'shop_sn' => $shopSn, 'is_abandoned' => 0])
            ->orderBy('clock_at', 'desc')->first();
        return $prevClockRecord;
    }

    /**
     * 获取上一条打卡记录
     * @param Clock $clock
     * @param bool $startAt 开始时间限制
     * @param bool $lockShop 是否锁定当前店铺
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getPrevClock(Clock $clock, $startAt = false, $lockShop = false)
    {
        $clockAt = $clock->getOriginal('clock_at');
        $staffSn = $clock->staff_sn;
        return Clock::where('staff_sn', $staffSn)
            ->where('is_abandoned', 0)
            ->where('clock_at', '<', $clockAt)
            ->when($startAt != false, function ($query) use ($startAt) {
                return $query->where('clock_at', '>', $startAt);
            })->when($lockShop, function ($query) use ($clock) {
                return $query->where('shop_sn', $clock->shop_sn);
            })->orderBy('clock_at', 'desc')->first();
    }

}
