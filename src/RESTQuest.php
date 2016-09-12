<?php

Namespace RESTQuest;

use RESTQuest\Exceptions\MalformedContentTypeHeader;

class RESTQuest
{
    const FORMDATA = 'multipart/form-data';
    const X_WWW_FORM_URLENCODED = 'application/x-www-form-urlencoded';
    const JSON = 'application/json';

    private $requestRawBody;
    private $requestMethod;
    private $contentType;
    private $requestParser;
    private $formDataBoundary;

    /**
     * RESTQuest constructor
     *
     * @param string|null $requestRawBody
     * @param string|null $requestMethod
     * @param string|null $contentType
     */
    public function __construct($requestRawBody = null, $requestMethod = null, $contentType = null)
    {
        //set content type
        if (isset($contentType)) {
            $this->contentType = $contentType;
        } else {
            $this->contentType = isset($_SERVER['CONTENT_TYPE']) ? $this->determineContentType() : null;
        }

        //set request raw body
        if (isset($requestRawBody)) {
            $this->requestRawBody = $requestRawBody;
        } else {
            $rawData = fopen("php://input", "r");

            // Read the data 1 KB at a time
            $this->requestRawBody = '';
            while ($chunk = fread($rawData, 1024))
                $this->requestRawBody .= $chunk;
        }

        //set server method
        if (isset($requestMethod)) {
            $this->requestMethod = $requestMethod;
        } else {
            $this->requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
        }

        $this->requestParser = new RequestParser($this->requestRawBody);
    }

    /**
     * Retrieves HTTP 'Content-Type' header and parses it when needed
     * (in multipart/form-data case)
     *
     * @return string
     * @throws MalformedContentTypeHeader
     */
    private function determineContentType()
    {
        if ($_SERVER['CONTENT_TYPE'] == $this::JSON) {
            return $this::JSON;
        } else if ($_SERVER['CONTENT_TYPE'] == $this::X_WWW_FORM_URLENCODED) {
            return $this::X_WWW_FORM_URLENCODED;
        } else if (strpos($_SERVER['CONTENT_TYPE'], $this::FORMDATA) !== false) {
            //in this case raw header looks something like this:
            //Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryabcd1234567
            //so we have to parse it
            preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
            if (!isset($matches[1]))
                throw new MalformedContentTypeHeader();

            $this->formDataBoundary = $matches[1];
            return $this::FORMDATA;
        } else {
            return '';
        }
    }

    /**
     * Parses the request and populates $_POST for cases covered
     *
     * Call this method somewhere at the start of your application (e.g. during bootstrapping)
     */
    public function parse()
    {
        if ($this->requestMethod == 'GET') {
            //do nothing, data is available in $_GET automatically
        } else if ($this->requestMethod == 'POST') {
            $this->parsePOST();
        } else if ($this->requestMethod == 'PUT' || $this->requestMethod == 'PATCH') {
            $this->parsePUTorPATCH();
        }
    }

    /**
     * POST request parsing
     *
     * multipart/form-data:               parsed automatically by PHP
     * application/x-www-form-urlencoded: parsed automatically by PHP
     * application/json:                  RESTQuest does the parsing
     */
    private function parsePOST()
    {
        if ($this->contentType == $this::JSON) {
            $this->requestParser->parseJSON($this->requestRawBody);
        }
    }

    /**
     * PUT/PATCH request parsing
     *
     * multipart/form-data:               RESTQuest does the parsing
     * application/x-www-form-urlencoded: RESTQuest does the parsing
     * application/json:                  RESTQuest does the parsing
     */
    private function parsePUTorPATCH()
    {

        if ($this->contentType == $this::FORMDATA) {
            $this->requestParser->parseFormData($this->requestRawBody, $this->formDataBoundary);
        } else if ($this->contentType == $this::X_WWW_FORM_URLENCODED) {
            $this->requestParser->parseXWWWFormUrlencoded($this->requestRawBody);
        } else if ($this->contentType == $this::JSON) {
            $this->requestParser->parseJSON($this->requestRawBody);
        }
    }
}