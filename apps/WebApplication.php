<?php
/**
 * Created by PhpStorm.
 * User: Ihor Borysyuk
 * Date: 12.06.15
 * Time: 14:58
 */

namespace yii2ddd\apps;

use yii\web\Application;

/**
 * This class allow you get repositories and services by alias
 * You must create sections 'repositories' and/or 'services' in your config file
 * like this :
 *    'repositories' => [
 *       'main' => 'common\repositories\MainRepository',
 *       'testData' => 'common\repositories\TestDataRepository',
 *    ],
 *
 *    'services' => [
 *       'main' => 'common\services\MainService',
 *       'testData' => 'common\services\TestDataService',
 *     ],
 * You should put alias in left side and in right side - Name of class with namespace
 *
 * Class WebApplication
 * @package yii2ddd\apps
 */
class WebApplication extends Application
{
    /**
     * @var array repository aliases
     */
    public $repositories = [];

    /**
     * @var array service aliases
     */
    public $services = [];
}
