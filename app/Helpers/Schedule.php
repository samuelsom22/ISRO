<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('getServerTimes')) {
    function getServerTimes(): array
    {
        $schudules = collect(DB::connection('shard')
            ->table("dbo._Schedule")
            ->select(array(
                "MainInterval_Type",
                "ScheduleDefineIdx",
                "SubInterval_DayOfWeek",
                "SubInterval_StartTimeHour",
                "SubInterval_StartTimeMinute",
                "SubInterval_DurationSecond"))
            ->whereIn("MainInterval_Type", array(1, 3))
            ->whereIn("ScheduleDefineIdx", array(1, 2, 3, 6, 9, 10, 11, 13, 17, 19, 21, 23))
            ->get()
        );
        $now = Carbon\Carbon::now();

        $times = $schudules->map(function ($item, $key) use($now) {
            switch ($item->MainInterval_Type) {
                case 1:
                    $dateStart = Carbon\Carbon::createFromTime($item->SubInterval_StartTimeHour, $item->SubInterval_StartTimeMinute);
                    $dateEnd = Carbon\Carbon::createFromTime($item->SubInterval_StartTimeHour, $item->SubInterval_StartTimeMinute)->addSeconds($item->SubInterval_DurationSecond);
                    if ($dateStart < $now && $dateEnd < $now) {
                        $dateStart->addDay(1);
                        $dateEnd->addDay(1);
                    }
                    if (in_array($item->ScheduleDefineIdx, array(6, 9, 10, 11))) {
                        $dateStart->addMinutes(30);
                    }
                    $status = $dateStart < $now && $now < $dateEnd;
                    break;
                case 3:
                    $dateStart = Carbon\Carbon::createFromTime($item->SubInterval_StartTimeHour, $item->SubInterval_StartTimeMinute);
                    $dateEnd = Carbon\Carbon::createFromTime($item->SubInterval_StartTimeHour, $item->SubInterval_StartTimeMinute)->addSeconds($item->SubInterval_DurationSecond);
                    if ($now->dayOfWeek != $item->SubInterval_DayOfWeek - 1 || $now->dayOfWeek == $item->SubInterval_DayOfWeek - 1 && $dateEnd < $now) {
                        $dateStart->next($item->SubInterval_DayOfWeek - 1);
                        $dateEnd->next($item->SubInterval_DayOfWeek - 1);
                    }
                    $dateStart->setTime($item->SubInterval_StartTimeHour, $item->SubInterval_StartTimeMinute);
                    $dateEnd->setTime($item->SubInterval_StartTimeHour, $item->SubInterval_StartTimeMinute)->addSeconds($item->SubInterval_DurationSecond);
                    if (in_array($item->ScheduleDefineIdx, array(6, 9, 10, 11))) {
                        $dateStart->addMinutes(30);
                    }
                    $status = $dateStart < $now && $now < $dateEnd;
                    break;
                default:
                    $dateStart = NULL;
                    $dateEnd = NULL;
                    $status = NULL;
                    break;
            }
            return array(
                "id" => $item->ScheduleDefineIdx,
                "start" => !is_null($dateStart) ? $dateStart->getTimestamp() : NULL,
                "end" => !is_null($dateEnd) ? $dateEnd->getTimestamp() : NULL,
                "status" => $status
            );
        });

        return array(
            "fortress" => $times->where("id", 6)->min(),
            "medusa" => $times->where("id", 2)->min(),
            "roc" => $times->where("id", 1)->min(),
            "special" => $times->where("id", 3)->min(),
            "selkis_neith" => $times->where("id", 9)->min(),
            "anubis_isis" => $times->where("id", 10)->min(),
            "haroeris_seth" => $times->where("id", 11)->min(),
            "ctf" => $times->where("id", 13)->min(),
            "ba_random" => $times->where("id", 17)->min(),
            "ba_party" => $times->where("id", 19)->min(),
            "ba_guild" => $times->where("id", 21)->min(),
            "ba_job" => $times->where("id", 23)->min()
        );
    }
}
