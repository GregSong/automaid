<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/22
 * Time: 12:32
 */

namespace Greg\ATC\Common\Service;

trait WaitressTraits {
    public  $waitress;

    /**
     * @param mixed $waitress
     */
    public function setWaitress($waitress)
    {
        $this->waitress = $waitress;
    }

} 