<?php

namespace RESTQuest;

use RESTQuest\Exceptions\NoActiveMethodException;

class RESTQuestOptions
{
    /**
     * array that holds the options
     *
     * @var array
     */
    protected $options;

    /**
     * currently active method
     *
     * @var string
     */
    private $currentMethod;


    /**
     * pushes method to option array
     *
     * @param $method
     * @return $this
     */
    public function forMethod($method)
    {
        $this->currentMethod = $method;
        $this->options[$method] = [];
        return $this;
    }

    /**
     * pushes content type to options[method] array
     *
     * @param $contentType
     * @return $this
     * @throws NoActiveMethodException
     */
    public function parse($contentType)
    {
        if (!$this->currentMethod)
            throw new NoActiveMethodException;

        $this->options[$this->currentMethod][] = $contentType;
        return $this;
    }

    /**
     * If the $method, $contentType combination is configured, it returns plain content type (Without e.g. boundary= etc.)
     * If passed combination is not supported, returns null
     *
     * @param $method
     * @param $contentType
     * @return null|string
     */
    public function getOption($method, $contentType)
    {
        if (isset($this->options[$method])) {
            foreach ($this->options[$method] as $ct) {
                if (strpos($contentType, $ct) !== false) {
                    return $ct;
                }
            }
            return null;
        } else {
            return null;
        }
    }
}