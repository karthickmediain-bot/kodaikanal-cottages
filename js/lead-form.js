/* Shared lead capture for the cottage enquiry forms (2, 3, 4 and 5 bedroom pages).
   Each page keeps its own validation and its own WhatsApp message; after its checks pass,
   it calls window.ckLeadForm(form) and this saves the lead the same way for every form.

     <form data-lead="page_quote_form"           lead type saved to the database
           data-lead-id="c3-form"                short id of this form
           data-lead-service="3 bedroom cottage"> what the visitor is asking about
     <input data-field="name">                   field key
   Field keys: name, phone, email, check_in, check_out, guests, cottage, message.
   window.ckLead (js/main.js) attaches the page and traffic source. */
(function () {
  "use strict";

  window.ckLeadForm = function (form) {
    if (!form || !window.ckLead) return;
    var data = {};
    Array.prototype.forEach.call(form.querySelectorAll("[data-field]"), function (el) {
      data[el.getAttribute("data-field")] = (el.value || "").trim();
    });
    var service = form.getAttribute("data-lead-service") || "";
    var guests = (data.guests || "").match(/\d+/);
    window.ckLead({
      lead_type: form.getAttribute("data-lead") || "page_quote_form",
      form_id: form.getAttribute("data-lead-id") || form.id || "form",
      service_interest: service + (data.cottage ? ": " + data.cottage : ""),
      name: data.name, phone: data.phone, email: data.email,
      check_in: data.check_in, check_out: data.check_out,
      guests: guests ? guests[0] : "", guests_text: data.guests,
      message: data.message,
    });
  };
})();
