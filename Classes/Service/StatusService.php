<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use Akeneo\Crowdin\Api\Status;

class StatusService extends BaseService
{
    public function get()
    {
        /** @var Status $api */
        $api = $this->client->api('status');
        $api->addUrlParameter('json', 1);
        return $api->execute();
    }
}
