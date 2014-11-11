<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/11/3
 * Time: 17:56
 */

namespace AutoMaid\Annotation;

/**
 * @Annotation
 */
class Tags
{
    protected $tags = array();

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function __construct($options)
    {
        if (isset($options['value'])){
            $value = $options['value'];
            if (is_array($value)) {
                $this->tags = $value;

            } else {
                throw new \Exception('Input of @Tags should be array of tag');
            }
        }
    }


} 