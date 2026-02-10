<div class="px-4 mb-5 scroll-behavior-smooth scrollspy" data-scrollspy="#system-category" data-rootMargin="-30% 0px -70% 0px" tabindex="0">
    <div class="d-flex flex-column gap-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column">
                        <h1>Support & Get Help</h1>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    License :
                    <span class="license-status">
                        <?php
                        if (class_exists('RTMKitPro\Core\Plugin') && \RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive()) {
                            echo \RTMKitPro\Modules\Licenses\LicenseStorage::instance()->get_product_name();
                        } else {
                            echo 'Free';
                        }
                        ?>
                    </span>
                </div>
                <p class="m-0">Explore help articles or contact us directly for faster support.</p>
            </div>
        </div>
        <div class="divider"></div>
        <div class="row row-cols-lg-3 row-cols-md-2 row-cols-1">
            <div class="col">
                <div class="card">
                    <h5 class="fw-semibold">Social Media</h5>
                    <ul class="list d-flex flex-column gap-2 mt-2 mb-0">
                        <li class="list-item border-bottom">
                            <a href="https://www.instagram.com/rometheme_studio/" target="_blank" class="d-flex w-100 justify-content-between btn btn-link border-0 rounded-0 px-0 py-2">
                                Instagram Rometheme Studio
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                        </li>
                        <li class="list-item border-bottom">
                            <a href="https://www.instagram.com/rtmkit/" target="_blank" class="d-flex w-100 justify-content-between btn btn-link border-0 rounded-0 px-0 py-2">
                                Instagram RTMkit
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                        </li>
                        <li class="list-item border-bottom">
                            <a href="https://www.tiktok.com/@rometheme_studio" target="_blank" class="d-flex w-100 justify-content-between btn btn-link border-0 rounded-0 px-0 py-2">
                                Tiktok Rometheme
                                <i class="fa-brands fa-tiktok"></i>
                            </a>
                        </li>
                        <li class="list-item border-bottom">
                            <a href="https://www.youtube.com/@Rometheme_Studio" target="_blank" class="d-flex w-100 justify-content-between btn btn-link border-0 rounded-0 px-0 py-2">
                                Youtube Rometheme
                                <i class="fa-brands fa-youtube"></i>
                            </a>
                        </li>
                        <li class="list-item border-bottom">
                            <a href="https://www.behance.net/romethemestudio" target="_blank" class="d-flex w-100 justify-content-between btn btn-link border-0 rounded-0 px-0 py-2">
                                Behance Rometheme Studio
                                <i class="fa-brands fa-behance"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col">
                <div class="card h-100">
                    <h5 class="fw-semibold">Knowledge Base</h5>
                    <div class="d-flex flex-column justify-content-between h-100">
                        <span>Explore our documentation to help guided about our product and installation.</span>
                        <a href="https://support.rometheme.net/docs/romethemekit/" class="btn btn-secondary px-4 py-3">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 7C12 5.93913 11.5786 4.92172 10.8284 4.17157C10.0783 3.42143 9.06087 3 8 3H2V18H9C9.79565 18 10.5587 18.3161 11.1213 18.8787C11.6839 19.4413 12 20.2044 12 21M12 7V21M12 7C12 5.93913 12.4214 4.92172 13.1716 4.17157C13.9217 3.42143 14.9391 3 16 3H22V18H15C14.2044 18 13.4413 18.3161 12.8787 18.8787C12.3161 19.4413 12 20.2044 12 21" stroke="#B0B0B0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Product Documentation
                        </a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100">
                    <h5 class="fw-semibold">Support</h5>
                    <div class="d-flex flex-column justify-content-between h-100">
                        <span>Get in touch or visit our community to get guided about wordpress, elementor and RTMkit Plugin</span>
                        <div class="d-flex flex-column gap-3">
                            <a href="https://rometheme.net/support" target="_blank" class="social-container d-flex flex-row gap-3 align-items-center">
                                <div class="social-icon">
                                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_1696_10883)">
                                            <path d="M26.2475 17.7266C26.641 16.4925 26.8547 15.1797 26.8547 13.817C26.8547 12.4543 26.6405 11.1411 26.2475 9.90743C26.2415 9.89066 26.2376 9.87346 26.2303 9.85712C24.555 4.67605 19.6866 0.916992 13.9547 0.916992C8.22279 0.916992 3.35476 4.67562 1.67905 9.85669C1.67217 9.87303 1.66787 9.89023 1.66185 9.907C1.2684 11.1411 1.05469 12.4539 1.05469 13.8166C1.05469 15.1792 1.26883 16.4925 1.66185 17.7261C1.66787 17.7429 1.67174 17.7601 1.67905 17.7764C3.35433 22.9575 8.22279 26.7166 13.9547 26.7166C19.6866 26.7166 24.5546 22.9579 26.2303 17.7764C26.2372 17.7601 26.2415 17.7433 26.2475 17.7266ZM24.1951 9.33768H19.6823C19.2691 6.81315 18.5075 4.69841 17.5069 3.21878C20.5027 4.22541 22.9348 6.46743 24.1951 9.33768ZM3.71424 18.2959H8.22709C8.64032 20.8204 9.40185 22.9351 10.4025 24.4148C7.40665 23.4081 4.97457 21.1661 3.71424 18.2959ZM17.4957 24.4186C18.1347 23.4658 18.6847 22.2381 19.1147 20.7602C19.2476 20.304 18.9853 19.8271 18.5295 19.6942C18.0728 19.5605 17.5959 19.8241 17.4635 20.2799C16.6297 23.1454 15.2524 24.997 13.9551 24.997C12.4037 24.997 10.7194 22.3938 9.98149 18.2963H15.8927C16.3674 18.2963 16.7527 17.911 16.7527 17.4363C16.7527 16.9616 16.3674 16.5763 15.8927 16.5763H9.73983C9.6491 15.7043 9.59836 14.7832 9.59836 13.817C9.59836 13.6545 9.60008 13.4928 9.60309 13.3315C9.61169 12.8568 9.23329 12.4651 8.75814 12.4565C8.75298 12.4565 8.74825 12.4565 8.74266 12.4565C8.27482 12.4565 7.89169 12.8314 7.88309 13.3014C7.88008 13.4726 7.87836 13.6446 7.87836 13.817C7.87836 14.7699 7.92308 15.6918 8.00693 16.5763H3.11998C2.89509 15.6935 2.77469 14.769 2.77469 13.817C2.77469 12.865 2.89509 11.9409 3.11998 11.0577H5.96787C6.44259 11.0577 6.82787 10.6724 6.82787 10.1977C6.82787 9.72296 6.44259 9.33768 5.96787 9.33768H3.71424C4.97629 6.46356 7.4131 4.21982 10.4141 3.21491C9.77423 4.16951 9.22297 5.40017 8.79254 6.88152C8.6601 7.33775 8.92283 7.81462 9.37863 7.94706C9.83529 8.0795 10.3122 7.8172 10.4446 7.36097C11.2779 4.49115 12.6561 2.63656 13.9547 2.63656C15.5061 2.63656 17.1904 5.23978 17.9283 9.33725H12.0494C11.5746 9.33725 11.1894 9.72253 11.1894 10.1973C11.1894 10.672 11.5746 11.0573 12.0494 11.0573H18.17C18.2607 11.9293 18.3114 12.8504 18.3114 13.8166C18.3114 13.9765 18.3097 14.1361 18.3067 14.2943C18.2981 14.769 18.6761 15.1612 19.1512 15.1698C19.1568 15.1698 19.1616 15.1698 19.1671 15.1698C19.635 15.1698 20.0181 14.7948 20.0267 14.3253C20.0297 14.1563 20.0314 13.9868 20.0314 13.8161C20.0314 12.8633 19.9867 11.9413 19.9029 11.0568H24.7894C25.0143 11.9396 25.1347 12.8641 25.1347 13.8161C25.1347 14.7682 25.0143 15.6922 24.7894 16.5754H21.9738C21.499 16.5754 21.1138 16.9607 21.1138 17.4354C21.1138 17.9102 21.499 18.2954 21.9738 18.2954H24.1951C22.9331 21.1696 20.4963 23.4133 17.4953 24.4182L17.4957 24.4186Z" fill="currentColor" />
                                            <path d="M9.00886 8.40088C8.03577 8.40088 7.24414 9.20498 7.24414 10.1931C7.24414 11.1813 8.03577 11.9854 9.00886 11.9854C9.98195 11.9854 10.7732 11.1813 10.7732 10.1931C10.7732 9.20498 9.98152 8.40088 9.00886 8.40088ZM8.96457 10.1931C8.96457 10.1454 8.99338 10.1209 9.00929 10.1209C9.02434 10.1209 9.05358 10.1467 9.05358 10.1931C9.05358 10.2869 8.965 10.2869 8.965 10.1931H8.96457Z" fill="#B0B0B0" />
                                            <path d="M19.1855 16.5757H18.7916C18.3169 16.5757 17.9316 16.961 17.9316 17.4357C17.9316 17.9104 18.3169 18.2957 18.7916 18.2957H19.1855C19.6602 18.2957 20.0455 17.9104 20.0455 17.4357C20.0455 16.961 19.6602 16.5757 19.1855 16.5757Z" fill="#B0B0B0" />
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_1696_10883">
                                                <rect width="27.52" height="27.52" fill="currentColor" />
                                            </clipPath>
                                        </defs>
                                    </svg>

                                </div>
                                <div class="d-flex flex-column">
                                    <h5 class="m-0">Submit Support Form</h5>
                                    <span>rometheme.net/support</span>
                                </div>
                            </a>
                            <a href="mailto:cs.rometheme@gmail.com" class="social-container d-flex flex-row gap-3 align-items-center">
                                <div class="social-icon">
                                    <i class="rtmicon rtmicon-envelope"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <h5 class="m-0">Mail</h5>
                                    <span>cs.rometheme@gmail.com</span>
                                </div>
                            </a>
                            <a href="https://www.facebook.com/groups/rometheme" target="_blank" class="social-container d-flex flex-row gap-3 align-items-center">
                                <div class="social-icon">
                                    <i class="rtmicon rtmicon-facebook"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <h5 class="m-0">Facebook Community</h5>
                                    <span>/groups/rometheme</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>