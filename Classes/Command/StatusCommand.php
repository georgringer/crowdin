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
        $configurationService = $this->getConfigurationService();
        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Project %s', $configurationService->getProject()->getIdentifier()));

        $service = new StatusService();

        $response = $service->get();
        if ($response) {
            $languageCodes = [];
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
                $languageCodes[] = $s['code'];
                $items[] = [
                    sprintf('%s - %s', $s['name'], $s['code']),
//                    $s['phrases'],
                    ($s['translated_progress'] === $s['approved_progress'] ? $s['approved_progress'] : (sprintf('%s / %s', $s['translated_progress'], $s['approved_progress'])))

                ];
            }
            $io->table($headers, $items);

            if (!empty(array_diff($languageCodes, $configurationService->getProject()->getLanguages()))) {
                sort($languageCodes);
                $configurationService->updateSingleConfiguration('languages', implode(',', $languageCodes));
            }
        }
    }
}
