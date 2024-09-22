jQuery(window).on('elementor:init', () => {
    var e = window.elementor;
    e.on('preview:loaded', function () {
        var interval = setInterval(function () {
            var el = e.$previewContents.find('.elementor-add-template-button');
            if (el.length) {
                var content = jQuery('<div class="rkit-add-template" ><img src="' + rkitLO.btnIcon + '"></div>');
                el.after(content),
                    e.$previewContents.on('click', '.elementor-editor-section-settings .elementor-editor-element-add', function () {
                        var el = jQuery(this).parents(".elementor-top-section").prev(".elementor-add-section").find(".elementor-add-template-button");
                        el.siblings(".rkit-add-template").length || el.after(content.clone());
                    }),
                    e.$previewContents.on('click', '.elementor-editor-container-settings .elementor-editor-element-add', function () {
                        var el = jQuery(this).parents(".e-con").prev(".elementor-add-section").find(".elementor-add-template-button");
                        el.siblings(".rkit-add-template").length || el.after(content.clone());
                    });
                clearInterval(interval);
                e.$previewContents.on('click', '.rkit-add-template', showModal);
            }
        }, 100);
    });

    function showModal() {
        
        var ei = jQuery(this).parents(".elementor-add-section-inline"),
            t = ei.next(".elementor-top-section , .e-con").data("model-cid"),
            a = {};
        ei.length &&
            (ei.find(".elementor-add-section-close").trigger("click"),
                jQuery.each(window.elementor.elements.models, function (c, r) {
                    r.cid === t && (a = { at: c });
                })),
            (window.rkitLO.modelOps = a);


        var modalOverlay = jQuery('<div class="dialog-widget dialog-lightbox-widget dialog-type-buttons dialog-type-lightbox elementor-templates-modal"></div>');
        // Add modal overlay
        jQuery('body').append(modalOverlay);

        // Add modal container
        var modalContainer = jQuery('<div class="dialog-widget-content dialog-lightbox-widget-content rkit-modal-dialog"></div>');
        modalOverlay.append(modalContainer);

        // Add modal content
        var modalContent = jQuery('<div class="dialog-message dialog-lightbox-message"></div>');

        var elmodalHeader = jQuery('<div class="dialog-header dialog-lightbox-header"></div>');
        var modalHeader = jQuery('<div class="elementor-templates-modal__header rkit-modal-header"></div>');
        modalHeader.append('<div class="elementor-templates-modal__header__logo-area rkit-logo-container"><img src="'+rkit_libs.logo_url+'" height="25"></img></div>')
        modalHeader.append('<div class="elementor-templates-modal__header__items-area"><div class="elementor-templates-modal__header__close elementor-templates-modal__header__close--normal elementor-templates-modal__header__item"><button class="rkit-close-modal"><i class="eicon-close"></i></button></div></div>');
        elmodalHeader.append(modalHeader);

        modalContainer.append(elmodalHeader);
        modalContainer.append(modalContent);

        var loading = jQuery('<div id="rkit-loading" class="rkit-loading"><div class="spinner"></div></div>');

        var modal_body = jQuery('<div class="dialog-content dialog-lightbox-content"></div>');
        var rkit_content = jQuery('<div class="rkit-template-library"></div>');
        var rkit_template_library = jQuery('<div class="rkit-template-container"></div>');

        var r_f = jQuery('<div style="width:100%; padding:1rem ; padding-block: 6rem ; text-align:center ; font-size:medium;"><h4 style="font-size:x-large">Stay tuned!</h4>More incredible templates are on the way.</div>')

        jQuery('.rkit-modal').css({ opacity: 0 }).show();
        jQuery('.rkit-modal').animate({ opacity: 1 }, 200)

        jQuery.ajax({
            url: rkitLO.api_url + '/wp-json/public/get_layout_api/',
            type: 'GET',
            dataType: 'json',
            beforeSend: function () {
                modalContent.append(loading);
            },
            success: function (data) {

                var banner_img = jQuery('<div class="rkit-template-library-banner"><img src="' + data.banner + '"></div>');
                var tabs = rkitLO.default_tab;
                var type = data.type;

                var logo_area = jQuery('.elementor-templates-modal__header__logo-area');

                var div = jQuery('<div class="elementor-templates-modal__header__menu-area"></div>');
                var e = jQuery('<div id="elementor-template-library-header-menu"></div>')
                for (let key in type) {
                    if (key == tabs) {
                        var header_item = jQuery('<div class="elementor-component-tab elementor-template-library-menu-item elementor-active" data-tab="' + key + '">' + key.toUpperCase() + '</div>');
                    } else {
                        var header_item = jQuery('<div class="elementor-component-tab elementor-template-library-menu-item" data-tab="' + key + '">' + key.toUpperCase() + '</div>');
                    }

                    header_item.click((event) => {
                        if (!jQuery(event.currentTarget).hasClass('elementor-active')) {
                            jQuery('#elementor-template-library-header-menu .elementor-template-library-menu-item').not(event.currentTarget).removeClass('elementor-active');
                            jQuery(event.currentTarget).addClass('elementor-active');
                        }
                        rkit_template_library.empty();
                        render_body(data);
                    });

                    e.append(header_item);
                }
                div.append(e);
                logo_area.after(div);
                render_body(data);
                rkit_content.append(banner_img);
                rkit_content.append(rkit_template_library);
                rkit_content.append(r_f);
                modal_body.append(rkit_content);
                modalContent.append(modal_body);
            },
            error: function (xhr, status, error) {
                // menangani kesalahan
                console.log(status);
            },
            complete: function () {
                jQuery('#rkit-loading').remove();
            },
        });


        function render_body(data) {
            var tab_active = jQuery('#elementor-template-library-header-menu .elementor-active').data('tab');
            let template = Object.values(data.template).filter(template => template.template_type == tab_active);
            var r_h = jQuery('<div class="r-header-' + tab_active + '"></div>');
            var r_t_l = jQuery('<div class="r-template-list"></div>');
            var list_cat = data.type[tab_active].list_category;

            if (tab_active == 'page') {
                var search = jQuery('<input class="r-search" type="text" placeholder="Search">');
                var slc = jQuery('<div class="r-select-container"></div>');
                var lcat = jQuery('<div class="r-list-page-category"></div>')
                var select = jQuery('<input type="text" class="r-select-cat" data-value="all" value="All" readonly>');
                var all = jQuery('<div class="r-page-category" data-value="all">All</div>');
                all.click((event) => {
                    r_t_l.empty();
                    render_list(template);
                    select.val('All')
                    select.data('value', 'all')
                });
                lcat.append(all)
                for (let key in list_cat) {
                    var value = list_cat[key];
                    var capitalize = value.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
                    var rl = jQuery('<div class="r-page-category" data-value="' + value + '">' + capitalize + '</div>');
                    rl.click((event) => {
                        var value = jQuery(event.currentTarget).data('value');
                        var capitalize = value.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
                        let template = Object.values(data.template).filter(template => template.template_type == tab_active && template.template_category == value);
                        r_t_l.empty();
                        render_list(template);
                        select.val(capitalize);
                        select.data('value', value);

                    })
                    lcat.append(rl);
                }
                search.on('input', (event) => {
                    var select = jQuery('.r-select-cat');
                    var cat_value = select.data('value');
                    var tab_active = jQuery('#elementor-template-library-header-menu .elementor-active').data('tab');
                    let template = (cat_value == 'all') ? Object.values(data.template).filter(template => template.post_title.toLowerCase().includes(jQuery(event.currentTarget).val().toLowerCase()) && template.template_type == tab_active) : Object.values(data.template).filter(template => template.post_title.toLowerCase().includes(jQuery(event.currentTarget).val().toLowerCase()) && template.template_type == tab_active && template.template_category == cat_value);
                    r_t_l.empty();
                    render_list(template);
                });
                slc.append(select);
                slc.append(lcat);
                r_h.append(slc);
                r_h.append(search);
                rkit_template_library.append(r_h);
                render_list(template);
                rkit_template_library.append(r_t_l);
            } else {
                list_cat.forEach(c => {
                    var cb = jQuery('<button data-category="' + c + '" class="r-category-btn">' + c.replace('-', ' ').toUpperCase() + '</button>');
                    cb.click((event) => {
                        r_t_l.empty();
                        var ct = jQuery(event.currentTarget).data('category');
                        var search = jQuery('<input class="r-search" type="text" placeholder="Search">');
                        search.on('input', (event) => {
                            var tab_active = jQuery('#elementor-template-library-header-menu .elementor-active').data('tab');
                            let template = Object.values(data.template).filter(template => template.post_title.toLowerCase().includes(jQuery(event.currentTarget).val().toLowerCase()) && template.template_type == tab_active && template.template_category == ct);
                            r_t_l.empty();
                            render_list(template);
                        });
                        r_h.append(search)
                        rkit_template_library.append(r_h);
                        let template = Object.values(data.template).filter(template => template.template_type == tab_active && template.template_category == ct);
                        render_list(template);
                        rkit_template_library.append(r_t_l);
                    })
                    r_t_l.append(cb);
                    // r_t_l.addClass('g-3');
                    rkit_template_library.append(r_t_l);
                });
            }

            function render_list(template) {
                for (let key in template) {
                    var post_date = new Date(template[key].post_date);
                    var now = new Date();
                    var slsh = Math.round((now - post_date) / (1000 * 60 * 60 * 24));
                    var t = jQuery('<div class="rkit-template ' + ((slsh < 30) ? "new-template-block" : "") + '"></div>');
                    var imgc = jQuery('<div class="r-img-container-' + tab_active + '"></div>');
                    var img = jQuery('<img class="' + tab_active + '-preview-img" src="' + template[key].preview_image + '">');
                    var t_f = jQuery('<div class="rkit-template-f"></div>');
                    imgc.append(img);
                    var title = jQuery('<div>' + template[key].post_title + '</div>')
                    var i = jQuery('<div style="display:flex; justify-content:center; gap:1rem ; "></div>');
                    var impor = jQuery('<button class="rkit-import-btn"  data-id-template="' + template[key].template_id + '"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/></svg>Import</button>');
                    var preview = jQuery('<button class="r-preview-btn" data-id-template="' + template[key].template_id + '"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-binoculars" viewBox="0 0 16 16"><path d="M3 2.5A1.5 1.5 0 0 1 4.5 1h1A1.5 1.5 0 0 1 7 2.5V5h2V2.5A1.5 1.5 0 0 1 10.5 1h1A1.5 1.5 0 0 1 13 2.5v2.382a.5.5 0 0 0 .276.447l.895.447A1.5 1.5 0 0 1 15 7.118V14.5a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 14.5v-3a.5.5 0 0 1 .146-.354l.854-.853V9.5a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v.793l.854.853A.5.5 0 0 1 7 11.5v3A1.5 1.5 0 0 1 5.5 16h-3A1.5 1.5 0 0 1 1 14.5V7.118a1.5 1.5 0 0 1 .83-1.342l.894-.447A.5.5 0 0 0 3 4.882V2.5zM4.5 2a.5.5 0 0 0-.5.5V3h2v-.5a.5.5 0 0 0-.5-.5h-1zM6 4H4v.882a1.5 1.5 0 0 1-.83 1.342l-.894.447A.5.5 0 0 0 2 7.118V13h4v-1.293l-.854-.853A.5.5 0 0 1 5 10.5v-1A1.5 1.5 0 0 1 6.5 8h3A1.5 1.5 0 0 1 11 9.5v1a.5.5 0 0 1-.146.354l-.854.853V13h4V7.118a.5.5 0 0 0-.276-.447l-.895-.447A1.5 1.5 0 0 1 12 4.882V4h-2v1.5a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5V4zm4-1h2v-.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5V3zm4 11h-4v.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5V14zm-8 0H2v.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5V14z"/></svg>Preview</buttton>');
                    preview.click((event) => {
                        var id = jQuery(event.currentTarget).data('id-template');
                        show_preview(template[key].preview_image, id, template[key].post_title);
                    });
                    i.append(impor);
                    i.append(preview);
                    t_f.append(title);
                    t_f.append(i);
                    t.append(imgc);
                    t.append(t_f);
                    impor.click((event) => {
                        var id = jQuery(event.currentTarget).data('id-template');
                        jQuery(event.currentTarget).html('Importing...');
                        jQuery(event.currentTarget).prop('disabled', true);
                        import_template(id);
                    })
                    r_t_l.append(t);
                }
            }
        }

        // Show modal when escape key is pressed
        jQuery(document).on('keydown', function (event) {
            if (event.keyCode == 27) {
                closeModal();
            }
        });

        // Close modal when close button is clicked
        jQuery('.rkit-close-modal').on('click', function () {
            closeModal();
        });
    }

});

function show_preview(src, id, title) {
    var preview_modal = jQuery('<div class="r-preview-modal-overlay"></div>');
    var hpv = jQuery('<div class="r-preview-header"></div>');
    var backbtn = jQuery('<button class="r-preview-back-btn"> <svg xmlns="http://www.w3.org/2000/svg" width=25" height="25" fill="currentColor" class="bi bi-arrow-bar-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M12.5 15a.5.5 0 0 1-.5-.5v-13a.5.5 0 0 1 1 0v13a.5.5 0 0 1-.5.5ZM10 8a.5.5 0 0 1-.5.5H3.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L3.707 7.5H9.5a.5.5 0 0 1 .5.5Z"/></svg> Back</button>')
    var impor = jQuery('<button class="r-preview-insert-btn"  data-id-template="' + id + '"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/></svg>Import</button>');
    var pb = jQuery('<div class="r-preview-body"></div>')
    var img = jQuery('<img class="r-preview-modal-img" src="' + src + '" >');
    var t = jQuery('<div class="r-preview-title"><h3>' + title + '</h3></div>')
    backbtn.click((event) => {
        close_preview();
    });
    impor.click((event) => {
        jQuery(event.currentTarget).html('Importing...');
        jQuery(event.currentTarget).prop('disabled', true);
        import_template(id);
    })
    hpv.append(backbtn);
    hpv.append(t);
    hpv.append(impor);
    pb.append(img);
    preview_modal.append(hpv);
    preview_modal.append(pb);
    jQuery('body').append(preview_modal);
    jQuery('.r-preview-modal-overlay').animate({ opacity: 1 }, 200, function () {
        jQuery(this).show();
    });
}

function close_preview() {
    jQuery('.r-preview-modal-overlay').animate({ opacity: 0 }, 200, function () {
        jQuery(this).remove();
    });
}

function closeModal() {
    jQuery('.elementor-templates-modal').animate({ opacity: 0 }, 200, function () {
        jQuery(this).remove();
    });
    jQuery('.rkit-modal').animate({ opacity: 0 }, 200, function () {
        jQuery(this).remove();
    });
    jQuery(document).off('keydown');
}

function import_template(id) {
    jQuery(document).ready(($) => {
        $.ajax({
            url: rkitLO.api_url + '/wp-json/public/get_layout_api/?id=' + id,
            type: 'GET',
            dataType: 'json',
            success: (response) => {
                data = JSON.parse(JSON.stringify(response));
                data.content.forEach((el) => {
                    el.id = elementorCommon.helpers.getUniqueId();
                    if (el.elements) {
                        el.elements.forEach((els) => {
                            els.id = elementorCommon.helpers.getUniqueId();
                            if (els.elements) {
                                els.elements.forEach((eell) => {
                                    eell.id = elementorCommon.helpers.getUniqueId();
                                });
                            }
                        });
                    }
                });
                var a = $e.run('document/elements/import', {
                    model: window.elementor.elementsModel,
                    data: data,
                    options: window.rkitLO.modelOps
                });
                if (a[0].view.isRendered) {
                    closeModal();
                    close_preview();
                }
            }
        });
    });
}