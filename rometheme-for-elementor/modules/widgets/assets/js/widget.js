jQuery(document).ready(function ($) {
    disableBtn = $('#disable-all');
    enableBtn = $('#enable-all');
    saveBtn = $('#save-widget-options');
    resetBtn = $('#reset-btn'); 

    disableBtn.click(function (e) {
        e.preventDefault();
        form = $(this).closest('#widgets_option');
        form.find('input').prop('checked', false);
    });

    enableBtn.click(function (e) {
        e.preventDefault();
        form = $(this).closest('#widgets_option');
        form.find('input').prop('checked', true);
    });

    saveBtn.click(function (e) {
        e.preventDefault();
        btn = $(this);
        btn.html('Saving...');
        btn.prop('disabled', true);
        form = $(this).closest('#widgets_option');
        data = form.serialize();

        $.ajax({
            method: 'POST',
            url: rometheme_ajax_url.ajax_url,
            data: data,
            success: function (res) {
                btn.html('Save Changes');
                btn.prop('disabled', false);

            }
        })
    });

    resetBtn.click(function (e) {
        e.preventDefault();
        $.ajax({
            method: 'POST',
            url: rometheme_ajax_url.ajax_url,
            data: {
                action : 'reset_widgets'
            },
            success: function (res) {
                resetBtn.html('Resetting');
                resetBtn.prop('disabled', false);
                window.location.reload();
            }
        })
    });
});