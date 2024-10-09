<?php

namespace brikdigital\craftopeninghours\data;

use Craft;
use yii\base\UnknownPropertyException;

/**
 * Class FieldData
 */
class ExclusionData extends \ArrayObject
{


    public array $exclusions;
    public function __construct(array|string $days)
    {
        parent::__construct($days === "" ? [] : $days);
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