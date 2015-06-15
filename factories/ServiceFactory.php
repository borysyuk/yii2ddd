<?php
/**
 * Created by PhpStorm.
 * User: Ihor Borysyuk
 * Date: 12.06.15
 * Time: 15:55
 */

namespace yii2ddd\factories;


/**
 * Factory of services
 *
 * Class ServiceFactory
 * @package yii2ddd\factories
 * @author Ihor Borysyuk
 */
class ServiceFactory extends AbstractFactory
{
    /**
     * @var string Name of Application property which contains array of aliases
     */
    static public $yiiConfigName = 'services';
}
