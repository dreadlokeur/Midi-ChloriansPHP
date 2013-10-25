<?php

namespace controllers;

use framework\mvc\Controller;
use framework\Security;
use framework\security\Api;
use framework\security\api\Form;
use framework\network\Http;

class Index extends Controller {

    public function __construct() {
        //var_dump($this->tpl->getUrlAsset('img'));
        $this->tpl->setFile('controllers' . DS . 'Index' . DS . 'index.tpl.php');
    }

    public function test($int) {
        //var_dump($int);
    }

    public function test2($int, $string) {
        //var_dump($int);
        //var_dump($string);
    }

    public function action() {
        //$db = \framework\Database::getDatabase('sql', true);
        //var_dump($db);
        //$db->set('SELECT * FROM test');
        //$db->execute();
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

    public function action2() {
        
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
            $this->router->show404(true);
    }

    public function captcha($formName, $type) {
        $captcha = Security::getApi(Api::TYPE_FORM)->getProtection(Http::getQuery($formName), Form::PROTECTION_CAPTCHA);
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