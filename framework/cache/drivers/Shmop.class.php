<?php

//http://www.php.net/manual/fr/ref.shmop.php
//TODO must be completed

namespace framework\cache\drivers;

use framework\cache\IDrivers;

class Shmop implements IDrivers {

    public function __construct($params = array()) {
        throw new \Exception('NOT YET');
    }

}

?>