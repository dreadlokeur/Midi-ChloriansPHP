<?php

namespace framework\mvc;

interface IModelObject {

    public function __construct($datas = array());

    public function hydrate($datas = array());
}

?>
