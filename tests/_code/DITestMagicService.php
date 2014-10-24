<?php
use AutoMaid\DIServiceTraits;

/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/23
 * Time: 20:53
 */


class DITestMagicService
{
    use DIServiceTraits;

    public function useService($name)
    {
        return print_r($this->amServices[$name], true);
    }
}