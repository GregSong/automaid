<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/21
 * Time: 16:12
 */

namespace AutoMaid;


/**
 * Class AMController
 * @package AutoMaid
 * @deprecated
 */
class AMController extends Service{
    /**
     * @var Route[]
     */
    protected $routes = array();
    /**
     * @var string
     */
    protected $baseRoute;

    public function addRoute($action, $url)
    {
        $this->routes[] = new Route($action, $url);
    }
} 