<?php

$templates = get_option('rtm_template_installed', []);
$upload_dir = wp_upload_dir();
$rtmTemplateDir = $upload_dir['basedir'] . '/rometheme_template';
$rtmTemplateUrl = $upload_dir['baseurl'] . '/rometheme_template';

// echo count($templates);
?>

<div class="px-5 py-3">

    <div class="row row-cols-3">
        <div class="col mb-3">
            <div class="drop-zone rounded-3 d-flex justify-content-center align-items-center p-5">
                <input type="file" class="drop-zone__input" id="upload-template" accept=".zip" />
                <div class="drop-zone__prompt text-center">
                    <i class="rtmicon rtmicon-upload display-4 mb-3"></i>
                    <div class="h5">Drop Template Kit ZIP here or click to upload</div>
                </div>
            </div>
        </div>
        <?php foreach ($templates as $template => $v) :
            $manifest = json_decode(file_get_contents($rtmTemplateDir . '/' . $template . '/manifest.json'));
            $imgurl = $rtmTemplateUrl . '/' . $template . '/' . $manifest->templates[0]->screenshot;
        ?>
            <div class="col mb-3">
                <div class="d-flex flex-column h-100 rounded-3 overflow-hidden glass-effect rtm-border">
                    <img class="img-fluid rounded-top" src="<?php echo esc_url($imgurl) ?>">
                    <div class="p-3 d-flex flex-column gap-3">
                        <div class="d-block">
                            <h5 class="text-truncate text-white m-0"><?php echo esc_html($manifest->title) ?></h5>
                        </div>
                        <div class="d-flex flex-row gap-2">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=rtmkit-templates&template_id=' . $template)) ?>" class="fw-light btn w-100 btn-gradient-accent rounded-2"><i class="far fa-eye me-2"></i>View Kit</a>
                            <button class="btn fw-light w-100 btn-danger rounded-2 delete-template" data-template="<?php echo esc_attr($template) ?>"><i class="far fa-trash-can me-2"></i>Delete</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>