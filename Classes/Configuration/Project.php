<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\Configuration;

use GeorgRinger\Crowdin\Utility\FileHandling;

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
    protected $key = '';

    /** @var string */
    protected $extensionkey = '';

    /** @var array */
    protected $languages = [];

    /**
     * Project constructor.
     * @param string $identifier
     * @param array $configuration
     */
    public function __construct(string $identifier, array $configuration)
    {
        $this->identifier = $identifier;
        $this->key = $configuration['key'];
        $this->extensionkey = $configuration['extensionKey'] ?? '';
        $this->languages = FileHandling::trimExplode(',', $configuration['languages'] ?? '', true);
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
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getExtensionkey(): string
    {
        return $this->extensionkey;
    }

    public function getBranch()
    {
        // todo configuration

        return 'master';
    }

    /**
     * @return array
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    public static function initializeByArray(string $identifier, $data)
    {
        return new self($identifier, $data);
    }

    public function __toString()
    {
        return json_encode([
            'identifier' => $this->identifier,
            'password' => $this->key
        ]);
    }
}
