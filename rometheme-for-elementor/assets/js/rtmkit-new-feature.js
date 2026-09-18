(function () {
  "use strict";

  var STORAGE_KEY = "rtmkit_drawer_feed";
  var CACHE_TTL = 6 * 60 * 60 * 1000; // 6 jam, sama dengan transient di server

  var trigger = document.querySelector("#wp-admin-bar-rtmkit_whats_new_bar");
  var drawer = document.getElementById("custom-admin-drawer");
  var overlay = document.getElementById("custom-drawer-overlay");
  var drawerContent = document.querySelector("#custom-admin-drawer .drawer-content");

  var drawerLoaded = false;
  var isLoading = false;

  var loadingHtml =
    '<div class="rtmkit-drawer-loading">' +
    '<div class="rtmkit-skeleton-item">' +
    '<div class="rtmkit-skeleton-title"></div>' +
    '<div class="rtmkit-skeleton-image"></div>' +
    '<div class="rtmkit-skeleton-text"></div>' +
    '<div class="rtmkit-skeleton-text short"></div>' +
    "</div>" +
    '<div class="rtmkit-skeleton-item">' +
    '<div class="rtmkit-skeleton-title"></div>' +
    '<div class="rtmkit-skeleton-image"></div>' +
    '<div class="rtmkit-skeleton-text"></div>' +
    '<div class="rtmkit-skeleton-text short"></div>' +
    "</div>" +
    "</div>";

  var errorHtml =
    '<div class="rtmkit-drawer-error">' +
    "<p>Unable to load content. Please try again.</p>" +
    '<button type="button" class="rtmkit-drawer-retry">Retry</button>' +
    "</div>";

  function showLoading() {
    if (!drawerContent) return;
    isLoading = true;
    drawerContent.innerHTML = loadingHtml;
  }

  function showError() {
    if (!drawerContent) return;
    drawerContent.innerHTML = errorHtml;

    var retryBtn = drawerContent.querySelector(".rtmkit-drawer-retry");
    if (retryBtn) {
      retryBtn.addEventListener("click", function () {
        drawerLoaded = false;
        loadDrawerContent();
      });
    }
  }

  // ---------- localStorage helpers ----------

  function readCache() {
    try {
      var raw = window.localStorage.getItem(STORAGE_KEY) || window.__rtmkitDrawerFeed;
      if (!raw) return null;
      var data = JSON.parse(raw);
      if (!data || !data.items || !data.fetchedAt) return null;
      // Cache kedaluwarsa
      if (Date.now() - data.fetchedAt > CACHE_TTL) return null;
      return data;
    } catch (e) {
      return null;
    }
  }

  function writeCache(items) {
    try {
      var payload = JSON.stringify({
        items: items,
        fetchedAt: Date.now(),
      });
      window.localStorage.setItem(STORAGE_KEY, payload);

      // pastikan benar-benar nempel
      if (window.localStorage.getItem(STORAGE_KEY) !== payload) {
        throw new Error("cache write verify failed");
      }

      window.__rtmkitDrawerFeed = payload;
    } catch (e) {
      console.warn("rtmkit_drawer_feed cache write failed:", e);
    }
  }

  // ---------- Read-state (isRead) logic ----------

  // Gabungkan item baru dari server dengan status isRead yang sudah ada di cache.
  // Item yang sudah pernah dibaca tetap isRead=true; item baru default false.
  function mergeReadState(newItems) {
    var cache = readCache();
    var readByLink = {};
    if (cache && cache.items) {
      cache.items.forEach(function (old) {
        if (old.isRead) {
          readByLink[old.link] = true;
        }
      });
    }
    return newItems.map(function (item) {
      item.isRead = !!readByLink[item.link];
      return item;
    });
  }

  // Ada feed baru jika masih ada item yang belum dibaca (isRead === false).
  function hasUnread(items) {
    return items.some(function (item) {
      return !item.isRead;
    });
  }

  function updateDot() {
    var cache = readCache();
    if (!cache || !trigger) return;
    var dot = trigger.querySelector(".rtmkit-new-feed-dot");
    if (hasUnread(cache.items)) {
      if (!dot) {
        dot = document.createElement("i");
        dot.className = "rtmkit-new-feed-dot";
        trigger.appendChild(dot);
      }
    } else if (dot) {
      dot.remove();
    }
  }

  // Tandai satu item sebagai sudah dibaca (isRead = true) lalu simpan.
  function markItemRead(link) {
    var cache = readCache();
    if (!cache) return;
    var changed = false;
    cache.items.forEach(function (item) {
      if (item.link === link && !item.isRead) {
        item.isRead = true;
        changed = true;
      }
    });
    if (changed) {
      writeCache(cache.items);
      updateDot();
    }
  }

  // ---------- Rendering ----------

  function renderItems(items) {
    if (!drawerContent) return;
    var html = "";
    items.forEach(function (item) {
      var buttonLink = item.isUpsale
        ? rtmkit_ajax.upgrade_url
        : item.link;
      var buttonClass = item.isUpsale ? "upgrade-btn" : "read-more";
      var buttonText = item.isUpsale ? "Upgrade Now" : "Learn More";
      var buttonIcon = item.isUpsale
        ? '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M3.49698 9.00236L4.78398 19.9374H19.227L20.513 9.00236L16.503 11.6754L12.005 5.37836L7.50698 11.6754L3.49698 9.00236ZM2.80598 6.13736L7.00498 8.93736L11.191 3.07636C11.2835 2.94673 11.4056 2.84107 11.5472 2.76816C11.6888 2.69526 11.8457 2.65723 12.005 2.65723C12.1642 2.65723 12.3212 2.69526 12.4628 2.76816C12.6044 2.84107 12.7265 2.94673 12.819 3.07636L17.005 8.93636L21.205 6.13736C21.3639 6.03169 21.5497 5.97368 21.7404 5.97019C21.9312 5.96669 22.119 6.01785 22.2817 6.11762C22.4443 6.2174 22.575 6.36163 22.6584 6.53328C22.7417 6.70493 22.7742 6.89684 22.752 7.08636L21.11 21.0534C21.0816 21.2968 20.9647 21.5213 20.7817 21.6843C20.5986 21.8472 20.3621 21.9373 20.117 21.9374H3.89398C3.6489 21.9373 3.41236 21.8472 3.22931 21.6843C3.04625 21.5213 2.92941 21.2968 2.90098 21.0534L1.25798 7.08736C1.2354 6.89761 1.26767 6.70536 1.35095 6.53337C1.43424 6.36138 1.56506 6.21686 1.72792 6.11691C1.89079 6.01696 2.07889 5.96576 2.26995 5.96939C2.461 5.97301 2.64702 6.0313 2.80598 6.13736ZM12.006 15.9374C11.7433 15.9374 11.4833 15.8858 11.2406 15.7853C10.9979 15.6849 10.7774 15.5376 10.5916 15.3519C10.4059 15.1663 10.2585 14.9458 10.1579 14.7032C10.0573 14.4606 10.0055 14.2005 10.0055 13.9379C10.0054 13.6752 10.0571 13.4151 10.1575 13.1725C10.258 12.9298 10.4052 12.7093 10.5909 12.5235C10.7766 12.3377 10.997 12.1904 11.2397 12.0898C11.4823 11.9892 11.7423 11.9374 12.005 11.9374C12.5354 11.9374 13.0441 12.1481 13.4192 12.5231C13.7943 12.8982 14.005 13.4069 14.005 13.9374C14.005 14.4678 13.7943 14.9765 13.4192 15.3516C13.0441 15.7266 12.5364 15.9374 12.006 15.9374Z" fill="#121416"></path></svg>'
        : "";

      html += '<div class="news-item">';
      html += '<h4 class="news-title">' + item.title + "</h4>";
      html += '<div class="featured-image">';
      html += '<img src="' + item.image + '" alt="">';
      html += '<span class="news-category">' + item.category + "</span>";
      html += "</div>";
      html += '<div class="news-description">' + item.excerpt + "</div>";
      html += "<div>";
      html +=
        '<a href="' +
        buttonLink +
        '" target="_blank" class="' +
        buttonClass +
        '" data-rtmkit-link="' +
        item.link +
        '">' +
        buttonIcon +
        buttonText +
        "</a>";
      html += "</div>";
      html += "</div>";
    });
    drawerContent.innerHTML = html;

    // Saat user mengklik link item, tandai item tsb sebagai sudah dibaca.
    var links = drawerContent.querySelectorAll("[data-rtmkit-link]");
    Array.prototype.forEach.call(links, function (link) {
      link.addEventListener("click", function () {
        markItemRead(link.getAttribute("data-rtmkit-link"));
      });
    });
  }

  // ---------- Loader ----------

  function fetchDrawerContent() {
    if (isLoading || !drawerContent) return;
    isLoading = true;

    if (typeof rtmkit_ajax === "undefined") {
      console.error("rtmkit_ajax is not available");
      showError();
      return;
    }

    showLoading();

    var params = new URLSearchParams();
    params.append("action", "rtmkit_load_drawer");
    params.append("nonce", rtmkit_ajax.nonce);

    fetch(rtmkit_ajax.ajax_url, {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: params,
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (res) {
        isLoading = false;

        if (res && res.success && res.data && res.data.items) {
          var items = mergeReadState(res.data.items);
          // Simpan ke localStorage untuk kunjungan berikutnya
          writeCache(items);
          renderItems(items);
          drawerLoaded = true;
          updateDot();
        } else if (res && res.success && res.data && res.data.html) {
          // Fallback: server lama yang hanya mengirim HTML
          drawerContent.innerHTML = res.data.html;
          drawerLoaded = true;
        } else {
          throw new Error("ajax response invalid");
        }
      })
      .catch(function (err) {
        console.error("Failed to load drawer content:", err);
        isLoading = false;
        drawerLoaded = false;
        showError();
      });
  }

  function loadDrawerContent() {
    if (drawerLoaded || isLoading || !drawerContent) return;

    // 1) Coba pakai cache dulu — tampilkan instan tanpa AJAX
    var cache = readCache();
    if (cache) {
      renderItems(cache.items);
      drawerLoaded = true;
      updateDot();
      return;
    }

    fetchDrawerContent();
  }

  // ---------- Events ----------

  if (trigger) {
    trigger.addEventListener("click", function (e) {
      e.preventDefault();
      drawer.classList.toggle("open");
      overlay.classList.toggle("active");
      loadDrawerContent();
    });
  }

  if (overlay) {
    overlay.addEventListener("click", function () {
      drawer.classList.remove("open");
      overlay.classList.remove("active");
    });
  }

  // Tampilkan dot merah jika ada feed yang belum dibaca (tanpa perlu buka drawer)
  updateDot();

  // Prefetch background supaya localStorage terisi dan dot bisa muncul walau drawer belum dibuka.
  if (trigger && drawerContent && !readCache()) {
    fetchDrawerContent();
  }
})();