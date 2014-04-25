<?php

namespace models\entities;

use framework\mvc\model\Entity;

/**
 * @entity(repository="models\reposteries\ArticleRepostery")
 */
class Article extends Entity {

    /**
     * @column(type="integer",unique="true",notNull="true",primary="true",required="true",autoIncrement="true")
     */
    protected $_id;

    /**
     * @column(
     *      type="string",
     *      length="255",
     *      notNull="true",
     *      required="true"
     * )
     */
    protected $_title;

    /**
     * @column(
     *      type="string"
     * )
     */
    protected $_content;

    /**
     * @column(
     *      type="integer",
     *      notNull="true",
     *      foreign="true",
     *      required="true"
     * )
     */
    protected $_userId;

    /**
     * @relation(
     *      type="manyToOne",
     *      entityTarget="models\entities\User",
     *      columnTarget="id",
     *      columnParent="userId"
     * )
     */
    protected $_user;

    public function __construct() {
        
    }

}

?>