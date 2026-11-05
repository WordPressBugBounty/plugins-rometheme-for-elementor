<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('rtmkit_render_pro_upgrade_modal')) {
    function rtmkit_render_pro_upgrade_modal(
        $id = 'moduleLockedModal',
        $heading = 'Unlock All Features with<br>the Pro Plan',
        $description = 'Upgrade and get access to every feature.',
        $cta_text = 'Upgrade to Pro',
        $cta_url = ''
    ) {
        // Default-nya dihitung di sini, bukan di daftar parameter: URL-nya
        // memuat kode situs, jadi bukan nilai konstan.
        if ('' === $cta_url) {
            $cta_url = rtmkit_upgrade_url('pro_modal.cta', 'store');
        }
        ?>
        <div class="modal fade moduleLockedModal" id="<?php echo esc_attr($id); ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
                <div class="modal-content border-0 rounded-5 overflow-hidden" style="background: linear-gradient(180deg, rgba(9, 32, 33, 0.98) 0%, rgba(2, 20, 22, 0.98) 100%); box-shadow: 0 30px 80px rgba(0, 0, 0, 0.45);">
                    <div class="modal-body p-0 position-relative" style="height: auto;">
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-4" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1) grayscale(1); opacity: .8;"></button>

                        <div class="px-4 pt-4 pb-0 text-center">
                            <img src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/pro-features.png'); ?>" alt="Pro Features" style="max-width: 100%; height: auto; display: block; margin: 0 auto 18px;">
                        </div>

                        <div class="px-4 pb-4 text-center">
                            <h3 class="m-0 mb-3 text-white">
                                <?php echo wp_kses_post($heading); ?>
                            </h3>
                            <p class="m-0 mb-4 text-white">
                            <?php echo esc_html($description); ?>
                            </p>

                            <a href="<?php echo esc_url($cta_url); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-accent w-100 fw-bold border-0 rounded-2 py-3 d-flex align-items-center justify-content-center gap-2">
                                <i class="fa-solid fa-crown"></i>
                                <?php echo esc_html($cta_text); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

if (! function_exists('rtmkit_render_addon_locked_modal')) {
    /**
     * Modal untuk fitur addon saat lisensi sudah aktif tapi addon-nya belum:
     * yang dibutuhkan mengaktifkan addon, bukan upgrade.
     */
    function rtmkit_render_addon_locked_modal($id = 'addonLockedModal')
    {
        rtmkit_render_pro_upgrade_modal(
            $id,
            'Activate the Addon<br>to Unlock This Feature',
            'This feature comes from an RTMKit addon that is not active on this site yet. Install and activate it from the Addons page.',
            'Open Addons',
            admin_url('admin.php?page=rtmkit&path=addons')
        );
        ?>
        <script>
            // Pemicu yang membawa data-addon-name (mis. kartu modul) mengganti
            // teks umum modal dengan nama addon yang harus diaktifkan.
            (function () {
                var modal = document.getElementById(<?php echo wp_json_encode($id); ?>);
                if (!modal) return;
                var heading = modal.querySelector('h3');
                var desc = modal.querySelector('h3 + p');
                var defaults = { heading: heading.innerHTML, desc: desc.textContent };

                modal.addEventListener('show.bs.modal', function (e) {
                    var trigger = e.relatedTarget;
                    var addon = trigger && trigger.dataset ? trigger.dataset.addonName : '';
                    if (!addon) {
                        heading.innerHTML = defaults.heading;
                        desc.textContent = defaults.desc;
                        return;
                    }
                    var feature = trigger.dataset.featureName || 'This feature';
                    heading.textContent = 'Activate ' + addon + ' to Unlock ' + feature;
                    desc.textContent = feature + ' is part of the ' + addon + ' addon. Open RTMKit → Addons, then install & activate ' + addon + ' to use it.';
                });
            })();
        </script>
        <?php
    }
}
