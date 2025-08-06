<div class="spacer-2"></div>
<?php
require_once(RomeTheme::plugin_dir() . 'view/header.php');
include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

$plugins = [
    'rtmkit' => [
        'file' => 'rometheme-for-elementor/RomeTheme.php',
        'class' => 'RomeTheme'
    ],
    'rtmform' => [
        'file' => 'romethemeform/rometheme-form.php',
        'class' => 'RomeThemeForm'
    ],
    'rtmkitpro' => [
        'file' => 'romethemekit-pro/RomeTheme_pro.php',
        'class' => 'RTMKitPro\Core\Plugin'
    ]
];

$rtmkitproInfo = \RomethemeKit\Update::get_pluginpro_info();

// echo (\RomethemeKit\Update::update_is_available()) ? "Update Available" : "no update";

?>

<div class="d-flex flex-column gap-3 me-3  mb-3 rtm-container rounded-2 rtm-bg-gradient-3 rtm-text-font" style="margin-top: -8rem;">
    <div class="px-5 rounded-3 pb-5">
        <div class="spacer"></div>
        <div class="d-flex flex-column gap-4">
            <div class="row row-cols-lg-2 row-cols-1">
                <div class="col col-lg-7">
                    <div class="d-flex flex-column gap-3">
                        <h1 class="text-white fw-light">Update</h1>
                        <div class="rtm-divider rounded"></div>
                    </div>
                </div>
            </div>
            <form class="d-flex flex-column gap-3">
                <?php foreach ($plugins as $k => $plugin) :
                    if (class_exists($plugin['class'])) :
                        $pluginData = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin['file']);
                        $update = new RomethemeKit\Update();
                        $pluginInfo = $update->get_plugin_info($k);

                ?>
                        <div class="rounded rtm-border bg-gradient-1 p-3 text-white">
                            <div class="d-flex flex-column gap-4">
                                <div class="d-flex flex-row gap-3">
                                    <div class="d-flex w-100 justify-content-between ps-4 align-items-center rounded bg-black rtm-border">
                                        <h6 class="m-0"><?php echo $pluginData['Name'] ?></h6>
                                        <span class="input-group-text border-0 bg-transparent accent-color px-4" id="basic-addon2"><?php echo $pluginData['Version'] ?></span>
                                    </div>
                                    <div class="w-25">
                                        <button class="btn btn-gradient-accent rounded w-100" data-update="<?php echo esc_attr($k) ?>"
                                            <?php echo (version_compare($pluginData['Version'], $pluginInfo->version, '>=')) ? 'disabled' : '' ?>>
                                            <?php echo (version_compare($pluginData['Version'], $pluginInfo->version, '>=')) ? wp_kses_post('<i class="fas fa-check me-2"></i>Up to date')  : esc_html('Update to v.' . $pluginInfo->version) ?>
                                        </button>
                                    </div>
                                </div>
                                <div class="d-flex gap-3 rollback-container">
                                    <div class="d-flex justify-content-between align-items-center w-100 ps-3">
                                        <p class="m-0">Rollback to Previous Version</p>
                                        <div class="w-25 h-100 rtm-border rounded bg-black overflow-hidden d-flex align-items-center px-3">
                                            <select name="version" class="form-select bg-black border-0">
                                                <?php $i = 0;
                                                unset($pluginInfo->versions->trunk);
                                                foreach ($pluginInfo->versions as $v => $link) : $i = $i + 1;
                                                    if ($v != 'trunk') : ?>
                                                        <option value="<?php echo esc_attr($v) ?>" <?php echo ($v == $pluginData['Version']) ? 'disabled' : '' ?> <?php echo ((count((array) $pluginInfo->versions) - 1) == $i) ? 'selected' : '' ?>><?php echo $v ?></option>
                                                <?php endif;
                                                endforeach;  ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="w-25">
                                        <button class="btn btn-gradient-accent rounded w-100" data-reinstall="<?php echo esc_attr($k) ?>">Reinstall</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php endif;
                endforeach; ?>
            </form>
        </div>
    </div>
</div>