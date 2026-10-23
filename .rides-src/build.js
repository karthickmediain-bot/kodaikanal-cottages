// Assembles the vehicle-hire and pickup/drop pages. Keeps each page's original
// <head> (SEO meta + schema) from git HEAD, swaps the page CSS, and wraps the
// hand-written body in the shared shell (header, icon sprite, footer, scripts).
// Usage: node .rides-src/build.js [slug ...]
const fs = require("fs");
const path = require("path");
const { execSync } = require("child_process");

const root = path.resolve(__dirname, "..");
const sprite = fs.readFileSync(path.join(__dirname, "sprite.html"), "utf8").trim().replace(/\r?\n/g, "");
const only = process.argv.slice(2);

// Tabs (Overview / Reviews / Location / FAQs), same idea as the tour packages
// page. Sections stay hand-written in bodies/; this step wraps them in panels
// and adds the shared Reviews + Location panels.
const ADDRESS = "Cottages Kodaikanal, Near Zion School Junction, MM Street, Lawsghat Road, Kodaikanal - 624101.";
const DIRECTIONS_FROM = {
  "madurai-to-kodaikanal": "Madurai",
  "coimbatore-to-kodaikanal": "Coimbatore",
  "dindigul-to-kodaikanal": "Dindigul",
  "palani-to-kodaikanal": "Palani",
  "kodai-road-to-kodaikanal": "Kodai Road Railway Station",
};
const TABS = [
  ["overview", "Overview", "i-list"],
  ["reviews", "Reviews", "i-chat"],
  ["location", "Location", "i-pin"],
  ["faqs", "FAQs", "i-help"],
];
const GUEST_REVIEWS = [
  ["We booked a family cottage for five of us and it never felt cramped. Staff arranged everything from bonfire wood to breakfast timing.", "Priya R.", "Chennai"],
  ["Traveled from Bengaluru for a college reunion &mdash; the group stay cottage fit all ten of us with room to spare.", "Arjun K.", "Bengaluru"],
  ["Our honeymoon stay had exactly the privacy we wanted &mdash; hills outside the window, a fireplace for the cold evenings.", "Meena &amp; Karthik", "Coimbatore"],
];
const ROUTE_CITIES = [
  ["coimbatore-to-kodaikanal", "Coimbatore"], ["madurai-to-kodaikanal", "Madurai"], ["dindigul-to-kodaikanal", "Dindigul"],
  ["palani-to-kodaikanal", "Palani"], ["kodai-road-to-kodaikanal", "Kodai Road"],
];

function reviewsSection() {
  return `<section class="rd-sec" id="reviews">
          <p class="rd-kicker">Guest reviews</p>
          <h2>What Our Guests Say</h2>
          <p class="rd-lede">Our cabs and jeeps are run by the same family that hosts these guests.</p>
          <div class="rd-revs" data-stagger>
${GUEST_REVIEWS.map(([q, n, c]) => `            <figure class="rd-rev"><div class="rd-stars" aria-label="5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div><blockquote>${q}</blockquote><figcaption>${n}<span>${c}</span></figcaption></figure>`).join("\n")}
          </div>
        </section>`;
}

function locationSection(slug) {
  const from = DIRECTIONS_FROM[slug];
  const dir = from
    ? ` &middot; <a href="https://www.google.com/maps/dir/?api=1&amp;origin=${encodeURIComponent(from)}&amp;destination=Cottages+Kodaikanal,+Lawsghat+Road,+Kodaikanal+624101" target="_blank" rel="noopener">Directions from ${from.replace("Railway Station", "station")}</a>`
    : "";
  return `<section class="rd-sec" id="location">
          <p class="rd-kicker">Find us</p>
          <h2>Where Are We Located?</h2>
          <p class="rd-lede">${ADDRESS}</p>
          <div class="rd-mapframe"><iframe src="https://www.google.com/maps?q=Cottages+Kodaikanal,+Near+Zion+School+Junction,+Lawsghat+Road,+Kodaikanal+624101&amp;output=embed" title="Cottages Kodaikanal on Google Maps" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>
          <p class="rd-note"><a href="https://www.google.com/maps/search/?api=1&amp;query=Cottages+Kodaikanal,+Near+Zion+School+Junction,+Lawsghat+Road,+Kodaikanal+624101" target="_blank" rel="noopener">Open in Google Maps</a>${dir} &middot; <a href="tel:+917502345777">Call +91 75023 45777</a></p>
          <h3 style="margin-top:22px">Where Can You Start Your Trip From?</h3>
          <div class="rd-chips">
${ROUTE_CITIES.map(([s, n]) => `            <a class="rd-chip" href="/${s}"${s === slug ? ' aria-current="page"' : ""}><svg class="rd-i"><use href="#i-route"/></svg>${n}</a>`).join("\n")}
          </div>
        </section>`;
}

function tabify(body, slug) {
  const navRe = /\n\s*<nav class="rd-jump"[\s\S]*?<\/nav>/;
  const asideAt = body.indexOf('<aside class="rd-aside">');
  const nav = body.match(navRe);
  if (!nav || asideAt < 0) throw new Error("unexpected structure in " + slug);
  const navEnd = nav.index + nav[0].length;
  const closeAt = body.lastIndexOf("</div>", asideAt); // closes .rd-main
  let sections = body.slice(navEnd, closeAt);

  const take = (re) => { const m = sections.match(re); if (m) sections = sections.replace(m[0], ""); return m ? m[0] : ""; };
  const faq = take(/\s*<section class="rd-sec" id="faq">[\s\S]*?<\/section>/);
  const ownReviews = take(/\s*<section class="rd-sec[^"]*" id="reviews">[\s\S]*?<\/section>/);
  if (!faq) throw new Error("no faq section in " + slug);

  const tabbar = `
        <div class="rd-tabbar" role="tablist" aria-label="Page sections" data-tabs data-page-tabs>
${TABS.map(([id, label, icon], i) => `          <button type="button" role="tab" class="rd-tab" id="tab-${id}" aria-controls="panel-${id}" aria-selected="${i === 0}"${i ? ' tabindex="-1"' : ""} title="${label}"><svg class="rd-i" aria-hidden="true"><use href="#${icon}"/></svg><span class="rd-tab-label">${label}</span></button>`).join("\n")}
        </div>`;
  const panel = (id, inner, on) =>
    `\n        <div class="rd-panel${on ? " is-in" : ""}" role="tabpanel" id="panel-${id}" aria-labelledby="tab-${id}"${on ? "" : " hidden"}>${inner}\n        </div>`;

  const panels =
    panel("overview", sections.replace(/\s+$/, ""), true) +
    panel("reviews", "\n        " + (ownReviews.trim() || reviewsSection())) +
    panel("location", "\n        " + locationSection(slug)) +
    panel("faqs", faq.replace(/^\s*/, "\n        "));

  return body.slice(0, nav.index) + tabbar + panels + "\n      " + body.slice(closeAt);
}

for (const f of fs.readdirSync(path.join(__dirname, "bodies"))) {
  const slug = f.replace(/\.html$/, "");
  if (only.length && !only.includes(slug)) continue;
  let body = fs.readFileSync(path.join(__dirname, "bodies", f), "utf8");
  const font = (body.match(/<!--font:(.*?)-->/) || [])[1];
  body = body.replace(/<!--font:.*?-->\s*/, "");
  body = tabify(body, slug);

  const orig = execSync(`git show HEAD:${slug}.html`, { cwd: root, encoding: "utf8" });
  let head = orig.slice(0, orig.indexOf("</head>"));
  // Strip anything a previous build injected, so rebuilding from a committed
  // page doesn't duplicate it.
  head = head.replace(/\s*<link rel="stylesheet" href="css\/(jeep-safari|coimbatore-to-kodaikanal|travels-in-kodaikanal|enquiry-form|rides)\.css" \/>/g, "");
  head = head.replace(/\s*<script>document\.documentElement\.className \+= " rd-js";<\/script>/g, "");
  if (font && !head.includes(`family=${font}&`)) head = head.replace("css2?family=Poppins", `css2?family=${font}&family=Poppins`);
  head = head.replace(
    '<link rel="stylesheet" href="css/styles.css?v=hdr" />',
    '<link rel="stylesheet" href="css/styles.css?v=hdr" />\n  <link rel="stylesheet" href="css/enquiry-form.css" />\n  <link rel="stylesheet" href="css/rides.css" />\n  <script>document.documentElement.className += " rd-js";</script>'
  );

  const openEnd = body.indexOf(">") + 1;
  const out =
    head.trimEnd() + "\n</head>\n" + body.slice(0, openEnd) + "\n" +
    '  <div class="rd-progress" aria-hidden="true"><span></span></div>\n' +
    '  <a class="skip-link" href="#main-content">Skip to main content</a>\n' +
    "  " + sprite + "\n" +
    '  <div id="site-header" data-include="partials/header.html"></div>\n  ' +
    body.slice(openEnd).trim() + "\n" +
    '  <button type="button" class="rd-top" aria-label="Back to top">&#8593;</button>\n' +
    '  <div id="site-footer" data-include="partials/footer.html"></div>\n' +
    '  <script src="js/include.js"></script>\n' +
    '  <script src="js/main.js"></script>\n' +
    '  <script src="js/enquiry-form.js"></script>\n' +
    '  <script src="js/rides.js"></script>\n' +
    "</body>\n</html>\n";
  fs.writeFileSync(path.join(root, slug + ".html"), out);
  console.log("built", slug);
}
