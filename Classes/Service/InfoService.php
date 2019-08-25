<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use Akeneo\Crowdin\Api\Download;
use Akeneo\Crowdin\Api\Info;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class InfoService extends AbstractService
{
    public function get()
    {
        /** @var Info $api */
        $api = $this->client->api('info');
        return $api->execute();
    }


}
