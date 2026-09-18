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
    const disconnectBtn = document.getElementById("disconnect-account");
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

    if (disconnectBtn) {
      disconnectBtn.onclick = (e) => {
        e.preventDefault();

        if (
          !confirm(
            "Disconnect this site from your RTMHub account? Your plan features and addons will no longer be available on this site until you connect again.",
          )
        ) {
          return;
        }

        const originalHtml = disconnectBtn.innerHTML;

        disconnectBtn.innerHTML = `<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
        <span role="status">Disconnecting...</span>`;

        disconnectBtn.disabled = true;

        fetch(window.rtmkit_ajax.ajax_url, {
          method: "POST",
          body: new URLSearchParams({
            action: "disconnect_account",
            nonce: window.rtmkit_ajax.nonce,
          }),
        })
          .then((res) => res.json())
          .then((res) => {
            if (res.success) {
              window.location.reload();
              return;
            }

            throw new Error(res.data?.message);
          })
          .catch((err) => {
            alert(err.message || "Failed to disconnect account. Please try again.");

            disconnectBtn.innerHTML = originalHtml;
            disconnectBtn.disabled = false;
          });
      };
    }
  }
});

