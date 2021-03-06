<?php
/**
 * @copyright	Copyright (C) 2007 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		Mozilla Public License, version 2.0
 * @link		http://github.com/joomlatools/joomla-console for the canonical source repository
 */

namespace Joomlatools\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Joomlatools\Console\Joomla\Bootstrapper;

class ExtensionInstallFile extends SiteAbstract
{
    protected $extension = array();

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('extension:installfile')
            ->setDescription('Install packaged extensions for file or directory into a site')
            ->addArgument(
                'extension',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'A list of file/directory extensions to install in the site'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->extension = $input->getArgument('extension');

        $this->check($input, $output);
        $this->install($input, $output);
    }

    public function check(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists($this->target_dir)) {
            throw new \RuntimeException(sprintf('Site not found: %s', $this->site));
        }
    }

    public function install(InputInterface $input, OutputInterface $output)
    {
        $app = Bootstrapper::getApplication($this->target_dir);

        // Output buffer is used as a guard against Joomla including ._ files when searching for adapters
        // See: http://kadin.sdf-us.org/weblog/technology/software/deleting-dot-underscore-files.html
        ob_start();
        
        $installer = $app->getInstaller();
        
        foreach ($this->extension as $package) {
            $file = $package;
            $remove = false;

            if (is_dir($package)) {
                $dir = $package;
            } else {
                $dir = \JInstallerHelper::unpack($package);
                $dir = $dir ? $dir['dir'] : false;
                $remove = true;
            } 
            if ($dir) {
                $installer->install(realpath($dir));
                if ($remove) {
                    \JFolder::delete($dir);
                }
            }
        }

        ob_end_clean();
    }
}
