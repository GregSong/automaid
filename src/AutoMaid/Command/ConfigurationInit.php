<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/24
 * Time: 19:04
 */
namespace AutoMaid\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigurationInit extends Command {
    protected function configure()
    {
        parent::configure(); // TODO: Change the autogenerated stub
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Step 1. handle app/config/config.yml
        $path = __DIR__ . '/../../../../../../app/config/config.yml';  // TODO make this configurable later
        if (file_exists($path)) {
            shell_exec(__DIR__ . '/../../../bin/handle_imports.sh ' . $path);
        }
    }

    public function getDescription()
    {
        return parent::getDescription(); // TODO: Change the autogenerated stub
    }
}