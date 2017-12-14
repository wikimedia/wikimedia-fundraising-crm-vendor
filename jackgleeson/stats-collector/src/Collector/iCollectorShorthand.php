<?php

namespace Statistics\Collector;

interface iCollectorShorthand
{

    public function get($namespace, $withKeys = null, $default = null);

    public function getWithKey($namespace, $default = null);

    public function add($name, $value, $options = []);

    public function clobber($name, $value, $options = []);

    public function del($namespace);

    public function inc($namespace, $increment);

    public function dec($namespace, $decrement);

    public function avg($namespace);

    public function sum($namespace);

    public function count($namespace);

    public function all();

    public function ns($namespace);


}