<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 08.06.15
 * Time: 18:01
 */

namespace yii2ddd\factories;


/**
 * Abstract Factory
 *
 * Class AbstractFactory
 * @package yii2ddd\factories
 */
abstract class AbstractFactory
{
    /**
     * @var array cache for created items
     */
    static protected $items = [];

    /**
     * @var null|array cache for aliases
     */
    static protected $aliases = null;

    /**
     * @var string Name of Application property which contains array of aliases
     */
    static public $yiiConfigName = '';

    /**
     * Get aliases from right Application property
     * @return array
     */
    public static function getAliases()
    {
        $configName = static::$yiiConfigName;
        if (empty(self::$aliases[$configName])) {
            self::$aliases[$configName] = \Yii::$app->$configName;
        }
        return self::$aliases[$configName];
    }

    /**
     * Create or take from cache right item by alias
     * @param string $alias - Alias of item
     * @return mixed Item
     */
    public static function get($alias)
    {
        if (empty(self::$items[static::$yiiConfigName][$alias])) {
            $aliases = static::getAliases();
            if (isset($aliases[$alias])) {
                self::$items[static::$yiiConfigName][$alias] = new $aliases[$alias]();
            }
        }
        return self::$items[static::$yiiConfigName][$alias];
    }
}
