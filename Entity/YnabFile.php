<?php

namespace Petrica\Ynab\Entity;

class YnabFile
{
    protected $path;
    protected $content;

    public function __construct($path, $content)
    {
        if (is_string($content)) {
            $content = json_decode($content);
        }

        $this->path = $path;
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Ready to be written to file
     */
    public function __toString()
    {
        return json_encode($this->getContent(), JSON_PRETTY_PRINT);
    }
}