<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/31
 * Time: 22:13
 */

namespace AutoMaid;


trait DirTrait {
    protected $projectDir;
    protected $projectSrc;

    /**
     * @return mixed
     */
    public function getProjectDir()
    {
        return $this->projectDir;
    }

    /**
     * @param mixed $projectDir
     */
    public function setProjectDir($projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * @return mixed
     */
    public function getProjectSrc()
    {
        return $this->projectSrc;
    }

    /**
     * @param mixed $projectSrc
     */
    public function setProjectSrc($projectSrc)
    {
        $this->projectSrc = $projectSrc;
    }

    public function detectProjectDir()
    {
        $this->projectDir = dirname(dirname(dirname(dirname(dirname((__DIR__))))));
        $this->projectSrc = $this->projectDir . '/src';
    }

} 