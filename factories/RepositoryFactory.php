<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 12.06.15
 * Time: 15:53
 */

namespace yii2ddd\factories;


/**
 * Factory of repositories
 *
 * Class RepositoryFactory
 * @package yii2ddd\factories
 */
class RepositoryFactory extends AbstractFactory
{
    /**
     * @var string Name of Application property which contains array of aliases
     */
    static public $yiiConfigName = 'repositories';
}
