<?php

//http://www.pureftpd.org/project/sharedance
//http://thethoughtlab.blogspot.fr/2007/01/session-management-with-phpdance.html
//http://infomath.online-talk.net/t1253-sharedance-et-gestion-de-session-en-php
//TODO must be completed

namespace framework\cache\drivers;

use framework\cache\IDrivers;

class Sharedance implements IDrivers {

    public function __construct($params = array()) {
        throw new \Exception('NOT YET');
    }

}

?>