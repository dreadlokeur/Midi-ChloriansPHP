<?php

namespace models;

use framework\mvc\model\Repostery;

/**
 * @repostery(table="article", tableAlias="A", databaseConfigName="default")
 */
class ArticleRepostery extends Repostery {

    public function __construct() {
        
    }

}

?>