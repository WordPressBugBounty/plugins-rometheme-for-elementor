<?php

$uri = urlencode(home_url());
$baseLink = 'https://login.rometheme.net/';
$link = $baseLink . '?reference=connector&source=wordpress&domain=' . $uri;

$payload = get_option('rtm_connector_last_payload');




?>


<div class="px-4 mb-5 scroll-behavior-smooth scrollspy" data-scrollspy="#widget-category" data-rootMargin="-30% 0px -70% 0px" tabindex="0">
    <div class="d-flex flex-column gap-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex flex-column gap-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column">
                        <h1>RTMKit Account Manager </h1>
                    </div>
                </div>
                <?php if (!class_exists('RTMKitPro\Core\Plugin') || !\RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive()) : ?>
                    <p class="m-0">Upgrade premium to unlock all features <a href="https://rometheme.net/plugins/rtmkit/pricing/" target="_blank">Upgrade Now</a></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="divider"></div>
        <div class="scrollspy-content gap-3" style="margin-bottom: 9rem;">
            <div class="card rounded-4  flex-column gap-3">
                <?php if (empty($payload)) : ?>
                    <div class="d-flex flex-row justify-content-between">
                        <div class="d-flex flex-row gap-3 flex-grow-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-cloud-slash" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M3.112 5.112a3 3 0 0 0-.17.613C1.266 6.095 0 7.555 0 9.318 0 11.366 1.708 13 3.781 13H11l-1-1H3.781C2.231 12 1 10.785 1 9.318c0-1.365 1.064-2.513 2.46-2.666l.446-.05v-.447q0-.113.018-.231zm2.55-1.45-.725-.725A5.5 5.5 0 0 1 8 2c2.69 0 4.923 2 5.166 4.579C14.758 6.804 16 8.137 16 9.773a3.2 3.2 0 0 1-1.516 2.711l-.733-.733C14.498 11.378 15 10.626 15 9.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 4.825 10.328 3 8 3c-.875 0-1.678.26-2.339.661z" />
                                <path d="m13.646 14.354-12-12 .708-.708 12 12z" />
                            </svg>
                            <div class="d-flex flex-column ">
                                <h4>Not Connected to RTMHub Account</h4>
                                <span>Connect your RTMHub Account to receive update directly from your account.</span>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <a href="<?php echo esc_url($link) ?>" id="connect-account" class="btn btn-accent px-3 py-2 rounded-2 text-nowrap gap-2">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                <span class="text-black">Connect Account</span>
                            </a>
                            <span>
                                <i class="fa-solid fa-lock"></i>
                                Secure connection to RTMHub
                            </span>
                        </div>
                    </div>
                <?php else :
                    $token = explode('|', $payload['access_token'], 2)[1];
                    $connectedAccount = $payload['connected_account'];
                    $data = $payload['payload'];
                    $plan = $data['plan'];
                    $addons = $data['features'];
                ?>
                    <div class="d-flex flex-row justify-content-between">
                        <div class="d-flex flex-row gap-3 flex-grow-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#00cea6" class="bi bi-cloud-check" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M10.354 6.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 8.793l2.646-2.647a.5.5 0 0 1 .708 0" />
                                <path d="M4.406 3.342A5.53 5.53 0 0 1 8 2c2.69 0 4.923 2 5.166 4.579C14.758 6.804 16 8.137 16 9.773 16 11.569 14.502 13 12.687 13H3.781C1.708 13 0 11.366 0 9.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383m.653.757c-.757.653-1.153 1.44-1.153 2.056v.448l-.445.049C2.064 6.805 1 7.952 1 9.318 1 10.785 2.23 12 3.781 12h8.906C13.98 12 15 10.988 15 9.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 4.825 10.328 3 8 3a4.53 4.53 0 0 0-2.941 1.1z" />
                            </svg>
                            <div class="d-flex flex-column ">
                                <h4>Connected to RTMHub Account</h4>
                                <span>Your RTMKit is linked to your RTMHub Account.</span>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <button id="sync-account" class="btn btn-accent btn-update-plugin px-3 py-2 rounded-2 text-nowrap gap-2">
                                <svg class="icon" width="16" height="16" viewBox="0 0 22 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_1696_11964)">
                                        <path d="M18.6321 1.20117V6.11315L13.4512 6.1131" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M3.68262 22.7988V17.8869H8.86359" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M20.4634 9.6886C20.6631 10.4482 20.7693 11.2445 20.7693 12.0649C20.7693 17.3308 16.3954 21.5997 10.9999 21.5997C8.08203 21.5997 5.46292 20.3511 3.67285 18.3716" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M1.51966 14.3769C1.33076 13.6367 1.23047 12.8622 1.23047 12.0649C1.23047 6.79905 5.60433 2.53021 10.9998 2.53021C14.0768 2.53021 16.8217 3.91869 18.6124 6.08869" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_1696_11964">
                                            <rect width="22" height="24" fill="white"></rect>
                                        </clipPath>
                                    </defs>
                                </svg>
                                <span class="text-black">Sync Account</span>
                            </button>
                            <span>
                                <i class="fa-solid fa-lock"></i>
                                Secure connection to RTMHub
                            </span>
                        </div>
                    </div>
                    <hr class="m-0" />
                    <div class="d-flex flex-column">
                        <div class="border-bottom d-flex align-items-center pb-2 mb-3">
                            <div class="w-25">
                                <h6>Connected Account</h6>
                            </div>
                            <div class="w-75">
                                <h6><?php echo esc_html($connectedAccount) ?></h6>
                            </div>
                        </div>
                        <div class="border-bottom d-flex align-items-center pb-2 mb-3">
                            <div class="w-25">
                                <h6>Plan</h6>
                            </div>
                            <div class="w-75">
                                <h6><?php echo esc_html($plan) ?></h6>
                            </div>
                        </div>
                        <div class="border-bottom d-flex align-items-center pb-2 mb-3">
                            <div class="w-25">
                                <h6>Addons</h6>
                            </div>
                            <div class="w-75">
                                <h6><?php echo esc_html( count($addons) . ' Addons' ) ?></h6>
                            </div>
                        </div>

                        <div class="border-bottom d-flex align-items-center pb-2 mb-3">
                            <div class="w-25">
                                <h6>Connected At</h6>
                            </div>
                            <div class="w-75">
                                <h6><?php echo esc_html(date('Y-m-d H:i:s', $payload['payload']['issued_at'])) ?></h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center pb-2 mb-3 gap-2">
                            <a href="https://my.rometheme.net" target="_blank" class="btn btn-secondary rounded-2 py-2 text-nowrap text-white gap-2 h-100 ">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                    <path d="M224 248a120 120 0 1 0 0-240 120 120 0 1 0 0 240zm-29.7 56C95.8 304 16 383.8 16 482.3 16 498.7 29.3 512 45.7 512l356.6 0c16.4 0 29.7-13.3 29.7-29.7 0-98.5-79.8-178.3-178.3-178.3l-59.4 0z" />
                                </svg>
                                My Account
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="d-flex gap-2 align-items-center border p-3 rounded-3">
                    <svg width="24" height="24" viewBox="0 0 24 24" class="accent-color" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2.01709C6.477 2.01709 2 6.49409 2 12.0171C2 17.5401 6.477 22.0171 12 22.0171C17.523 22.0171 22 17.5401 22 12.0171C22 6.49409 17.523 2.01709 12 2.01709ZM17.646 17.6631C16.138 19.1711 14.133 20.0021 12 20.0021C9.867 20.0021 7.862 19.1711 6.354 17.6631C4.846 16.1551 4.015 14.1501 4.015 12.0171C4.015 9.88409 4.846 7.87909 6.354 6.37109C7.862 4.86309 9.867 4.03209 12 4.03209C14.133 4.03209 16.138 4.86309 17.646 6.37109C19.154 7.87909 19.985 9.88409 19.985 12.0171C19.985 14.1501 19.154 16.1551 17.646 17.6631Z" fill="currentColor" />
                        <path d="M12.4601 7.02295H11.5391C11.0671 7.02295 10.6931 7.41995 10.7201 7.89095L11.0491 13.5639C11.0741 13.9979 11.4331 14.3369 11.8681 14.3369H12.1301C12.5651 14.3369 12.9241 13.9979 12.9491 13.5639L13.2781 7.89095C13.3051 7.41995 12.9311 7.02295 12.4591 7.02295H12.4601Z" fill="currentColor" />
                        <path d="M11.9999 16.996C12.5506 16.996 12.9969 16.5496 12.9969 15.999C12.9969 15.4483 12.5506 15.002 11.9999 15.002C11.4493 15.002 11.0029 15.4483 11.0029 15.999C11.0029 16.5496 11.4493 16.996 11.9999 16.996Z" fill="currentColor" />
                    </svg>
                    <span class="lh-1">
                        Need Help ? Visit our <a href="">Our Documentation</a> or contact our <a href="">Support</a>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
</div>