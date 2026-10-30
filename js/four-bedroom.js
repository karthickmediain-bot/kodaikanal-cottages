// 4 bedroom cottage page: cottage picker, tabs, FAQ accordion, enquiry form (builds a WhatsApp
// message, then goes to the thank-you page), reading progress and back-to-top.
(function () {
  "use strict";

  var NUMBER = "917502345777";
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  function wa(text) { return "https://wa.me/" + NUMBER + "?text=" + encodeURIComponent(text); }
  function guests() { var g = $("#fb-guests"); return g ? g.value : ""; }
  function cottage() { var a = $(".fb-opt.is-active"); return a ? a.getAttribute("data-name") : ""; }

  function message(name) {
    var text = "Hi, I checked your 4 Bedroom Cottage in Kodaikanal page";
    if (name) text += " and I am interested in " + name;
    var g = guests();
    return text + (g ? ". We are " + g + " guests. Please share availability and the price." : ". Please share availability and the price.");
  }

  function refreshLinks() {
    $$("[data-fb-wa]").forEach(function (a) { a.href = wa(message(a.getAttribute("data-fb-wa"))); });
    var reserve = $("#fb-reserve");
    if (reserve) reserve.href = wa(message(cottage()));
  }

  // Cottage picker
  var opts = $$(".fb-opt");
  function pick(btn) {
    opts.forEach(function (o) { o.classList.toggle("is-active", o === btn); o.setAttribute("aria-pressed", o === btn ? "true" : "false"); });
    var img = $("#fb-stage-img");
    if (img) { img.src = btn.getAttribute("data-img"); img.alt = btn.getAttribute("data-name") + " in Kodaikanal"; }
    $("#fb-stage-name").textContent = btn.getAttribute("data-name");
    $("#fb-stage-tag").textContent = btn.getAttribute("data-tag");
    $("#fb-spec-sleeps").textContent = btn.getAttribute("data-sleeps");
    $("#fb-spec-feature").textContent = btn.getAttribute("data-feature");
    var sel = $("#fb-cottage");
    if (sel) sel.value = btn.getAttribute("data-name");
    refreshLinks();
  }
  opts.forEach(function (o) { o.addEventListener("click", function () { pick(o); }); });

  // Tabs
  var tabs = $$(".fb-tab");
  function showTab(tab, focus) {
    tabs.forEach(function (t) {
      var on = t === tab;
      t.setAttribute("aria-selected", on ? "true" : "false");
      t.tabIndex = on ? 0 : -1;
      var panel = document.getElementById(t.getAttribute("aria-controls"));
      if (panel) {
        panel.hidden = !on;
        panel.classList.toggle("is-in", on);
      }
    });
    if (focus) tab.focus();
  }
  tabs.forEach(function (t, i) {
    t.addEventListener("click", function () { showTab(t, false); });
    t.addEventListener("keydown", function (e) {
      var n = tabs.length, to = null;
      if (e.key === "ArrowRight") to = tabs[(i + 1) % n];
      else if (e.key === "ArrowLeft") to = tabs[(i - 1 + n) % n];
      else if (e.key === "Home") to = tabs[0];
      else if (e.key === "End") to = tabs[n - 1];
      if (to) { e.preventDefault(); showTab(to, true); }
    });
  });
  // Links that point at a section inside another tab open that tab first.
  $$("a[href^='#']").forEach(function (a) {
    a.addEventListener("click", function () {
      var target = document.getElementById(a.getAttribute("href").slice(1));
      var panel = target && target.closest(".fb-panel");
      if (panel && panel.hidden) {
        var tab = document.getElementById(panel.getAttribute("aria-labelledby"));
        if (tab) showTab(tab, false);
      }
    });
  });

  // FAQ accordion
  $$(".fb-faq-q").forEach(function (q) {
    q.addEventListener("click", function () {
      var item = q.parentNode, open = !item.classList.contains("is-open");
      item.classList.toggle("is-open", open);
      q.setAttribute("aria-expanded", open ? "true" : "false");
    });
  });

  // Enquiry form
  var form = $("#fb-form");
  if (form) {
    var g = $("#fb-guests"), c = $("#fb-cottage");
    if (g) g.addEventListener("change", refreshLinks);
    if (c) c.addEventListener("change", function () {
      var match = opts.filter(function (o) { return o.getAttribute("data-name") === c.value; })[0];
      if (match) pick(match);
      refreshLinks();
    });
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      if (!form.checkValidity()) { form.reportValidity(); return; }
      var date = $("#fb-date").value, msg = $("#fb-msg").value.trim();
      var text = "Hi, I'd like a quote for a 4 bedroom cottage in Kodaikanal.\n" +
        "Name: " + $("#fb-name").value.trim() + "\n" +
        "Phone: " + $("#fb-phone").value.trim() +
        (date ? "\nCheck-in: " + date : "") +
        "\nGuests: " + guests() +
        "\nCottage: " + (c ? c.value : "") +
        (msg ? "\nMessage: " + msg : "");
      if (window.ckLeadForm) window.ckLeadForm(form);
      window.open(wa(text), "_blank", "noopener");
      if (window.ckThankYouUrl) location.href = window.ckThankYouUrl();
    });
  }

  // Progress bar and back-to-top
  var bar = $(".fb-progress span"), top = $(".fb-top");
  function onScroll() {
    var h = document.documentElement, max = h.scrollHeight - h.clientHeight;
    if (bar) bar.style.width = (max > 0 ? (h.scrollTop / max) * 100 : 0) + "%";
    if (top) top.classList.toggle("is-on", h.scrollTop > 700);
  }
  window.addEventListener("scroll", onScroll, { passive: true });
  if (top) top.addEventListener("click", function () { window.scrollTo({ top: 0, behavior: "smooth" }); });
  onScroll();

  refreshLinks();
})();
