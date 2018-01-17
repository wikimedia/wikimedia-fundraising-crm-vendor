<?php

namespace Statistics\Helper;

class ArrayHelper
{

    /**
     * Flatten a multi-dimensional array down to a single array
     *
     * TODO: work out a way to handle clashing keys better (currently overwrites)
     *
     * @param array $array
     *
     * @return array
     */
    public function flatten($array = [])
    {
        $flattened = [];
        array_walk_recursive($array, function ($value, $key) use (&$flattened) {
            if (is_numeric($key) === false) {
                $flattened[$key] = $value;
            } else {
                $flattened[] = $value;
            }
        });
        return $flattened;
    }
}