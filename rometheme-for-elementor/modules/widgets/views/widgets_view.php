<div class="spacer-2"></div>
<?php

require RomeTheme::plugin_dir() . 'view/header.php';

$options = get_option('rkit-widget-options');

$optionJson = \RomethemeKit\RkitWidgets::listWidgets();

$headeroptions = array_filter($options, function ($value) {
    return $value['category'] == 'header';
});
$rkitoptions = array_filter($options, function ($value) {
    if (class_exists('WooCommerce')) {
        return $value['category'] == 'rkit' || $value['category'] == 'woocommerce';
    } else {
        return $value['category'] == 'rkit';
    }
});

$optionsPro = (\RomethemePlugin\Plugin::isProActive()) ? get_option('rkit-widget-pro-options') : \RomethemeKit\RkitWidgets::listWidgetPro();
$optionsProJSON =  \RomethemeKit\RkitWidgets::listWidgetPro();
$postOptions = \RomethemeKit\RkitWidgets::listWidgetPostPro();

$extensions = get_option('rtm_extensions');
$extensionsJSON = \RomethemeKit\RTMExtension::get_extension();

$formOptions = get_option('rform-widget-options');
$formJson = \RomethemeKit\RkitWidgets::listWidgetsForm();

$isProActivated = \RomethemePlugin\Plugin::isProActive();


?>
<style>
    h5 {
        margin: 0;
    }
</style>
<div class="d-flex flex-column gap-3 me-3  mb-3 rtm-container rounded-2 rtm-bg-gradient-3" style="margin-top: -8rem;">
    <div class="px-5 rounded-3 mb-4">
        <div class="spacer"></div>
        <div class="row row-cols-xl-2 row-cols-1 rtm-text-font py-5">
            <div class="col col-xl-7">
                <div class="d-flex flex-column text-white gap-0 px-2 ">
                    <h4 class="fw-bold text-white m-0">Widgets & Extentions</h4>
                    <div class="d-flex gap-2">
                        <span class="text-gray fw-bold"><?php echo (count($optionJson) + count($optionsProJSON) + count($postOptions)) + count($formJson) ?> All</span> &#8226;
                        <span class="text-secondary"><?php echo (count($optionJson)) + count($formJson) ?> Free</span> &#8226;
                        <span class="text-secondary"><?php echo count($optionsProJSON) + count($postOptions)  ?> Pro</span>&#8226;
                        <span class="text-secondary"><?php echo count($extensionsJSON)  ?> Extensions</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="rtm-text-font">
            <form id="widgets_option">
                <input type="text" name="action" value="save_options" hidden>
                <div class="d-flex flex-row justify-content-between mb-3">
                    <div class="d-flex gap-2">
                        <button class="btn btn-gradient-accent rounded-pill" id="enable-all">Enable All</button>
                        <button class="btn btn-outline-accent rounded-pill " id="disable-all">Disable All</button>
                        <button class="btn btn-outline-accent rounded-pill " id="reset-btn">Reset</button>
                    </div>
                    <div>
                        <button class="btn btn-gradient-accent rounded-pill" id="save-widget-options">Save Settings</button>
                    </div>
                </div>
                <div class="mt-4 bg-gradient-3 rtm-border rounded-3 overflow-hidden">
                    <div class="d-flex w-100 px-3 py-4 text-white rtm-border-bottom bg-gradient-3 mb-1">
                        <h5>Header & Footer Builder</h5>
                    </div>
                    <div class="row row-cols-xxl-3 row-cols-xl-3 m-0 px-2 pb-2">
                        <?php foreach ($headeroptions as $h_opt => $value) : ?>
                            <div class="col m-0 p-2">
                                <div class="card rtm-border mw-100 bg-gradient-3 rounded-3 w-100 m-0 p-3">
                                    <div class="d-flex flex-row align-items-center justify-content-between">
                                        <div class="col-9">
                                            <div class="d-flex flex-row align-items-center gap-3 text-white">
                                                <i class="accent-color <?php echo esc_attr($value['icon']) ?>" style="font-size:40px;"></i>
                                                <div class="d-flex flex-column">
                                                    <span><?php echo esc_html($value['name']) ?><?php echo (isset($value['is_new']) && $value['is_new']) ? wp_kses_post('<span class="badge align-text-bottom bg-accent-color ms-2 rounded-pill">New</span>') : '' ?></span>
                                                    <div class="d-flex link-docs">
                                                        <a href="<?php echo esc_url($optionJson[$h_opt]['docsURL']) ?>" target="_blank" class="">Docs</a>
                                                        <span>&#8226;</span>
                                                        <a href="<?php echo esc_url($optionJson[$h_opt]['previewURL']) ?>" target="_blank" class="">Demo</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="d-flex w-100 justify-content-end">
                                                <input name="<?php echo esc_attr($h_opt) ?>" value="false" hidden>
                                                <label class="switch">
                                                    <input name="<?php echo esc_attr($h_opt) ?>" class="switch-input" type="checkbox" value="true" <?php echo ($value['status']) ? 'checked' : ''  ?>>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="mt-4 bg-gradient-3 rtm-border rounded-3 overflow-hidden">
                    <div class="d-flex w-100 text-white px-3 py-4 rtm-border-bottom bg-gradient-3 mb-1">
                        <h5>General Widgets</h5>
                    </div>
                    <div class="row row-cols-xxl-3 row-cols-xl-3 m-0 px-2 pb-2">
                        <?php foreach ($rkitoptions as $h_opt => $value) : ?>
                            <div class="col m-0 p-2">
                                <div class="card mw-100 rounded-3 rtm-border bg-gradient-3 w-100 m-0 p-3">
                                    <div class="d-flex flex-row align-items-center justify-content-between">
                                        <div class="col-9">
                                            <div class="d-flex flex-row align-items-center gap-3 text-white">
                                                <i class="accent-color <?php echo esc_attr($value['icon']) ?>" style="font-size:40px;"></i>
                                                <div class="d-flex flex-column">
                                                    <span><?php echo esc_html($value['name']) ?><?php echo (isset($value['is_new']) && $value['is_new']) ? wp_kses_post('<span class="badge align-text-bottom bg-accent-color ms-2 rounded-pill">New</span>') : '' ?></span>
                                                    <div class="d-flex link-docs">
                                                        <a href="<?php echo esc_url($optionJson[$h_opt]['docsURL']) ?>" target="_blank" class="">Docs</a>
                                                        <span>&#8226;</span>
                                                        <a href="<?php echo esc_url($optionJson[$h_opt]['previewURL']) ?>" target="_blank" class="">Demo</a>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-3">
                                            <div class="d-flex w-100 justify-content-end">
                                                <input name="<?php echo esc_attr($h_opt) ?>" value="false" hidden>
                                                <label class="switch">
                                                    <input name="<?php echo esc_attr($h_opt) ?>" class="switch-input" type="checkbox" value="true" <?php echo ($value['status']) ? 'checked' : ''  ?>>
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </form>
            <form id="form_options">
                <div class="mt-4 bg-gradient-3 rtm-border rounded-3 overflow-hidden">
                    <div class="d-flex w-100 text-white px-3 py-4 rtm-border-bottom bg-gradient-3 mb-1">
                        <h5>Form Builder</h5>
                        <span class="accent-color ms-2" data-tooltips-right="This widget requires RTMForm Builder for Elementor and is only visible in the Form Editor."><i class="fa-solid fa-circle-exclamation"></i></span>
                    </div>
                    <div class="row row-cols-xxl-3 row-cols-xl-3 m-0 px-2 pb-2">
                        <?php foreach ($formOptions as $h_opt => $value) : ?>
                            <div class="col m-0 p-2">
                                <div class="card mw-100 rounded-3 rtm-border bg-gradient-3 w-100 m-0 p-3">
                                    <div class="d-flex flex-row align-items-center justify-content-between">
                                        <div class="col-9">
                                            <div class="d-flex flex-row align-items-center gap-3 text-white">
                                                <i class="accent-color <?php echo esc_attr($value['icon']) ?>" style="font-size:40px;"></i>
                                                <div class="d-flex flex-column">
                                                    <span><?php echo esc_html($value['name']) ?><?php echo (isset($value['is_new']) && $value['is_new']) ? wp_kses_post('<span class="badge align-text-bottom bg-accent-color ms-2 rounded-pill">New</span>') : '' ?></span>
                                                    <div class="d-flex link-docs">
                                                        <a href="<?php echo esc_url($formJson[$h_opt]['docsURL']) ?>" target="_blank" class="">Docs</a>
                                                        <span>&#8226;</span>
                                                        <a href="<?php echo esc_url($formJson[$h_opt]['previewURL']) ?>" target="_blank" class="">Demo</a>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-3">
                                            <?php if (class_exists('RomeThemeForm')) : ?>
                                                <div class="d-flex w-100 justify-content-end">
                                                    <input name="<?php echo esc_attr($h_opt) ?>" value="false" hidden>
                                                    <label class="switch">
                                                        <input name="<?php echo esc_attr($h_opt) ?>" class="switch-input" type="checkbox" value="true" <?php echo ($value['status']) ? 'checked' : ''  ?>>
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            <?php else : ?>
                                                <a href="http://localhost/wp_plugin/wp-admin/admin.php?page=romethemeform-entries" class="d-flex w-100 justify-content-end">
                                                    <i class="rtmicon rtmicon-lock" style="font-size: 1.3em ; color:#00cea6"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </form>
            <form id="widgets_option_pro">
                <?php if (\RomethemePlugin\Plugin::isProActive()) : ?>
                    <input type="text" name="action" value="save_options_pro" hidden>
                <?php endif; ?>
                <div class="mt-4 bg-gradient-3 rtm-border rounded-3 overflow-hidden">
                    <div class="d-flex w-100 text-white px-3 py-4 rtm-border-bottom bg-gradient-3 mb-1">
                        <h5>Widgets Pro Version</h5>
                    </div>
                    <div class="row row-cols-xxl-3 row-cols-xl-3 m-0 px-2 pb-2">
                        <?php foreach ($optionsPro as $h_opt => $value) : ?>
                            <div class="col m-0 p-2">
                                <?php if (\RomethemePlugin\Plugin::isProActive()) : ?>
                                    <div class="card mw-100 rounded-3 rtm-border bg-gradient-3 w-100 m-0 p-3">
                                        <div class="d-flex flex-row align-items-center justify-content-between">
                                            <div class="col-9">
                                                <div class="d-flex flex-row align-items-center gap-3 text-white">
                                                    <i class="accent-color <?php echo esc_attr($value['icon']) ?>" style="font-size:40px;"></i>
                                                    <div class="d-flex flex-column">
                                                        <span><?php echo esc_html($value['name']) ?><?php echo (isset($value['is_new']) && $value['is_new']) ? wp_kses_post('<span class="badge align-text-bottom bg-accent-color ms-2 rounded-pill">New</span>') : '' ?></span>
                                                        <div class="d-flex link-docs">
                                                            <a href="<?php echo esc_url($optionsProJSON[$h_opt]['docsURL']) ?>" target="_blank" class="">Docs</a>
                                                            <span>&#8226;</span>
                                                            <a href="<?php echo esc_url($optionsProJSON[$h_opt]['previewURL']) ?>" target="_blank" class="">Demo</a>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="col-3">
                                                <div class="d-flex w-100 justify-content-end">
                                                    <input name="<?php echo esc_attr($h_opt) ?>" value="false" hidden>
                                                    <label class="switch">
                                                        <input name="<?php echo esc_attr($h_opt) ?>" class="switch-input" type="checkbox" value="true" <?php echo ($value['status']) ? 'checked' : ''  ?>>
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <div class="card mw-100 rounded-3 rtm-border bg-gradient-3 w-100 m-0 p-3 ">
                                        <div class="d-flex flex-row align-items-center justify-content-between">
                                            <div class="col-9">
                                                <div class="d-flex flex-row align-items-center gap-3 text-white">
                                                    <i class="accent-color <?php echo esc_attr($value['icon']) ?>" style="font-size:40px;"></i>
                                                    <div class="d-flex flex-column">
                                                        <span><?php echo esc_html($value['name']) ?><?php echo (isset($value['is_new']) && $value['is_new']) ? wp_kses_post('<span class="badge align-text-bottom bg-accent-color ms-2 rounded-pill">New</span>') : '' ?></span>
                                                        <div class="d-flex link-docs">
                                                            <a href="<?php echo esc_url($optionsProJSON[$h_opt]['docsURL']) ?>" target="_blank" class="">Docs</a>
                                                            <span>&#8226;</span>
                                                            <a href="<?php echo esc_url($optionsProJSON[$h_opt]['previewURL']) ?>" target="_blank" class="">Demo</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <a href="https://rometheme.net/plugins/rtmkit/pricing/" target="_blank" class="d-flex w-100 justify-content-end">
                                                    <i class="rtmicon rtmicon-lock" style="font-size: 1.3em ; color:#00cea6"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </form>
            <div id="widgets_option_post">
                <div class="mt-4 bg-gradient-3 rtm-border rounded-3 overflow-hidden">
                    <div class="d-flex w-100 text-white px-3 py-4 rtm-border-bottom bg-gradient-3 mb-1">
                        <h5>Single & Archive Post</h5>
                        <span class="accent-color ms-2" data-tooltips-right="This widget requires RTMKit Pro Version, This Widget is only visible in the Single and Archive Builder."><i class="fa-solid fa-circle-exclamation"></i></span>
                    </div>
                    <div class="row row-cols-xxl-3 row-cols-xl-3 m-0 px-2 pb-2">
                        <?php foreach ($postOptions as $h_opt => $value) : ?>
                            <div class="col m-0 p-2">
                                <div class="card mw-100 rounded-3 rtm-border bg-gradient-3 w-100 m-0 p-3">
                                    <div class="d-flex flex-row align-items-center justify-content-between">
                                        <div class="col-9">
                                            <div class="d-flex flex-row align-items-center gap-3 text-white">
                                                <i class="accent-color <?php echo esc_attr($value['icon']) ?>" style="font-size:40px;"></i>
                                                <div class="d-flex flex-column">
                                                    <span><?php echo esc_html($value['name']) ?><?php echo (isset($value['is_new']) && $value['is_new']) ? wp_kses_post('<span class="badge align-text-bottom bg-accent-color ms-2 rounded-pill">New</span>') : '' ?></span>
                                                    <div class="d-flex link-docs">
                                                        <a href="<?php echo esc_url($value['docsURL']) ?>" class="">Docs</a>
                                                        <span>&#8226;</span>
                                                        <a href="<?php echo esc_url($value['previewURL']) ?>" class="">Demo</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <a href="https://rometheme.net/plugins/rtmkit/pricing/" target="_blank" class="d-flex w-100 justify-content-end">
                                                <i class="rtmicon rtmicon-lock" style="font-size: 1.3em ; color:#00cea6"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                </form>
            </div>
            <form id="extensions_option">
                <input type="text" name="action" value="save_extensions" hidden>
                <div class="mt-4 bg-gradient-3 rtm-border rounded-3 overflow-hidden">
                    <div class="d-flex w-100 text-white px-3 py-4 rtm-border-bottom bg-gradient-3 mb-1">
                        <h5>Extensions</h5>
                    </div>
                    <div class="row row-cols-xxl-3 row-cols-xl-3 m-0 px-2 pb-2">
                        <?php foreach ($extensions as $h_opt => $value) : ?>

                            <div class="col m-0 p-2">
                                <div class="card mw-100 rounded-3 rtm-border bg-gradient-3 w-100 m-0 p-3">
                                    <div class="d-flex flex-row align-items-center justify-content-between">
                                        <div class="col-9">
                                            <div class="d-flex flex-row align-items-center gap-3 text-white">
                                                <i class="accent-color <?php echo esc_attr($extensionsJSON[$h_opt]['icon']) ?>" style="font-size:40px;"></i>
                                                <div class="d-flex flex-column">
                                                    <span><?php echo esc_html($extensionsJSON[$h_opt]['name']) ?>
                                                        <?php 
                                                            echo (isset($extensionsJSON[$h_opt]['is_new']) && $extensionsJSON[$h_opt]['is_new']) ? wp_kses_post('<span class="badge align-text-bottom bg-accent-color ms-2 rounded-pill">New</span>') : '';
                                                            echo (isset($extensionsJSON[$h_opt]['is_pro']) && $extensionsJSON[$h_opt]['is_pro']) ? wp_kses_post('<span class="badge align-text-bottom bg-accent-color ms-2 rounded-pill">Pro</span>') : ''  
                                                        ?>
                                                    </span>
                                                    <div class="d-flex link-docs">
                                                        <a href="<?php echo esc_url($extensionsJSON[$h_opt]['docsURL']) ?>" target="_blank" class="">Docs</a>
                                                        <span>&#8226;</span>
                                                        <a href="<?php echo esc_url($extensionsJSON[$h_opt]['previewURL']) ?>" target="_blank" class="">Demo</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="d-flex w-100 justify-content-end">
                                                <?php if ((isset($extensionsJSON[$h_opt]['is_pro']) && $extensionsJSON[$h_opt]['is_pro'] === true && $isProActivated) || !isset($extensionsJSON[$h_opt]['is_pro'])) : ?>
                                                    <input name="<?php echo esc_attr($h_opt) ?>" value="false" hidden>
                                                    <label class="switch">
                                                        <input name="<?php echo esc_attr($h_opt) ?>" class="switch-input" type="checkbox" value="true" <?php echo ($value['status']) ? 'checked' : '' ?>>
                                                        <span class="slider round"></span>
                                                    </label>
                                                <?php else: ?>
                                                    <a href="https://rometheme.net/plugins/rtmkit/pricing/" target="_blank" class="d-flex w-100 justify-content-end">
                                                        <i class="rtmicon rtmicon-lock" style="font-size: 1.3em ; color:#00cea6"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </form>
        </div>
    </div>