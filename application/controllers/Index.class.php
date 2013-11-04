<?php

namespace controllers;

use framework\mvc\Controller;
use framework\Security;
use framework\security\Form;
use framework\network\Http;
use framework\Language;

class Index extends Controller {

    public function __construct() {
        //echo $v;
        //throw new \Exception('test exception');
        echo $_POST['truc'];
        $this->tpl->setFile('controllers' . DS . 'Index' . DS . 'index.tpl.php');
    }

    public function language($language) {
        if (!is_string($language) || ($language == Language::getInstance()->getLanguage()) || !Http::isAjaxRequest())
            $this->router->show404(true);

        $this->setAjaxController();
        $this->session->add('language', $language, true, false);
        $this->addAjaxDatas('updated', true);
    }

    public function captcha($formName, $type) {
        $captcha = Security::getSecurity(Security::TYPE_FORM)->getProtection(Http::getQuery($formName), Form::PROTECTION_CAPTCHA);
        if (!$captcha)
            $this->router->show404(true);

        if (Http::getQuery($type) == 'refresh') {
            $this->setAjaxController();
            $captcha->flush();
            $this->addAjaxDatas('imageUrl', $captcha->get('image', true));
            $this->addAjaxDatas('audioUrl', $captcha->get('audio', true));
        } else {
            if (Http::getQuery($type) == 'image') {
                if (!$captcha->getImage())
                    $this->router->show404(true);
                $captcha->get('image');
            } elseif (Http::getQuery($type) == 'audio') {
                if (!$captcha->getAudio())
                    $this->router->show404(true);
                $captcha->get('audio');
            }
            $this->setAutoCallDisplay(false);
        }
    }

}

?>