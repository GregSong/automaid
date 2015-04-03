<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/24
 * Time: 19:06
 */

namespace AutoMaid\Command;


use AutoMaid\AutoMaid;
use AutoMaid\DirTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigurationGen extends Command {

    use DirTrait;

    protected $paths = array();
    protected $lazy = null;
    protected function configure()
    {
        $this->setDefinition(
            array(
                new InputOption(
                    'path',
                    'p',
                    InputOption::VALUE_REQUIRED,
                    'Source file path which contains source code of service (based on project path)'
                ),
                new InputOption(
                    'lazy',
                    'z',
                    InputOption::VALUE_NONE,
                    'Overwrite lazy setting of service'
                )
            )
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectProjectDir();

        $paths    = $input->getOption('path');
        if (!empty($paths)) {
            $this->paths = preg_split('/[ ]*,[ ]*/', $paths);
        } else {
            $this->paths[] = $this->getProjectSrc();
        }

        $this->lazy = $input->getOption('lazy');

        $automaid = new AutoMaid();
        $automaid->setLazy($this->lazy);
        $automaid->setProjectDir($this->projectDir);
        $automaid->init();

        foreach ($this->paths  as $path) {
            $automaid->loadFiles($path);
        }

        $automaid->parseServices();

        $automaid->writeServiceConfiguration();
    }

    public function getDescription()
    {
        return 'Generate service configuration based on annotations';
    }
}