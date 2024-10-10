<?php

namespace brikdigital\craftopeninghours\data;

use Craft;
use yii\base\UnknownPropertyException;

/**
 * Class FieldData
 */
class PeriodData extends \ArrayObject
{
    /**
     * @var \DateTime Start date of this period
     */
    public $from;

    /**
     * @var \DateTime End date of this period
     */
    public $till;

    public array $exclusions;

    /**
     * @param int $dayIndex
     * @param array $input
     */
    public function __construct(\DateTime|null $from, \DateTime|null $till, array $days)
    {
        $this->from = $from;
        $this->till = $till;
        parent::__construct($days);
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
}