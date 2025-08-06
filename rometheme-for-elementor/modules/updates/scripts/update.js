jQuery(document).ready(($) => {
  $("[data-update]").on("click", (e) => {
    e.preventDefault();
    const $this = $(e.currentTarget);

    let confirmUpdate = confirm("Are You Sure Want Update this plugin ?");

    if (confirmUpdate) {
      $this.html(` <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
  <span role="status">Updating...</span>`);

      $this.prop("disabled", true);

      let plugin = $this.data("update");
      let data = {
        action: "update_plugin",
        nonce: rtmkit_update.nonce,
        plugin: plugin,
      };

      $.ajax({
        type: "POST",
        url: rtmkit_update.ajax_url,
        data: data,
        success: function (res) {
          if (res.success) {
            window.location.reload();
          }
        },
      });
    }
  });

  $("[data-reinstall]").on("click", (e) => {
    e.preventDefault();

    const $this = $(e.currentTarget);

    let version = $this
      .closest(".rollback-container")
      .find("select[name=version]")
      .val();

    let confirmRollBack = confirm(
      "Are You Sure rollback plugin to version " + version
    );

    if (confirmRollBack) {
      let plugin = $this.data("reinstall");

      let data = {
        action: "rollback_plugin",
        nonce: rtmkit_update.nonce,
        plugin: plugin,
        version: version,
      };

      $this.html(` <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
  <span role="status">Installing...</span>`);

      $this.prop("disabled", true);

      $.ajax({
        type: "POST",
        url: rtmkit_update.ajax_url,
        data: data,
        success: function (res) {
          if (res.success) {
            window.location.reload();
          }
        },
      });
    }
  });
});
