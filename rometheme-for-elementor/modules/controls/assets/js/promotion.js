jQuery(window).on("elementor:init", () => {
  const e = window.elementor;

  e.on("preview:loaded", () => {
    e.hooks.addAction(
      "panel/open_editor/widget",
      function (panel, model, view) {
        setTimeout(function () {
          const promoControl = panel.currentPageView.$childViewContainer.find(
            ".rkit-promotion-wrapper"
          );

          promoControl.on("click", function (event) {
            const $this = jQuery(event.currentTarget); // Gunakan currentTarget

            const $reactEl = $this.find(".e-promotion-react-wrapper");
             e.promotion.showDialog({
                    // translators: %s: Widget Title.
                    title: sprintf(
                      wp.i18n.__("%s", "rometheme-for-elementor"),
                      $reactEl.data('promotion')
                    ),
                    content: sprintf(
                      wp.i18n.__(
                        "%s"
                      ),
                      $reactEl.data('promotion-description')
                    ),
                    position: {
                      blockStart: "-10",
                    },
                    targetElement: $this,
                    actionButton: {
                      url: "https://rometheme.net/plugins/rtmkit/pricing/",
                      text: "Upgrade Now",
                      classes: ["elementor-button", "go-pro"],
                    },
                  });
          });
        }, 100);
      }
    );
  });
});
