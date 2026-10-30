// 5 Bedroom Cottage page: cottage showroom, page tabs, room plan, FAQ,
// quote card (builds a WhatsApp message), progress bar and back-to-top.
(function () {
  "use strict";

  var NUMBER = "917502345777";
  var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function all(sel, root) { return [].slice.call((root || document).querySelectorAll(sel)); }
  function waLink(msg) { return "https://wa.me/" + NUMBER + "?text=" + encodeURIComponent(msg); }

  var chosen = "";

  // Showroom: each pill carries its cottage data in data-* attributes.
  function initShowroom() {
    var opts = all(".fb-pills .fb-opt");
    var stage = document.querySelector("[data-show-img]");
    function bind(key, val) { all('[data-bind="' + key + '"]').forEach(function (el) { el.textContent = val; }); }
    function choose(opt) {
      opts.forEach(function (o) { var on = o === opt; o.classList.toggle("is-active", on); o.setAttribute("aria-pressed", String(on)); });
      var d = opt.dataset;
      if (stage) { stage.src = d.img; stage.alt = d.alt; }
      bind("name", d.name); bind("tag", d.tag); bind("sleeps", d.sleeps); bind("meals", d.meals); bind("note", d.note);
      chosen = d.name;
      var cta = document.getElementById("fb-show-cta");
      if (cta) cta.href = waLink("Hi, I'd like a quote for " + d.name + " (5 bedroom) in Kodaikanal.");
      var sel = document.getElementById("fb-q-cottage");
      if (sel) sel.value = d.name;
    }
    opts.forEach(function (o) { o.addEventListener("click", function () { choose(o); }); });
    if (opts[0]) choose(opts[0]);
  }

  // Page tabs: Overview / Location / FAQs.
  function initTabs() {
    var bar = document.querySelector("[data-page-tabs]");
    if (!bar) return;
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
      t.addEventListener("click", function () {
        show(n);
        var top = bar.getBoundingClientRect().top;
        if (top < 0 || top > 200) window.scrollTo({ top: top + window.scrollY - 100, behavior: reduce ? "auto" : "smooth" });
        try { history.replaceState(null, "", "#" + t.id.replace("tab-", "")); } catch (e) {}
      });
      t.addEventListener("keydown", function (e) {
        if (e.key === "ArrowRight") { e.preventDefault(); show((n + 1) % tabs.length, true); }
        if (e.key === "ArrowLeft") { e.preventDefault(); show((n - 1 + tabs.length) % tabs.length, true); }
      });
    });
    var h = location.hash.replace("#", "");
    var t = tabs.filter(function (x) { return x.id === "tab-" + h; })[0];
    if (t) t.click();
  }

  // Room plan: sample splits only, not a promise of any one cottage's layout.
  function initPlan() {
    var PLANS = {
      family: [["Grandparents", 2], ["Parents + 1 child", 3], ["Parents + 1 child", 3], ["Uncle and aunt", 2], ["Teens", 2]],
      friends: [["Pair 1", 2], ["Pair 2", 2], ["Pair 3", 2], ["Pair 4", 2], ["Pair 5", 2]],
      office: [["Team A", 3], ["Team B", 3], ["Team C", 3], ["Team D", 3], ["Team E", 3]]
    };
    var tabs = all("[data-plan]");
    var rooms = all(".fb-rooms li");
    tabs.forEach(function (tab) {
      tab.addEventListener("click", function () {
        tabs.forEach(function (t) { t.classList.toggle("is-active", t === tab); t.setAttribute("aria-pressed", String(t === tab)); });
        PLANS[tab.getAttribute("data-plan")].forEach(function (r, i) {
          rooms[i].querySelector("span").textContent = r[0];
          rooms[i].querySelector("small").textContent = r[1] + " guests";
        });
      });
    });
  }

  function initFaq() {
    all(".fb-faq-item").forEach(function (item) {
      var q = item.querySelector(".fb-faq-q");
      q.addEventListener("click", function () {
        var open = !item.classList.contains("is-open");
        item.classList.toggle("is-open", open);
        q.setAttribute("aria-expanded", String(open));
      });
    });
  }

  // Quote card: date, group size and cottage go into the WhatsApp message.
  function initQuote() {
    var form = document.getElementById("fb-quote");
    if (!form) return;
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var date = form.elements.date.value;
      var guests = form.elements.guests.value;
      var cottage = form.elements.cottage.value;
      var msg = "Hi, I need a quote for a 5 bedroom cottage in Kodaikanal.";
      if (cottage) msg += " Cottage: " + cottage + ".";
      if (date) msg += " Check-in: " + date + ".";
      if (guests) msg += " Group size: " + guests + ".";
      if (window.ckLeadForm) window.ckLeadForm(form);
      window.open(waLink(msg), "_blank", "noopener");
      if (window.ckThankYouUrl) location.href = window.ckThankYouUrl();
    });
  }

  // data-wa links elsewhere on the page.
  function initLinks() {
    all("[data-wa]").forEach(function (a) { a.href = waLink(a.getAttribute("data-wa")); });
  }

  function initChrome() {
    var prog = document.querySelector(".fb-progress span");
    var top = document.querySelector(".fb-top");
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
  }

  function init() {
    initShowroom();
    initTabs();
    initPlan();
    initFaq();
    initQuote();
    initLinks();
    initChrome();
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
})();
