<?php

namespace brikdigital\craftopeninghours\data;

use Craft;
use yii\base\UnknownPropertyException;

/**
 * Class FieldData
 */
class DayData extends \ArrayObject
{

    /**
     * @param int $dayIndex
     * @param array $input
     */
    public function __construct(public int $dayIndex, array $input, public $isExclusion = false, public $exclusionReason = null, public $exclusionDate = null)
    {
        parent::__construct($input);
    }

    /**
     * @todo This can go away once https://github.com/twigphp/Twig/pull/2749 is merged
     * into a Twig release.
     *
     * @param string $name
     * @return mixed
     * @throws UnknownPropertyException
     */
    public function __get($name)
    {
        if ($this->offsetExists($name)) {
            return $this[$name];
        }

        throw new UnknownPropertyException('Undefined property: ' . $name);
    }

    /**
     * Returns the day name
     *
     * @param string|null $length The format length that should be returned. Values: `\craft\i18n\Locale::LENGTH_ABBREVIATED`, `::SHORT`, `::MEDIUM`, `::FULL`
     * @return string
     */
    public function getName(string $length = null): string
    {
        return Craft::$app->getLocale()->getWeekDayName($this->dayIndex, $length);
    }

    /**
     * Returns whether the day has any time slots filled in.
     *
     * @return bool
     */
    public function getIsBlank(): bool
    {
        foreach ($this as $slot) {
            if ($slot !== null) {
                return false;
            }
        }
        return true;
    }
}