jQuery(window).on('elementor/frontend/init', function () {
  elementorFrontend.hooks.addAction('frontend/element_ready/rkit-woo_product_carousel.default', function ($scope, $) {
    const slider = $scope.find('.swiper');
    if (!slider.length) return;

    const config = slider.data('config') || {
      rtl: false,
      arrows: true,
      dots: false,
      speed: 1000,
      slidesPerGroup: 1,
      slidesPerView: 3,
      loop: false,
      watchOverflow: true,
      breakpoints: {
        320: { slidesPerView: 1, slidesPerGroup: 1, spaceBetween: 10 },
        768: { slidesPerView: 2, slidesPerGroup: 1, spaceBetween: 10 },
        1025: { slidesPerView: 3, slidesPerGroup: 1, spaceBetween: 15 },
      }
    };
    const nextButton = $scope.find('.rkit-swiper-button-next-woo-product-carousel')[0];
    const prevButton = $scope.find('.rkit-swiper-button-prev-woo-product-carousel')[0];
    const paginationEl = $scope.find('.rkit-woo-product-carousel-pagination')[0];

    console.log('slider:', slider);
    console.log('config:', config);
    console.log('nextButton:', nextButton);
    console.log('prevButton:', prevButton);
    console.log('paginationEl:', paginationEl);

    const swiper = new Swiper(slider[0], {
      ...config,
      grabCursor: true,
      initialSlide: config.initial_slide || 0,
      watchOverflow: true,
      updateOnWindowResize: true,
      navigation: {
        nextEl: nextButton,
        prevEl: prevButton,
      },
      pagination: {
        el: paginationEl,
        type: 'bullets',
        clickable: true,
        bulletClass: 'rkit-woo-carousel-bullet',
        bulletActiveClass: 'rkit-woo-carousel-bullet-active',
        clickableClass: 'rkit-woo-carousel-bullet-clickable',
      },
      on: {
        init: function () {
          // Hapus kelas jika swiper bisa di-scroll
          const swiperNav = $scope.find('.swiper')[0];
          if (this.isBeginning && this.isEnd) {
            swiperNav.classList.add('swiper-navigation-disabled');
          } else {
            swiperNav.classList.remove('swiper-navigation-disabled');
          }
        },
      }
    });

        
    console.log('swiper:', swiper);

    // Jika dots false, sembunyikan pagination secara manual
    if (!config.dots && paginationEl) {
      paginationEl.style.margin = '0';
      paginationEl.style.padding = '0';
      paginationEl.style.position = 'absolute';
      paginationEl.style.opacity = '0';
      paginationEl.style.pointerEvents = 'none';
      paginationEl.style.height = '0';
      paginationEl.style.overflow = 'hidden';
    }


    
  });
});
