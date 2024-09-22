jQuery(window).on('elementor:init', () => {
    var e = window.elementor;
    e.on('preview:loaded', function () {
        var intval = setInterval(function () {
            var panel = e.panel.$el.find('#elementor-panel-category-romethemekit_widgets_pro');
            if (panel.length) {
                if (rtmpro.is_pro === 'false') {
                    var panelItems = panel.find('.elementor-panel-category-items');
                    panel.show();
                    jQuery.each(rtmpro.widgets, function (index, val) {
                        if (val['status'] == true) {
                            var elWrapper = jQuery('<div class="elementor-element-wrapper elementor-element--promotion"></div>');
                            var ele = jQuery('<div class="elementor-element"></div>');
                            var lockIcon = jQuery('<i class="eicon-lock"></i>');
                            var icon = jQuery('<div class="icon"><i class="rkit-widget-icon ' + val['icon'] + '" aria-hidden="true"></i></div>');
                            var title = jQuery('<div class="title-wrapper"><div class="title">' + val['name'] + '</div></div>');
                            ele.append(lockIcon);
                            ele.append(icon);
                            ele.append(title);
                            elWrapper.append(ele);
                            panelItems.append(elWrapper);
                        }
                    });
                }
                clearInterval(intval);
            }
        }, 100);
    });
});