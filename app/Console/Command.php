<?php
namespace App\Console;

use Illuminate\Console\Command as BaseCommand;

class Command extends BaseCommand {


    /**
     * @param string|array $string
     * @param null $verbosity
     * @param bool $leadingLine
     * @param bool $trailingLine
     */
    public function error($string, $verbosity = null, $leadingLine = false, $trailingLine = false) {
        $lineLength = value(function () use ($string) {
            return (is_array($string) ? max(array_map('strlen', $string)) : strlen($string)) + 4;
        });

        $leadingLine && parent::error(str_repeat(' ', $lineLength), $verbosity);

        if (is_array($string)) {
            foreach ($string as $error) {
                parent::error(
                    sprintf(
                        $leadingLine ? '  %s' : '%s',
                        str_pad($error, $leadingLine ? $lineLength - 2 : $lineLength, ' ', STR_PAD_RIGHT)
                    ),
                    $verbosity
                );
            }
        } else {
            parent::error(
                sprintf(
                    $leadingLine ? '  %s' : '%s',
                    str_pad($string, $leadingLine ? $lineLength - 2 : $lineLength, ' ', STR_PAD_RIGHT)
                ),
                $verbosity
            );
        }

        $trailingLine && parent::error(str_repeat(' ', $lineLength), $verbosity);
    }


    /**
     * @param string|array $string
     * @param null $verbosity
     * @param bool $leadingLine
     * @param bool $trailingLine
     */
    public function info($string, $verbosity = null, $leadingLine = false, $trailingLine = false) {
        $lineLength = value(function () use ($string) {
            return (is_array($string) ? max(array_map('strlen', $string)) : strlen($string)) + 4;
        });

        $leadingLine && parent::info(str_repeat(' ', $lineLength), $verbosity);

        if (is_array($string)) {
            foreach ($string as $info) {
                parent::info(sprintf('%s', str_pad($info, $lineLength, ' ', STR_PAD_RIGHT)), $verbosity);
            }
        } else {
            parent::info(sprintf('%s', str_pad($string, $lineLength, ' ', STR_PAD_RIGHT)), $verbosity);
        }

        $trailingLine && parent::info(str_repeat(' ', $lineLength), $verbosity);
    }
}
