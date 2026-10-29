# 3 Bedroom Cottage in Kodaikanal: Design Plan

Page: `3-bedroom-cottage-in-kodaikanal.html`
Date: 2026-10-28
Status: draft for approval. No copy is written yet.
Keyword source: `keyword analysis/3-bedroom-cottage-in-kodaikanal.md` plus the live-page terms (see SEO).

## Identity

- **Subject:** Cottages Kodaikanal, a local booking team for private cottages.
- **Audience:** two families, or a group of 6 to 10, planning a hill trip and comparing a whole cottage against three hotel rooms.
- **Primary action:** tap WhatsApp with dates and guest count. There is no online checkout on this site, so every conversion element ends in a pre-filled WhatsApp message.
- **Design idea:** the page is built around the house itself. A simple floor plan (three bedrooms, living room, kitchen, sit-out) acts as the page's signature visual, and the rest of the page answers "will this house fit us?" in the order a booker asks it.

## Token system

Inherits the site's teal and amber so the page feels like Cottages Kodaikanal, but with a deeper pine and a flat, paper-like surface instead of the card-and-shadow look used on the stay pages.

| Token | Hex | Role |
|---|---|---|
| ink | `#14262c` | Body text, headings |
| pine | `#145066` | Brand primary, dark bands, links |
| mist | `#eaf7fa` | Alternate section background |
| paper | `#ffffff` | Base background |
| amber | `#f5a623` | The only CTA-fill colour on the page, plus highlights |
| whatsapp | `#1f9d55` | WhatsApp button only |

- **Type:** Bricolage Grotesque (display: 600/700) for headings and numbers, Poppins (body: 400/500) which the site already loads. Two families.
- **Scale:** base 17px mobile / 18px desktop, ratio 1.25. H1 40/56px, H2 28/38px, H3 20/24px, small 14px.
- **Spacing:** 8px base unit. Section padding 56px mobile / 104px desktop. Headline to subhead gap 12px, subhead to content 32px.
- **Restraint:** 6 colours, 2 radii (6px controls, 14px images), 2 font families, no entrance animation except a one-time count-up on the trust strip.

## Conversion system (applies to the whole page)

1. **Sticky action bar.** Mobile: fixed bottom bar with "WhatsApp for rates" (green) and "Call" buttons, hidden while the hero is visible. Desktop: the same as a slim top bar after the hero scrolls away.
2. **One CTA fill colour.** Amber is the only filled button colour; WhatsApp green appears only on the WhatsApp action. Secondary actions are text links with an underline.
3. **CTA cadence.** A primary CTA appears every 2 to 3 sections: hero, listings, price, how to book, final. Never more than 900px of scroll without one.
4. **Message pre-fill.** Every CTA carries context. Hero form sends dates and guests; listing buttons send the cottage name; price section sends "3 bedroom cottage price for [dates]". Uses `wa.me/917502345777` as on other pages.
5. **Friction cut.** No form fields beyond check-in date, number of guests and a name. No account, no payment wall; say so next to the button.
6. **Objection placement.** Price (S6), "is it cheaper than hotels?" (S4) and "who cooks?" (S8) all appear before the second CTA, since these are the three reasons visitors leave.

## Section-by-section plan

Page order changed from the approved copy plan: the **listing cards move up to section 3** so visitors see bookable properties before they read explanations. The copy plan's word counts stay the same.

### 1. Hero, asymmetric with enquiry card
- **Job:** state what it is, who it is for, and take the first enquiry.
- **Layout:** photo-led. Left 60%: H1, 60-word paragraph, three fact chips ("Sleeps 6 to 10", "Full kitchen", "Whole house, private"). Right 40%: a compact enquiry card (check-in date, guests stepper, "Get rates on WhatsApp" amber button). On mobile the card sits under the paragraph.
- **Image:T:text:** 60% image (`images/properties/cottage-showcase-3bhk.webp` as full-bleed background with a pine overlay on the text side).
- **Type:** H1 Bricolage 700, 56px desktop. Chips in Poppins 500, 14px, hairline border, no fill.
- **SEO:** the only `<h1>`; `<header>` with `<section aria-label="3 bedroom cottage overview">`; alt text describes the cottage exterior.
- **Spacing:** 96 to 128px vertical on desktop, 48 to 64px mobile.
- **This is the page's heaviest visual moment.** Nothing later is allowed to out-weigh it.

### 2. Trust strip, stat band
- **Job:** one quick credibility hit before the listings.
- **Layout:** four large numbers on a pine band, no cards or icons. The numbers are placeholders until you supply real ones: years operating, cottages listed, guests hosted, average WhatsApp reply time. Do not publish numbers we cannot back.
- **Image:text:** 0% image; typography only.
- **SEO:** `<section aria-label="Why guests book with us">`, no heading.
- **Spacing:** 56px vertical.

### 3. Our 3-bedroom cottages, listing cards (moved up)
- **Job:** let the visitor pick a property immediately.
- **Layout:** one large featured card (first listing, spans two columns) and four smaller cards in a 2x2 grid. Each card: photo, name, capacity, bathrooms, kitchen yes/no, distance from lake, price band, and a "WhatsApp this cottage" button. Five existing properties: Gladstone, Ryan, KSK, Lakshmi Villa, Smilena.
- **Image:text:** 50%. All photos a single 4:3 crop; mixed ratios fail.
- **Cards:** flat surface with hairline border, no shadow, so it does not match the identical-shadow tell.
- **SEO:** `<h2>`, each card an `<article>` with `<h3>` as the cottage name; alt text names the cottage and the room shown.
- **Keywords:** "cottages for lease in kodaikanal" once in the intro.
- **Facts needed from you:** capacity, bathrooms, kitchen, lake distance and price band for each of the five.

### 4. Kodaikanal Holiday Home for Two Families, floor-plan feature (signature)
- **Job:** show what "3 bedroom" actually means.
- **Layout:** left 55%: a clean line-drawn SVG floor plan (3 bedrooms, 2 or 3 bathrooms, living, kitchen, sit-out) with numbered hotspots you can tap to reveal a one-line description and a photo. Right 45%: H2, short paragraph, bed-configuration list ("Bedroom 1: double, Bedroom 2: double, Bedroom 3: two singles"). Hotspot numbers are real here (sequential tour), so numbering is justified.
- **Image:text:** 55:45.
- **Photos used:** `images/interiors/bedroom-timber-2.webp`, `living-room-sofas.webp`, `kitchen.webp`.
- **SEO:** `<h2>`; the SVG gets `role="img"` and a text alternative; keyword "3 bedroom cottage in kodaikanal" once.

### 5. Private Cottages in Kodaikanal, full-bleed with inset panel
- **Job:** sell privacy and whole-house use.
- **Layout:** full-width photo (`images/interiors/garden-play-area.webp`) with a pine text panel inset on the right corner. Panel: H2, 100 words, text link "See photos on WhatsApp".
- **Image:text:** 75:25. A rhythm break after the floor plan.
- **SEO:** `<h2>`.

### 6. Best Cottage in Kodaikanal for Family, comparison table (objection killer)
- **Job:** answer "is a cottage cheaper and better than three hotel rooms?"
- **Layout:** a 3-column table: Cottage (3 bedrooms) vs 3 hotel rooms vs 2 homestay rooms. Rows: shared living space, kitchen, privacy, cost split per family, parking, noise. The cottage column has an amber top rule. Real rates fill the cost row once supplied.
- **Image:text:** 0%.
- **SEO:** `<h2>`, literal `<table>` with `<caption>`.
- **CTA:** a text link "Get a side-by-side quote on WhatsApp".

### 7. Group Stay for 6 to 10 Guests, guest-fit selector
- **Job:** help groups self-qualify.
- **Layout:** row of chips (4, 6, 8, 10, 12+). Tapping one shows a one-line verdict ("Fits comfortably", "Fits with extra mattresses", "Ask about two cottages") and a WhatsApp button with the number pre-filled. Small supporting paragraph left; link to the Group Stay page.
- **Image:text:** 10%.
- **SEO:** `<h2>`; chips are `<button>` elements; the verdict uses `aria-live="polite"`.

### 8. Cottage Near Kodaikanal Lake, distance strip
- **Job:** location confidence.
- **Layout:** a horizontal strip of 4 to 5 place-and-minutes pairs (lake, Coaker's Walk, Pillar Rocks, Bryant Park, bus stand), plus a static map image. Text left, strip right.
- **Image:text:** 35:65 (map image small).
- **SEO:** `<h2>`; distances must be confirmed with drivers before publishing.

### 9. 3 Bedroom Cottages in Kodaikanal with Price, price section
- **Job:** the conversion point. Meets the live-page keyword "with price".
- **Layout:** three price-band cards (Off-season weekday, Regular, Peak April to May and December). No invented figures: use real bands, or show "Ask for today's rate" with the WhatsApp button until you provide numbers. A "what changes the price" list sits beside the cards. The peak card gets an amber label saying "Fills first".
- **Image:text:** 0 to 10%.
- **SEO:** `<h2>`; keywords "kodaikanal cottage price" and "kodaikanal low price cottage" once each; FAQ link.
- **CTA:** the page's main amber button, "Get today's rate on WhatsApp".

### 10. Cottages in Kodaikanal with Kitchen, alternating row
- **Job:** answer self-catering and cook questions.
- **Layout:** image left (`images/interiors/kitchen.webp`), text right, with a short checklist (stove, fridge, utensils, cook on request, groceries delivery). Flips side from section 5.
- **Image:text:** 50:50.
- **SEO:** `<h2>`.

### 11. How to Book, three-step process
- **Job:** remove booking doubt and push the CTA.
- **Layout:** horizontal 3-step line (send dates and guests; get 2 or 3 matching options within the hour; confirm and pay a small advance). Numbering is justified because the steps are sequential. Amber CTA beneath.
- **Image:text:** 0%.
- **SEO:** `<h2>` ("cottage for rent in kodaikanal" once), `<ol>` markup, HowTo schema optional.

### 12. 2, 3 or 4 Bedrooms: Which Size?, size guide
- **Job:** catch visitors who are unsure and keep them in the funnel.
- **Layout:** a compact three-column comparison (2, 3, 4 bedrooms) with sleeps, best for, and a link to the matching page. The 3-bedroom column is emphasised. One line each on **3 bedroom homestay** and **3 bedroom villa** options with links to the Homestay and Villas pages.
- **Image:text:** 0 to 10%.
- **SEO:** `<h2>`; internal links to the 2, 4 and 5 bedroom pages, Villas and Homestay.

### 13. Best Time to Visit Kodaikanal with Family, season band
- **Job:** informational reassurance plus urgency.
- **Layout:** a 12-month strip showing demand, not weather (low/medium/peak using the Keyword Planner seasonality: April and May peak, February and September quiet), with a one-line tip per band. Text above, strip below.
- **Image:text:** 0%.
- **SEO:** `<h2>`.
- **CTA:** "Check dates for [peak month]".

### 14. FAQ
- **Layout:** a single column accordion, 6 questions, first one open. Button text links to WhatsApp after the last answer.
- **SEO:** `<h2>`, `<details>` markup, FAQPage schema matching on-page text exactly.

### 15. Final CTA, pine band
- **Layout:** full-width pine band, H2, one line, amber button, phone number under it. Lightest section: no image.
- **SEO:** `<h2>`.

## Visual flow summary

Rhythm by section: photo-led hero, number band, card grid, plan-and-text split, full-bleed image, table, chips, strip, price cards, alternating row, steps, table, strip, accordion, band. No layout repeats back to back, and the two heaviest-text sections (table, chips) are separated by a full-bleed image. Eye path is Z across the hero (headline, enquiry card, photo, CTA) and a left-aligned F pattern after that. The terminal point is the final CTA band at the bottom right of the Gutenberg diagram.

## SEO structure

- **H1:** 3 Bedroom Cottage in Kodaikanal for Families and Groups
- **H2s (in page order):** Our 3 Bedroom Cottages; Kodaikanal Holiday Home for Two Families; Private Cottages in Kodaikanal; Best Cottage in Kodaikanal for Family; Cottages in Kodaikanal for Group Stay, 6 to 10 Guests; Cottage Near Kodaikanal Lake; 3 Bedroom Cottages in Kodaikanal with Price; Cottages in Kodaikanal with Kitchen; How to Book a Cottage for Rent in Kodaikanal; 2, 3 or 4 Bedroom Cottage: Which Size?; Best Time to Visit Kodaikanal with Family; FAQ.
- **Live-page keywords carried over** (from the current meta keywords tag):
  - "3 bedroom cottages in kodaikanal with price" becomes the price H2.
  - "3 bedroom homestay in kodaikanal" and "3 bedroom villa in kodaikanal" are mentioned once each in the size guide, with links to the Homestay and Villas pages.
  - "three bedroom cottage" is used once in running copy as a natural variant.
  - "3 bed rooms" and "3 bed room" (spaced) are **not** used. They are likely misspellings, Keyword Planner shows no data for them, and they would read as errors.
- **Schema:** LocalBusiness/LodgingBusiness for Cottages Kodaikanal, ItemList for the five listings, FAQPage, BreadcrumbList. Remove the meta keywords tag.
- **Alt text:** descriptive, one per image, naming the cottage and the room.
- **Noindex:** keep until launch, as on other pages.

## Self-critique log

- Dropped the usual card-and-soft-shadow treatment for listings in favour of flat hairline borders.
- Avoided cream plus serif plus terracotta; the palette stays on the site's teal and amber.
- Numbered markers appear only where order is real (floor-plan hotspots, booking steps).
- Removed ALL-CAPS eyebrow labels and arrow-glued CTAs from the plan.
- Image ratios vary by purpose (60% hero, 0% stat band and table, 75% full-bleed), not a flat 50/50.
- Entrance animations limited to one count-up; no per-section fade-ins.
- Moved listings above the explanatory sections after checking against the conversion goal. This changes the approved copy order and needs your sign-off.

## Open items before build

- Real facts for the five listings: capacity, bathrooms, kitchen, lake distance, price band, extras.
- Real trust numbers for section 2, or drop the strip.
- Price bands for section 9, or keep "Ask for today's rate".
- Confirm distances in section 8 with drivers.
- Approve the reorder (listings to position 3).
- Decide whether to draw the floor plan as a generic layout or per cottage.
