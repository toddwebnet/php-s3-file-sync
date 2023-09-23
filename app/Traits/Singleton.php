<?php

namespace App\Traits;

/**
 * Allow us to make any class use a singleton design pattern.
 */
trait Singleton
{
    protected static object $instance;

    /**
     * Create or return an existing singleton instance.
     */
    final public static function instance()
    {
        return static::$instance ?? (static::$instance = new static);
    }

    /**
     * @param $instance
     */
    final public static function setInstance($instance)
    {
        static::$instance = $instance;
    }

    final public static function newInstance()
    {
        return self::resetInstance();
    }

    /**
     * resets instance with fresh instance
     */
    final public static function resetInstance()
    {
        static::$instance = new static;
        return self::instance();
    }

    /**
     * Give us a constructor that calls a bootstrap method.
     */
    final private function __construct()
    {
        $this->init();
    }

    /**
     * Initialize the singleton.
     */
    protected function init()
    {
    }

    /**
     * Trigger errors if we try to clone or deserialize a singleton.
     */
    public function __clone()
    {
        trigger_error('Cloning ' . __CLASS__ . ' is not allowed because it is a singleton.', E_USER_ERROR);
    }

    public function __wakeup()
    {
        trigger_error('Class ' . __CLASS__ . ' may not be deserialized because it is a singleton.', E_USER_ERROR);
    }
}
