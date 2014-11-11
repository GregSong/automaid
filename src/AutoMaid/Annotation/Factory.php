<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/11/8
 * Time: 18:37
 */

namespace AutoMaid\Annotation;

/**
 * Class Factory
 * @package AutoMaid\Annotation
 * @Annotation
 * You have to pass two parameters with annotation, both of them are mandatory
 * 1. factory class;
 * 2. factory method;
 */
class Factory {
    protected $class;
    protected $method;


    function __construct(array $options)
    {
        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new \InvalidArgumentException(sprintf('Property "%s" does not exist', $key));
            }

            $this->$key = $value;
        }

        if (empty($this->class) || empty($this->method)) {
            throw new \Exception('You have to provide both class and method for Factory');
        }
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }


} 