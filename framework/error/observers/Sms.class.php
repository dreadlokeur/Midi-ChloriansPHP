<?php

// see http://macsim.labolinux.net/2008/07/137-smsalert-envoyer-des-sms-gratuitement-depuis-ses-serveurs/
// use google notification by sms
// need create a tool sms with this method, and use it on this class and logger sms driver

namespace framework\error\observers;

class Sms implements \SplObserver {

    public function __construct() {
        
    }

    public function update(\SplSubject $subject, $isException = false) {
        
    }

}

?>