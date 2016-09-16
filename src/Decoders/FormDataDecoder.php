<?php

namespace RESTQuest\Decoders;

use RESTQuest\Exceptions\DecodeException;

/**
 * multipart/form-data content type decoder
 *
 * Class FormDataDecoder
 * @package RESTQuest\Decoders
 */
class FormDataDecoder extends Decoder
{
    /**
     * The number of bytes to read when using `fread()`
     *
     * @var integer
     */
    protected $bytesPerRead;

    /**
     * The max size of an uploaded file in bytes
     *
     * @var int
     */
    protected $maxFileSize;

    /**
     * The max number of uploaded files per request
     *
     * @var int
     */
    protected $maxFileCount;

    /**
     * The number of files processed
     *
     * @var int
     */
    protected $fileCount = 0;

    /**
     * The input stream to read multipart data from
     *
     * @var resource
     */
    protected $fp;

    /**
     * The multipart field boundary
     *
     * @var string
     */
    protected $boundary;

    /**
     * The final field boundary, with the end dashes
     *
     * @var string
     */
    protected $lastBoundary;

    /**
     * The contents of the input stream that have been read, but not processed
     *
     * @var string
     */
    protected $buffer = "";

    /**
     * @param string $path The path to the stream to read from
     * @param string $contentType The content type of the request body
     * @param int $contentLength The size of the request body
     * @param \sndsgd\http\data\decoder\DecoderOptions
     * @param int $bytesPerRead The number of bytes to read at a time
     */
    public function __construct(
        $path,
        $contentType,
        $contentLength,
        $options = null,
        $bytesPerRead = 8192
    )
    {
        parent::__construct($path, $contentType, $contentLength, $options);
        $this->maxFileSize = $this->options->getMaxFileSize();
        $this->maxFileCount = $this->options->getMaxFileCount();
        $this->bytesPerRead = $bytesPerRead;
    }


    public function decode()
    {
        $this->boundary = $this->getBoundary();
        $this->lastBoundary = "{$this->boundary}--";

        $this->fp = fopen($this->path, "r");
        if ($this->fp === false) {
            throw new \RuntimeException(
                "failed to open '{$this->path}' for reading"
            );
        }

        while ($this->fieldsRemain() === true) {
            $fieldHeader = $this->getFieldHeader();

            # if a filename wasn't provided, assume the field is not a file
            if ($fieldHeader['filename'] === "") {
                $value = $this->getValueFromField();

                # check if we are dealing with an array
                $this->addValue($this->post, $fieldHeader['name'], $value);
            } else {
                if ($this->fileCount < $this->maxFileCount) {
                    $file = $this->getFileFromField($fieldHeader['name'], $fieldHeader['filename'], $fieldHeader['contentType']);
                    $this->addValue($this->files, $fieldHeader['name'], $file);
                } else {

                }

                $this->fileCount++;
            }
        }

        fclose($this->fp);

        return $this->getValuesAsArray();
    }

    /**
     * Retrieve the parameter boundary from the content type header
     *
     * @return string
     * @throws DecodeException If a boundary is not present
     */
    protected function getBoundary()
    {
        $pos = strpos($this->contentType, "boundary=");
        if ($pos === false) {
            throw new DecodeException(
                "missing value for 'boundary' in content-type header"
            );
        }
        return "--" . substr($this->contentType, $pos + 9);
    }

    /**
     * Determine if any more fields remain in the stream
     * @return bool
     * @throws DecodeException
     */
    protected function fieldsRemain()
    {
        $bufferlen = strlen($this->buffer);
        $minlen = strlen($this->lastBoundary);

        # if the buffer is too short to contain the last boundary
        # read enough bytes into the buffer to allow for a strpos test
        if ($bufferlen < $minlen) {
            if (feof($this->fp)) {
                fclose($this->fp);
                throw new DecodeException(
                    "Invalid multipart data encountered; " .
                    "end of content was reached before expected"
                );
            }

            $bytes = fread($this->fp, $this->bytesPerRead);
            if ($bytes === false) {
                fclose($this->fp);
                throw new \RuntimeException(
                    "failed to read $minlen bytes from input stream"
                );
            }

            $this->buffer .= $bytes;
        }

        # if the buffer starts with the last boundary, there are no more fields
        return (strpos($this->buffer, $this->lastBoundary) !== 0);
    }

    /**
     * Read the header values for the current field from the input stream
     * @return array
     * @throws DecodeException
     */
    protected function getFieldHeader()
    {
        # read the input stream until the empty line after the header
        $position = $this->readUntil("\r\n\r\n");

        # separate the header from the field content
        # remove the header content from the buffer
        $header = substr($this->buffer, 0, $position);
        $this->buffer = substr($this->buffer, $position + 4);

        $regex =
            "/content-disposition:[\t ]+?form-data;" .
            "[\t ]+(?:name=\"(.*?)\")?" .
            "(?:;[\t ]+?filename=\"(.*?)\")?/i";

        if (preg_match($regex, $header, $matches) !== 1) {
            fclose($this->fp);
            throw new DecodeException(
                "Invalid multipart data; 'Content-Disposition' " .
                "malformed or missing in file field header"
            );
        }
        # we have no need for the entire match, so we drop it here
        $paddedMatches = array_pad($matches, 3, "");
        $name = $paddedMatches[1];
        $filename = $paddedMatches[2];

        # if a filename was in the content disposition
        # attempt to find its content type in the field header
        if ($filename !== "") {
            $regex = "/content-type:[\t ]+?(.*)(?:;|$)/mi";
            if (preg_match($regex, $header, $matches) === 1) {
                $contentType = strtolower($matches[1]);
            } else {
                $contentType = "";
            }
        } else {
            $contentType = "";
        }

        return [
            'name'        => $name,
            'filename'    => $filename,
            'contentType' => $contentType
        ];
    }

    /**
     * Read the input stream into the buffer until a string is encountered
     *
     * @param string $search The string to read until
     * @return int The position of the string in the buffer
     * @throws DecodeException
     */
    protected function readUntil($search)
    {
        while (($position = strpos($this->buffer, $search)) === false) {
            if (feof($this->fp)) {
                fclose($this->fp);
                throw new DecodeException(
                    "Invalid multipart data encountered; " .
                    "end of content was reached before expected"
                );
            }
            $this->buffer .= fread($this->fp, $this->bytesPerRead);
        }
        return $position;
    }

    /**
     * Get the value of the current field in the input stream
     *
     * @return string
     */
    private function getValueFromField()
    {
        $position = $this->readUntil($this->boundary);

        # there is always a newline after the value and before the boundary
        # exclude that newline from the value
        $value = substr($this->buffer, 0, $position - 2);

        # update the buffer to exclude the value and the pre boundary newline
        $this->buffer = substr($this->buffer, $position);

        return $value;
    }

    /**
     * Inserts the value into the passed array.
     * Used to parse input array from HTTP request body, if needed
     *
     * @param array $fieldsArray
     * @param string $name name of the field
     * @param string $value value of the field
     */
    protected function addValue(&$fieldsArray, $name, $value)
    {
        $arrayCheck = substr($name, -2);

        if ($arrayCheck == '[]') {
            $nameSubstr = substr($name, 0, -2);
            if (!isset($fieldsArray[$nameSubstr]))
                $fieldsArray[$nameSubstr] = [];

            $fieldsArray[$nameSubstr][] = $value;
        } else {
            $fieldsArray[$name] = $value;
        }
    }

    /**
     * Copy file contents from the input stream to a temp file
     *
     * @param string $name The field name
     * @param string $filename The name of the uploaded file
     * @param string $unverifiedContentType The user provided content type
     * @return array
     */
    protected function getFileFromField(
        $name,
        $filename,
        $unverifiedContentType
    )
    {
        # create and open a temp file to write the contents to
        $tempPath = $this->getTempFilePath();
        $tempHandle = fopen($tempPath, "w");
        if ($tempHandle === false) {
            fclose($this->fp);
            throw new \RuntimeException("failed to open '$tempPath' for writing");
        }

        # number of bytes read from the input stream in the last loop cycle
        $bytesRead = 0;
        # the total number of bytes written to the temp file
        $bytesWritten = 0;

        # if anything is left over from the previous field, add it to the file
        if ($this->buffer !== "") {
            $bytesRead = fwrite($tempHandle, $this->buffer);
            if ($bytesRead === false) {
                fclose($this->fp);
                fclose($tempHandle);
                throw new \RuntimeException(
                    "fwrite() failed to write to '$tempPath'"
                );
            }
            $bytesWritten += $bytesRead;
        }

        while (($pos = strpos($this->buffer, $this->boundary)) === false) {
            $this->buffer = fread($this->fp, $this->bytesPerRead);
            $bytesRead = fwrite($tempHandle, $this->buffer);
            if ($bytesRead === false) {
                fclose($this->fp);
                fclose($tempHandle);
                throw new \RuntimeException(
                    "fwrite() failed to write to '$tempPath'"
                );
            }
            $bytesWritten += $bytesRead;
        }

        # determine the size of the file based on the boundary position
        $size = $bytesWritten - $bytesRead + $pos - 2;

        # trim the excess contents of the local buffer to the object buffer
        $this->buffer = substr($this->buffer, $pos);

        # if the uploaded file was empty
        if ($size < 1) {
            return $this->fileUploadError(
                UPLOAD_ERR_NO_FILE,
                $tempPath,
                $tempHandle,
                $filename,
                $unverifiedContentType,
                0
            );
        } # if the file exceeded the max upload size
        elseif ($size > $this->maxFileSize) {
            return $this->fileUploadError(
                UPLOAD_ERR_INI_SIZE,
                $tempPath,
                $tempHandle,
                $filename,
                $unverifiedContentType,
                0
            );
        }

        ftruncate($tempHandle, $size);
        fclose($tempHandle);

        return [
            "name"     => $filename,
            "type"     => $unverifiedContentType,
            "tmp_name" => $tempPath,
            "error"    => UPLOAD_ERR_OK,
            "size"     => $size
        ];
    }

    /**
     * Allow for stubbing the result of tempnam using reflection
     *
     * @return string
     */
    protected function getTempFilePath()
    {
        return tempnam(sys_get_temp_dir(), "php");
    }

    /**
     * Handle an invalid file upload
     *
     * @param int $errorCode The relevant PHP file upload error
     * @param string $tempPath The absolute path to the temp file
     * @param resource $tempHandle The handle for the temp file
     * @param string $filename The file name as provided by the client
     * @param string $contentType The mime type of the uploaded file
     * @param int $size The bytesize of the uploaded file
     * @return array
     */
    protected function fileUploadError(
        $errorCode,
        $tempPath,
        $tempHandle,
        $filename,
        $contentType,
        $size
    )
    {
        fclose($tempHandle);
        if ($tempPath && file_exists($tempPath)) {
            unlink($tempPath);
        }

        return [
            "name"     => $filename,
            "type"     => $contentType,
            "tmp_name" => $tempPath,
            "error"    => $errorCode,
            "size"     => $size
        ];
    }

    /**
     * Remove the temp file when the object is destroyed
     */
    public function __destruct()
    {
        foreach ($this->files as $file)
        {
            if ($file['tmp_name'] !== "" && file_exists($file['tmp_name'])) {
                unlink($file['tmp_name']);
            }
        }
    }

}