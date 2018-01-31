<?php

namespace Punic\Website;

use Exception;

class FileUtils
{
    /**
     * @param string $path
     * @param bool $emptyOnlyDir
     *
     * @throws Exception
     */
    public static function deleteFromFilesystem($path, $emptyOnlyDir = false)
    {
        $maxRetries = 5;
        if (is_file($path) || is_link($path)) {
            for ($i = 1; ; ++$i) {
                if (@unlink($path) === false) {
                    if ($i === $maxRetries) {
                        throw new Exception("Failed to delete the file {$path}");
                    }
                } else {
                    break;
                }
            }
        } elseif (is_dir($path)) {
            $contents = @scandir($path);
            if ($contents === false) {
                throw new Exception("Failed to retrieve the contents of the directory {$path}");
            }
            foreach (array_diff($contents, ['.', '..']) as $item) {
                static::deleteFromFilesystem($path.'/'.$item);
            }
            if (!$emptyOnlyDir) {
                for ($i = 1; ; ++$i) {
                    if (@rmdir($path) === false) {
                        if ($i === $maxRetries) {
                            throw new Exception("Failed to delete the directory {$path}");
                        }
                    } else {
                        break;
                    }
                }
            }
        }
    }

    /**
     * @param string $path
     *
     * @throws Exception
     */
    public static function createDirectory($path)
    {
        if (!is_dir($path)) {
            if (@mkdir($path, 0777, true) !== true) {
                throw new Exception("Failed to create the directory {$path}");
            }
        }
    }
}
