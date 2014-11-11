<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/11/6
 * Time: 16:05
 */

namespace AutoMaid\Annotation;


/**
 * Class Father
 * @package AutoMaid\Annotation
 * @Annotation
 */
class Father
{
    protected $parent;
    /**
     * @param array $option
     */
    public function __construct(array $option)
    {
        if (isset($options['value'])) {
            $options['parent'] = $options['value'];
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
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }


} 