<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/20
 * Time: 16:34
 */

namespace Swozr\Taskr\Server\Tools\OutputStyle;


class Cli
{
    /**
     * Returns true if STDOUT supports colorization.
     * This code has been copied and adapted from
     * \Symfony\Component\Console\Output\OutputStream.
     *
     * @return boolean
     */
    public static function isSupportColor(): bool
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return '10.0.10586' === PHP_WINDOWS_VERSION_MAJOR . '.' . PHP_WINDOWS_VERSION_MINOR . '.' . PHP_WINDOWS_VERSION_BUILD ||
                false !== getenv('ANSICON') ||
                'ON' === getenv('ConEmuANSI') ||
                'xterm' === getenv('TERM')// || 'cygwin' === getenv('TERM')
                ;
        }

        if (!defined('STDOUT')) {
            return false;
        }

        return self::isInteractive(STDOUT);
    }
    /**
     * Returns if the file descriptor is an interactive terminal or not.
     *
     * @param int|resource $fileDescriptor
     *
     * @return boolean
     */
    public static function isInteractive($fileDescriptor): bool
    {
        /** @noinspection PhpComposerExtensionStubsInspection */
        return function_exists('posix_isatty') && @posix_isatty($fileDescriptor);
    }
}