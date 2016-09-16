<?php

Namespace RESTQuest;

class RESTQuest
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
     * @var RESTQuestOptions - configuration object
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
        ContentTypes::FORMDATA => \RESTQuest\Decoders\FormDataDecoder::class,
        ContentTypes::JSON => \RESTQuest\Decoders\JSONDecoder::class,
        ContentTypes::X_WWW_FORM_URLENCODED => \RESTQuest\Decoders\FormUrlEncodedDecoder::class,
    ];

    /**
     * RESTQuest constructor
     *
     * @param null $server
     * @param null $input
     * @param RESTQuestOptions|null $options
     */
    public function __construct($server = null, $input = null, RESTQuestOptions $options = null)
    {
        if (!isset($server))
            $server = $_SERVER;

        if (!isset($input))
            $input = "php://input";

        if (!isset($options)) {
            //default options
            $options = new RESTQuestOptions();

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
        if (!$this->server['CONTENT_TYPE']) {
            trigger_error("RESTQuest warning: Content Type header not set. Decoding not executed.", E_USER_WARNING);
            return;
        }

        if (!$this->server['REQUEST_METHOD']) {
            trigger_error("RESTQuest warning: Request Method not set. Decoding not executed.", E_USER_WARNING);
            return;
        }


        $option = $this->options->getOption($this->server['REQUEST_METHOD'], $this->server['CONTENT_TYPE']);

        if ($option && isset($this->decoders[$option])) {
            $this->decoder = new $this->decoders[$option](
                $this->input,
                $_SERVER['CONTENT_TYPE'],
                $_SERVER['CONTENT_LENGTH']
            );

            $data = $this->decoder->decode();

            if ($data['post'])
                $post = $data['post'];

            if ($data['files'])
                $files = $data['files'];
        }
    }
}