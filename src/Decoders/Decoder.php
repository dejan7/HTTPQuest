<?php

namespace HTTPQuest\Decoders;

class Decoder
{
    /**
     * The path to the stream to decode
     *
     * @var string
     */
    protected $path;

    /**
     * The type of content to decode
     *
     * @var string
     */
    protected $contentType;
    /**
     * The length for the content to decode
     *
     * @var int
     */
    protected $contentLength;

    /**
     * An object containing ini settings related to body decoding
     *
     * @var \HTTPQuest\Decoders\DecoderOptions
     */
    protected $options;

    /**
     * An array of decoded request body parameters (representation of things which PHP puts in $_POST by default)
     *
     * @var array
     */
    protected $post;

    /**
     * An array of uploaded files (representation of things which PHP puts in $_FILES by default)
     *
     * @var array
     */
    protected $files;

    /**
     * @param string $path
     * @param string $contentType
     * @param int $contentLength
     * @param \HTTPQuest\Decoders\DecoderOptions|null $options
     */
    public function __construct(
        $path,
        $contentType,
        $contentLength,
        DecoderOptions $options = null
    )
    {
        $this->path = $path;
        $this->contentType = $contentType;
        $this->contentLength = $contentLength;
        $this->options = $options ? $options : new DecoderOptions();

        $this->post = [];
        $this->files = [];
    }

    /**
     * Gets the parsed post
     *
     * @return array
     */
    public function getValuesAsArray()
    {
        //converts the files array with multiple values into PHP's ugly format
        //http://php.net/manual/en/reserved.variables.files.php#89674
        if ($this->files) {
            $parsedFileArray = [];
            foreach ($this->files as $name => $file) {
                if (!isset($file['name'])) {
                    if (!isset($parsedFileArray[$name])) {
                        $parsedFileArray[$name]['name'] = [];
                        $parsedFileArray[$name]['type'] = [];
                        $parsedFileArray[$name]['tmp_name'] = [];
                        $parsedFileArray[$name]['error'] = [];
                        $parsedFileArray[$name]['size'] = [];
                    }
                    foreach ($file as $subfile) {
                        $parsedFileArray[$name]['name'][] = $subfile['name'];
                        $parsedFileArray[$name]['type'][] = $subfile['type'];
                        $parsedFileArray[$name]['tmp_name'][] = $subfile['tmp_name'];
                        $parsedFileArray[$name]['error'][] = $subfile['error'];
                        $parsedFileArray[$name]['size'][] = $subfile['size'];
                    }
                } else {
                    $parsedFileArray[$name] = $file;
                }
            }
        }

        return [
            'post'  => $this->post,
            'files' => isset($parsedFileArray) ? $parsedFileArray : $this->files
        ];
    }
}