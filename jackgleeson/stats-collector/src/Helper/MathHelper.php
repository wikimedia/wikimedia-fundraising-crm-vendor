<?php

namespace Statistics\Helper;

use Statistics\Exception\StatisticsCollectorHelperException;

class MathHelper
{

    /**
     * @var \Statistics\Helper\TypeHelper
     */
    protected $typeHelper;

    /**
     * MathHelper constructor.
     */
    public function __construct()
    {
        $this->typeHelper = new TypeHelper();
    }

    /**
     * Check if value is a number or a collection of numbers available to
     * summed.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isSummable($value)
    {
        return $this->typeHelper->isIntOrFloatRecursive($value);
    }

    /**
     * Check if value is a number or a collection of numbers available to
     * averaged.
     *
     * @param $value
     *
     * @return bool
     */
    public function isAverageable($value)
    {
        return $this->typeHelper->isIntOrFloatRecursive($value);
    }

    /**
     * Get the average(mean) of the supplied values
     *
     * @param mixed $values
     *
     * @return float|int
     * @throws \Statistics\Exception\StatisticsCollectorHelperException
     */
    public function average($values)
    {
        switch (gettype($values)) {
            case "integer":
            case "double":
                return $values;
                break;
            case "array":
                return (count($values) > 0) ? array_sum($values) / count($values) : 0;
                break;
            default:
                throw new StatisticsCollectorHelperException("Unable to return sum for supplied arguments (are the values numeric?)");
                break;
        }
    }

    /**
     * Get the sum of supplied values
     *
     * @param mixed $values
     *
     * @return float|int
     * @throws \Statistics\Exception\StatisticsCollectorException
     */
    public function sum($values)
    {
        switch (gettype($values)) {
            case "integer":
            case "double":
                return $values;
                break;
            case "array":
                return array_sum($values);
                break;
            default:
                throw new StatisticsCollectorHelperException("Unable to return sum for supplied arguments (are the values numeric?)");
                break;
        }
    }

    /**
     * Get the count of items supplied
     *
     * @param mixed $values
     *
     * @return float|int
     */
    public function count($values)
    {
        switch (gettype($values)) {
            case "array":
                return count($values);
                break;
            default:
                return 1;
                break;
        }
    }
}