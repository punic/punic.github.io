<?php

namespace Punic\Website;

use Exception;

class VolatileDirectory
{
    /**
     * The path of this volatile directory.
     *
     * @var string
     */
    protected $path;

    /**
     * Initializes the instance.
     *
     * @param string $parentDirectory the parent directory that will contain this volatile directory
     *
     * @throws Exception
     */
    public function __construct($parentDirectory)
    {
        $parentDirectory = is_string($parentDirectory) ? rtrim(str_replace(DIRECTORY_SEPARATOR, '/', $parentDirectory), '/') : '';
        if ($parentDirectory === '') {
            throw new Exception('Unable to retrieve the temporary directory.');
        }
        if (!is_dir($parentDirectory)) {
            throw new Exception("The temporary directory {$parentDirectory} does not exist.");
        }
        if (!is_writable($parentDirectory)) {
            throw new Exception("The temporary directory {$parentDirectory} is not writable.");
        }
        for ($i = 0; ; ++$i) {
            $path = $parentDirectory.'/volatile-'.$i.'-'.uniqid();
            if (!file_exists($path)) {
                FileUtils::createDirectory($path);
                break;
            }
        }
        $this->path = $path;
    }

    /**
     * Clear and delete this volatile directory.
     */
    public function __destruct()
    {
        if ($this->path !== null) {
            try {
                FileUtils::deleteFromFilesystem($this->path);
            } catch (Exception $foo) {
            }
            $this->path = null;
        }
    }

    /**
     * Get the absolute path of this volatile directory (always with '/' as directory separator, without the trailing '/').
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
