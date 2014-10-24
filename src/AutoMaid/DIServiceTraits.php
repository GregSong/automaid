<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/21
 * Time: 11:35
 */

namespace AutoMaid;


use BadFunctionCallException;

Trait DIServiceTraits
{
    protected $amServices = array();

    public function __call($name, $arguments)
    {
        $matches = array();
        if (preg_match('/set([a-zA-Z_0-9]+)/', $name, $matches) && is_array(
                $arguments
            ) && sizeof($arguments) == 1
        ) {
            $this->amServices[$matches[1]] = $arguments[0];
        } else {
            throw new BadFunctionCallException(
                "No such method ($name) found ."
            );
        }
    }

    /**
     * @param array $services
     */
    public function setAmServices($services)
    {
        $this->amServices = array_merge($this->amServices, $services);
    }

    /**
     * @return string
     */
    public function dump()
    {
        return print_r($this->amServices, true);
    }

    /**
     * @return array
     */
    public function get($id)
    {
        return $this->amServices[$id];
    }
}