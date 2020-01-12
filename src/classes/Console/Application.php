<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace App\Console;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * The console application
 */
class Application extends \Symfony\Bundle\FrameworkBundle\Console\Application
{
    /**
     * Constructor.
     *
     * @param KernelInterface $kernel
     *
     * @internal param string $name The name of the application
     * @internal param string $version The version of the application
     *
     * @api
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);

        $this->setName('UNKNOWN');
        $this->setVersion("UNKNOWN");

        // we set locale to EN.UTF8 to not getting into UTF-8 issues, eg. when dealing with umlauts & escapeshellarg()
        setlocale(LC_ALL, ['en.utf8', 'en_US.utf8', 'en_GB.utf8']);

        // allow to register commands here (e.g. through plugins)
    }

    /**
     * Gets the default input definition.
     *
     * @return InputDefinition An InputDefinition instance
     */
    protected function getDefaultInputDefinition()
    {
        $inputDefinition = parent::getDefaultInputDefinition();
        $inputDefinition->addOption(new InputOption('ignore-maintenance-mode', null, InputOption::VALUE_NONE, 'Set this flag to force execution in maintenance mode'));
        $inputDefinition->addOption(new InputOption('maintenance-mode', null, InputOption::VALUE_NONE, 'Set this flag to force maintenance mode while this task runs'));

        return $inputDefinition;
    }
}
