<?php

namespace brikdigital\craftopeninghours\data;

use Craft;
use craft\helpers\DateTimeHelper;
use DateTime;
use Illuminate\Support\Arr;

/**
 * Class FieldData
 */
class FieldData extends \ArrayObject
{
        /**
     * Returns today’s hours.
     *
     * @return DayData
     */
    public function getToday(): DayData
    {
        return $this->_hoursByDate(new DateTime());
    }

    /**
     * Returns yesterday’s hours.
     *
     * @return DayData
     */
    public function getYesterday(): DayData
    {
        return $this->_hoursByDate(new DateTime('-1 day'));
    }

    /**
     * Returns tomorrow’s hours.
     *
     * @return DayData
     */
    public function getTomorrow(): DayData
    {
        return $this->_hoursByDate(new DateTime('+1 day'));
    }

    /**
     * @param DateTime $date
     * @return DayData
     */
    private function _hoursByDate(DateTime $date): DayData
    {
        $day = (int)$date->format('w');
        return $this[$day];
    }

    /**
     * Returns a range of the days in the current period.
     *
     * Specify days using these integers:
     *
     * - `0` – Sunday
     * - `1` – Monday
     * - `2` – Tuesday
     * - `3` – Wednesday
     * - `4` – Thursday
     * - `5` – Friday
     * - `6` – Saturday
     *
     * For example, `getRange(1, 5)` would give you data for Monday-Friday.
     *
     * If the ending day is omitted, then all days will be returned, but with the start day listed first.
     * For example, `getRange(1)` would give you data for Monday-Sunday.
     *
     * @param int $start The first day to return
     * @param int|null $end The last day to return. If null, it will be whatever day comes before `$start`.
     * @return DayData[]
     */
    public function getRange(int $start, int $end = null): array
    {
        if ($end === null) {
            $end = $start === 0 ? 6 : $start - 1;
        }

        $data = (array)$this;

        if ($end >= $start) {
            return array_slice($data, $start, $end - $start + 1);
        }

        return array_merge(
            array_slice($data, $start),
            array_slice($data, 0, $end + 1)
        );
    }

    /**
     * Returns whether any day has any time slots filled in.
     *
     * @return bool
     */
    public function getIsAllBlank(): bool
    {
        foreach ($this as $day) {
            if (!$day->getIsBlank()) {
                return false;
            }
        }

        return true;
    }

    public function getPeriods($getAllPeriods = false)
    {
        return array_filter($this['periodData'], function ($val, $key) use ($getAllPeriods) {
            $now = new DateTime();
            if(
                (
                    !is_string($key) &&
                    property_exists($val,'till') &&
                    $val->till->getTimestamp() >= $now->getTimestamp()
                ) ||
                (
                    !is_string($key) &&
                    $getAllPeriods == true
                )
            ) {
                return true;
            }
            return false;
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function getExclusions($getAllExclusions = false)
    {
        if($getAllExclusions) return $this['periodData']['exclusions'];
        return array_filter($this['periodData']['exclusions'], function ($val) {
            $now = new DateTime();
            if(property_exists($val,'till') && $val->till->getTimestamp() >= $now->getTimestamp()) return true;
            return false;
        });
    }

    public function getExclusionsBetween(string $start, string $end)
    {
        $start = new DateTime($start);
        $end = new DateTime($end);
        $exclusions = $this['periodData']['exclusions'];
        $res = [];

        foreach ($exclusions as $exclusion) {
            $exDate = new DateTime($exclusion[0]['date']);
            if ($exDate->getTimestamp() >= $start->getTimestamp() && $exDate->getTimestamp() <= $end->getTimestamp()) {
                array_push($res, $exclusion);
            }
        }

        $res = array_filter($res);

        return $res;
    }

    public function todaysTimes()
    {
        $today = new DateTime();
        $day = $this->findDayInPeriods($today);
        $exclusion = $this->findExclusionByDay($today);

        if ($exclusion) {
            return $exclusion['days'];
        }
        if ($day) {
            return $day;
        }

        return null;
    }

    public function isOpenToday(): bool
    {
        $today = new DateTime();

        $exclusion = $this->findExclusionByDay($today);
        if ($exclusion) {
            return (!empty($exclusion['slot0']['time']) && !empty($exclusion['slot1']['time']));
        }

        $day = $this->findDayInPeriods($today);
        if (isset($day['open']) && isset($day['closed'])) {
            return true;
        }

        return false;
    }

    public function isOpenNow(): bool
    {
        $today = new DateTime();
        $isOpen = $this->isOpenToday();

        if ($isOpen) {
            $day = $this->findDayInPeriods($today);
            $exclusion = $this->findExclusionByDay($today);
            $now = $this->minuteOfDay($today);

            if ($exclusion) {
                $slot0 = new DateTime($exclusion['slot0']['time']);
                $slot1 = new DateTime($exclusion['slot1']['time']);
                return $now >= $this->minuteOfDay($slot0) && $now <= $this->minuteOfDay($slot1);
            }

            return $now >= $this->minuteOfDay($day['open']) && $now < $this->minuteOfDay($day['closed']);
        }


        return false;
    }

    private function findDayInPeriods(DateTime $day)
    {
        $res = null;
        $num = $day->format('w');

        foreach ($this['periodData'] as $period) {
            foreach ($period as $day) {
                if ($num == $day->dayIndex) {
                    $res = $day;
                    break 2;
                }
            }
        }

        return $res;
    }

    private function findExclusionByDay(DateTime $day)
    {
        $res = null;
        $num = $day->setTime(0,0,0,0)->format('U');

        foreach ($this['periodData']['exclusions'] as $exclusion) {
            $exclusionDate = (new DateTime($exclusion[0]['date']))->setTime(0,0,0,0)->format('U');
            if ($num == $exclusionDate) {
                $res = $exclusion;
                break;
            }
        }

        return $res;
    }

    private function minuteOfDay(DateTime $date): int
    {
        return (int)$date->format('G') * 60 + (int)ltrim($date->format('s'), '0');
    }
}