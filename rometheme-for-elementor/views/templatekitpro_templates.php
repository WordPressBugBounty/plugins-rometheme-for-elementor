<?php
if (!defined('ABSPATH'))
    exit;
$current_page = max(1, intval($datas['pagination']['current_page'] ?? ($_POST['paged'] ?? 1)));
$total_pages = max(1, intval($datas['pagination']['total_pages'] ?? 1));
$has_more = $current_page < $total_pages;
?>
<div class="row row-cols-lg-4 row-cols-md-2 row-cols-1 g-3 card-templatekit">
    <?php foreach ($datas['data_template'] as $data): ?>
        <div class="col">
            <div class="card rounded-4 shadow-sm p-0 h-100 gap-3">
                <div class="position-relative">
                    <img src="<?php echo esc_url($data['image_preview']) ?>" class="card-img-top rounded-top-4"
                        alt="<?php echo esc_attr($data['name']) ?>" loading="lazy">
                    <div class="position-absolute" style="top: 0px; right: 0px; transform: translateY(-35%)">
                        <span class="badge pro bg-warning text-black text-white shadow">
                            <i class="fa-solid fa-crown"></i>
                            Pro
                        </span>
                    </div>
                </div>
                <div class="card-body d-flex flex-column gap-3 h-100 justify-content-between p-3 pt-0">
                    <h5 class="card-title"><?php echo esc_html($data['name']) ?></h5>
                    <div class="d-flex flex-row justify-content-between align-items-center">
                        <div class="d-flex flex-row gap-2">
                            <?php if ($data['has_installed']): ?>
                                <button class="btn btn-success rounded-2 view-template"
                                    data-template="<?php echo esc_attr($data['installed']) ?>">
                                    <i class="fas fa-check"></i>
                                    View
                                </button>
                            <?php else:
                                $upgrade_button_html = '<a href="' . esc_url(rtmkit_upgrade_url('templatekit.pro_card')) . '" target="_blank" class="btn btn-secondary rounded-2 text-white">
                                    <i class="fa-solid fa-crown"></i>
                                    Upgrade to Pro
                                </a>';
                                echo apply_filters('rtmkit_upgrade_to_pro_button', $upgrade_button_html, $data);
                            endif; ?>
                            <a href="<?php echo esc_url($data['preview_url']) ?>" target="_blank"
                                class="btn btn-link rounded-2">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                        <div class="d-flex gap-2 align-items-center download-count"
                            style="color:var(--accent-color)!important;">
                            <i class="fas fa-download"></i>
                            <span
                                style="color:var(--accent-color)!important;"><?php echo esc_html($data['downloads']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="rtmkit-auto-pagination d-flex justify-content-center mt-4 <?php echo $has_more ? '' : ' d-none'; ?>" data-current-page="<?php echo esc_attr($current_page); ?>"
    data-has-more="<?php echo $has_more ? '1' : '0'; ?>">
    <div class="spinner-grow text-success" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>