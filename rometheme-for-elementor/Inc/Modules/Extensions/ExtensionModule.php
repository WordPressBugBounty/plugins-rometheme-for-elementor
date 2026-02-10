<?php

namespace RTMKit\Modules\Extensions;

class ExtensionModule
{
    private static $instance;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        $this->load();
    }

    private function load()
    {
        // $this->update_ext_opt();
        \RTMKit\Modules\Extensions\ExtensionStorage::instance()->init();
    }
}
