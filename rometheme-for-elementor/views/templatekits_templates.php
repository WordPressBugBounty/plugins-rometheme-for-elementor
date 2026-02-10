<div class="row row-cols-lg-3 row-cols-md-2 row-cols-1 g-3">
    <?php foreach ($datas['data_template'] as $data) : ?>
        <div class="col">
            <div class="card rounded-4 shadow-sm p-3 h-100 gap-3">
                <div class="position-relative">
                    <img src="<?php echo esc_url($data['image_preview']) ?>" class="card-img-top rounded-2" alt="<?php echo esc_attr($data['name']) ?>" loading="lazy">
                    <div class="position-absolute" style="top: 0px; right: 0px; transform: translateY(-35%)">
                        <?php if ($data['type'] === 'pro') : ?>
                            <span class="badge pro bg-primary text-white shadow">
                                <i class="fa-solid fa-crown"></i>
                                Pro</span>
                        <?php else : ?>
                            <span class="badge bg-white text-black shadow">
                                <svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.29449 4.08625C8.22449 3.94125 8.07741 3.84916 7.91657 3.84916H5.53074L6.10699 1.41083C6.15657 1.21375 6.07074 1.00708 5.89449 0.903329C5.71407 0.797496 5.48491 0.826662 5.33741 0.974579C5.33032 0.981662 5.32324 0.989162 5.31657 0.997079L1.76532 5.10541C1.65782 5.22958 1.63282 5.40541 1.70116 5.55458C1.76949 5.70416 1.91866 5.8 2.08282 5.8H4.48157L3.88616 8.62083C3.84116 8.82166 3.93532 9.0275 4.11741 9.12541C4.18616 9.16208 4.26032 9.18 4.33407 9.18C4.46032 9.18 4.58407 9.12791 4.67366 9.02958C4.67991 9.0225 4.68616 9.01541 4.69199 9.00833L8.24491 4.53041C8.34491 4.40416 8.36407 4.23166 8.29407 4.08666L8.29449 4.08625ZM5.04116 7.21708L5.41074 5.46666C5.43699 5.34291 5.40574 5.21375 5.32616 5.11541C5.24657 5.01708 5.12657 4.96 4.99991 4.96H3.00116L4.93366 2.72416L4.59157 4.1725C4.56199 4.2975 4.59157 4.42916 4.67074 4.52958C4.75032 4.63041 4.87157 4.68875 4.99991 4.68875H7.04741L5.04157 7.21708H5.04116Z" fill="#1E2124" />
                                </svg>
                                Free
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body d-flex flex-column gap-3 h-100 justify-content-between p-0">
                    <h5 class="card-title"><?php echo esc_html($data['name']) ?></h5>
                    <div class="d-flex flex-row justify-content-between align-items-center">
                        <div class="d-flex flex-row gap-2">
                            <?php if ($data['has_installed']) : ?>
                                <button class="btn btn-success rounded-2 view-template" data-template="<?php echo esc_attr($data['installed']) ?>">
                                    <i class="fas fa-check"></i>
                                    View
                                </button>
                            <?php else : ?>
                                <?php if ($data['type'] === 'pro') : ?>
                                    <?php if (class_exists('\\RTMKitPro\\Core\\Plugin') && \RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive()) : ?>
                                        <button class="btn btn-secondary rounded-2 btn-install-template" data-template="<?php echo esc_attr($data['id']) ?>">
                                            <i class="fas fa-plus"></i>
                                            Install
                                        </button>
                                    <?php else : ?>
                                        <a href="https://rometheme.net/plugins/rtmkit/pricing/" target="_blank" class="btn btn-secondary rounded-2 text-white">
                                            <i class="fa-solid fa-crown"></i>
                                            Upgrade to Pro
                                        </a>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <button class="btn btn-secondary rounded-2 btn-install-template" data-template="<?php echo esc_attr($data['id']) ?>">
                                        <i class="fas fa-plus"></i>
                                        Install
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                            <a href="<?php echo esc_url($data['preview_url']) ?>" target="_blank" class="btn btn-link rounded-2">
                                <i class="fas fa-eye"></i>
                                Preview
                            </a>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <i class="fas fa-download"></i>
                            <span class="fs-6"><?php echo esc_html($data['downloads']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>