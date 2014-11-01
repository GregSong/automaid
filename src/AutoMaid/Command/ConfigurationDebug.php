<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/31
 * Time: 22:55
 */

namespace AutoMaid\Command;


use AutoMaid\AutoMaid;
use AutoMaid\DirTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigurationDebug extends Command
{
    use DirTrait;
    protected function configure()
    {
        $this->setDefinition(
            array(
                new InputOption(
                    'service',
                    's',
                    InputOption::VALUE_REQUIRED,
                    'Service name'
                )
            )
        );
    }

    public function getDescription()
    {
        return "Debug service information";
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasOption('service')) {
            $serviceName = $input->getOption('service');
        }
        $this->detectProjectDir();
        $automaid = new AutoMaid();
        $automaid->setProjectDir($this->projectDir);
        $automaid->init();

        $automaid->loadFiles($this->projectSrc);
        $automaid->parseServices();
        echo $automaid->getServiceInfo($serviceName);
    }
}