<?php

namespace RTMKit\Modules\Submission;

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
?>
        <div class="card rounded-4">
            <div id="rtmtable">
                <form method="post">
                    <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
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
