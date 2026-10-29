/* Travel guide hero enquiry form: builds a WhatsApp message from its fields. */
(function () {
  var WA = "https://wa.me/917502345777?text=";
  document.querySelectorAll("form[data-tg-form]").forEach(function (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var lines = [form.getAttribute("data-intro")];
      form.querySelectorAll("[data-label]").forEach(function (f) {
        if (f.value.trim()) lines.push(f.getAttribute("data-label") + ": " + f.value.trim());
      });
      window.open(WA + encodeURIComponent(lines.join("\n")), "_blank", "noopener");
    });
  });
})();
