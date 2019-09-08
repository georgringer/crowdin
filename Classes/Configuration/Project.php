<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\Configuration;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration as ExtensionConfigurationService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
final class Project
{

    /** @var string */
    protected $identifier = '';

    /** @var string */
    protected $password = '';

    /**
     * Project constructor.
     * @param string $identifier
     * @param string $password
     */
    public function __construct(string $identifier, string $password)
    {
        $this->identifier = $identifier;
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public static function initializeByJson(string $json)
    {
        $decoded = json_decode($json, true);
        return new self($decoded['identifier'], $decoded['password']);
    }

    public function __toString()
    {
        return json_encode([
            'identifier' => $this->identifier,
            'password' => $this->password
        ]);
    }
}
