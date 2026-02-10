<?php

get_header();

if (is_single()) :
?>
    <article id="content" <?php post_class('site-main'); ?>>
        <?php
        do_action('rmtkit/render_single_template');
        the_content();
        ?>
    </article>

<?php
endif;

get_footer();
?>