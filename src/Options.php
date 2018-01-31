<?php

namespace Punic\Website;

/**
 * Command options.
 */
class Options
{
    /**
     * The path to the temporary directory.
     *
     * @var string
     */
    protected $tempDirectory;

    /**
     * The directory where Punic can be found.
     *
     * @var string
     */
    protected $punicDirectory;

    /**
     * The directory where the generated docs will be created.
     *
     * @var string
     */
    protected $outputDirectory;

    /**
     * Initializes the instance.
     */
    protected function __construct()
    {
        $this->tempDirectory = static::getDefaultTempDirectory();
        $this->punicDirectory = '';
        $this->outputDirectory = static::getDefaultOutputDirectory();
    }

    /**
     * Get the path to the temporary directory.
     *
     * @return string
     */
    public function getTempDirectory()
    {
        return $this->tempDirectory;
    }

    /**
     * Get the directory where Punic can be found.
     *
     * @return string
     */
    public function getPunicDirectory()
    {
        return $this->punicDirectory;
    }

    /**
     * Get the directory where the generated docs will be created.
     *
     * @return string
     */
    public function getOutputDirectory()
    {
        return $this->outputDirectory;
    }

    /**
     * @param array $options
     *
     * @return static
     */
    public static function fromArray(array $options)
    {
        $result = new static();
        $n = count($options);
        for ($i = 0; $i < $n; ++$i) {
            if (preg_match('/^(--[^=]+)=(.*)$/', $options[$i], $matches)) {
                $currentOption = $matches[1];
                $nextOption = $matches[2];
                $advanceNext = false;
            } else {
                $currentOption = $options[$i];
                $nextOption = $i + 1 < $n ? $options[$i + 1] : '';
                $advanceNext = true;
            }

            $optionWithValue = false;
            switch (strtolower($currentOption)) {
                case '-h':
                case '--help':
                    static::showHelp();
                    exit(0);
                case '--temp':
                case '-t':
                    $optionWithValue = true;
                    if ($nextOption === '') {
                        throw new UserMessageException('Please specify the path of the temporary directory');
                    }
                    $result->tempDirectory = static::normalizeDirectoryPath($nextOption);
                    if ($result->tempDirectory === '' || !is_dir($result->tempDirectory)) {
                        throw new UserMessageException('Unable to find the directory '.$nextOption);
                    }
                    break;
                case '--punic':
                case '-p':
                    $optionWithValue = true;
                    if ($nextOption === '') {
                        throw new UserMessageException('Please specify the where Punic resides');
                    }
                    $result->punicDirectory = static::normalizeDirectoryPath($nextOption);
                    if ($result->punicDirectory === '' || !is_dir($result->punicDirectory)) {
                        throw new UserMessageException('Unable to find the directory '.$nextOption);
                    }
                    break;
                case '--output':
                case '-o':
                    $optionWithValue = true;
                    if ($nextOption === '') {
                        throw new UserMessageException('Please specify the path of the output directory');
                    }
                    $result->outputDirectory = static::normalizeDirectoryPath($nextOption);
                    if ($result->outputDirectory === '') {
                        throw new UserMessageException('Invalid output directory '.$nextOption);
                    }
                    break;
                default:
                    throw new UserMessageException("Unknown option: {$currentOption}\nUse -h (or --help) to get the list of available options");
            }
            if ($optionWithValue && $advanceNext) {
                ++$i;
            }
        }

        return $result;
    }

    /**
     * Get the default temporary directory.
     *
     * @return string
     */
    protected static function getDefaultTempDirectory()
    {
        return static::normalizeDirectoryPath(sys_get_temp_dir());
    }

    /**
     * Get the default output directory.
     *
     * @return string
     */
    protected static function getDefaultOutputDirectory()
    {
        return static::normalizeDirectoryPath(dirname(__DIR__).'/docs');
    }

    /**
     * @param string|mixed $path
     *
     * @return string|null
     */
    protected static function normalizeDirectoryPath($path)
    {
        $result = '';
        if (is_string($path)) {
            $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
            if (stripos(PHP_OS, 'WIN') === 0) {
                $invalidChars = implode('', array_map('chr', range(0, 31))).'*?"<>|';
            } else {
                $invalidChars = '';
            }
            $path = rtrim($path, '/');
            if ($path !== '' && ($invalidChars === '' || strpbrk($path, $invalidChars) === false)) {
                $result = $path;
            }
        }

        return $result;
    }

    protected static function showHelp()
    {
        $defaultTempDirectory = str_replace('/', DIRECTORY_SEPARATOR, static::getDefaultTempDirectory());
        $defaultOutputDirectory = str_replace('/', DIRECTORY_SEPARATOR, static::getDefaultOutputDirectory());
        echo <<<EOT
Available options:

  --help|-h
    Show this help message

  --temp=<path>|-t <path>
    The path a temporary directory (default: {$defaultTempDirectory})

  --punic=<path>|-p <path>
    The path of Punic.
    If not specified, we'll fetch the last version of Punic from GitHub.

  --output=<path>|-o <path>
    The path of the generated docs directory (default: {$defaultOutputDirectory}).
    WARNING! This directory will be emptied!!!

EOT;
    }
}
