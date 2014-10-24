<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/21
 * Time: 17:53
 */

namespace AutoMaid\Annotation;

/**
 * @Annotation
 */
class DepOn {
    protected $services = array();

    public function __construct($options)
    {
        if (isset($options['value'])) {
            $options['services'] = $options['value'];
            unset($options['value']);
        }

        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new \InvalidArgumentException(sprintf('Property "%s" does not exist', $key));
            }

            $this->$key = $value;
        }
    }

    /**
     * @return string[]
     */
    public function getServices()
    {
        return $this->services;
    }
} 