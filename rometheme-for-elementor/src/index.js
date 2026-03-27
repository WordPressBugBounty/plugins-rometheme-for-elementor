import { render, useEffect, useState, createRoot } from "@wordpress/element";
import { ToastContainer, toast } from "react-toastify";

window.reactToast = toast;
const Loader = `
<div class="loader">
      <div class="loading">
        <svg
          class="load-logo"
          xmlns="http://www.w3.org/2000/svg"
          xmlnsXlink="http://www.w3.org/1999/xlink"
          viewBox="0 0 492.94 492.94"
          shapeRendering="geometricPrecision"
          textRendering="geometricPrecision"
        >
          <g transform="matrix(1.658927 0 0 1.658927 -187.604845 -149.806187)">
            <rect
              width="82.32"
              height="82.32"
              rx="0"
              ry="0"
              transform="translate(123.22 294.99)"
              fill="currentColor"
              strokeWidth="0"
            />
            <g>
              <polygon
                points="342.61,268.16 316.74,293.64 261.59,238.49 287.45,212.63 342.61,268.16"
                opacity="0.6"
                fill="currentColor"
                strokeWidth="0"
              />
              <polygon
                points="400.1,377.31 288.12,377.31 270.64,359.83 260.83,350.02 205.69,294.88 123.22,212.41 123.22,100.44 123.43,100.65 400.06,377.27 400.1,377.31"
                fill="currentColor"
                strokeWidth="0"
              />
            </g>
            <path
              d="M395.54,206.04c2.63,2.62,2.61,6.89-.03,9.49l-18.16,17.89-.21.21-34.52,34.53-11.88,11.33l3.92-3.74c4.36-4.16,4.45-11.1.18-15.37L197.92,123.48h114.16c.53,0,1.04.21,1.41.58l82.04,81.98h.01Z"
              fill="currentColor"
              strokeWidth="0"
            />
          </g>
        </svg>
      </div>
    </div>
`;

window.RTMLoader = Loader;

const getPath = () => {
  const params = new URLSearchParams(window.location.search);
  return params.get("path") || "dashboard";
};

const initScrollSpy = () => {
  const scrollSpyElements = document.querySelectorAll("[data-scrollspy]");
  scrollSpyElements.forEach((el) => {
    new Scrollspy(el, {
      // target: el.getAttribute("data-bs-target"),
      // smoothScroll : true,
      rootMargin: el.getAttribute("data-rootMargin") ?? "0px 0px -25%",
      threshold: 0,
      root: document.querySelector(".rtmkit-app__content"),
    });
  });
};

const initTabSlider = () => {
  const tabSliderEl = document.querySelectorAll(".menu-switcher");

  tabSliderEl.forEach((el) => {
    tabSlider(el, (e) => {
      let activeType = e.dataset.type;
      const freeWidgets = document.querySelectorAll(
        '[data-widget-type="free"]'
      );
      const proWidgets = document.querySelectorAll('[data-widget-type="pro"]');
      const allWidgets = document.querySelectorAll("[data-widget-type]");
      switch (activeType) {
        case "all":
          allWidgets.forEach((el) => {
            el.parentElement.style.display = "block";
          });
          break;
        case "pro":
          freeWidgets.forEach((el) => {
            el.parentElement.style.display = "none";
          });

          proWidgets.forEach((el) => {
            el.parentElement.style.display = "block";
          });

          break;
        case "free":
          freeWidgets.forEach((el) => {
            el.parentElement.style.display = "block";
          });

          proWidgets.forEach((el) => {
            el.parentElement.style.display = "none";
          });
          break;
      }
    });
  });
};

const initTabSliderModule = () => {
  const tabSliderEl = document.querySelectorAll(".menu-switcher");

  tabSliderEl.forEach((el) => {
    tabSlider(el, (e) => {
      let activeType = e.dataset.type;
      const freeModule = document.querySelectorAll('[data-module-type="free"]');
      const proModule = document.querySelectorAll('[data-module-type="pro"]');
      const allModules = document.querySelectorAll("[data-module-type]");
      switch (activeType) {
        case "all":
          allModules.forEach((el) => {
            el.parentElement.style.display = "block";
          });
          break;
        case "pro":
          freeModule.forEach((el) => {
            el.parentElement.style.display = "none";
          });

          proModule.forEach((el) => {
            el.parentElement.style.display = "block";
          });

          break;
        case "free":
          freeModule.forEach((el) => {
            el.parentElement.style.display = "block";
          });

          proModule.forEach((el) => {
            el.parentElement.style.display = "none";
          });
          break;
      }
    });
  });
};
const Searching = (inputIDSelector, targetSelector) => {
  const searchInput = document.querySelector(`#${inputIDSelector}`);

  if (searchInput !== null) {
    searchInput.addEventListener("change", (ev) => {
      ev.preventDefault();
      scrollToText(ev.target.value, targetSelector);
    });
  }
};

const scrollToText = (search, targetSelector) => {
  const elementToSearch = document.querySelectorAll(targetSelector);
  let lowerText = search.toLowerCase();

  elementToSearch.forEach((el) => {
    if (el.textContent.toLowerCase().includes(lowerText)) {
      el.scrollIntoView({ behavior: "smooth", block: "center" });
      el.classList.add("animation-scale");
      setTimeout(() => {
        el.classList.remove("animation-scale");
      }, 2000);
      return;
    }
  });
  console.warn("Not Found: ", text);
};

const App = () => {
  const [content, setContent] = useState(Loader);
  const [sidebarContent, setSidebarContent] = useState("");
  const [path, setPath] = useState(getPath());

  useEffect(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const path = urlParams.get("path") ?? "dashboard";
    // console.log(path);
    fetch(rtmkit_ajax.ajax_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "get_sidebar_content",
        nonce: rtmkit_ajax.nonce,
        path: path || "dashboard",
      }),
    })
      .then((res) => res.json())
      .then((res) => {
        if (res.success) {
          setSidebarContent(res.data);
        } else {
          console.error("Failed to fetch sidebar content:", res.data.message);
        }
      });

    const links = document.querySelectorAll(
      ".toplevel_page_rtmkit > ul.wp-submenu a , .rtmkit-app__sidebar .menu-link"
    );
    links.forEach((link) => {
      const url = new URL(link.href, window.location.origin);
      if (
        url.pathname.endsWith("admin.php") &&
        url.searchParams.get("page") === "rtmkit"
      ) {
        link.addEventListener("click", (e) => {
          e.preventDefault();
          const path = url.searchParams.get("path") || "dashboard";
          const newUrl =
            path === "dashboard"
              ? "admin.php?page=rtmkit"
              : `admin.php?page=rtmkit&path=${path}`;
          window.history.pushState({}, "", newUrl);
          window.dispatchEvent(new PopStateEvent("popstate"));
        });
      }
    });
  }, [sidebarContent]);

  // Highlight the active menu item
  useEffect(() => {
    const all = document.querySelectorAll(
      ".toplevel_page_rtmkit > ul.wp-submenu .current"
    );
    const all1 = document.querySelectorAll(
      ".rtmkit-app__sidebar .menu-link.current"
    );

    all.forEach((el) => el.classList.remove("current"));
    all1.forEach((el) => el.classList.remove("current"));

    const selector =
      path === "dashboard"
        ? '.toplevel_page_rtmkit > ul.wp-submenu a[href="admin.php?page=rtmkit"]'
        : `.toplevel_page_rtmkit > ul.wp-submenu a[href="admin.php?page=rtmkit&path=${path}"]`;

    const selector1 =
      path === "dashboard"
        ? '.rtmkit-app__sidebar a[href="admin.php?page=rtmkit"]'
        : `.rtmkit-app__sidebar a[href="admin.php?page=rtmkit&path=${path}"]`;

    const current = document.querySelector(selector);
    const current1 = document.querySelector(selector1);

    if (current?.parentElement) {
      current.parentElement.classList.add("current");
    }

    if (current1) {
      current1.classList.add("current");

      const menuSection = current1.closest(".menu-section");
      const dropdown = current1.closest(".collapse");

      if (menuSection) {
        const dropdownBtn = menuSection.querySelector(".menu-dropdown");
        if (dropdownBtn) {
          dropdownBtn.setAttribute("aria-expanded", "true");
        }
      }

      if (dropdown && !dropdown.classList.contains("show")) {
        dropdown.classList.add("show");
      }
    }
  }, [path]);

  // Update state ketika back/forward
  useEffect(() => {
    const handlePopState = () => {
      setPath(getPath());
    };
    window.addEventListener("popstate", handlePopState);
    return () => window.removeEventListener("popstate", handlePopState);
  }, []);

  // Fetch content for the current path
  useEffect(() => {
    setContent(Loader);
    // Ambil semua param dari URL
    const urlParams = new URLSearchParams(window.location.search);
    // console.log(urlParams);

    // Tambahkan action & nonce (overwrite kalau sudah ada)
    urlParams.set("action", "get_content");
    urlParams.set("nonce", rtmkit_ajax.nonce);
    if (urlParams.get("path") === undefined || urlParams.get("path") === null) {
      urlParams.set("path", "dashboard");
    }
    fetch(rtmkit_ajax.ajax_url, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: urlParams,
    })
      .then((res) => res.json())
      .then((res) => {
        if (res.success) {
          setContent(res.data);
        } else {
          console.error("Failed to fetch content:", res.data.message);
          setContent(`<div class="error">${res.data.message}</div>`);
        }
      })
      .catch((error) => {
        console.error("Error fetching content:", error);
        setContent(`<div class="error">Error loading content.</div>`);
      });
  }, [path]);

  useEffect(() => {
    switch (path) {
      case "widgets":
        initScrollSpy();
        initTabSlider();
        Searching("search-widget", ".widgets");
        switcher(
          document.querySelector("input#enable-all"),
          document.querySelectorAll(".switch-status")
        );
        saveWidgetsOption();
        resetAllWidgets();
        break;
      case "modules":
        initTabSliderModule();
        switcher(
          document.querySelector("input#enable-all"),
          document.querySelectorAll(".switch-status")
        );
        Searching("search-modules", ".modules");
        saveModules();
        reset_modules();
        break;
      case "themebuilder":
        // tabs();
        ThemebuilderTabs();
        addThemebuilder();
        break;
      case "templates":
        Templates();
        break;
      case "global-kit-setup":
        settings();
        break;
      case "license":
        license();
        break;
      case "submission":
        submission();
        break;
      case "updates":
        updates();
        break;
      case "system-status":
        initScrollSpy();
        break;
    }
  }, [content]);

  useEffect(() => {
    window.setReactPath = setPath;

    return () => {
      delete window.setReactPath;
    };
  }, []);

  return (
    <div className="rtmkit-app">
      <div
        className="rtmkit-app__sidebar"
        dangerouslySetInnerHTML={{ __html: sidebarContent }}
      ></div>
      <div
        className="rtmkit-app__content"
        dangerouslySetInnerHTML={{ __html: content }}
      ></div>
      <ToastContainer
        position="bottom-right"
        autoClose={3000}
        hideProgressBar={false}
        newestOnTop={false}
        closeOnClick
        rtl={false}
        pauseOnFocusLoss
        draggable
        pauseOnHover
      />
    </div>
  );
};

document.addEventListener("DOMContentLoaded", () => {
  const target = document.getElementById("rtmkit-root");
  if (target) {
    const root = createRoot(target);
    root.render(<App />);
  }
});
