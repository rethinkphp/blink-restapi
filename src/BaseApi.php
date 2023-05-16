<?php

namespace blink\restapi;

use blink\http\Request;
use blink\http\Response;
use rethink\typedphp\ApiInterface;
use rethink\typedphp\InputValidator;
use rethink\typedphp\TypeValidator;
use rethink\typedphp\TypeParser;
use blink\core\HttpException;

/**
 * Class BaseApi
 *
 * @package blink\restapi
 */
abstract class BaseApi implements ApiInterface
{
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

        $this->validateParameters($this->request);
        $this->validateRequestBody($this->request);

        if (! $this->request->params->boolean('schema_validation', $this->schemaValidation)) {
            return;
        }

        if (app()->environment !== 'prod') {
            $this->response->middleware([
                'class' => ResponseValidator::class,
                'responses' => static::responses() + $this->defaultResponses(),
                'schemaParser' => function (int $mode, $schema) {
                    return $this->parseSchema($mode, $schema, true);
                }
            ]);
        }
    }

    protected function validateParameters(Request $request)
    {
        if (! ($parameters = static::parameters())) {
            return;
        }

        $routingParams = $request->getAttribute('routing', []);
        $queryParams = $request->params->all();

        $validator = new InputValidator([
            'path' => $routingParams,
            'query' => $queryParams,
        ]);

        $parameters = $this->parseSchema(TypeParser::MODE_JSON_SCHEMA, $parameters);

        if (! $validator->validate($parameters)) {
            return $this->badRequest($validator->getErrors()[0]);
        }

        $validData = $validator->getData();

        $request->params->add($validData['query'] ?? []);
        $request->setAttribute('routing', $validData['path'] ?? []);
    }

    protected function validateRequestBody(Request $request)
    {
        if (! ($body = static::requestBody())) {
            return;
        }

        if ($this->isMultipartFormDataRequest($request)) {
            return;
        }

        $definition = $this->parseSchema(TypeParser::MODE_JSON_SCHEMA, $body);


        $validator = new TypeValidator();
        if (! $validator->validate($request->payload->all(), $definition)) {
            return $this->badRequest($validator->getErrors()[0]);
        }
    }

    /**
     * @param int $mode
     * @param string|array|object $schema
     * @param bool $withSharedSchemas
     * @return array
     */
    protected function parseSchema(int $mode, $schema, bool $withSharedSchemas = false): array
    {
        /** @var TypeParser $parser */
        $parser = app()->restapi->makeTypeParser($mode);

        $result = $parser->parse($schema);
        if ($withSharedSchemas) {
            $result['components']['schemas'] = $parser->getSchemas();
        }

        return $result;
    }

    private function isMultipartFormDataRequest(Request $request)
    {
        // possible value: multipart/form-data; boundary=------------------------f80f7f383827c25b
        $requestedContentType = $request->headers->first('content-type');

        return strpos($requestedContentType, 'multipart/form-data') !== false;
    }

    protected function defaultResponses()
    {
        return [];
    }

    protected function ok($result, $status = 201)
    {
        $this->response->statusCode = $status;
        $this->response->data = $result;
    }

    protected function noContent()
    {
        $this->response->statusCode = 204;
    }

    protected function unauthorised($message = 'Unauthorised')
    {
        throw new HttpException(401, $message);
    }

    protected function badRequest($message = 'Bad request')
    {
        throw new HttpException(400, $message);
    }

    protected function forbid($message = 'Permission denied')
    {
        throw new HttpException(403, $message);
    }

    protected function notFound($message = 'Not Found')
    {
        throw new HttpException(404, $message);
    }
}
