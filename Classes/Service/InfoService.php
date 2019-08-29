<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use Akeneo\Crowdin\Api\Info;

class InfoService extends AbstractService
{
    public function get()
    {
        /** @var Info $api */
        $api = $this->client->api('info');
        return $api->execute();
    }
}
