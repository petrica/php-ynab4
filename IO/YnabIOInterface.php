<?php

namespace Petrica\Ynab\IO;

use Petrica\Ynab\Entity\YnabFile;

/**
 * Interface YnabIOInterface
 * @package Petrica\Ynab\IO
 */
interface YnabIOInterface
{
    /**
     * Read file content from device
     *
     * @param $filepath string Path to file
     * @return YnabFile
     */
    public function read($filepath);

    /**
     * Write file content to device
     *
     * @param $ynabFile YnabFile
     * @return mixed
     */
    public function write($ynabFile);

    /**
     * List files in particular path
     *
     * @param $path
     * @return string[]
     */
    public function ls($path);
}