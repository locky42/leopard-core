<?php

namespace App\Core\Controllers;

/**
 * Abstract class ApiController
 *
 * This class serves as a base controller for API-related functionality.
 * Extend this class to implement specific API endpoints and logic.
 *
 * @package Core\Controllers
 */
abstract class ApiController extends AbstractController
{
    /**
     * @var string The response type for the API controller.
     * Default is 'json', which can be overridden in subclasses.
     */
    protected string $responseType = 'json';

    /**
     * Sets the response type for the API controller.
     *
     * @param string $type The response type to set (e.g., 'json', 'xml').
     * @return void
     */
    public function setResponseType(string $type): void
    {
        $this->responseType = $type;
    }

    /**
     * Formats the given data into a string response.
     *
     * @param mixed $data The data to be formatted.
     * @return string The formatted string response.
     */
    protected function formatResponse(mixed $data): string
    {
        switch ($this->responseType) {
            case 'xml':
                $xml = new \SimpleXMLElement('<root/>');
                array_walk_recursive($data, function ($value, $key) use ($xml) {
                    $xml->addChild($key, $value);
                });
                $response = $this->get('response')->withHeader('Content-Type', 'application/xml');
                $this->container->set('response', function () use ($response) {
                    return $response;
                });
                return $xml->asXML();
            case 'json':
            default:
                $response = $this->get('response')->withHeader('Content-Type', 'application/json');
                $this->container->set('response', function () use ($response) {
                    return $response;
                });
                return json_encode($data);
        }
    }
}
