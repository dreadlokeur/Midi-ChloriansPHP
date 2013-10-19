<?php

namespace controllers;

use framework\mvc\Controller;
use framework\Security;
use framework\security\Api;
use framework\security\api\Form;
use framework\network\Http;

class Index extends Controller {

    public function __construct() {
        $this->tpl->setTemplateFile('controllers' . DS . 'Index' . DS . 'index.tpl.php');
    }

    public function action() {
        //$cache = \framework\Cache::getCache('default');
        //$cache->write('test', '1fff', true, 10, $cache::TYPE_NUMBER);
        //$cache->write('test', '1fff');
        //$cache->delete('test');
        //$cache->lock('test', 10);
        //$cache->unlock('test');
        //$cache = \framework\Cache::getCache('default2');
        //$cache->write('test', 1, 100);
        //$cache->increment('test', 1);
        //$cache->clearGroup('group1');
        //$cache->clearGroup('group2');
        //$cache::clearGroupsAllCaches(false, false);
        //var_dump($cache->read('test'));
        //var_dump($cache->getExpireTime('test'));
        //var_dump($cache);
        //$cache->clear();
        //$cache->purge();
    }

    public function language() {
        if (Http::isAjaxRequest()) {
            $this->setAjaxController();
            $language = Http::getPost('language');
            $updated = in_array($language, $this->config->getLanguageList());
            if ($updated)
                $this->session->add('language', $language, true, false);
            $this->addAjaxDatas('updated', $updated);
        }
        else
            $this->dispatcher->show404(true);
    }

    public function captcha() {
        if (is_null(Http::getQuery('parameter')) || is_null(Http::getQuery('parameter2')))
            $this->dispatcher->show404(true);

        $captcha = Security::getApi(Api::TYPE_FORM)->getProtection(Http::getQuery('parameter'), Form::PROTECTION_CAPTCHA);
        if (!$captcha)
            $this->dispatcher->show404(true);

        if (Http::getQuery('parameter2') == 'refresh') {
            $this->setAjaxController();
            $captcha->flush();
            $this->addAjaxDatas('imageUrl', $captcha->get('image', true));
            $this->addAjaxDatas('audioUrl', $captcha->get('audio', true));
        } else {
            if (Http::getQuery('parameter2') == 'image') {
                if (!$captcha->getImage())
                    $this->dispatcher->show404(true);
                $captcha->get('image');
            } elseif (Http::getQuery('parameter2') == 'audio') {
                if (!$captcha->getAudio())
                    $this->dispatcher->show404(true);
                $captcha->get('audio');
            }
            $this->setAutoCallDisplay(false);
        }
    }

}

?>