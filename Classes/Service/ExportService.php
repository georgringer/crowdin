<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use Akeneo\Crowdin\Api\Export;

class ExportService extends AbstractService
{
    public function export(string $branch)
    {
        /** @var Export $api */
        $api = $this->client->api('export');
        $api->setBranch($branch);
        $api->execute();
    }
}
