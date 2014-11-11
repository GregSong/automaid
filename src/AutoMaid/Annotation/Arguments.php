<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/11/6
 * Time: 23:32
 */

namespace AutoMaid\Annotation;

/**
 * Class Arguments
 * @package AutoMaid\Annotation
 * @Annotation
 *
 */
class Arguments
{
    protected $arguments = array();

    public function __construct(array $options)
    {
        if (isset($options['value'])) {
            $options['arguments'] = $options['value'];
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
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }
}
