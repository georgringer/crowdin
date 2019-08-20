<?php

declare(strict_types=1);

namespace GeorgRinger\Crowdin\Service;

use Crowdin\Client;

class AbstractService
{

    /** @var Client */
    protected $client;

    /**
     * @todo: no hardcoding ;)
     */
    public function __construct()
    {
        $this->client = new Client('typo3-cms', 'bc0fd8047b2e10ec18e9f8ac4f92b995');
    }


    public function initializeClient(string $project, string $apiKey): void
    {
        $this->client = new Client($project, $apiKey);
    }

}
