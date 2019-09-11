<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use GeorgRinger\Crowdin\Service\StatusService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StatusCommand extends BaseCommand
{

    /**
     * Defines the allowed options for this command
     *
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Get status');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->showProjectIdentifier($io);

        $service = new StatusService();

        $response = $service->get();
        if ($response) {
            $status = json_decode($response->getContents(), true);
//            $io->table(array_keys($status[0]), $status);
            $headers = [
                'Name',
//                'phrases',
//                'translated',
                'Progress (%)'
            ];
            $items = [];
            foreach ($status as $s) {
                $items[] = [
                    sprintf('%s - %s', $s['name'], $s['code']),
//                    $s['phrases'],
                    ($s['translated_progress'] === $s['approved_progress'] ? $s['approved_progress'] : (sprintf('%s / %s', $s['translated_progress'], $s['approved_progress'])))

                ];
            }
            $io->table($headers, $items);

//
        }
    }
}
