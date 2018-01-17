<?php

namespace Statistics\Collector;

interface iSingleton
{

    public static function getInstance();

    public static function tearDown();

}