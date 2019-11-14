<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\Command\Cleanup;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class OriginalAttributeCommand extends Command
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setDescription('Sets the proper original attribute for XLF files')
            ->addArgument('path', InputArgument::REQUIRED, 'Path');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);


        $this->runParser($input->getArgument('path'));
    }

    protected function runParser(string $path, string $overrideName = '')
    {
        $path = rtrim($path, '/');

        if (!$overrideName) {
            $split = explode('/', trim($path, '/'));
            $overrideName = end($split);
        }

//        print_r($overrideName);die;

        $files = [];
        $path = Environment::getPublicPath() . '/typo3/sysext/';
        $files = GeneralUtility::getAllFilesAndFoldersInPath([], $path, 'xlf', false, 99);

        foreach ($files as $file) {
            $shortName = str_replace(Environment::getPublicPath(), 'EXT:', $file);
            $shortName = str_replace('/typo3/sysext/', '', $shortName);
            $data = str_replace('original="messages"', 'original="' . $shortName . '"', file_get_contents($file));
            file_put_contents($file, $data);
        }
        die;

        $finder = new Finder();
        $finder->files()
//            ->contains('original="messages"')
            ->depth(10)
//            ->name('*.xlf')
            ->in($path . '/*/Resources/Private/Language');

        foreach ($finder as $item) {
            print_r($item);
            die;
            /** @var SplFileInfo $item */
            $originalName = 'EXT:' . str_replace($path, '', $item->getPath()) . '/' . $item->getFilename();
            $originalName = str_replace('EXT:/', 'EXT:', $originalName);

            $data = str_replace('original="messages"', 'original="' . $originalName . '"', $item->getContents());
            file_put_contents($item->getRealPath(), $data);
        }
    }
}
