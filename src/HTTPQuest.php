<?php

Namespace HTTPQuest;

class HTTPQuest
{
    /**
     * @var array - server config array
     */
    protected $server;

    /**
     * @var string - location of the raw body e.g. php://input
     */
    protected $input;

    /**
     * @var HTTPQuestOptions - configuration object
     */
    protected $options;

    /**
     * @var object - active decoder instance
     */
    protected $decoder;

    /**
     * @var array - currently supported decoders
     */
    protected $decoders = [
        ContentTypes::FORMDATA => \HTTPQuest\Decoders\FormDataDecoder::class,
        ContentTypes::JSON => \HTTPQuest\Decoders\JSONDecoder::class,
        ContentTypes::X_WWW_FORM_URLENCODED => \HTTPQuest\Decoders\FormUrlEncodedDecoder::class,
    ];

    /**
     * HTTPQuest constructor
     *
     * @param null $server
     * @param null $input
     * @param HTTPQuestOptions|null $options
     */
    public function __construct($server = null, $input = null, HTTPQuestOptions $options = null)
    {
        if (!isset($server))
            $server = $_SERVER;

        if (!isset($input))
            $input = "php://input";

        if (!isset($options)) {
            //default options
            $options = new HTTPQuestOptions();

            $options->forMethod(Requests::POST)
                ->parse(ContentTypes::JSON);

            $options->forMethod(Requests::PUT)
                ->parse(ContentTypes::FORMDATA)
                ->parse(ContentTypes::X_WWW_FORM_URLENCODED)
                ->parse(ContentTypes::JSON);

            $options->forMethod(Requests::PATCH)
                ->parse(ContentTypes::FORMDATA)
                ->parse(ContentTypes::X_WWW_FORM_URLENCODED)
                ->parse(ContentTypes::JSON);
        }

        $this->server = $server;
        $this->input = $input;
        $this->options = $options;
    }

    /**
     * @param $post - decoded body key value pairs will be passed to this variable
     * @param $files - uploaded files will be passed to this variable
     */
    public function decode(&$post, &$files)
    {
        if (!isset($this->server['CONTENT_TYPE']) || !isset($this->server['REQUEST_METHOD'])) {
            return;
        }

        $option = $this->options->getOption($this->server['REQUEST_METHOD'], $this->server['CONTENT_TYPE']);

        if ($option && isset($this->decoders[$option])) {
            $this->decoder = new $this->decoders[$option](
                $this->input,
                $this->server['CONTENT_TYPE'],
                isset($this->server['CONTENT_LENGTH']) ? $this->server['CONTENT_LENGTH'] : 0
            );

            $data = $this->decoder->decode();

            if ($data['post'])
                $post = $data['post'];

            if ($data['files'])
                $files = $data['files'];
        }
    }
}