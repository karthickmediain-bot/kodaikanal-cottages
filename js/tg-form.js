/* Travel guide hero enquiry form: builds a WhatsApp message from its fields,
   then sends the visitor to the thank-you page. */
(function () {
  var WA = "https://wa.me/917502345777?text=";
  document.querySelectorAll("form[data-tg-form]").forEach(function (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var lines = [form.getAttribute("data-intro")];
      form.querySelectorAll("[data-label]").forEach(function (f) {
        if (f.value.trim()) lines.push(f.getAttribute("data-label") + ": " + f.value.trim());
      });
      var field = function (label) { var el = form.querySelector('[data-label="' + label + '"]'); return el ? el.value.trim() : ""; };
      if (window.ckLead) window.ckLead({
        lead_type: "page_quote_form", form_id: "tg-hero-form", service_interest: form.getAttribute("data-intro"),
        name: field("Name"), phone: field("Phone"), check_in: field("Travel date"), traveller_type: field("Travellers"),
      });
      window.open(WA + encodeURIComponent(lines.join("\n")), "_blank", "noopener");
      if (window.ckThankYouUrl) location.href = window.ckThankYouUrl();
    });
  });
})();
