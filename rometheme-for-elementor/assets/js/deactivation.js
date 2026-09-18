/**
 * Quick feedback saat RTMKit dinonaktifkan.
 *
 * Aturan yang memandu seluruh berkas ini: apa pun yang terjadi, pengguna harus
 * sampai ke tautan Deactivate-nya. Setiap jalan keluar dari modal ini berakhir
 * di leave(), termasuk jalur gagal dan sebuah penjaga waktu — supaya permintaan
 * yang menggantung tidak pernah menyandera orang yang cuma ingin mematikan
 * plugin.
 *
 * Pemasangannya ditunda sampai DOM selesai dibaca. wp-admin mencetak skrip
 * footer (admin_print_footer_scripts) lebih dulu daripada isi footer per
 * halaman (admin_footer-plugins.php), jadi saat berkas ini dijalankan modalnya
 * memang belum ada.
 */
(function () {
  "use strict";

  if (typeof window.rtmkitDeactivation === "undefined") {
    return;
  }

  var config = window.rtmkitDeactivation;

  /** Slug plugin dari href, dibandingkan setelah di-decode. */
  function pluginOf(href) {
    var match = /[?&]plugin=([^&]+)/.exec(href || "");

    if (!match) {
      return "";
    }

    try {
      return decodeURIComponent(match[1].replace(/\+/g, " "));
    } catch (e) {
      return "";
    }
  }

  function isOurDeactivateLink(link) {
    var href = link.getAttribute("href") || "";

    // Teks tautannya ikut bahasa situs, jadi yang dicocokkan aksi dan slug-nya.
    return href.indexOf("action=deactivate") !== -1 && pluginOf(href) === config.slug;
  }

  function boot() {
    var modal = document.querySelector("[data-rtmkit-deact]");

    if (!modal) {
      return;
    }

    var detail = modal.querySelector("[data-rtmkit-deact-detail]");
    var comment = modal.querySelector("[data-rtmkit-deact-comment]");
    var submit = modal.querySelector("[data-rtmkit-deact-submit]");

    var deactivateUrl = "";
    var lastFocus = null;
    var leaving = false;

    function open() {
      lastFocus = document.activeElement;
      modal.hidden = false;
      document.body.classList.add("rtmkit-deact-locked");

      var first = modal.querySelector("[data-rtmkit-deact-reason]");

      if (first) {
        first.focus();
      }
    }

    function close() {
      modal.hidden = true;
      document.body.classList.remove("rtmkit-deact-locked");

      if (lastFocus && lastFocus.focus) {
        lastFocus.focus();
      }
    }

    /** Lanjutkan ke tautan Deactivate. Hanya boleh terjadi sekali. */
    function leave() {
      if (leaving || !deactivateUrl) {
        return;
      }

      leaving = true;
      window.location.href = deactivateUrl;
    }

    document.addEventListener("click", function (event) {
      var link = event.target.closest("a");

      if (!link || !isOurDeactivateLink(link)) {
        return;
      }

      event.preventDefault();
      deactivateUrl = link.href;
      open();
    });

    // Pilihan alasan membuka kolom penjelasan, kalau alasan itu memang perlu
    // dijelaskan, dan membuka tombol kirim.
    modal.addEventListener("change", function (event) {
      var radio = event.target.closest("[data-rtmkit-deact-reason]");

      if (!radio) {
        return;
      }

      var placeholder = radio.getAttribute("data-placeholder");

      if (detail && comment) {
        detail.hidden = !placeholder;
        comment.placeholder = placeholder || "";

        if (placeholder) {
          comment.focus();
        } else {
          comment.value = "";
        }
      }

      if (submit) {
        submit.disabled = false;
      }
    });

    modal.addEventListener("click", function (event) {
      if (event.target.closest("[data-rtmkit-deact-cancel]")) {
        event.preventDefault();
        close();

        return;
      }

      if (event.target.closest("[data-rtmkit-deact-skip]")) {
        event.preventDefault();
        leave();

        return;
      }

      if (!event.target.closest("[data-rtmkit-deact-submit]")) {
        return;
      }

      event.preventDefault();

      var checked = modal.querySelector("[data-rtmkit-deact-reason]:checked");

      if (!checked) {
        leave();

        return;
      }

      Array.prototype.forEach.call(modal.querySelectorAll("button"), function (el) {
        el.disabled = true;
      });

      modal.classList.add("is-busy");

      // Penjaga terakhir: kalau permintaannya menggantung, tetap lanjut.
      window.setTimeout(leave, 8000);

      fetch(config.ajax_url, {
        method: "POST",
        credentials: "same-origin",
        body: new URLSearchParams({
          action: config.action,
          nonce: config.nonce,
          reason: checked.value,
          comment: comment ? comment.value : "",
        }),
      })
        .then(leave)
        .catch(leave);
    });

    document.addEventListener("keydown", function (event) {
      if ("Escape" === event.key && !modal.hidden) {
        close();
      }
    });
  }

  if ("loading" === document.readyState) {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
