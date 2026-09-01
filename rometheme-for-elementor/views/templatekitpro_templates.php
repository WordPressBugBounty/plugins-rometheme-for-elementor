<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="row row-cols-lg-4 row-cols-md-2 row-cols-1 g-3">
    <?php foreach ( $datas['data_template'] as $data ) : ?>
        <div class="col">
            <div class="card rounded-4 shadow-sm p-3 h-100 gap-3">
                <div class="position-relative">
                    <img src="<?php echo esc_url( $data['image_preview'] ) ?>" class="card-img-top rounded-2" alt="<?php echo esc_attr( $data['name'] ) ?>" loading="lazy">
                    <div class="position-absolute" style="top: 0px; right: 0px; transform: translateY(-35%)">
                        <span class="badge pro bg-primary text-white shadow">
                            <i class="fa-solid fa-crown"></i>
                            Pro
                        </span>
                    </div>
                </div>
                <div class="card-body d-flex flex-column gap-3 h-100 justify-content-between p-0">
                    <h5 class="card-title"><?php echo esc_html( $data['name'] ) ?></h5>
                    <div class="d-flex flex-row justify-content-between align-items-center">
                        <div class="d-flex flex-row gap-2">
                            <?php if ( $data['has_installed'] ) : ?>
                                <button class="btn btn-success rounded-2 view-template" data-template="<?php echo esc_attr( $data['installed'] ) ?>">
                                    <i class="fas fa-check"></i>
                                    View
                                </button>
                            <?php else : ?>
                                <?php if ( \RTMKit\Core\Plugin::instance()->pro_is_active() ) : ?>
                                    <button class="btn btn-secondary rounded-2 btn-install-template" data-template="<?php echo esc_attr( $data['id'] ) ?>">
                                        <i class="fas fa-plus"></i>
                                        Install
                                    </button>
                                <?php else : ?>
                                    <a href="https://rometheme.net/plugins/rtmkit/pricing/" target="_blank" class="btn btn-secondary rounded-2 text-white">
                                        <i class="fa-solid fa-crown"></i>
                                        Upgrade to Pro
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                            <a href="<?php echo esc_url( $data['preview_url'] ) ?>" target="_blank" class="btn btn-link rounded-2">
                                <i class="fas fa-eye"></i>
                                Preview
                            </a>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <i class="fas fa-download"></i>
                            <span class="fs-6"><?php echo esc_html( $data['downloads'] ) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>