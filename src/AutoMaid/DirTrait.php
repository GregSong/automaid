<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/31
 * Time: 22:13
 */

namespace AutoMaid;


trait DirTrait
{
    protected $projectDir;
    protected $projectSrc;
    protected $projectApp;
    protected $projectConfig;
    protected $detected = false;

    /**
     * @return mixed
     */
    public function getProjectDir()
    {
        if(!$this->detected){
            $this->detectProjectDir();
        }
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
        if(!$this->detected){
            $this->detectProjectDir();
        }
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
        $this->projectDir   = dirname(
            dirname(dirname(dirname(dirname((__DIR__)))))
        );
        $this->projectSrc   = $this->projectDir . '/src';
        $this->projectApp   = $this->projectDir . '/app';
        $this->projectConfig = $this->projectApp . '/config';
    }

    /**
     * @return mixed
     */
    public function getProjectApp()
    {
        if(!$this->detected){
            $this->detectProjectDir();
        }
        return $this->projectApp;
    }

    /**
     * @param mixed $projectApp
     */
    public function setProjectApp($projectApp)
    {
        $this->projectApp = $projectApp;
    }

    /**
     * @return mixed
     */
    public function getProjectConfig()
    {
        if(!$this->detected){
            $this->detectProjectDir();
        }
        return $this->projectConfig;
    }

    /**
     * @param mixed $projectConfig
     */
    public function setProjectConfig($projectConfig)
    {
        $this->projectConfig = $projectConfig;
    }

} 