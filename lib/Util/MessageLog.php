<?php

namespace Util;

/**
 * Class MessageLog
 * @package Util
 *
 * Manage reading and writing to log files
 */
class MessageLog {

    /**
     * @var bool true if file exists
     */
    private $can_access;

    /**
     * @var string file name to read/write from
     */
    private $file_name;

    public function __construct($file_name)
    {
        $this->can_access = false;

        $this->file_name = $file_name;
        if(file_exists($this->file_name)) {
            $this->can_access = true;
        } else {
            error_log("Unable to access file at $file_name");
        }
    }

    /**
     * Attempt to write text to file
     *
     * @param $text data to write to file
     * @return bool true if write succeeded, false otherwise
     */
    public function writeLog($text) {
        // verify file can be accessed
        if($this->can_access === false) {
            return false;
        }

        // write text to file
        return (file_put_contents($this->file_name, $text, FILE_APPEND) > 0);
    }

    /**
     * Attempt to read text from file
     *
     * @return bool|string false if read failed, otherwise return the file text
     */
    public function readLog() {
        // verify file can be accessed
        if($this->can_access === false) {
            return false;
        }

        return file_get_contents($this->file_name);
    }
}