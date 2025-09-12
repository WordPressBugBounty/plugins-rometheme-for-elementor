window.addEventListener("elementor/frontend/init", function () {
  elementorFrontend.hooks.addAction(
    "frontend/element_ready/global",
    function ($scope) {
      let stickyTarget = jQuery(".rtmkit-sticky-header-enabled");
      let stickyOnScroll = jQuery(".rtmkit-sticky-on-scroll-enabled");

      // console.log(stickyTarget);

      if (stickyTarget.length) {
        let offset =
          parseInt(
            getComputedStyle(stickyTarget[0]).getPropertyValue(
              "--sticky-header-offset"
            ),
            10
          ) || 0;

        let sticky = new Sticky(".rtmkit-sticky-header-enabled", {
          wrap: true,
          marginTop: offset,
          stickyClass: "rtmkit-sticky-active",
          wrapWith: '<div class="rtmkit-sticky-sentinel"></div>',
        });

        let lastScrollTop = 0;

        jQuery(window).on("scroll", function () {
          let currentScroll = jQuery(this).scrollTop();
          if (currentScroll > lastScrollTop) {
            // Scrolling down
            stickyOnScroll
              .stop(true, true)
              .css("pointer-events", "none")
              .addClass("rtmkit-sticky-on-scroll-hidden")
              .removeClass("rtmkit-sticky-on-scroll-shown");
          } else {
            // Scrolling up
            stickyOnScroll
              .stop(true, true)
              .css("pointer-events", "auto")
              .removeClass("rtmkit-sticky-on-scroll-hidden")
              .addClass("rtmkit-sticky-on-scroll-shown");
          }
          lastScrollTop = currentScroll <= 0 ? 0 : currentScroll; // Anti negatif
        });
      }
    }
  );
});