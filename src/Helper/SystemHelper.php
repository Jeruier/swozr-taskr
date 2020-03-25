<?php
/**
 * Created by PhpStorm.
 * User: Zhangrui
 * Date: 2020/3/25
 * Time: 11:55
 */

namespace Swozr\Taskr\Server\Helper;


class SystemHelper
{

    /**
     * get bash is available
     *
     * @return bool
     */
    public static function shIsAvailable(): bool
    {
        // $checkCmd = "/usr/bin/env bash -c 'echo OK'";
        // $shell = 'echo $0';
        $checkCmd = "sh -c 'echo OK'";

        return self::execute($checkCmd, false) === 'OK';
    }

    /**
     * Method to execute a command in the sys
     * Uses :
     * - system
     * - exec
     * - shell_exec
     *
     * @param string      $command
     * @param bool        $returnStatus
     * @param string|null $cwd
     *
     * @return array|string
     */
    public static function execute(string $command, bool $returnStatus = true, string $cwd = null)
    {
        $exitStatus = 1;

        if ($cwd) {
            chdir($cwd);
        }

        // system
        if (function_exists('system')) {
            ob_start();
            system($command, $exitStatus);
            $output = ob_get_clean();

            //exec
        } elseif (function_exists('exec')) {
            exec($command, $output, $exitStatus);
            $output = implode("\n", $output);

            //shell_exec
        } elseif (function_exists('shell_exec')) {
            $output = shell_exec($command);
        } else {
            $output     = 'Command execution not possible on this system';
            $exitStatus = 0;
        }

        if ($returnStatus) {
            return [
                'output' => trim($output),
                'status' => $exitStatus
            ];
        }

        return trim($output);
    }

    /**
     * get screen size of the terminal
     *
     * ```php
     * list($width, $height) = Sys::getScreenSize();
     * ```
     *
     * @from Yii2
     *
     * @param boolean $refresh whether to force checking and not re-use cached size value.
     *                         This is useful to detect changing window size while the application is running but may
     *                         not get up to date values on every terminal.
     *
     * @return array|boolean An array of ($width, $height) or false when it was not able to determine size.
     */
    public static function getScreenSize(bool $refresh = false)
    {
        static $size;
        if ($size !== null && !$refresh) {
            return $size;
        }

        if (self::shIsAvailable()) {
            // try stty if available
            $stty = [];
            if (exec('stty -a 2>&1', $stty)) {
                $sttyText = implode(' ', $stty);
                // linux: speed 38400 baud; rows 97; columns 362; line = 0;
                $pattern = '/rows\s+(\d+);\s*columns\s+(\d+);/mi';

                // mac: speed 9600 baud; 97 rows; 362 columns;
                if (self::isMac()) {
                    $pattern = '/(\d+)\s+rows;\s*(\d+)\s+columns;/mi';
                }

                if (preg_match($pattern, $sttyText, $matches)) {
                    return ($size = [$matches[2], $matches[1]]);
                }
            }

            // fallback to tput, which may not be updated on terminal resize
            if (($width = (int)exec('tput cols 2>&1')) > 0 && ($height = (int)exec('tput lines 2>&1')) > 0) {
                return ($size = [$width, $height]);
            }

            // fallback to ENV variables, which may not be updated on terminal resize
            if (($width = (int)getenv('COLUMNS')) > 0 && ($height = (int)getenv('LINES')) > 0) {
                return ($size = [$width, $height]);
            }
        }

        if (self::isWindows()) {
            $output = [];
            exec('mode con', $output);

            if (isset($output[1]) && strpos($output[1], 'CON') !== false) {
                return ($size = [
                    (int)preg_replace('~\D~', '', $output[3]),
                    (int)preg_replace('~\D~', '', $output[4])
                ]);
            }
        }

        return ($size = false);
    }

    /**
     * is windows OS
     *
     * @return bool
     */
    public static function isWindows(): bool
    {
        return stripos(PHP_OS, 'WIN') === 0;
    }

    /**
     * is mac os
     *
     * @return bool
     */
    public static function isMac(): bool
    {
        return stripos(PHP_OS, 'Darwin') !== false;
    }
}