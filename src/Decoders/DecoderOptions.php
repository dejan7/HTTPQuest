<?php

namespace RESTQuest\Decoders;

class DecoderOptions
{
    /**
     * The max upload filesize in bytes
     *
     * @var int
     */
    protected $maxFileSize;

    /**
     * Stubbable method for retrieving ini values
     *
     * @param string $name
     * @return mixed
     */
    public function readValue($name)
    {
        return ini_get($name);
    }

    /**
     * Retrieve the `max_input_vars` ini setting
     *
     * @return int
     */
    public function getMaxVars()
    {
        return (int) $this->readValue("max_input_vars");
    }

    /**
     * Retrieve the `max_input_nesting_level` ini setting
     *
     * @return int
     */
    public function getMaxNestingLevels()
    {
        return (int) $this->readValue("max_input_nesting_level");
    }

    /**
     * Retrieve the `enable_post_data_reading` ini setting
     *
     * @return bool
     */
    public function getPostDataReadingEnabled()
    {
        return (bool) $this->readValue("enable_post_data_reading");
    }

    /**
     * Retrieve the `max_file_uploads` ini setting
     *
     * @return int
     */
    public function getMaxFileCount()
    {
        return (int) $this->readValue("max_file_uploads");
    }

    /**
     * Retrieve the `upload_max_filesize` ini setting
     *
     * @return int
     */
    public function getMaxFileSize()
    {
        if ($this->maxFileSize === null) {
            $value = $this->readValue("upload_max_filesize");
            $units = "BKMGT";
            $unit = preg_replace("/[^$units]/i", "", $value);
            $value = floatval($value);
            if ($unit) {
                $value *= pow(1024, stripos($units, $unit[0]));
            }
            $this->maxFileSize = (int) $value;
        }

        return $this->maxFileSize;
    }
}