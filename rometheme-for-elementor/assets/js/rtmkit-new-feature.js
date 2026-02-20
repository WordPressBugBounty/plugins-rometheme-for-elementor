document.addEventListener("DOMContentLoaded", function () {
  const trigger = document.querySelector("#wp-admin-bar-rtmkit_whats_new_bar");
  const drawer = document.getElementById("custom-admin-drawer");
  const overlay = document.getElementById("custom-drawer-overlay");

  if (trigger) {
    trigger.addEventListener("click", function (e) {
      e.preventDefault();
      drawer.classList.toggle("open");
      overlay.classList.toggle("active");
    });
  }

  overlay.addEventListener("click", function () {
    drawer.classList.remove("open");
    overlay.classList.remove("active");
  });
});
