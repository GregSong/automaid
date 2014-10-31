<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/31
 * Time: 11:54
 */

namespace Greg\ATC\TestBundle\Common\Service;

use AutoMaid\Annotation\DepOn;

/**
 * Class DummyServiceTwoTraits
 * @package Greg\ATC\Common\Service
 * @DepOn({
 * "dummyServiceTwo":"@dummy_service_two"
 * })
 */
trait DummyServiceTwoTraits
{
    protected $dummy_service_two;

    /**
     * @param mixed $dummy_service_two
     */
    public function setDummyServiceTwo($dummy_service_two)
    {
        $this->dummy_service_two = $dummy_service_two;
    }
} 