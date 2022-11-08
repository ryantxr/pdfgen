<?php
namespace Coxcode\Pdfgen;

use Psr\Log\LoggerInterface;


class Log implements LoggerInterface
{
    public static $log = null;
    
    public static function init(LoggerInterface $log)
    {
        self::$log = $log;
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency($message, array $context = array())
    {
        if ( self::$log ) self::$log->emergency($message, $context);
    }
    
    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert($message, array $context = array())
    {
        if ( self::$log ) self::$log->alert($message, $context);
        
    }
    
    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical($message, array $context = array())
    {
        if ( self::$log ) self::$log->critical($message, $context);
        
    }
    
    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error($message, array $context = array())
    {
        if ( self::$log ) self::$log->error($message, $context);
        
    }
    
    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning($message, array $context = array())
    {
        if ( self::$log ) self::$log->warning($message, $context);
        
    }
    
    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice($message, array $context = array())
    {
        if ( self::$log ) self::$log->notice($message, $context);
        
    }
    
    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = array())
    {
        if ( self::$log ) self::$log->info($message, $context);
        
    }
    
    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug($message, array $context = array())
    {
        if ( self::$log ) self::$log->debug($message, $context);
        
    }
    
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        if ( self::$log ) self::$log->log($level, $message, $context);
        
    }


}