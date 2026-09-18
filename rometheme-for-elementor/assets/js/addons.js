window.addEventListener("DOMContentLoaded", function () {
  const nonce = () => window.rtmkit_ajax.nonce;
  const ajaxUrl = () => window.rtmkit_ajax.ajax_url;

  async function postAction(action, extra = {}) {
    const res = await fetch(ajaxUrl(), {
      method: "POST",
      body: new URLSearchParams({ action, nonce: nonce(), ...extra }),
    });
    return res.json();
  }

  function toast(type, msg) {
    window.reactToast[type](msg, {
      position: "bottom-right",
      autoClose: 5000,
      className: "rtmkit-toast",
      theme: "dark",
    });
  }

  // Delegated click handler — nempel sekali ke document, jalan terus
  // walau tombol addon di-mount/unmount ulang oleh SPA.
  document.addEventListener("click", async (e) => {
    const installBtn = e.target.closest(".install-addon");
    const activateBtn = e.target.closest(".activate-addon");
    const deleteBtn = e.target.closest(".delete-addon");
    const syncBtn = e.target.closest("#sync-addons");

    if (installBtn) {
      e.preventDefault();
      const addon = installBtn.getAttribute("data-addon");
      installBtn.disabled = true;
      installBtn.textContent = "Downloading...";

      const dataDownload = await postAction("rtmkit_addons_install", { addon });
      if (!dataDownload.success) {
        installBtn.disabled = false;
        installBtn.textContent = "Install";
        return;
      }

      installBtn.textContent = "Activating...";
      const dataActivated = await postAction("rtmkit_addon_activate", {
        addon: dataDownload.data.plugin,
      });

      if (dataActivated.success) {
        toast("success", "Addon Installed and Activated Successfully");
      } else {
        console.log(dataActivated.data.message);
        toast("error", "Activate addon Failed");
      }
      window.location.reload();
      return;
    }

    if (activateBtn) {
      e.preventDefault();
      const addon = activateBtn.getAttribute("data-addon");
      activateBtn.disabled = true;
      activateBtn.innerHTML = "Activating...";

      const data = await postAction("rtmkit_addon_activate", {
        addon: addon + "/" + addon + ".php",
      });

      if (data.success) {
        toast("success", "Activate Addon Successfully");
        window.location.reload();
      } else {
        console.log(data.data.message);
        toast("error", data.data.message);
        activateBtn.disabled = false;
        activateBtn.innerHTML = "Activate";
      }
      return;
    }

    if (deleteBtn) {
      e.preventDefault();
      const addon = deleteBtn.getAttribute("data-addon");
      deleteBtn.disabled = true;
      deleteBtn.innerHTML = "Deleting...";

      const data = await postAction("rtmkit_addon_delete", { addon });
      if (data.success) {
        toast("success", "Addon Deleted Successfully");
        window.location.reload();
      }
      return;
    }

    if (syncBtn) {
      e.preventDefault();
      syncBtn.disabled = true;
      syncBtn.querySelector(".text").innerHTML = "Synchronizing...";
      syncBtn.classList.add("loading");

      const data = await postAction("sync_addons");
      if (data.success) window.location.reload();
    }
  });
});
