// 3 Bedroom Cottage in Kodaikanal: cottage picker, tab bar, floor-plan
// hotspots, guest-fit selector, FAQ, enquiry card and page chrome.
(function () {
  "use strict";

  var NUMBER = "917502345777";

  function wa(message) {
    return "https://wa.me/" + NUMBER + "?text=" + encodeURIComponent(message);
  }

  function $(sel, root) { return (root || document).querySelector(sel); }
  function $$(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

  // Cottage picker (showcase panel)
  var show = $(".c3-show");
  if (show) {
    var img = $("[data-bind='img']", show);
    var name = $("[data-bind='name']", show);
    var tag = $("[data-bind='tagline']", show);
    var guests = $("[data-bind='guests']", show);
    var setting = $("[data-bind='setting']", show);
    var note = $("[data-bind='note']", show);
    var reserve = $("[data-bind='wa']", show);
    var opts = $$(".c3-opt", show);

    opts.forEach(function (b) {
      b.addEventListener("click", function () {
        opts.forEach(function (o) { o.classList.remove("is-active"); o.setAttribute("aria-pressed", "false"); });
        b.classList.add("is-active");
        b.setAttribute("aria-pressed", "true");
        var d = b.dataset;
        img.src = d.img;
        img.alt = d.alt;
        name.textContent = d.name;
        tag.textContent = d.tagline;
        guests.textContent = d.guests;
        setting.textContent = d.setting;
        note.textContent = d.note;
        reserve.href = wa("Hi, I'd like rates for " + d.name + " in Kodaikanal (3 bedroom).");
      });
    });
  }

  // Tab bar: highlight the section in view
  var tabs = $$(".c3-tab");
  if (tabs.length && "IntersectionObserver" in window) {
    var map = {};
    tabs.forEach(function (t) { map[t.getAttribute("href").slice(1)] = t; });
    var spy = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        tabs.forEach(function (t) { t.classList.remove("is-on"); });
        var t = map[en.target.id];
        if (t) {
          t.classList.add("is-on");
          if (t.scrollIntoView && t.parentNode.scrollWidth > t.parentNode.clientWidth) {
            t.parentNode.scrollLeft = t.offsetLeft - 20;
          }
        }
      });
    }, { rootMargin: "-30% 0px -60% 0px" });
    Object.keys(map).forEach(function (id) {
      var s = document.getElementById(id);
      if (s) spy.observe(s);
    });
  }

  // FAQ accordion
  $$(".c3-faq-q").forEach(function (q) {
    q.addEventListener("click", function () {
      var item = q.parentNode;
      var open = item.classList.toggle("is-open");
      q.setAttribute("aria-expanded", open ? "true" : "false");
    });
  });

  // Floor-plan hotspots
  var hotspots = $$(".c3-hot");
  var panel = document.getElementById("c3-hot-panel");
  if (hotspots.length && panel) {
    var pt = $("strong", panel);
    var px = $("p", panel);
    hotspots.forEach(function (btn) {
      btn.addEventListener("click", function () {
        hotspots.forEach(function (b) { b.setAttribute("aria-pressed", "false"); });
        btn.setAttribute("aria-pressed", "true");
        pt.textContent = btn.dataset.title;
        px.textContent = btn.dataset.text;
      });
    });
  }

  // Guest-fit selector
  var chips = $$(".c3-chips button");
  var out = document.getElementById("c3-fit-out");
  if (chips.length && out) {
    var verdicts = {
      "4": ["Gladstone is the match", "Gladstone takes 4 to 12 guests. The other cottages start at 6."],
      "6": ["Fits comfortably", "Gladstone, Ryan and KSK all take 6 guests with room to spare."],
      "8": ["Fits comfortably", "Gladstone, Ryan and KSK each take 8 guests across three bedrooms."],
      "10": ["Fits well", "Gladstone, Ryan and KSK take up to 12, so 10 guests is well within range."],
      "12": ["Fits at the upper limit", "Gladstone, Ryan and KSK take up to 12. Ask us about extra mattresses before you book."],
      "13": ["Ask about two cottages", "For 13 or more we suggest two cottages close together. Send your dates and we check availability."]
    };
    var ft = $("strong", out);
    var fp = $("p", out);
    var fa = $("a", out);

    var pick = function (btn) {
      chips.forEach(function (b) { b.setAttribute("aria-pressed", "false"); });
      btn.setAttribute("aria-pressed", "true");
      var n = btn.dataset.n;
      ft.textContent = verdicts[n][0];
      fp.textContent = verdicts[n][1];
      fa.href = wa("Hi, we are " + (n === "13" ? "13 or more" : n) + " guests and need a 3 bedroom cottage in Kodaikanal. Please share options.");
    };
    chips.forEach(function (b) { b.addEventListener("click", function () { pick(b); }); });
    pick($(".c3-chips button[data-n='8']"));
  }

  // Sticky enquiry card -> pre-filled WhatsApp message
  var form = document.getElementById("c3-form");
  if (form) {
    var today = new Date();
    var iso = today.getFullYear() + "-" + ("0" + (today.getMonth() + 1)).slice(-2) + "-" + ("0" + today.getDate()).slice(-2);
    $$("input[type='date']", form).forEach(function (d) { d.min = iso; });

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var f = form.elements;
      var phone = (f.phone.value || "").trim();
      if (!(f.name.value || "").trim() || !/^[0-9+\s\-]{10,15}$/.test(phone)) {
        f.name.focus();
        form.reportValidity && form.reportValidity();
        return;
      }
      var m = "Hi, I'd like rates for a 3 bedroom cottage in Kodaikanal.";
      m += "\nName: " + f.name.value.trim() + "\nPhone: " + phone;
      if (f.checkin.value) m += "\nCheck-in: " + f.checkin.value;
      if (f.checkout.value) m += "\nCheck-out: " + f.checkout.value;
      m += "\nGuests: " + f.guests.value + "\nCottage: " + f.cottage.value;
      if ((f.message.value || "").trim()) m += "\nNote: " + f.message.value.trim();
      window.open(wa(m), "_blank", "noopener");
    });
  }

  // Page chrome: progress bar and back-to-top
  var bar = $(".c3-progress span");
  var top = $(".c3-top");
  var ticking = false;
  function onScroll() {
    var h = document.documentElement;
    var max = h.scrollHeight - h.clientHeight;
    if (bar && max > 0) bar.style.transform = "scaleX(" + Math.min(window.scrollY / max, 1) + ")";
    if (top) top.classList.toggle("is-show", window.scrollY > 900);
    ticking = false;
  }
  window.addEventListener("scroll", function () {
    if (!ticking) { ticking = true; requestAnimationFrame(onScroll); }
  }, { passive: true });
  if (top) top.addEventListener("click", function () { window.scrollTo({ top: 0, behavior: "smooth" }); });
})();
