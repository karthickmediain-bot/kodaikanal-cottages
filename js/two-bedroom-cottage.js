// 2 Bedroom Cottage in Kodaikanal (css/two-bedroom-cottage.css, bc- prefix).
// Own script for this page only. Driven by data attributes:
//   [data-pick="cottage"] > .bc-opt[data-value][data-set-KEY]  showroom picker
//   [data-bind="KEY"]  receives the chosen value (text, img src, or a
//                      WhatsApp href when the key starts with "wa")
//   [data-tabs] > [role=tab][aria-controls]                    tab sets
//   [data-ask="Cottage name"]   links that open WhatsApp for that cottage
//   form[data-bc-form]          booking card: date, guests, cottage
(function () {
  "use strict";

  var NUMBER = "917502345777";
  var WA = "https://wa.me/" + NUMBER + "?text=";
  var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var current = "";

  function all(sel, root) { return [].slice.call((root || document).querySelectorAll(sel)); }

  function pretty(value) {
    if (!value) return "";
    var d = new Date(value + "T00:00:00");
    return isNaN(d.getTime()) ? "" : d.toLocaleDateString("en-IN", { day: "numeric", month: "short", year: "numeric" });
  }

  // One enquiry message, filled from the booking card if the visitor used it.
  function message(name) {
    var form = document.querySelector("[data-bc-form]");
    var date = form ? pretty(form.querySelector('[name="date"]').value) : "";
    var guests = form ? form.querySelector('[name="guests"]').value : "";
    var msg = "Hi, I'd like to check availability for " + (name ? name + " (2 bedroom cottage)" : "a 2 bedroom cottage") + " in Kodaikanal";
    if (date) msg += " from " + date;
    if (guests) msg += " for " + guests;
    return msg + ". Please share the rate per night.";
  }

  function refreshLinks() {
    all("[data-ask]").forEach(function (a) { a.href = WA + encodeURIComponent(message(a.getAttribute("data-ask"))); });
    all('[data-bind="wa"]').forEach(function (a) { a.href = WA + encodeURIComponent(message(current)); });
  }

  // ---- showroom picker -----------------------------------------------------
  function applySet(el) {
    Object.keys(el.dataset).forEach(function (k) {
      if (k.indexOf("set") !== 0 || k.length < 4) return;
      var key = k.slice(3).replace(/^[A-Z]/, function (c) { return c.toLowerCase(); })
        .replace(/[A-Z]/g, function (c) { return "-" + c.toLowerCase(); });
      var val = el.dataset[k];
      all('[data-bind="' + key + '"]').forEach(function (t) {
        if (t.tagName === "IMG") t.src = val;
        else if (key.indexOf("wa") !== 0) t.textContent = val;
      });
    });
  }

  function initPicker() {
    var group = document.querySelector('[data-pick="cottage"]');
    if (!group) return;
    var opts = all(".bc-opt", group);
    var select = document.querySelector("[data-bc-cottage]");

    function choose(opt, fromSelect) {
      opts.forEach(function (o) {
        var on = o === opt;
        o.classList.toggle("is-active", on);
        o.setAttribute("aria-pressed", String(on));
      });
      current = opt.getAttribute("data-value");
      applySet(opt);
      if (select && !fromSelect) select.value = current;
      refreshLinks();
    }
    opts.forEach(function (o) { o.addEventListener("click", function () { choose(o, false); }); });
    if (select) {
      select.addEventListener("change", function () {
        var match = opts.filter(function (o) { return o.getAttribute("data-value") === select.value; })[0];
        if (match) choose(match, true);
        else current = "";
        refreshLinks();
      });
    }
    choose(group.querySelector(".bc-opt.is-active") || opts[0], false);
  }

  // ---- booking card --------------------------------------------------------
  function initForm() {
    var form = document.querySelector("[data-bc-form]");
    if (!form) return;
    var d = new Date();
    form.querySelector('[name="date"]').min = d.getFullYear() + "-" + String(d.getMonth() + 1).padStart(2, "0") + "-" + String(d.getDate()).padStart(2, "0");
    form.addEventListener("change", refreshLinks);
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var name = form.querySelector("[data-bc-cottage]").value;
      if (window.ckLeadForm) window.ckLeadForm(form);
      window.open(WA + encodeURIComponent(message(name)), "_blank", "noopener");
      if (window.ckThankYouUrl) location.href = window.ckThankYouUrl();
    });
    document.addEventListener("pointerdown", function (e) {
      if (e.target.closest && e.target.closest("[data-ask], [data-bind='wa']")) refreshLinks();
    });
  }

  // ---- tab sets (page tabs and who-is-travelling) ---------------------------
  function initTabs() {
    all("[data-tabs]").forEach(function (bar) {
      var tabs = all('[role="tab"]', bar);
      function show(i, focus) {
        tabs.forEach(function (t, n) {
          var on = n === i;
          t.setAttribute("aria-selected", String(on));
          t.tabIndex = on ? 0 : -1;
          var p = document.getElementById(t.getAttribute("aria-controls"));
          if (p) { p.hidden = !on; p.classList.toggle("is-in", on); }
        });
        if (focus) tabs[i].focus();
      }
      tabs.forEach(function (t, n) {
        t.addEventListener("click", function () { show(n); });
        t.addEventListener("keydown", function (e) {
          if (e.key === "ArrowRight") { e.preventDefault(); show((n + 1) % tabs.length, true); }
          if (e.key === "ArrowLeft") { e.preventDefault(); show((n - 1 + tabs.length) % tabs.length, true); }
        });
      });
    });
  }

  // Page tabs: deep links (#reviews, #location, #faqs or any section id inside a
  // hidden panel) open the right tab first.
  function initPageTabs() {
    var bar = document.querySelector("[data-page-tabs]");
    if (!bar) return;
    var tabs = all('[role="tab"]', bar);
    function panelOf(el) { return el && el.closest ? el.closest(".bc-panel") : null; }
    function open(el) {
      var p = panelOf(el);
      if (!p || !p.hidden) return false;
      var tab = tabs.filter(function (t) { return t.getAttribute("aria-controls") === p.id; })[0];
      if (tab) tab.click();
      return true;
    }
    tabs.forEach(function (t) {
      t.addEventListener("click", function () {
        var top = bar.getBoundingClientRect().top;
        if (top < 0 || top > 200) window.scrollTo({ top: top + window.scrollY - 100, behavior: reduce ? "auto" : "smooth" });
        try { history.replaceState(null, "", "#" + t.id.replace("tab-", "")); } catch (e) {}
      });
    });
    document.addEventListener("click", function (e) {
      var a = e.target.closest && e.target.closest('a[href^="#"]');
      if (!a || a.getAttribute("href").length < 2) return;
      var target = document.getElementById(a.getAttribute("href").slice(1));
      if (open(target)) {
        e.preventDefault();
        setTimeout(function () { target.scrollIntoView({ behavior: reduce ? "auto" : "smooth", block: "start" }); }, 60);
      }
    });
    var h = location.hash.replace("#", "");
    if (!h) return;
    var t = tabs.filter(function (x) { return x.id === "tab-" + h; })[0];
    if (t) t.click();
    else open(document.getElementById(h));
  }

  // ---- floor plan -----------------------------------------------------------
  function initPlan() {
    var rooms = all(".bc-planroom");
    var items = all(".bc-room");
    function highlight(name) {
      rooms.forEach(function (r) { r.classList.toggle("is-active", r.getAttribute("data-room") === name); });
      items.forEach(function (b) { b.classList.toggle("is-active", b.getAttribute("data-room") === name); });
    }
    rooms.concat(items).forEach(function (el) {
      var name = el.getAttribute("data-room");
      el.addEventListener("mouseenter", function () { highlight(name); });
      el.addEventListener("focus", function () { highlight(name); });
      el.addEventListener("click", function () { highlight(name); });
    });
  }

  // ---- FAQ ------------------------------------------------------------------
  function initFaq() {
    all(".bc-faq-item").forEach(function (item) {
      var q = item.querySelector(".bc-faq-q");
      q.addEventListener("click", function () {
        var open = !item.classList.contains("is-open");
        item.classList.toggle("is-open", open);
        q.setAttribute("aria-expanded", String(open));
      });
    });
  }

  // ---- reveal on scroll, progress bar, back to top ----------------------------
  function initReveal() {
    var els = all(".bc-lead, .bc-tabbar, .bc-sec, .bc-book, .bc-final");
    if (reduce || !("IntersectionObserver" in window)) return;
    els.forEach(function (el) { el.classList.add("bc-rv"); });
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add("is-in"); io.unobserve(e.target); }
      });
    }, { threshold: 0.06, rootMargin: "0px 0px -30px 0px" });
    els.forEach(function (el) { io.observe(el); });
  }

  function initChrome() {
    var prog = document.querySelector(".bc-progress span");
    var top = document.querySelector(".bc-top");
    var tick = false;
    function onScroll() {
      var h = document.documentElement.scrollHeight - innerHeight;
      if (prog) prog.style.transform = "scaleX(" + (h > 0 ? Math.min(scrollY / h, 1) : 0) + ")";
      if (top) top.classList.toggle("is-show", scrollY > 900);
      tick = false;
    }
    window.addEventListener("scroll", function () { if (!tick) { tick = true; requestAnimationFrame(onScroll); } }, { passive: true });
    if (top) top.addEventListener("click", function () { window.scrollTo({ top: 0, behavior: reduce ? "auto" : "smooth" }); });
    onScroll();

    // Keep the sticky booking card fully on screen on short windows.
    var aside = document.querySelector(".bc-aside");
    function fit() {
      if (!aside) return;
      var cs = getComputedStyle(aside);
      if (cs.position !== "sticky" || cs.display === "contents") { aside.style.top = ""; return; }
      aside.style.top = Math.min(100, innerHeight - aside.offsetHeight - 16) + "px";
    }
    fit();
    window.addEventListener("resize", fit);
    setTimeout(fit, 400);
  }

  function init() {
    initPicker();
    initForm();
    initTabs();
    initPageTabs();
    initPlan();
    initFaq();
    initReveal();
    initChrome();
    refreshLinks();
  }

  if (document.getElementById("site-header")) document.addEventListener("partials:loaded", init, { once: true });
  else init();
})();
