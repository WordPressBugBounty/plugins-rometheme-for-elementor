class Scrollspy {
  constructor(el, args = {}) {
    this.el = el;
    this.options = {
      root: args.root ?? null, // NULL berarti pakai viewport
      rootMargin: args.rootMargin ?? "0px 0px -50% 0px", // Biar trigger saat elemen di tengah
      threshold: args.threshold ?? 0.1, // Sedikit aja masuk, langsung trigger
    };
    this.init();
  }

  init() {
    const spyElements = this.el.querySelectorAll("[id]");
    if (!spyElements.length) {
      console.warn("⚠️ No spy elements found inside:", this.el);
    }

    const observer = new IntersectionObserver(
      this.handleObserver.bind(this),
      this.options
    );

    spyElements.forEach((e) => {
      // console.log("🧐 Observing:", e.id);
      observer.observe(e);
    });
  }

  handleObserver(entries) {
    let target = this.el.getAttribute("data-scrollspy");
    if (!target.startsWith("#")) {
      target = "#" + target;
    }

    const targetEl = document.querySelector(target);
    if (!targetEl) {
      console.warn("⚠️ Target element not found:", target);
      return;
    }

    entries.forEach((entry) => {
      const idActive = entry.target.getAttribute("id");
      const selector = `a[href="#${CSS.escape(idActive)}"]`;
      const link = targetEl.querySelector(selector);

      if (!link) return;

      if (entry.isIntersecting) {
        // console.log("✅ Visible:", idActive);
        link.classList.add("active");
      } else {
        // console.log("🚫 Not Visible:", idActive);
        link.classList.remove("active");
      }
    });
  }
}
