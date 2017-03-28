<?php

namespace Petrica\Ynab\Parser;

use Petrica\Ynab\Entity\YnabDiffVersion;

/**
 * Class YnabDiffVersionParser
 *
 * @package Petrica\Ynab\Parse
 */
class YnabDiffVersionParser extends YnabBaseVersionParser
    implements YnabParserInterface
{
    /**
     * @var string
     */
    protected $diffName;

    /**
     * YnabDiffVersionParser constructor.
     *
     * @param $diffName
     */
    public function __construct($diffName)
    {
        $this->diffName = $diffName;
    }

    /**
     * @return YnabDiffVersion
     */
    public function parse()
    {
        $parts = explode('_', $this->diffName);
        if (count($parts) !== 2) {
            throw new \RuntimeException(sprintf('Failed parsing diff filename to version %s', $this->diffName));
        }

        $diff = new YnabDiffVersion();
        $diff->setFrom($this->parseVersionList($parts[0]));
        $diff->setTo($this->parseVersion($parts[1]));

        return $diff;
    }
}