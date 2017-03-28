<?php

namespace Petrica\Ynab\Utils;

use Petrica\Ynab\Entity\YnabVersion;

/**
 * Class YnabVersionUtils
 *
 * @package Petrica\Ynab\Utils
 */
class YnabVersionUtils
{
    /**
     * Merge devices knowledge to the latest version contained in A
     *
     * @param $knowledgeA YnabVersion[]
     * @param $knowledgeB YnabVersion[]
     * @return YnabVersion[]
     */
    public function mergeKnowledge($knowledgeA, $knowledgeB)
    {
        foreach ($knowledgeB as $dev => $knowledge) {
            if (!isset($knowledgeA[$dev])) {
                $knowledgeA[$dev] = $knowledge;
            }
        }
        ksort($knowledgeA);

        return $knowledgeA;
    }

    /**
     * Determine if version is new knowledge
     *
     * @param $version YnabVersion
     * @param $knowledge YnabVersion[]
     * @return bool
     */
    public function isNewKnowledge($version, $knowledge)
    {
        if (isset($knowledge[$version->getShortId()]) &&
            $knowledge[$version->getShortId()]->getIncrement() >= $version->getIncrement()) {

            return false;
        }

        return true;
    }

    /**
     * Pack knowledge
     */
    public function packKnowledge($knowledge)
    {
        if (is_array($knowledge)) {
            $items = array_map(function ($item) {
                return $item->__toString();
            }, $knowledge);

            return implode(',', $items);
        }

        return null;
    }
}