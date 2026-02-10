<?php

namespace RTMKit\Modules\Extensions;

class ExtensionStorage
{
    private static $instance = null;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        if (wp_doing_ajax()) {
            add_action('wp_ajax_save_extensions', [$this, 'save_extensions']);
        }
        add_action('rtmkit_register_extension', [$this, 'register_extension']);
        do_action('rtmkit_register_extension');
    }

    public function get_extension()
    {
        $extensionsFileJson     = file_get_contents(RTM_KIT_DIR . '/metadata/extensions.json');
        $extensionsProFileJson  = file_exists(RTM_KIT_DIR . '/metadata/extensions-pro.json')
            ? file_get_contents(RTM_KIT_DIR . '/metadata/extensions-pro.json')
            : '{}';

        $extensions    = json_decode($extensionsFileJson, true);
        $extensionsPro = json_decode($extensionsProFileJson, true);

        // Gabungkan dua array tanpa overwrite jika key sama
        $merged = array_merge($extensions, array_diff_key($extensionsPro, $extensions));

        // Sorting berdasarkan nama
        uasort($merged, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $merged;
    }

    public function register_extension()
    {
        $exts = \RTMKit\Modules\Storage::instance()->get_active_modules(['category' => 'extension']);

        foreach ($exts as $key => $ext) {
            if ($ext['status']) {
                $class = '\\RTMKit\\Extensions\\' . $ext['classname'];
                if (class_exists($class)) {
                    (new $class())->init();
                    // error_log("Instantiated class: $class");
                }
            }
        }
    }

    public function update_option_pro()
    {
       $currentOptions = get_option('rtm_extensions');
       if($currentOptions == false) {
        update_option('rtm_extensions' , $this->get_extension());
       }
    }

    public function get_extension_options()
    {
        $options = get_option('rtm_extensions', []);
        return $options;
    }

    public static function get_logo(){
        $logo = '
		<svg
          xmlns="http://www.w3.org/2000/svg"
          xmlnsXlink="http://www.w3.org/1999/xlink"
          viewBox="0 0 492.94 492.94"
          shapeRendering="geometricPrecision"
          textRendering="geometricPrecision"
		  style="
		    width: 18px;
    		margin-bottom: -3px;
    		margin-right: 4px; 
		  "
        >
          <g transform="matrix(1.658927 0 0 1.658927 -187.604845 -149.806187)">
            <rect
              width="82.32"
              height="82.32"
              rx="0"
              ry="0"
              transform="translate(123.22 294.99)"
              fill="#00cea6"
              strokeWidth="0"
            />
            <g>
              <polygon
                points="342.61,268.16 316.74,293.64 261.59,238.49 287.45,212.63 342.61,268.16"
                opacity="0.6"
                fill="#00cea6"
                strokeWidth="0"
              />
              <polygon
                points="400.1,377.31 288.12,377.31 270.64,359.83 260.83,350.02 205.69,294.88 123.22,212.41 123.22,100.44 123.43,100.65 400.06,377.27 400.1,377.31"
                fill="#00cea6"
                strokeWidth="0"
              />
            </g>
            <path
              d="M395.54,206.04c2.63,2.62,2.61,6.89-.03,9.49l-18.16,17.89-.21.21-34.52,34.53-11.88,11.33l3.92-3.74c4.36-4.16,4.45-11.1.18-15.37L197.92,123.48h114.16c.53,0,1.04.21,1.41.58l82.04,81.98h.01Z"
              fill="#00cea6"
              strokeWidth="0"
            />
          </g>
        </svg>
		';
        return $logo;
    }
}
