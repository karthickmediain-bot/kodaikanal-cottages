// Page-aware WhatsApp messages + thank-you redirect.
// Shared widgets (floating WhatsApp button, default enquiry link) are the
// same on every page, so this sets their message from the page being viewed.
(function () {
  "use strict";

  var NUMBER = "917502345777";

  // slug (file name without .html) -> what the visitor was looking at
  var PAGES = {
    "index": ["Cottages Kodaikanal home", "Hi, I was checking your website and I need a quote for a cottage stay in Kodaikanal."],
    "hotels-in-kodaikanal": ["Hotels in Kodaikanal", "Hi, I checked your Hotels in Kodaikanal page and I need a quote."],
    "resorts-in-kodaikanal": ["Resorts in Kodaikanal", "Hi, I checked your Resorts in Kodaikanal page and I need a quote."],
    "home-stay-in-kodaikanal": ["Kodaikanal Homestay", "Hi, I checked your Kodaikanal Homestay page and I need a quote."],
    "dormitory-in-kodaikanal": ["Dormitory Stay", "Hi, I checked your Dormitory Stay page and I need a quote for beds."],
    "tentstay-in-kodaikanal": ["Tent Stay", "Hi, I checked your Tent Stay page and I need a quote."],
    "groupstay-in-kodaikanal": ["Group Stay", "Hi, I checked your Group Stay page and I need a quote for our group."],
    "lodges-in-kodaikanal": ["Lodges in Kodaikanal", "Hi, I checked your Lodges in Kodaikanal page and I need a quote."],
    "villas-in-kodaikanal": ["Villas in Kodaikanal", "Hi, I checked your Villas in Kodaikanal page and I need a quote."],
    "tour-packages-in-kodaikanal": ["Tour Packages", "Hi, I checked your Kodaikanal Tour Packages page and I need a quote."],
    "sleeping-tour-packages-in-kodaikanal": ["Tour Packages", "Hi, I checked your Kodaikanal Tour Packages page and I need a quote."],
    "kodaikanal-one-day-trip-package": ["1 Day Tour Package", "Hi, I checked your Kodaikanal 1 Day Tour Package page and I need a quote."],
    "kodaikanal-2-days-tour-package": ["2 Day Tour Package", "Hi, I checked your Kodaikanal 2 Day Tour Package page and I need a quote."],
    "kodaikanal-tour-packages-for-3-days": ["3 Day Tour Package", "Hi, I checked your Kodaikanal 3 Day Tour Package page and I need a quote."],
    "kodaikanal-group-tour-package": ["Group Tour Package", "Hi, I checked your Kodaikanal Group Tour Package page and I need a quote."],
    "kodaikanal-berijam-forest-tour": ["Berijam Lake Tour", "Hi, I checked your Berijam Lake Tour page and I need a quote."],
    "kodaikanal-park-tour": ["Park Tour", "Hi, I checked your Kodaikanal Park Tour page and I need a quote."],
    "kodaikanal-pepper-falls-tour": ["Pepper Falls Tour", "Hi, I checked your Pepper Falls adventure tour page and I need a quote."],
    "kodaikanal-picnic-tour": ["Picnic Tour", "Hi, I checked your Kodaikanal Picnic Tour page and I need a quote."],
    "kodaikanal-valley-tour": ["Valley Tour", "Hi, I checked your Kodaikanal Valley Tour page and I need a quote."],
    "kodaikanal-village-tour": ["Village Tour", "Hi, I checked your Kodaikanal Village Tour page and I need a quote."],
    "kodaikanal-jeep-safari": ["Jeep Safari", "Hi, I checked your Kodaikanal Jeep Safari page and I need a quote."],
    "cabs-in-kodaikanal": ["Cab Service", "Hi, I checked your Cab Service in Kodaikanal page and I need a fare quote."],
    "kodaikanal-call-taxi": ["Taxi Service", "Hi, I checked your Taxi Service in Kodaikanal page and I need a taxi quote."],
    "car-hire-in-kodaikanal": ["Car Hire", "Hi, I checked your Car Hire in Kodaikanal page and I need a quote."],
    "car-rental-in-kodaikanal": ["Car Rental", "Hi, I checked your Car Rental in Kodaikanal page and I need a quote."],
    "travels-in-kodaikanal": ["Travels", "Hi, I checked your Travels in Kodaikanal page and I need a trip quote."],
    "coimbatore-to-kodaikanal": ["Coimbatore to Kodaikanal", "Hi, I checked your Coimbatore to Kodaikanal page and I need a cab quote."],
    "madurai-to-kodaikanal": ["Madurai to Kodaikanal", "Hi, I checked your Madurai to Kodaikanal page and I need a cab quote."],
    "dindigul-to-kodaikanal": ["Dindigul to Kodaikanal", "Hi, I checked your Dindigul to Kodaikanal page and I need a cab quote."],
    "palani-to-kodaikanal": ["Palani to Kodaikanal", "Hi, I checked your Palani to Kodaikanal page and I need a cab quote."],
    "kodai-road-to-kodaikanal": ["Kodai Road to Kodaikanal", "Hi, I checked your Kodai Road to Kodaikanal page and I need a cab quote."]
  };

  function slugOf(path) {
    var s = (path || location.pathname).replace(/\/+$/, "").split("/").pop().replace(/\.html$/, "");
    return s || "index";
  }

  function link(message) {
    return "https://wa.me/" + NUMBER + "?text=" + encodeURIComponent(message);
  }

  var slug = slugOf();
  var page = PAGES[slug];

  // Used by forms: where to send the visitor after an enquiry goes out.
  window.ckThankYouUrl = function (service) {
    var q = "?from=" + encodeURIComponent(slug) + (service ? "&service=" + encodeURIComponent(service) : "");
    return "thank-you.html" + q;
  };

  function applyPageMessages() {
    if (!page) return;
    var floating = document.querySelector(".floating-cta-whatsapp");
    if (floating) floating.href = link(page[1]);

    // Default enquiry-form link, unless the page already set its own label.
    var qf = document.querySelector("[data-qf-whatsapp]");
    if (qf && !document.body.dataset.enquiryLabel) qf.href = link(page[1]);
  }

  function applyThankYou() {
    var params = new URLSearchParams(location.search);
    var from = PAGES[params.get("from")];
    var service = (params.get("service") || "").slice(0, 60);
    var tag = document.querySelector("[data-ty-service]");
    var label = service || (from && from[0]);
    if (tag && label) tag.textContent = " for " + label;

    var wa = document.querySelector("[data-ty-whatsapp]");
    if (wa) {
      wa.href = link(
        label
          ? "Hi, I just sent an enquiry for " + label + " on your website. Please share the details and price."
          : "Hi, I just sent an enquiry on your website. Please share the details and price."
      );
    }
  }

  function init() {
    if (slug === "thank-you") applyThankYou();
    else applyPageMessages();
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
  document.addEventListener("partials:loaded", init);
})();
