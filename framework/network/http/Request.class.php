<?php

namespace framework\network\http;

use framework\network\Http;

class Request extends Http {

    use debugger\Debug;
    
    protected $_posts = array();

    public function __construct() {
    }

}

?>