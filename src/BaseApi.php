<?php

namespace blink\restapi;

use rethink\typedphp\ApiInterface;

/**
 * Class BaseApi
 *
 * @package blink\restapi
 */
abstract class BaseApi implements ApiInterface
{
    public static $op;
    public static $path;
    public static $verb;
}
