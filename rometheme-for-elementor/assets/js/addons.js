window.addEventListener("DOMContentLoaded", function () {
  const originalFetch = window.fetch;

  window.fetch = async function (...args) {
    const response = await originalFetch.apply(this, args);

    const options = args[1] || {};

    if (options.body instanceof URLSearchParams) {
      const params = Object.fromEntries(options.body.entries());

      if (params.path === "addons") {
        requestAnimationFrame(addons);
      }
    }
    return response;
  };

  function addons() {
    const installBtns = document.querySelectorAll(".install-addon");
    const deleteBtns = document.querySelectorAll(".delete-addon");
    const activateBtns = document.querySelectorAll(".activate-addon");

    installBtns.forEach(async (btn) => {
      btn.onclick = async (e) => {
        e.preventDefault();
        const addon = btn.getAttribute("data-addon");
        const nonce = window.rtmkit_ajax.nonce;
        const url = window.rtmkit_ajax.ajax_url;

        const thisBtn = btn;
        thisBtn.disabled = true;
        thisBtn.textContent = "Downloading...";

        const download = await fetch(url, {
          method: "POST",
          body: new URLSearchParams({
            action: "rtmkit_addons_install",
            nonce: nonce,
            addon: addon,
          }),
        });

        const dataDownload = await download.json();

        if (dataDownload.success) {
          const data = dataDownload.data;

          thisBtn.textContent = "Activating...";

          const activated = await fetch(url, {
            method: "POST",
            body: new URLSearchParams({
              action: "rtmkit_addon_activate",
              nonce: nonce,
              addon: data.plugin,
            }),
          });

          const dataActivated = await activated.json();

          if (dataActivated.success) {
            window.reactToast.success(
              "Addon Installed and Activated Successfully",
              {
                position: "bottom-right",
                autoClose: 5000,
                className: "rtmkit-toast",
                theme: "dark",
              },
            );
            window.location.reload();
          } else {
            console.log(dataActivated.data.message);
            window.reactToast.error("Activate addon Failed", {
              position: "bottom-right",
              autoClose: 5000,
              className: "rtmkit-toast",
              theme: "dark",
            });
            window.location.reload();
          }
        }
      };
    });
    activateBtns.forEach((btn) => {
      btn.onclick = async (e) => {
        e.preventDefault();
        const addon = btn.getAttribute("data-addon");
        const nonce = window.rtmkit_ajax.nonce;
        const url = window.rtmkit_ajax.ajax_url;

        btn.innerHTML = "Activating...";
        btn.disabled = true;

        const activate = await fetch(url, {
          method: "POST",
          body: new URLSearchParams({
            action: "rtmkit_addon_activate",
            nonce: nonce,
            addon: addon + "/" + addon + ".php",
          }),
        });
        const activateData = await activate.json();

        if (activateData.success) {
          window.reactToast.success("Activate Addon Successfully", {
            position: "bottom-right",
            autoClose: 5000,
            className: "rtmkit-toast",
            theme: "dark",
          });
          window.location.reload();
        } else {
          console.log(activateData.data.message);
          window.reactToast.error(activateData.data.message, {
            position: "bottom-right",
            autoClose: 5000,
            className: "rtmkit-toast",
            theme: "dark",
          });
        }
      };
    });
    deleteBtns.forEach((btn) => {
      btn.onclick = (e) => {
        e.preventDefault();
        const addon = btn.getAttribute("data-addon");
        const nonce = window.rtmkit_ajax.nonce;
        const url = window.rtmkit_ajax.ajax_url;

        btn.disabled = true;
        btn.innerHTML = "Deleting...";

        fetch(url, {
          method: "POST",
          body: new URLSearchParams({
            action: "rtmkit_addon_delete",
            addon: addon,
            nonce: nonce,
          }),
        })
          .then((res) => res.json())
          .then((res) => {
            if (res.success) {
              window.reactToast.success("Addon Deleted Successfully", {
                position: "bottom-right",
                autoClose: 5000,
                className: "rtmkit-toast",
                theme: "dark",
              });
              window.location.reload();
            }
          });
      };
    });
    const syncBtn = document.getElementById("sync-addons");
    if (syncBtn) {
      syncBtn.onclick = async (e) => {
        e.preventDefault();
        const nonce = window.rtmkit_ajax.nonce;
        const url = window.rtmkit_ajax.ajax_url;
        const thisBtn = e.currentTarget;
        thisBtn.disabled = true;
        thisBtn.querySelector(".text").innerHTML = "Synchronizing...";
        thisBtn.classList.add("loading");
        const syncFetch = await fetch(url, {
          method: "POST",
          body: new URLSearchParams({
            action: "sync_addons",
            nonce: nonce,
          }),
        });
        const syncResponse = await syncFetch.json();
        if (syncResponse.success) {
          window.location.reload();
        }
      };
    }
  }

  // async function render_addon_content() {
  //   const nonce = window.rtmkit_ajax.nonce;
  //   const url = window.rtmkit_ajax.ajax_url;

  //   const loading = `<div class="d-flex justify-content-center align-items-center" style="min-height:20rem">${window.RTMLoader}</>`;

  //   const container = document.querySelector(".rtmkit-app__content");

  //   container.innerHTML = loading;

  //   const urlParams = new URLSearchParams(window.location.search);
  //   urlParams.set("action", "get_content");
  //   urlParams.set("nonce", nonce);

  //   const fetchContent = await fetch(url, {
  //     method: "POST",
  //     body: urlParams,
  //   });

  //   const content = await fetchContent.json();

  //   if (content.success) {
  //     container.innerHTML = content.data;
  //     addons();
  //   }
  // }
});
