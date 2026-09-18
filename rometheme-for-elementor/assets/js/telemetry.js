/**
 * Izin pengiriman data pemakaian RTMKit.
 *
 * Halaman admin RTMKit adalah satu halaman yang isinya diganti lewat AJAX, jadi
 * notice dan toggle-nya belum ada di DOM saat skrip ini jalan. Karena itu semua
 * penanganannya lewat delegasi di document, bukan listener per elemen.
 */
(function () {
  "use strict";

  if (typeof window.rtmkitTelemetry === "undefined") {
    return;
  }

  var config = window.rtmkitTelemetry;

  function send(action, params) {
    var body = new URLSearchParams(
      Object.assign({ action: action, nonce: config.nonce }, params || {}),
    );

    return fetch(config.ajax_url, {
      method: "POST",
      credentials: "same-origin",
      body: body,
    }).then(function (response) {
      return response.json();
    });
  }

  function feedback(scope, message) {
    var target = scope && scope.querySelector(".rtmkit-telemetry-feedback");

    if (target) {
      target.textContent = message || "";
    }
  }

  /**
   * Kartu izin bisa muncul di halaman yang sudah penuh kartu lain, jadi
   * pesannya ditaruh di dalam penanda scope-nya sendiri, bukan di .card
   * terdekat yang kebetulan membungkusnya.
   */
  function scopeOf(el) {
    return el.closest("[data-rtmkit-telemetry-scope]") || el.closest(".card");
  }

  /**
   * Tombol silang bawaan WordPress hanya menyembunyikan notice-nya di layar
   * ini. Penutupannya ikut disimpan supaya tidak muncul lagi di setiap halaman
   * wp-admin. Gagal menyimpan bukan masalah: notice-nya tinggal muncul lagi.
   */
  document.addEventListener("click", function (event) {
    if (!event.target.closest(".notice-dismiss")) {
      return;
    }

    if (!event.target.closest("[data-rtmkit-telemetry-admin-notice]")) {
      return;
    }

    send("rtmkit_telemetry_dismiss_admin_notice").catch(function () {});
  });

  document.addEventListener("click", function (event) {
    var button = event.target.closest("[data-rtmkit-telemetry-consent]");

    if (!button) {
      return;
    }

    event.preventDefault();

    var notice = button.closest("[data-rtmkit-telemetry-notice]");
    var scope = scopeOf(button);
    var choice = button.getAttribute("data-rtmkit-telemetry-consent");
    var buttons = notice
      ? notice.querySelectorAll("[data-rtmkit-telemetry-consent]")
      : [button];

    function enable(state) {
      Array.prototype.forEach.call(buttons, function (el) {
        el.disabled = !state;
      });
    }

    enable(false);
    feedback(scope, "Saving…");

    send("rtmkit_telemetry_consent", { consent: choice })
      .then(function (response) {
        if (!response || !response.success) {
          throw new Error("consent-failed");
        }

        // Tidak perlu menyimpan "sudah ditutup": izin yang sudah dijawab
        // membuat kedua permukaan berhenti dirender dengan sendirinya. Yang
        // tersisa cuma membereskan yang sedang tampil di layar ini.
        if (notice) {
          notice.remove();
          return;
        }

        feedback(scope, response.data && response.data.message);
      })
      .catch(function () {
        enable(true);
        feedback(scope, "Could not save your choice. Please try again.");
      });
  });

  document.addEventListener("change", function (event) {
    var toggle = event.target.closest("[data-rtmkit-telemetry-toggle]");

    if (!toggle) {
      return;
    }

    var scope = scopeOf(toggle);
    var wanted = toggle.checked;

    toggle.disabled = true;
    feedback(scope, "Saving…");

    send("rtmkit_telemetry_consent", { consent: wanted ? "1" : "0" })
      .then(function (response) {
        if (!response || !response.success) {
          throw new Error("consent-failed");
        }

        feedback(scope, response.data && response.data.message);
      })
      .catch(function () {
        toggle.checked = !wanted;
        feedback(scope, "Could not save your choice. Please try again.");
      })
      .finally(function () {
        toggle.disabled = false;
      });
  });
})();
