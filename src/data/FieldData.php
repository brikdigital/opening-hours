<?php

namespace brikdigital\craftopeninghours\data;

use craft\helpers\DateTimeHelper;
use DateTime;
use Illuminate\Support\Arr;

/**
 * Class FieldData
 */
class FieldData extends \ArrayObject
{
    /**
     * Returns Sunday’s hours
     *
     * @return DayData
     */
    public function getSun(): DayData
    {
        return $this[0];
    }

    /**
     * Returns Monday’s hours
     *
     * @return DayData
     */
    public function getMon(): DayData
    {
        return $this[1];
    }

    /**
     * Returns Tuesday’s hours
     *
     * @return DayData
     */
    public function getTue(): DayData
    {
        return $this[2];
    }

    /**
     * Returns Wednesday’s hours
     *
     * @return DayData
     */
    public function getWed(): DayData
    {
        return $this[3];
    }

    /**
     * Returns Thursday’s hours
     *
     * @return DayData
     */
    public function getThu(): DayData
    {
        return $this[4];
    }

    /**
     * Returns Friday’s hours
     *
     * @return DayData
     */
    public function getFri(): DayData
    {
        return $this[5];
    }

    /**
     * Returns Saturday’s hours
     *
     * @return DayData
     */
    public function getSat(): DayData
    {
        return $this[6];
    }

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
     * Returns a range of the days.
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

    public function getPeriods()
    {
        return array_filter($this['periodData'], function ($val, $key) {
            if (!is_string($key)) return true;
            return false;
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function getExclusions()
    {
        return $this['periodData']['exclusions'];
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
            return $exclusion;
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
        $num = $day->format('w');

        foreach ($this['periodData']['exclusions'] as $exclusion) {
            $exclusionDate = (new DateTime($exclusion[0]['date']))->format('w');
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