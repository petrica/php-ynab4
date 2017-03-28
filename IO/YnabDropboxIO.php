<?php

namespace Petrica\Ynab\IO;

use Dropbox\Client;
use Dropbox\WriteMode;
use Petrica\Ynab\Entity\YnabFile;

/**
 * Class YnabDropboxIO
 * @package Petrica\Ynab\Entity
 */
class YnabDropboxIO implements YnabIOInterface
{
    /**
     * @var Client
     */
    protected $dropbox;

    /**
     * YnabDropboxIO constructor.
     *
     * @param $dropbox
     */
    public function __construct($dropbox)
    {
        $this->dropbox = $dropbox;
    }

    /**
     * Read
     *
     * @param $filePath
     * @return YnabFile
     */
    public function read($filePath)
    {
        $stream = fopen('php://memory', 'w+');
        $meta = $this->dropbox->getFile($filePath, $stream);
        if (!$meta) {
            throw new \RuntimeException(sprintf('Could not find file %s', $filePath));
        }
        rewind($stream);
        $file = new YnabFile($meta['path'], stream_get_contents($stream));

        return $file;
    }

    /**
     * Write
     *
     * @param $ynabFile YnabFile
     * @return mixed
     */
    public function write($ynabFile)
    {
        $meta = $this->dropbox->uploadFileFromString($ynabFile->getPath(), WriteMode::force(), $ynabFile->__toString());
        if (null === $meta) {
            throw new \RuntimeException(sprintf('Could not write file to dropbox %s', $ynabFile->getPath()));
        }

        return $meta;
    }

    /**
     * Return a list of folders and files for a particular path
     *
     * @param $path
     * @return string[] Full path to file
     */
    public function ls($path)
    {
        $content = [];
        $meta = $this->dropbox->getMetadataWithChildren($path);
        if (null !== $meta && isset($meta['contents'])) {
            foreach ($meta['contents'] as $file) {
                $content[] = $file['path'];
            }
        }

        return $content;
    }
}