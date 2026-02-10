<?php

namespace RTMKit\Modules\Helper;

class Banner
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'call']);
    }

    public function get_active_banner()
    {
        $api_handler = \RTMKit\Modules\Helper\APIHandler::instance();
        $active_banner = $api_handler->remote('/wp-json/rtm/v1/banner?active' , [], null , false, true, 'GET');
        if (is_wp_error($active_banner)) {
            return null;
        }
        return $active_banner;
    }

    public function call()
    {
        $banner = $this->get_active_banner();

        if ($banner) {
            add_action('admin_head', [$this, 'enqueue_scripts']);
            foreach ($banner as $key => $value) {
                add_action('admin_notices', function () use ($key, $value) {

                    // =========================
                    // CEK HALAMAN SEKARANG
                    // =========================
                    global $pagenow;
                    $screen = get_current_screen();

                    // 1. Dashboard
                    $is_dashboard = ($pagenow === 'index.php');

                    // 2. Halaman milik plugin romethemekit
                    $is_romethemekit_page = (
                        isset($screen->id)
                        && strpos($screen->id, 'romethemekit') !== false
                    );

                    // 3. Jangan tampil di Post, Page, CPT editor
                    $is_post_editor = (
                        isset($screen->base)
                        && in_array($screen->base, [
                            'post',
                            'edit',
                        ])
                    );

                    if ($is_post_editor) {
                        return; // blokir di post/page/editor
                    }

                    // =========================
                    // SANITASI & OUTPUT
                    // =========================
                    echo $value['content_html'];
                });
            }
        }
    }

    public function enqueue_scripts()
    {
        echo "<script>
            document.addEventListener('DOMContentLoaded' , function(){
            const dismissbtn = document.querySelector('.rkit-notice-banner .dismiss-banner');
  
  dismissbtn.addEventListener('click' , function(e) {
      e.preventDefault();
    const _this = e.currentTarget.closest('.rkit-notice-banner');
    _this.style.display = 'none';
  });
            });
</script>";
    }
}
