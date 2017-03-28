<?php

namespace Petrica\Ynab\Parser;

interface YnabParserInterface
{
    /**
     * Parse input data
     *
     * @return mixed
     */
    public function parse();
}