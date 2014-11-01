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
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Yaml\Yaml;

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
        $definition = $automaid->getServiceInfo($serviceName);

        // TODO Greg: by default just dump the detail of definition
        echo Yaml::dump($this->dumpDefinition($definition));
    }

    /**
     * @param $definition
     *
     * @return array
     */
    private function dumpDefinition(Definition $definition)
    {
        $data = array(
            'class'     => (string)$definition->getClass(),
            'scope'     => $definition->getScope(),
            'public'    => $definition->isPublic(),
            'synthetic' => $definition->isSynthetic(),
            'file'      => $definition->getFile(),
        );

        $data['tags'] = array();
        if (count($definition->getTags())) {
            foreach ($definition->getTags() as $tagName => $tagData) {
                foreach ($tagData as $parameters) {
                    $data['tags'][] = array('name'       => $tagName,
                                            'parameters' => $parameters
                    );
                }
            }
        }

        return $data;
    }
}