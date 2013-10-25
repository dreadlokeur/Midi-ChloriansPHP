<?php

namespace framework\config\readers;

use framework\config\Reader;
use framework\utility\Validate;

class Xml extends Reader {

    protected $_filename;
    protected $_datas;

    public function __construct($filename) {
        if (!file_exists($filename))
            throw new \Exception('File : "' . $filename . '" not exists');
        if (!is_readable($filename))
            throw new \Exception('File : "' . $filename . '" is not readable');
        if (!Validate::isFileMimeType('xml', $filename))
            throw new \Exception('File : "' . $filename . '" is not a xml file');

        $this->_filename = $filename;
    }

    public function read() {
        $xml = @simplexml_load_file($this->_filename);
        if ($xml === null || $xml === false)
            throw new \Exception('Invalid xml file : "' . $this->_filename . '"');

        return json_decode(json_encode($xml), true);
    }

}

?>
