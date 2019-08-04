<?php

namespace blink\restapi;

use blink\http\Request;
use blink\http\Response;
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

    protected $schemaValidation = true;
    protected $request;
    protected $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function before($action)
    {
        if ($this->schemaValidation && app()->environment !== 'prod') {
            $this->response->middleware([
                'class' => ResponseValidator::class,
                'responses' => static::responses() + $this->defaultResponses(),
            ]);
        }
    }

    protected function defaultResponses()
    {
        return [];
    }
}
