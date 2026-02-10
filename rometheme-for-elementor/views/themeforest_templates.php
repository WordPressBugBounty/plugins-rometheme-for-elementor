<div class="row row-cols-lg-3 row-cols-md-2 row-cols-1 g-3">
    <?php foreach ($datas['matches'] as $data) : ?>
        <div class="col">
            <div class="card rounded-4 shadow-sm p-3 h-100 gap-3">
                <div class="position-relative">
                    <img src="<?php echo esc_url($data['previews']['landscape_preview']['landscape_url']) ?>" class="card-img-top rounded-2" alt="<?php echo esc_attr($data['name']) ?>" loading="lazy">
                </div>
                <div class="card-body d-flex flex-column gap-3 h-100 justify-content-between p-0">
                    <h5 class="card-title"><?php echo esc_html($data['name']) ?></h5>
                    <div class="d-flex flex-row justify-content-between align-items-center">
                        <div class="d-flex flex-row gap-2">

                            <a href="<?php echo esc_url($data['url']) ?>" target="_blank" class="btn btn-secondary rounded-2 text-white btn-install-click"
                                data-template-id="<?php echo esc_attr($data['id']) ?>"
                                data-template-name="<?php echo esc_attr($data['name']) ?>"
                            >
                                <i class="fas fa-plus"></i>
                                Install
                            </a>
                            <a href="<?php echo esc_url($data['previews']['live_site']['url']) ?>" target="_blank" class="btn btn-link rounded-2 btn-preview-click"
                                data-template-id="<?php echo esc_attr($data['id']) ?>"
                                data-template-name="<?php echo esc_attr($data['name']) ?>"
                            >
                                <i class="fas fa-eye"></i>
                                Preview
                            </a>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <i class="fas fa-download"></i>
                            <span class="fs-6"><?php echo esc_html($data['number_of_sales']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
$total_pages  = ceil(intval($datas['total_hits']) / 24);
$current_page = max(1, intval($_POST['paged'] ?? 1));
$window       = 2; // jumlah halaman kiri-kanan current
?>

<div class="d-flex justify-content-center align-items-center w-100 mt-4">
    <nav aria-label="Page navigation">
        <ul class="pagination">

            <!-- Previous -->
            <li class="page-item <?= $current_page == 1 ? 'disabled' : '' ?>">
                <button class="page-link" data-paged="<?= $current_page - 1 ?>">&laquo;</button>
            </li>

            <?php
            // Page 1
            if ($current_page > ($window + 2)) {
                echo '<li class="page-item"><button class="page-link" data-paged="1">1</button></li>';
                echo '<li class="page-item disabled"><button class="page-link">...</button></li>';
            }

            // Middle pages
            for ($i = max(1, $current_page - $window); $i <= min($total_pages, $current_page + $window); $i++) {
                $active = $i == $current_page ? 'active' : '';
                echo "<li class='page-item $active'>
                        <button class='page-link' data-paged='$i'>$i</button>
                      </li>";
            }

            // Last page
            if ($current_page < ($total_pages - $window - 1)) {
                echo '<li class="page-item disabled"><button class="page-link">...</button></li>';
                echo "<li class='page-item'>
                        <button class='page-link' data-paged='$total_pages'>$total_pages</button>
                      </li>";
            }
            ?>

            <!-- Next -->
            <li class="page-item <?= $current_page == $total_pages ? 'disabled' : '' ?>">
                <button class="page-link" data-paged="<?= $current_page + 1 ?>">&raquo;</button>
            </li>

        </ul>
    </nav>
</div>