<?php

namespace RTMKit\Modules;

class Manager
{
    protected static $instance;
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        \RTMKit\Modules\Storage::instance()->init();
    }
}
