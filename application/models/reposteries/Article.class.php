<?php

namespace models\reposteries;

use framework\mvc\model\Repostery;

/**
 * @repostery(table="article", tableAlias="A", databaseConfigName="default")
 */
class Article extends Repostery {

    public function __construct() {
        
    }

}

?>