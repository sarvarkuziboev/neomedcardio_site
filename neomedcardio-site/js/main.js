/* ==========================================================================
   NEO MED CARDIO — main.js
   ========================================================================== */

(function () {
  "use strict";

  /* ---------------------------------------------------------------------
     1) Header / Footer partiallarini yuklash
  --------------------------------------------------------------------- */
  function loadPartial(url, mountId, callback) {
    const mount = document.getElementById(mountId);
    if (!mount) return;
    fetch(url)
      .then((res) => res.text())
      .then((html) => {
        mount.innerHTML = html;
        if (callback) callback();
        nmcApplyLang(nmcGetLang());
        initIcons(mount);
      })
      .catch((err) => console.error("Partial yuklanmadi:", url, err));
  }

  /* ---------------------------------------------------------------------
     1b) Iconlar — <span class="icon" data-icon="nom"> ichiga
     images/icons/icon-<nom>.svg faylini o'qib, inline SVG sifatida
     joylaydi (shu sabab rangi currentColor orqali to'g'ri ishlaydi).
     Iconni almashtirish uchun shu papkadagi faylni xuddi shu nom bilan
     ustidan yozish kifoya.
  --------------------------------------------------------------------- */
  const iconCache = {};
  function initIcons(root) {
    (root || document).querySelectorAll(".icon[data-icon]").forEach((el) => {
      const name = el.getAttribute("data-icon");
      const url = `images/icons/icon-${name}.svg`;
      if (iconCache[url]) {
        el.innerHTML = iconCache[url];
        return;
      }
      fetch(url)
        .then((res) => res.text())
        .then((svgText) => {
          iconCache[url] = svgText;
          el.innerHTML = svgText;
        })
        .catch((err) => console.error("Icon yuklanmadi:", url, err));
    });
  }

  function initHeader() {
    // joriy sahifani nav'da aktiv qilish
    const page = document.body.getAttribute("data-page") || "index";
    document.querySelectorAll("#main-nav a").forEach((a) => {
      if (a.dataset.page === page) a.classList.add("is-active");
    });

    // burger menyu
    const burger = document.getElementById("burger-btn");
    const nav = document.getElementById("main-nav");
    if (burger && nav) {
      burger.addEventListener("click", () => {
        const open = nav.classList.toggle("is-open");
        burger.classList.toggle("is-open", open);
        burger.setAttribute("aria-expanded", open ? "true" : "false");
        document.body.classList.toggle("no-scroll", open);
      });
      nav.querySelectorAll("a").forEach((a) =>
        a.addEventListener("click", () => {
          nav.classList.remove("is-open");
          burger.classList.remove("is-open");
          document.body.classList.remove("no-scroll");
        })
      );
    }

    // scroll qilinganda header'ga soya
    const headerEl = document.getElementById("site-header-el");
    if (headerEl) {
      const onScroll = () => headerEl.classList.toggle("is-scrolled", window.scrollY > 12);
      onScroll();
      window.addEventListener("scroll", onScroll, { passive: true });
    }
  }

  function initFooter() {
    const y = document.getElementById("footer-year");
    if (y) y.textContent = new Date().getFullYear();
  }

  /* ---------------------------------------------------------------------
     2) Stagger — grid/lists ichidagi har bir elementga ketma-ket reveal
  --------------------------------------------------------------------- */
  function initStagger() {
    const groupSelectors = [
      ".directions-grid",
      ".doctor-grid",
      ".adv-grid",
      ".gallery-grid",
      ".steps-grid",
      ".service-grid",
      ".faq-wrap.accordion",
      ".info-list",
    ];
    groupSelectors.forEach((sel) => {
      document.querySelectorAll(sel).forEach((group) => {
        Array.from(group.children).forEach((child, i) => {
          if (!child.hasAttribute("data-reveal")) {
            child.setAttribute("data-reveal", "");
            child.setAttribute("data-reveal-delay", String(Math.min(i * 65, 420)));
          }
        });
      });
    });
  }

  /* ---------------------------------------------------------------------
     3) Scroll-reveal (Intersection Observer) — [data-reveal]
  --------------------------------------------------------------------- */
  function initReveal() {
    const items = document.querySelectorAll("[data-reveal]");
    if (!items.length) return;

    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      items.forEach((el) => el.classList.add("is-visible"));
      return;
    }

    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const delay = entry.target.getAttribute("data-reveal-delay") || 0;
            setTimeout(() => entry.target.classList.add("is-visible"), Number(delay));
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15, rootMargin: "0px 0px -60px 0px" }
    );

    items.forEach((el) => io.observe(el));
  }

  /* ---------------------------------------------------------------------
     3b) Hero parallaks — scroll qilganda vizual sekin siljiydi
  --------------------------------------------------------------------- */
  function initParallax() {
    const visual = document.querySelector(".hero__visual");
    if (!visual) return;
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
    if (window.matchMedia("(max-width: 900px)").matches) return;

    let ticking = false;
    window.addEventListener(
      "scroll",
      () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
          const y = Math.min(window.scrollY, 600) * 0.12;
          visual.style.transform = `translateY(${y}px)`;
          ticking = false;
        });
      },
      { passive: true }
    );
  }

  /* ---------------------------------------------------------------------
     3) Statistik raqamlar animatsiyasi — [data-count]
  --------------------------------------------------------------------- */
  function initCounters() {
    const counters = document.querySelectorAll("[data-count]");
    if (!counters.length) return;

    const animate = (el) => {
      const target = el.getAttribute("data-count");
      const match = target.match(/^(\d+)(.*)$/); // raqam + qo'shimcha (masalan "24/7", "120")
      if (!match) {
        el.textContent = target;
        return;
      }
      const end = parseInt(match[1], 10);
      const suffix = match[2] || "";
      const duration = 1200;
      const start = performance.now();

      function tick(now) {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const value = Math.floor(eased * end);
        el.textContent = value + suffix;
        if (progress < 1) requestAnimationFrame(tick);
        else el.textContent = end + suffix;
      }
      requestAnimationFrame(tick);
    };

    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            animate(entry.target);
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.6 }
    );
    counters.forEach((el) => io.observe(el));
  }

  /* ---------------------------------------------------------------------
     4) FAQ akkordeon
  --------------------------------------------------------------------- */
  function initAccordion() {
    document.querySelectorAll(".accordion__item").forEach((item) => {
      const btn = item.querySelector(".accordion__trigger");
      if (!btn) return;
      btn.addEventListener("click", () => {
        const isOpen = item.classList.contains("is-open");
        item.closest(".accordion").querySelectorAll(".accordion__item").forEach((i) => i.classList.remove("is-open"));
        if (!isOpen) item.classList.add("is-open");
      });
    });
  }

  /* ---------------------------------------------------------------------
     5) Filtr tugmalari (xizmatlar / shifokorlar sahifasi)
  --------------------------------------------------------------------- */
  function initFilters() {
    document.querySelectorAll("[data-filter-group]").forEach((group) => {
      const targetSelector = group.getAttribute("data-filter-group");
      const cards = document.querySelectorAll(targetSelector);
      const buttons = group.querySelectorAll("[data-filter]");

      buttons.forEach((btn) => {
        btn.addEventListener("click", () => {
          buttons.forEach((b) => b.classList.remove("is-active"));
          btn.classList.add("is-active");
          const filter = btn.getAttribute("data-filter");

          cards.forEach((card) => {
            const cats = (card.getAttribute("data-cat") || "").split(" ");
            const show = filter === "all" || cats.includes(filter);
            card.style.display = show ? "" : "none";
            if (show) {
              card.classList.remove("is-visible");
              requestAnimationFrame(() => card.classList.add("is-visible"));
            }
          });
        });
      });
    });
  }

  /* ---------------------------------------------------------------------
     6) Hero ECG chizig'ini "chizib" ko'rsatish (stroke-dashoffset)
  --------------------------------------------------------------------- */
  function initEcgDraw() {
    document.querySelectorAll(".ecg-path").forEach((path) => {
      const length = path.getTotalLength();
      path.style.strokeDasharray = length;
      path.style.strokeDashoffset = length;
      requestAnimationFrame(() => {
        path.style.transition = "stroke-dashoffset 2.1s cubic-bezier(.22,.9,.3,1) .2s";
        path.style.strokeDashoffset = "0";
      });
    });
  }

  /* ---------------------------------------------------------------------
     7) Contact forma (front-end demo — Telegramga yo'naltiradi)
  --------------------------------------------------------------------- */
  function initContactForm() {
    const form = document.getElementById("contact-form");
    if (!form) return;
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const name = form.querySelector("#f-name").value.trim();
      const phone = form.querySelector("#f-phone").value.trim();
      const service = form.querySelector("#f-service").value;
      const msg = form.querySelector("#f-message").value.trim();

      const text = `Salom! Ismim: ${name}. Tel: ${phone}. Yo'nalish: ${service}. ${msg ? "Izoh: " + msg : ""}`;
      const tgUrl = "https://t.me/neomedcardioclinicbot?start=" + encodeURIComponent(text.slice(0, 60));

      const successBox = document.getElementById("form-success");
      if (successBox) successBox.classList.add("is-visible");
      form.reset();

      window.open(tgUrl, "_blank");
    });
  }

  /* ---------------------------------------------------------------------
     init
  --------------------------------------------------------------------- */
  document.addEventListener("DOMContentLoaded", () => {
    loadPartial("partials/header.html", "site-header", initHeader);
    loadPartial("partials/footer.html", "site-footer", initFooter);

    initIcons(document);
    initStagger();
    initReveal();
    initParallax();
    initCounters();
    initAccordion();
    initFilters();
    initEcgDraw();
    initContactForm();
  });
})();
