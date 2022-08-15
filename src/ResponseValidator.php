<?php

namespace blink\restapi;

use blink\core\BaseObject;
use blink\core\HttpException;
use blink\core\MiddlewareContract;
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
     * @var callable
     */
    public $schemaParser;

    /**
     * @param Response $response
     * @throws HttpException
     */
    public function handle($response)
    {
        $responses = $this->responses;
        $code = $response->getStatusCode();

        if (! array_key_exists($code, $responses)) {
            throw new HttpException(500, "The response schema of status code: $code is not defined");
        }

        if ($responses[$code] === null) {
            if ($response->data !== null) {
                throw new HttpException(500, "The response of status code: $code is incorrect, no response body required");
            }
            return;
        }

        $data = $response->data;
        if ($data === null) {
            $body = (string)$response->getBody();
            if (empty($body) {
                $data = null;
            } else {
                $data = Json::decode($body);
            }
        } else {
            $data = Json::decode(Json::encode($data));
        }

        $definition = ($this->schemaParser)(TypeParser::MODE_JSON_SCHEMA, $responses[$code]);

        $validator = new TypeValidator();
        if (!$validator->validate($data, $definition)) {
            throw new HttpException(500, sprintf(
                "Response schema validation failed. error: %s, response data: %s",
                Json::encode($validator->getErrors()),
                Json::encode($data)
            ));
        }
    }
}
