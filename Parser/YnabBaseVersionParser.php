<?php

namespace MTools\Ynab\Parser;

use MTools\Ynab\Entity\YnabVersion;

class YnabBaseVersionParser
{
    /**
     * Parse string from D-0 to version object
     *
     * @param $string
     * @return YnabVersion
     */
    protected function parseVersion($string)
    {
        list($dev, $version) = explode('-', $string);

        if (empty($dev)) {
            throw new \RuntimeException(sprintf('Could not determine device short id for version %s', $string));
        }

        if (empty($version)) {
            $version = '0';
        }

        return new YnabVersion($dev, $version);
    }

    /**
     * @param $list string
     * @return YnabVersion[]
     */
    protected function parseVersionList($list)
    {
        $parts = explode(',', $list);

        $versions = [];

        if (!is_array($parts)) {
            new \RuntimeException(sprintf('Fail parsing knowledge string to versions %s', $list));
        }

        foreach ($parts as $part) {
            $version = $this->parseVersion($part);
            $versions[$version->getShortId()] = $version;
        }
        ksort($versions);

        return $versions;
    }
}