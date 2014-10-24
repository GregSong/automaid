<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/21
 * Time: 10:54
 */

namespace AutoMaid\Annotation;

/**
 * @Annotation
 */
class ServiceAnnotation {
    protected $name;

    public function __construct($options)
    {
        if (isset($options['value'])) {
            $options['name'] = $options['value'];
            unset($options['value']);
        }

        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new \InvalidArgumentException(sprintf('Property "%s" does not exist', $key));
            }

            $this->$key = $value;
        }
        if (empty($this->name)) {
            throw new \InvalidArgumentException("Service name is missing");
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}