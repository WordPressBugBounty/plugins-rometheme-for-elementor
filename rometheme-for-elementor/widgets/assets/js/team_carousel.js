jQuery(window).on("elementor/frontend/init", function () {
  elementorFrontend.hooks.addAction(
    "frontend/element_ready/rtm-team_carousel.default",
    function ($scope, $) {

      const slider = $scope.find('.rkit-container-team-carousel');
      var config = slider.data('config');
  
      var conf = {
        rtl: config.rtl,
        arrows: config.arrows,
        dots: config.dots,
        autoplay: (config.autoplay) ? {
          delay: 3000,
          disableOnInteraction: false,
          pauseOnMouseEnter: config.pauseOnHover
        } : false,
        loop: config.loop,
        speed: config.speed,
        slidesPerView: config.slidesPerView,
        slidesPerGroup: config.slidesPerGroup,
        breakpoints: config.breakpoints,
        grabCursor : true
      }
  
      var pagination = {
        pagination: {
          enabled: config.dots,
          el: '.rkit-team-carousel-pagination',
          type: 'bullets',
          clickable: true,
          bulletClass: 'rkit-pagination-bullet',
          bulletActiveClass: 'rkit-pagination-bullet-active',
          clickableClass: 'rkit-pagination-bullet-clickable'
        }
      }
  
      var navigation = {
        navigation: {
          enabled: config.arrows,
          nextEl: '.rkit-team-carousel-button-next',
          prevEl: '.rkit-team-carousel-button-prev'
        }
      }
  
      var dataConfig = { ...conf, ...pagination, ...navigation }
  
      let container = $scope.find('.team-carousel-card-container');
      console.log(container);
      console.log('config',config);
      
      console.log(dataConfig);
      
      
      const swiper = new Swiper(container[0], dataConfig);
    }
  );
});
