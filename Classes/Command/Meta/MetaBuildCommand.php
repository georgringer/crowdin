<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\Command\Meta;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use GeorgRinger\Crowdin\Command\BaseCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MetaBuildCommand extends BaseCommand
{

    /**
     * Defines the allowed options for this command
     *
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Meta :: Trigger build of a project')
            ->setHelp('Only if a project has been exported it is possible to get the latest translations. ');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('crowdin:build');

        $arguments = [
            'command' => 'crowdin:build',
            'async' => true,
        ];

        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);

        print_r($returnCode);
    }
}
