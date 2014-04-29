<?php

namespace controllers;

use framework\mvc\Controller;
use framework\Security;
use framework\security\Form;
use framework\network\Http;
use framework\utility\Cookie;
use framework\mvc\Model;

class Index extends Controller {

    public function __construct() {
        $this->tpl->setFile('controllers' . DS . 'Index' . DS . 'index.tpl.php');

        $model = Model::getInstance();
        //$article = $model->factoryRepostery('article')->find(10);
        //$model->delete($article);
        //$article = $model->factoryRepostery('article')->find(10);
        //$article->title = 'test';
        //$model->save($article);
        $article = $model->factoryEntity('article');
        //$article->title = 'test';
        //$model->attach($article);
        $model->delete($article);
        //$model->flush();
        //\framework\Debugger::dump($article->getRepostery()->getDatabase());
        //\framework\Debugger::dump($article, true);
    }

    public function setAjax($check = false) {
        if (!Http::isAjaxRequest() && $check)
            Http::redirect($this->router->getUrl('index'));

        if (Http::isAjaxRequest())
            $this->setAjaxController();
    }

    public function language($language) {
        if (!is_string($language))
            $language = (string) $language;

        $this->session->add('language', $language, true, false);
        $this->addAjaxDatas('updated', true);

        //create cookie
        new Cookie('language', $language, true, Cookie::EXPIRE_TIME_INFINITE, str_replace(Http::getServer('SERVER_NAME'), '', $this->router->getHost()));
    }

    public function captcha($formName, $type) {
        $captcha = Security::getSecurity(Security::TYPE_FORM)->getProtection($formName, Form::PROTECTION_CAPTCHA);
        if (!$captcha)
            $this->router->show404(true);

        if ($type == 'refresh') {
            $this->setAjaxController();
            $captcha->flush();
            $this->addAjaxDatas('imageUrl', $captcha->get('image', true));
            $this->addAjaxDatas('audioUrl', $captcha->get('audio', true));
        } else {
            if ($type == 'image') {
                if (!$captcha->getImage())
                    $this->router->show404(true);
                $captcha->get('image');
            } elseif ($type == 'audio') {
                if (!$captcha->getAudio())
                    $this->router->show404(true);
                $captcha->get('audio');
            } else
                $this->router->show404(true);

            $this->setAutoCallDisplay(false);
        }
    }

}

?>