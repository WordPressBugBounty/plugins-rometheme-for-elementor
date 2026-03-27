<?php
if (file_exists(WP_PLUGIN_DIR . '/romethemeform/rometheme-form.php')) {
    $btn['text'] = 'Activate RTMForm';
    $btn['action'] = 'Activating';
    $btn['url'] = wp_nonce_url('plugins.php?action=activate&plugin=romethemeform/rometheme-form.php&plugin_status=all&paged=1', 'activate-plugin_romethemeform/rometheme-form.php');
} else {
    $btn['text'] = 'Install RTMForm';
    $btn['action'] = 'Installing';
    $btn['url'] = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=romethemeform'), 'install-plugin_romethemeform');
}

?>

<div class="card rounded-4 banner flex-column justify-content-between gap-3  h-100 p-0">
    <div class="row row-cols-2">
        <div class="col col-lg-8">
            <div class="d-flex flex-column gap-3 justify-content-center h-100 px-5 py-4">
                <span class="accent-color">Advanced Forms Engine</span>
                <h1 class="m-0 fw-bold fs-3">
                    Unlock Full Form Functionality <br> with <span class="accent-color fs-2">RTMForm</span>
                </h1>
                <p class="m-0">
                    RTMForm powers all form functionality in RTMKit. Activate it to unlock full capabilities and ensure everything runs seamlessly.
                <div class="d-flex gap-3">
                    <a id="install-rtmform" data-action="<?php echo $btn['action']; ?>" class="btn btn-accent fw-bold px-4 py-3 gap-2" href="<?php 
                    echo $btn['url']; 
                    ?>">
                        <?php echo $btn['text']; ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <img src="<?php echo RTM_KIT_URL . 'assets/images/form.png'; ?>" alt="RTMForm Banner" class="img-fluid">
        </div>
    </div>
</div>