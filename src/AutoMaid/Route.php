<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/21
 * Time: 16:14
 */

namespace AutoMaid;


class Route {
    /**
     * @var string
     */
    protected $url;
    /**
     * @var string
     */
    protected $action;

    function __construct($action, $url)
    {
        $this->action = $action;
        $this->url    = $url;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }


} 