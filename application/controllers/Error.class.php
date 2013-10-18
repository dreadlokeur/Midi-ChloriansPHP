<?php

namespace controllers;

use framework\mvc\Controller;
use framework\mvc\Dispatcher;
use framework\network\Http;
use framework\error\ErrorManager;
use framework\error\ExceptionManager;

class Error extends Controller {

    public function __construct() {
        if (is_null(Http::getQuery(Dispatcher::getInstance()->getUrlKeyAction())))
            $this->notFound();
        $this->tpl->setTemplateFile('controllers' . DS . 'Error' . DS . 'index.tpl.php');
    }

    public function debugger() {
        $ex = ExceptionManager::getInstance()->getException();
        $err = ErrorManager::getInstance()->getError();
        if ($ex) {
            $this->tpl->set('exception', $ex);
            $this->tpl->setTemplateFile('controllers' . DS . 'Error' . DS . 'exception.tpl.php');
        } else {
            $this->tpl->set('error', $err);
            $this->tpl->setTemplateFile('controllers' . DS . 'Error' . DS . 'error.tpl.php');
        }
    }

    public function badRequest() {
        //ErrorDocument 400 /error/400.html Bad Request La syntaxe de la requête est erronée
        $this->tpl->set('errorInfo', array('code' => '400', 'message' => 'Bad Request'), false, true)->set('title', 'Bad Request', false, true);
    }

    public function unauthorized() {
        //ErrorDocument 401 /error/401.html Unauthorized Une authentification est nécessaire pour accéder à la ressource
        $this->tpl->set('errorInfo', array('code' => '401', 'message' => 'Unauthorized'), false, true)->set('title', 'Unauthorized', false, true);
    }

    public function forbidden() {
        //ErrorDocument 403 /error/403.html Forbidden L’authentification est refusée. Contrairement à l’erreur 401, aucune demande d’authentification ne sera faite
        $this->tpl->set('errorInfo', array('code' => '403', 'message' => 'Forbidden'), false, true)->set('title', 'Forbidden');
    }

    public function notFound() {
        //ErrorDocument 404 /error/404.html Not Found  Ressource non trouvée
        $this->tpl->set('errorInfo', array('code' => '404', 'message' => 'Not Found'), false, true)->set('title', 'Not Found', false, true);
    }

    public function methodNotAllowed() {
        //ErrorDocument 405 /error/405.html Method Not Allowed Méthode de requête non autorisée
        $this->tpl->set('errorInfo', array('code' => '405', 'message' => 'Method Not'), false, true)->set('title', 'Method Not', false, true);
    }

    public function internalServerError() {
        //ErrorDocument 500 /error/500.html Internal Server Error Erreur interne du serveur
        $this->tpl->set('errorInfo', array('code' => '500', 'message' => 'Internal Server Error'), false, true)->set('title', 'Internal Server Error', false, true);
    }

    public function badGateway() {
        //ErrorDocument 502 /error/502.html Bad Gateway Mauvaise réponse envoyée à un serveur intermédiaire par un autre serveur.
        $this->tpl->set('errorInfo', array('code' => '502', 'message' => 'Bad Gateway'), false, true)->set('title', 'Bad Gateway', false, true);
    }

    public function serviceUnavailable() {
        //ErrorDocument 503 /error/503.html Service Unavailable Service temporairement indisponible ou en maintenance
        $this->tpl->set('errorInfo', array('code' => '503', 'message' => 'Service Unavailable Service'), false, true)->set('title', 'Service Unavailable Service', false, true);
    }

}

?>