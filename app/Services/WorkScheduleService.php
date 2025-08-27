<?php
namespace App\Services;

use App\Models\MstParam;
use Carbon\Carbon;

class WorkScheduleService
{
    public function get(): array
    {
        $row = MstParam::where('param_key','work_schedules')->where('status',true)->first();
        return $row ? json_decode($row->param_value, true) : [];
    }

    public function isWorkTime(Carbon $time): bool
    {
        $s = $this->get();                         // ambil jadwal dari mst_params
        $d = $time->dayOfWeekIso;                  // 1=Senin ... 7=Minggu
        $m = $time->hour*60 + $time->minute;

        $slots = $d>=1&&$d<=4 ? ($s['1-4']??[]) : ($d===5?($s['5']??[]) : ($d===6?($s['6']??[]):[]));
        foreach ($slots as $slot) {
            [$sh,$sm] = $slot['start']; [$eh,$em] = $slot['end'];
            if ($m >= $sh*60+$sm && $m <= $eh*60+$em) return true;
        }
        return false;
    }
}
