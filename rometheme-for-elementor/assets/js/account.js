window.addEventListener("DOMContentLoaded", function () {
  const originalFetch = window.fetch;

  window.fetch = async function (...args) {
    const response = await originalFetch.apply(this, args);

    const options = args[1] || {};

    if (options.body instanceof URLSearchParams) {
      const params = Object.fromEntries(options.body.entries());

      if (params.path === "account") {
        requestAnimationFrame(account);
      }
    }
    return response;
  };
  let popup = null;
  window.addEventListener("message", (event) => {
    if (event.origin !== window.location.origin) {
      return;
    }

    

    if (event.data?.type === "rtmhub-connected" || event.data?.type === 'rtmhub-connect-failed') {
      if (popup && !popup.closed) {
        popup.close();
      }
      location.reload();
    }
  });

  function account() {
    const connectBtn = document.getElementById("connect-account");
    const syncBtn = document.getElementById("sync-account");
    if (connectBtn) {
      connectBtn.onclick = (e) => {
        e.preventDefault();

        popup = window.open(
          connectBtn.href,
          "rtmhub_connect",
          "width=700,height=750,resizable=yes,scrollbars=yes",
        );
      };
    }
    if (syncBtn) {
      syncBtn.onclick = (e) => {
        e.preventDefault();
        const nonce = window.rtmkit_ajax.nonce;
        const url = window.rtmkit_ajax.ajax_url;

        syncBtn.querySelector("span").innerHTML = "Synchronizing";

        syncBtn.classList.add("loading");

        syncBtn.disabled = true;

        fetch(url, {
          method: "POST",
          body: new URLSearchParams({
            action: "connect_sync",
            nonce: nonce,
          }),
        })
          .then((res) => res.json())
          .then((res) => {
            if (res.success) {
              window.location.reload();
            }
          });
      };
    }
  }
});
