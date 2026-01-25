<?php

namespace Leopard\Core\Controllers;

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

    protected array $xmlSettings = [
        'version' => '1.0',
        'encoding' => 'UTF-8',
        'rootElement' => 'root',
        'rootAttributes' => [],
    ];

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
     * Sets the XML settings for the API controller.
     */
    public function setXmlSettings(array $settings): void
    {
        $this->xmlSettings = array_merge($this->xmlSettings, $settings);
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
                $xml = new \SimpleXMLElement(
                    '<?xml version="' . $this->xmlSettings['version'] . '" encoding="' . $this->xmlSettings['encoding'] . '"?>
                    <' . $this->xmlSettings['rootElement'] . '/>');
                foreach ($this->xmlSettings['rootAttributes'] as $attrName => $attrValue) {
                    $xml->addAttribute($attrName, $attrValue);
                }
                $this->xmlRecursive($xml, (array)$data);
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

    /**
     * Recursively converts an array to XML elements.
     *
     * @param \SimpleXMLElement $xml The SimpleXMLElement to append to.
     * @param array $data The data array to convert.
     * @param string|null $key The key for the current element (used for nested arrays).
     * @return void
     */
    protected function xmlRecursive(\SimpleXMLElement $xml, array $data, $key = null): void
    {
        foreach ($data as $dataKey => $value) {
            $xmlElement = ($key !== null && !is_numeric($key)) ? $xml->addChild($key) : $xml;

            if (is_array($value)) {
                $this->xmlRecursive($xmlElement, $value, $dataKey);
            } elseif (is_object($value)) {
                $this->xmlRecursive($xmlElement, (array)$value, $dataKey);
            } else {
                $xmlElement->addChild($dataKey, htmlspecialchars($value));
            }
        }
    }
}
