const upgradeToProLink = document.querySelector(
  '.toplevel_page_rtmkit > ul.wp-submenu a[href="admin.php?page=rtmkit-upgrade-to-pro"]'
);

if (upgradeToProLink) {
  upgradeToProLink.onclick = (e) => {
    e.preventDefault();
    window.open(rtmkitPanel.upgrade_url, "_blank");
  };
}
