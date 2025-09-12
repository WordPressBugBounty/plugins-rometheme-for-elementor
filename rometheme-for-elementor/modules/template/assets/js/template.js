jQuery(document).ready(function ($) {
  // console.log(window.rtm);

  const params = new URLSearchParams(window.location.search);

  if (!params.has("template_id")) {
    if (!params.has("tabs") || params.get("tabs") == "pro-template" ) {
      const tab = params.get("tabs");
      if (tab == "pro-template" || !tab) {
        let current_page = params.has("paged")
          ? parseInt(params.get("paged"))
          : 1;
        const data = {
          action: "fetch_envato_template",
          wpnonce: rometheme_ajax.nonce,
          paged: current_page,
        };

        if (params.has("search")) {
          data.search = params.get("search");
        }
        const TemplateContainer = $("#pro-template-container");
        $.ajax({
          type: "POST",
          url: rometheme_ajax.ajax_url,
          data: data,
          success: function (res) {
            if (res.success) {
              TemplateContainer.empty();
              let datas = res.data;
              console.log(Math.ceil(datas.total_hits / 24));
              let matches = datas.matches;
              matches.forEach((v) => {
                let name = v.name;
                let previewImgUrl = v.previews.landscape_preview.landscape_url;
                let previewUrl = v.previews.live_site.url;
                let itemUrl = v.url;
                let downloads = v.number_of_sales;
                let id = v.id;

                let $col = $('<div class="col mb-4"></div>');
                let $card = $(
                  '<div class="d-flex flex-column h-100 rounded-3 glass-effect rtm-border"></div>'
                );
                let $previewImg = $(
                  `<img class="img-fluid rounded-top" src=${previewImgUrl}>`
                );
                let $cardBody = $(
                  `<div class="p-3 d-flex flex-column gap-3"></div>`
                );
                let $title = $(
                  `<div class="d-block"><h5 class="text-truncate text-white m-0">${name}</h5></div>`
                );

                let btnContainer = $(
                  '<div class="d-flex flex-row gap-2"></div>'
                );

                btnInstall = $(
                  `<a href="${itemUrl}" target="_blank" class="fw-light btn w-100 btn-gradient-accent rounded-2"><i class="fas fa-plus me-2"></i>Install</a>`
                );

                let btnPreview = $(
                  `<a target="_blank" href="${previewUrl}" class="btn fw-light w-100 border-white text-white rounded-2" data-template="${v.id}"><i class="far fa-eye me-2"></i>Preview</button>`
                );

                let totalDownloads = $(
                  `<button class="btn btn-outline-accent d-flex gap-2" data-tooltips="Total Download"><i class="fas fa-download"></i>${downloads}</button>`
                );
                btnContainer.append(btnInstall);
                btnContainer.append(btnPreview);
                btnContainer.append(totalDownloads);

                $cardBody.append($title);
                // $cardBody.append(``);
                $cardBody.append(btnContainer);
                $card.append($previewImg);
                $card.append($cardBody);
                $col.append($card);
                TemplateContainer.append($col);
              });

              let totalPages = Math.ceil(datas.total_hits / 24);
              if (totalPages > 1) {
                const paginationPro = $("#pro-template-pagination");
                paginationPro.empty();
                let url = window.location.href;
                let prevURL =
                  current_page - 1 <= 0
                    ? "#"
                    : url + "&paged=" + (current_page - 1);
                let nextURL =
                  current_page + 1 > totalPages
                    ? "#"
                    : url + "&paged=" + (current_page + 1);
                let prev = $(`<li class="page-item glass-effect">
              <a class="page-btn" href="${prevURL}" aria-label="Previous" ${
                  prevURL === "#" ? "disabled" : ""
                }>
                <span aria-hidden="true">&laquo;</span>
              </a>
            </li>`);

                paginationPro.append(prev);
                for (let i = 1; i <= totalPages; i++) {
                  // tampilkan selalu: page 1, page terakhir, dan range sekitar current_page
                  if (
                    i === 1 ||
                    i === totalPages ||
                    (i >= current_page - 1 && i <= current_page + 1)
                  ) {
                    let pageURL = url + "&paged=" + i;
                    const activeClass = i === current_page ? "active" : "";
                    paginationPro.append(
                      `<li class="page-item glass-effect ${activeClass}">
        <a href="${pageURL}" class="page-btn" data-page="${i}">${i}</a>
      </li>`
                    );
                  }
                  // tambahkan "..." di sela-sela
                  else if (i === 2 && current_page > 3) {
                    paginationPro.append(
                      `<li class="page-item glass-effect">
        <span class="page-btn" >...</span>
      </li>`
                    );
                  } else if (
                    i === totalPages - 1 &&
                    current_page < totalPages - 2
                  ) {
                    paginationPro.append(
                      `<li class="page-item glass-effect">
        <span class="page-btn" >...</span>
      </li>`
                    );
                  }
                }

                let next = $(`<li class="page-item glass-effect">
              <a class="page-btn" href="${nextURL}" aria-label="Next" ${
                  nextURL === "#" ? "disabled" : ""
                }>
                <span aria-hidden="true">&raquo;</span>
              </a>
            </li>`);
                paginationPro.append(next);
              }
            }
          },
        });
      }
    } else if (params.get("tabs") == "free-template") {
      // Buat objek data
      const data = {
        action: "fetch_lib",
        wpnonce: rometheme_ajax.nonce,
      };

      // Tambahkan parameter hanya jika ada
      if (params.has("search")) {
        data.search = params.get("search");
        $("#template-search").val(params.get("search"));
      }

      if (params.has("category")) {
        data.category = params.get("category");
      }

      if (params.has("paged")) {
        data.paged = params.get("paged");
      }
      $.ajax({
        type: "POST",
        url: rometheme_ajax.ajax_url,
        data: data,
        success: function (res) {
          if (res.success) {
            let dataTemplate = res.data.data_template;
            // console.log(dataTemplate);

            const TemplateContainer = $("#template-container");
            if (dataTemplate.length != 0) {
              TemplateContainer.empty();
              $.each(dataTemplate, (i, v) => {
                let $col = $('<div class="col mb-4"></div>');
                let $card = $(
                  '<div class="d-flex flex-column h-100 rounded-3 glass-effect rtm-border"></div>'
                );
                let $previewImg = $(
                  `<img class="img-fluid rounded-top" src=${v.image_preview}>`
                );
                let $cardBody = $(
                  `<div class="p-3 d-flex flex-column gap-3"></div>`
                );
                let $title = $(
                  `<div class="d-block"><h5 class="text-truncate text-white m-0">${v.name}</h5></div>`
                );

                let btnContainer = $(
                  '<div class="d-flex flex-row gap-2"></div>'
                );
                let btnInstall;
                if (v.type == "pro") {
                  if (window.rtm.isPro) {
                    if (v.has_installed) {
                      btnInstall = $(
                        `<a href="${
                          res.data.template_url + "&template_id=" + v.installed
                        }" class="fw-light btn w-100 btn-gradient-accent rounded-2"><i class="fas fa-plus me-2"></i>View Kit</a>`
                      );
                    } else {
                      btnInstall = $(
                        `<button class="fw-light btn w-100 btn-gradient-accent rounded-2 "><i class="fas fa-plus me-2"></i>Install</button>`
                      );

                      btnInstall.on("click", function (e) {
                        $(this).html("Installing...");
                        $(this).prop("disabled", true);
                        download_template(v.id);
                      });
                    }
                  } else {
                    btnInstall = $(
                      `<a href="http://rometheme.net/pricing" target="_blank" class="fw-light btn w-100 btn-gradient-accent rounded-2"><i class="fas fa-plus me-2"></i>Upgrade</a>`
                    );
                  }
                } else {
                  if (v.has_installed) {
                    btnInstall = $(
                      `<a href="${
                        res.data.template_url + "&template_id=" + v.installed
                      }" class="fw-light btn w-100 btn-gradient-accent rounded-2"><i class="fas fa-plus me-2"></i>View Kit</a>`
                    );
                  } else {
                    btnInstall = $(
                      `<button class="fw-light btn w-100 btn-gradient-accent rounded-2 "><i class="fas fa-plus me-2"></i>Install</button>`
                    );

                    btnInstall.on("click", function (e) {
                      $(this).html("Installing...");
                      $(this).prop("disabled", true);
                      download_template(v.id);
                    });
                  }
                }
                let btnPreview = $(
                  `<a target="_blank" href="${v.preview_url}" class="btn fw-light w-100 border-white text-white rounded-2" data-template="${v.id}"><i class="far fa-eye me-2"></i>Preview</button>`
                );

                let totalDownloads = $(
                  `<button class="btn btn-outline-accent d-flex gap-2" data-tooltips="Total Download"><i class="fas fa-download"></i>${v.downloads}</button>`
                );

                btnContainer.append(btnInstall);
                btnContainer.append(btnPreview);
                btnContainer.append(totalDownloads);

                $cardBody.append($title);
                // $cardBody.append(``);
                $cardBody.append(btnContainer);
                $card.append($previewImg);
                $card.append($cardBody);
                $col.append($card);
                TemplateContainer.append($col);
              });

              if (res.data.pagination.total_pages > 1) {
                const pagination = $("#pagination");
                pagination.empty();
                let p =
                  res.data.pagination.current_page == 1
                    ? "#"
                    : res.data.pagination.current_page - 1;
                let prevLink =
                  p === "#"
                    ? ""
                    : `href="${res.data.template_url + "&paged=" + p}"`;
                const prev = $(`<li class="page-item glass-effect">
              <a class="page-btn" ${prevLink} aria-label="Previous" ${
                  p === "#" ? "disabled" : ""
                }>
                <span aria-hidden="true">&laquo;</span>
              </a>
            </li>`);

                let n =
                  res.data.pagination.current_page ==
                  res.data.pagination.total_pages
                    ? "#"
                    : res.data.pagination.current_page + 1;
                let nextLink =
                  n === "#"
                    ? ""
                    : `href="${res.data.template_url + "&paged=" + n}"`;
                const next = $(`<li class="page-item glass-effect">
              <a class="page-btn" ${nextLink} aria-label="Next" ${
                  n === "#" ? "disabled" : ""
                }>
                <span aria-hidden="true">&raquo;</span>
              </a>
            </li>`);

                pagination.append(prev);
                for (let i = 1; i <= res.data.pagination.total_pages; i++) {
                  const activeClass =
                    i === res.data.pagination.current_page ? "active" : "";
                  pagination.append(
                    `<li class="page-item glass-effect ${activeClass}"><a href="${
                      res.data.template_url + "&paged=" + i
                    }" class="page-btn" data-page="${i}">${i}</a></li>`
                  );
                }

                pagination.append(next);
              }
            } else {
              $("#loader").empty();
              $("#loader").append(
                `<h4 class="text-light">Sorry, Result Not Found</h4>`
              );
            }
          } else {
            // console.log(res);
          }
        },
      });
    }
  } else {
    const importFullBtn = $("#import-full-template");
    importFullBtn.on("click", function () {
      const templates = $("[data-template][data-path]");
      if (!templates.length) {
        alert("All templates have been successfully imported.");
        return;
      }
      window.confirm("Are you sure want to import full template?") &&
        (importFullBtn.prop("disabled", true),
        templates.prop("disabled", true),
        import_full_template(importFullBtn));
    });

    const deleteAllBtn = $("#delete-all-installed-templates");
    deleteAllBtn.on("click", function () {
      const templates = $("[data-template][data-item-template]");
      if (!templates.length) {
        alert("There are no templates to delete.");
        return;
      }
      window.confirm("Are you sure want to delete all installed templates?") &&
        (deleteAllBtn.prop("disabled", true),
        templates.prop("disabled", true),
        delete_all_installed_templates(deleteAllBtn));
    });
  }

  async function import_full_template(btn) {
    const loading = $(`<div class="spinner-border text-light" role="status">
    <span class="visually-hidden">Loading...</span>
  </div>`);
    const infoText = $('<span class="info-text text-white"></span>');
    const templates = $("[data-template][data-path]");
    let index = 0;
    btn.empty();
    btn.append(loading);
    btn.append(infoText);
    let success = true;
    for (const t of templates) {
      index++;
      let $el = $(t);
      let id = $el.data("template");
      let template_name = $el.data("template-name");
      let path = $el.data("path");

      let data = {
        action: "import_rtm_template",
        template: id,
        template_name: template_name,
        path: path,
        wpnonce: rometheme_ajax.nonce,
      };

      infoText.text(`Importing ${index} of ${templates.length}`);
      try {
        await $.post(rometheme_ajax.ajax_url, data);
        // alert(`Successfully imported: ${template_name}`);
      } catch (err) {
        console.error("Error importing:", template_name, err);
        success = false;
        alert(`Error importing: ${template_name}. Check console for details.`);
        break;
      }
    }

    if (success) {
      window.location.reload();
    }
  }

  async function delete_all_installed_templates(btn) {
    const loading = $(`<div class="spinner-border text-light" role="status">
    <span class="visually-hidden">Loading...</span>
  </div>`);
    const infoText = $('<span class="info-text text-white"></span>');
    let index = 0;
    btn.empty();
    btn.append(loading);
    btn.append(infoText);
    let success = true;
    const templates = $("[data-template][data-item-template]");
    for (const el of templates) {
      index++;
      const $el = $(el);
      const template = $el.data("template");
      const itemTemplate = $el.data("item-template");
      infoText.text(`Deleting ${index} of ${templates.length}`);
      try {
        await $.ajax({
          type: "POST",
          url: rometheme_ajax.ajax_url,
          data: {
            action: "delete_installed_template",
            template: template,
            template_id: itemTemplate,
            wpnonce: rometheme_ajax.nonce,
          },
        });
      } catch (err) {
        console.error(`Error deleting template ${template}:`, err);
        success = false;
        alert(`Error importing: ${template_name}. Check console for details.`);
      }
    }
    if (success) {
      window.location.reload();
    }
  }

  function download_template(id) {
    $.ajax({
      type: "POST",
      url: rometheme_ajax.ajax_url,
      data: {
        action: "download_template",
        template: id,
        wpnonce: rometheme_ajax.nonce,
      },
      success: function (res) {
        if (res.success) {
          window.location.reload();
        }
      },
    });
  }

  $(".import-template").on("click", function (e) {
    e.preventDefault();
    const $this = $(this);
    let path = $this.data("path");
    let template = $this.data("template");
    let template_name = $this.data("template-name");

    $this.html("Importing 0%");
    $(".import-template").prop("disabled", true);

    // Fungsi untuk memeriksa progres
    function checkProgress() {
      $.ajax({
        type: "POST",
        url: rometheme_ajax.ajax_url,
        data: {
          action: "get_import_progress",
          template: template,
          template_name: template_name,
        },
        success: function (res) {
          if (res.success) {
            $this.html(`Importing ${res.data.progress}%`);
            if (res.data.progress < 100) {
              setTimeout(checkProgress, 1000); // Cek progres setiap 1 detik
            }
          }
        },
      });
    }

    // Mulai proses impor
    $.ajax({
      type: "POST",
      url: rometheme_ajax.ajax_url,
      data: {
        action: "import_rtm_template",
        template: template,
        template_name: template_name,
        path: path,
        wpnonce: rometheme_ajax.nonce,
      },
      success: function (res) {
        if (res.success) {
          // console.log(res.data);

          $this.html("Import Complete!");
          let $link = $(
            `<a href="${res.data.edit_url}" class="fw-light btn w-100 btn-gradient-accent rounded-2 text-nowrap"><i class="far fa-eye me-2"></i>View Template</a>`
          );
          let $deleteButton = $(`
                    <button 
                        class="btn w-100 btn-danger text-nowrap delete-installed-template" 
                        data-template="${template}" 
                        data-item-template="${res.data.template_id}">
                        <i class="far fa-trash-can me-2"></i>
                        Delete
                    </button>
                `);

          $deleteButton.on("click", function () {
            $deleteButton.html("Deleting...");
            $deleteButton.prop("disabled", true);
            $.ajax({
              type: "POST",
              url: rometheme_ajax.ajax_url,
              data: {
                action: "delete_installed_template",
                template: template,
                template_id: res.data.template_id,
                wpnonce: rometheme_ajax.nonce,
              },
              success: function (deleteRes) {
                if (deleteRes.success) {
                  alert("Template deleted successfully!");
                  window.location.reload();
                }
              },
            });
          });

          window.location.reload();
        } else {
          $this.html("Import Failed");
        }
      },
    });

    // Mulai polling progres
    checkProgress();
  });

  $(".delete-template").on("click", function (e) {
    e.preventDefault();
    let template = $(this).data("template");
    $(this).html("Deleting...");
    $.ajax({
      type: "POST",
      url: rometheme_ajax.ajax_url,
      data: {
        action: "delete_template",
        template: template,
        wpnonce: rometheme_ajax.nonce,
      },
      success: function (res) {
        if (res.success) {
          window.location.reload();
        }
      },
    });
  });

  $(".delete-installed-template").on("click", function (e) {
    e.preventDefault();
    $this = $(this);
    $this.prop("disabled", true);
    $this.html("Deleting...");
    let template = $this.data("template");
    let id = $this.data("item-template");

    $.ajax({
      type: "POST",
      url: rometheme_ajax.ajax_url,
      data: {
        action: "delete_installed_template",
        template: template,
        template_id: id,
        wpnonce: rometheme_ajax.nonce,
      },
      success: function (res) {
        if (res.success) {
          alert("Template deleted successfully!");
          window.location.reload();
        }
      },
    });
  });

  $(".btn-install-requirements").on("click", function (e) {
    e.preventDefault();
    $this = $(this);
    let datamiss = $(this).data("missing"); // Ambil data plugin yang hilang
    let total = datamiss.length; // Total plugin yang harus diinstall

    if (!Array.isArray(datamiss) || total === 0) {
      alert("No plugins to install.");
      return;
    }

    $(this).prop("disabled", true); // Disable tombol untuk mencegah klik ganda

    function installPlugin(index) {
      if (index < total) {
        // Update teks untuk plugin yang sedang diproses
        let percen = (index / datamiss.length) * 100;
        $this.html(`Installing...`);

        // Kirim AJAX untuk menginstall plugin
        $.post(rometheme_ajax.ajax_url, {
          action: "install_requirements",
          plugin: datamiss[index].file,
          wpnonce: rometheme_ajax.nonce,
        })
          .done(function (res) {})
          .fail(function () {})
          .always(function () {
            // Setelah selesai, lanjutkan ke plugin berikutnya
            installPlugin(index + 1);
          });
      } else {
        // Semua plugin sudah diinstall
        window.location.reload();
      }
    }

    // Mulai proses dari plugin pertama
    installPlugin(0);
  });

  // Drag and Drop Upload
  const dropZone = $(".drop-zone");
  const fileInput = dropZone.find(".drop-zone__input");
  const prompt = dropZone.find(".drop-zone__prompt");

  dropZone.on("click", function () {
    fileInput[0].click(); // pakai native click
  });

  dropZone.on("dragover", function (e) {
    e.preventDefault();
    dropZone.addClass("drop-zone--over");
  });

  dropZone.on("dragleave", function (e) {
    e.preventDefault();
    dropZone.removeClass("drop-zone--over");
  });

  dropZone.on("drop", function (e) {
    e.preventDefault();
    dropZone.removeClass("drop-zone--over");

    const files = e.originalEvent.dataTransfer.files;

    if (!files.length) return;

    // filter zip only
    const zipFiles = [...files].filter((f) =>
      f.name.toLowerCase().endsWith(".zip")
    );
    if (!zipFiles.length) {
      prompt.html(
        `<span style="color:red">❌ Hanya file .zip yang diperbolehkan</span>`
      );
      return;
    }

    prompt.html(`<div class="spinner-border" style="width: 3rem; height: 3rem;" role="status">
    <span class="visually-hidden">Loading...</span>
  </div><span>Uploading Template Kit...</span>`);

    // proses upload langsung
    zipFiles.forEach(uploadFile);
  });

  fileInput.on("click", function (e) {
    e.stopPropagation(); // cegah loop
  });

  fileInput.on("change", function (e) {
    const files = e.target.files;
    const zipFiles = [...files].filter((f) =>
      f.name.toLowerCase().endsWith(".zip")
    );

    if (!zipFiles.length) {
      prompt.html(
        `<span style="color:red">❌ Hanya file .zip yang diperbolehkan</span>`
      );
      return;
    }

    prompt.html(`<div class="spinner-border" style="width: 3rem; height: 3rem;" role="status">
    <span class="visually-hidden">Loading...</span>
  </div><span>Uploading Template Kit...</span>`);

    zipFiles.forEach(uploadFile);
  });

  function uploadFile(file) {
    dropZone.off("click");
    dropZone.off("dragover");
    dropZone.off("dragleave");
    dropZone.off("drop");
    fileInput.off("click");
    fileInput.off("change");
    let formData = new FormData();
    formData.append("action", "rtm_handle_upload_template");
    formData.append("file", file);
    formData.append("nonce", rometheme_ajax.nonce);
    $.ajax({
      url: rometheme_ajax.ajax_url,
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (res) {
        if (res.success) {
          window.location.reload();
        }
      },
      error: function (err) {
        console.error(err);
      },
    });
  }
});
