<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/11/7
 * Time: 09:32
 */

namespace AutoMaid\Annotation;

/**
 * Class Scope
 * @package AutoMaid\Annotation
 * @Annotation
 */
class Scope {
    protected $scope;

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    function __construct($options)
    {
        if (isset($options['value'])) {
            $options['scope'] = $options['value'];
            unset($options['value']);
        }

        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new \InvalidArgumentException(sprintf('Property "%s" does not exist', $key));
            }

            $this->$key = $value;
        }
    }
}