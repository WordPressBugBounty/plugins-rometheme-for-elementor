<?php

namespace RTMKit\Modules\Submission;

if (! defined('ABSPATH')) exit;

class SubmissionModule
{
    protected static $instance;

    protected $menus;

    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        if (wp_doing_ajax()) {
            add_action('wp_ajax_get_submission_content', [$this, 'get_submission_content']);
        }
    }

    public function get_submission_content()
    {

        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (! current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 403);
        }

        if (!class_exists('RomeThemeForm')) {
            ob_start();
            require_once RTM_KIT_DIR . 'views/rtmform-not-active.php';
            $content = ob_get_clean();
            wp_send_json_success($content);
        }

        if (isset($_POST['entries_id'])) {
            ob_start();
            require \RomeThemeForm::module_dir() . 'form/views/entries-view.php';
            $content = ob_get_clean();
            wp_send_json_success($content);
        } else {
            ob_start();
            $this->render_submission_table();
            $content = ob_get_clean();

            wp_send_json_success($content);
        }
    }

    private function render_submission_table()
    {
        $submissionTable = new \RTMKit\Modules\Submission\SubmissionTable();
        $submissionTable->prepare_items();
        $request_page = isset($_REQUEST['page']) ? sanitize_key(wp_unslash($_REQUEST['page'])) : '';
?>
        <div class="card rounded-4">
            <div id="rtmtable">
                <form method="post">
                    <input type="hidden" name="page" value="<?php echo esc_attr($request_page); ?>" />
                    <div class="d-flex flex-row justify-content-between mb-3">
                        <?php
                        $submissionTable->views();
                        $submissionTable->render_custom_pagination();
                        ?>
                    </div>
                    <?php
                    $submissionTable->display();
                    ?>
                </form>
            </div>
        </div>
<?php
    }
}
