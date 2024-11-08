<?php
namespace App\Composer;

use Composer\Script\Event;

class ScriptHandler extends \Incenteev\ParameterHandler\ScriptHandler
{
    public static function createKeyfile(Event $event)
    {
        $options = self::getOptions($event);
        $consoleDir = self::getConsoleDir($event, 'create keyfile');

        if (null === $consoleDir) {
            return;
        }

        static::executeCommand($event, $consoleDir, 'app:create-keyfile', $options['process-timeout']);
    }
}