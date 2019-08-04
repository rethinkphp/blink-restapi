<?php

namespace blink\restapi;

use blink\core\BaseObject;
use blink\core\MiddlewareContract;
use blink\core\HttpException;
use blink\http\Response;
use blink\support\Json;
use rethink\typedphp\TypeParser;
use rethink\typedphp\TypeValidator;

/**
 * Class ResponseValidator
 *
 * @package blink\restapi
 */
class ResponseValidator extends BaseObject implements MiddlewareContract {

    public $responses = [];

    /**
     * @param Response $response
     * @throws HttpException
     */
    public function handle($response)
    {
        $responses = $this->responses;
        $code = $response->getStatusCode();

        if (!isset($responses[$code])) {
            throw new HttpException(500, "The response schema of status code: $code is not defined");
        }

        $data = $response->data;
        if ($data === null) {
            $data = Json::decode((string)$response->getBody());
        } else {
            $data = Json::decode(Json::encode($data));
        }

        $definition = app()->restapi->makeTypeParser(TypeParser::MODE_JSON_SCHEMA)->parse($responses[$code]);

        $validator = new TypeValidator();
        if (!$validator->validate($data, $definition)) {
            throw new HttpException(500, "Response schema validation failed: " . Json::encode($validator->getErrors()));
        }
    }
}
