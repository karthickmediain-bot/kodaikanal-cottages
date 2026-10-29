# 5 Bedroom Cottage in Kodaikanal — Design Plan

Page: `5-bedroom-cottage-in-kodaikanal.html`. Written before any copy or code. Keyword source: [5-bedroom-cottage-in-kodaikanal.md](5-bedroom-cottage-in-kodaikanal.md) plus the six phrases from the live page's meta keywords tag.

## Identity

- **Subject:** one whole house, five bedrooms, for one group, run by a local team (Cottages Kodaikanal, Lawsghat Road).
- **Audience:** a trip organiser (family elder, friend group lead or HR/event planner) who has to answer "will everyone fit, what does it cost, can I book today" for a group chat.
- **Primary action:** WhatsApp or call with dates and headcount. Secondary: the enquiry form.
- **Positioning:** "Sleeps your whole group under one roof, priced up front." The page is built around the organiser's three questions: *does it fit us, what is the price, how do I book*.
- **Unique idea:** a **"Who sleeps where" room plan** (a five-room diagram that sets out how a group splits across the bedrooms) plus **group-size chips** that filter the six cottages and pre-fill the WhatsApp message. No other page on the site does either, and neither is a generic card grid.

## Token System (inherit the site tokens in `css/styles.css`; do not invent new ones)

| Token | Hex | Role |
|---|---|---|
| Primary teal | `#145066` | Headings, dark bands, links |
| Primary dark | `#103e4e` | Hover, footer-adjacent bands |
| Mist | `#eaf7fa` | Alternate section background, chip fill |
| CTA blue | `#22a6d9` | The single filled-button colour (Book, WhatsApp quote, Call) |
| Ink | `#1c1f24` | Body text |
| Highlight cyan | `#4dd9ff` | Tiny accents only (badge dots, active chip underline), never large fills |

- **Type:** Poppins only (already loaded site-wide). Weights 400, 500, 600, 700. Scale 1.25: body 16 / small 14 / h3 20 / h2 32 (24 on mobile) / h1 44 (30 on mobile). Line length 55 to 70 characters.
- **Spacing:** 8px base. Section padding 96/64 desktop/mobile (standard), 64/48 (dense bands), hero 112/56. Card gap 24 desktop, 16 mobile.
- **Restraint ceiling:** 6 colours, 2 radii (12px for cards and images, pill for chips and buttons), 1 font family, 1 motion signature (a 150ms chip and card state change, nothing else). No entrance animations.
- **Why this fits:** it keeps the page visibly part of the site (same teal, same CTA, same footer and header partials) while the layout, not the palette, carries the originality.

## Conversion system (applies to the whole page)

1. **Sticky action bar** on mobile (bottom): `Call` and `WhatsApp quote`, reusing `partials/floating-cta.html`. On desktop, a slim header-attached bar after the hero scrolls out, holding the same two actions.
2. **Pre-filled WhatsApp** in every CTA with page-specific text: "Hi, I'd like a quote for a 5 bedroom cottage in Kodaikanal for [group size] from [dates]." Each property card pre-fills the property name.
3. **CTA repetition after high-intent sections** (spec strip, property cards, price/inclusions, final band). Always the same button style, so the action reads as one system.
4. **Trust row** under the hero: "12 years local" (confirm; it appears on the villas page meta), reply-time promise, real guest rating or review count only if real. No invented numbers.
5. **Friction removal:** no account, no payment wall, "free quote" wording, 24-hour reply promise (confirm).
6. **Thank-you page:** form submissions go to the existing `thank-you.html`.

## Section-by-section plan

Image:text target and layout are set by the section's job. The sequence alternates dense and light on purpose.

### 1. Hero — split, image-led (heaviest moment on the page)
- **Job:** confirm "this is the right page" in 3 seconds and start the quote.
- **Layout:** 55/45. Left: H1, a 60-word paragraph, group-size chips (`10 · 15 · 20 · 30+`), a CTA button. Right: one large photo (`images/properties/cottage-showcase-5bhk.webp`), 12px radius, with a small floating spec card overlapping its lower-left corner (5 bedrooms, sleeps [x], from ₹[x]).
- **Imagery share:** about 55%.
- **Type:** H1 700 at 44. Paragraph 400 at 18, ink.
- **Colour:** white background, teal H1, CTA blue button, mist chips.
- **CTA:** "Get a free quote on WhatsApp", with "or call +91 75023 45777" as a text link.
- **SEO:** the only `<h1>` ("5 Bedroom Cottages in Kodaikanal"), `<header>` plus `<section aria-label="Overview">`, alt text "Five bedroom cottage in Kodaikanal with garden and valley view".

```
+--------------------------------------------------+
| [H1]                         +------------------+ |
| 60-word paragraph            |                  | |
| (10) (15) (20) (30+)         |   hero photo     | |
| [Get a free quote]           |  +------------+  | |
| or call +91 ...              |  | spec card  |  | |
|                              +--+------------+--+ |
| 12 yrs local · 24h reply · free quote            |
+--------------------------------------------------+
```

### 2. Spec strip — stat band (typography only, light)
- **Job:** answer "does it fit us" at a glance.
- **Layout:** a single teal band, five equal cells: `5 bedrooms` | `[x] guests` | `Kitchen or chef` | `Free parking` | `[x] km to Kodaikanal Lake`. Large number or word at 24/700, label at 14/400.
- **Imagery share:** 0%. Padding 64/48.
- **CTA:** a quiet text link: "Check your dates →" no, keep it plain: "Check your dates" with a WhatsApp link.
- **SEO:** `<section aria-label="At a glance">`, a `<dl>` for the five facts.

### 3. How booking works — three-step row (dense, short)
- **Job:** remove the fear of a complicated booking. H2: "Booking a 5 Bedroom Cottage in Kodaikanal".
- **Layout:** three columns joined by a thin line: *Send dates and headcount* → *Get 2 to 3 matching options* → *Confirm and pay the advance*. Each step is 2 lines. The steps are genuinely sequential, so numbering is earned here.
- **Imagery share:** 5% (line icons only). Mist background.
- **CTA:** button "Start on WhatsApp".
- **SEO:** H2, `<ol>`.

### 4. Who sleeps where — signature room plan (visual-led)
- **Job:** show a group how it fits and speak to the family intent. H2: "5 Bedroom Cottages in Kodaikanal for Family".
- **Layout:** 40/60. Left: 80 to 100 words on family fit (play area, chef, safety). Right: a clean SVG plan of five rooms in a 3+2 grid, each with a bed icon and a chip showing a sample split ("Grandparents", "Parents + 2 kids", "Cousins", "Friends"). A small toggle above the plan switches the example between *Family of 12*, *Friends 10* and *Office group 15* (CSS only or a few lines of JS).
- **Imagery share:** 60% (the diagram is the visual).
- **Colour:** mist plan background, teal strokes, highlight cyan on the active toggle.
- **SEO:** SVG with `<title>` and `<desc>` as the alt equivalent, H2, `<figure>` plus `<figcaption>`.

```
+----------------------------------------------+
| H2                | Family 12 | Friends 10 |  |
| copy              | +----+ +----+ +----+      |
|                   | | R1 | | R2 | | R3 |      |
|                   | +----+ +----+ +----+      |
|                   | +----+ +----+             |
|                   | | R4 | | R5 |             |
|                   | +----+ +----+             |
+----------------------------------------------+
```

### 5. The six cottages — filterable cards (the conversion core, dense)
- **Job:** compare and pick. H2: "5 Bedroom Cottage Stay Options in Kodaikanal". Each cottage is an H3.
- **Layout:** a chip bar above (`All · 10 · 15 · 20 · 30+`) that shows or hides cards by capacity (a few lines of JS, with all six visible if JS fails). Below: 3×2 grid on desktop, a horizontal scroll-snap row on mobile. The six properties (Green Hill, Shankar, Moonlight, Inba Holidays, Kalpana, Olive Tree) become cards with: photo (16:10 for all six), capacity chip, one standout feature line, distance to the lake, `From ₹[x]/night`, and two buttons: `WhatsApp this cottage` (pre-filled with its name) and a small `Call`.
- **Imagery share:** 40%. Every photo uses the same aspect ratio.
- **Colour:** white cards on mist, 1px `#dfeaec` border, no shadow stack.
- **SEO:** H2, six H3s, `<article>` per card, `ItemList` schema (flag for the build).
- **Open item:** the photos in `images/properties/` need checking against these six names.

### 6. Compare — table (dense; keep short)
- **Job:** serve the "best for family" comparison intent. H2: "Best 5 Bedroom Cottage in Kodaikanal for Family".
- **Layout:** table with columns Cottage, Sleeps, To lake, Best for, From ₹, and a final column holding the WhatsApp icon link. On mobile it collapses into stacked rows.
- **Imagery share:** 0%. A short intro line of 30 words.
- **SEO:** real `<table>` with `<th scope>`.

### 7. Large group and holiday home — full-bleed photo band (light, breaks the grid pattern)
- **Job:** the group, reunion and corporate intent. H2: "Holiday Home in Kodaikanal for a Large Group Stay".
- **Layout:** full-width photo (`images/interiors/campfire-evening.webp`) with a dark teal gradient on the left half and the copy over it. Three short bullets (reunions, New Year, corporate offsites). A text link to the group-stay page.
- **Imagery share:** 70%. This must stay quieter than the hero (smaller type, no spec card).
- **CTA:** "Plan a group stay" button.
- **Contrast:** the text sits on a solid teal block, never directly on the busy part of the photo.

### 8. Near the lake — distance ruler (graphic, light)
- **Job:** the location intent. H2: "5 Bedroom Cottages Near Kodaikanal Lake".
- **Layout:** one horizontal line with five stops (Chettiar Park, Kodaikanal Lake, Bryant Park, Silver Falls, Vattakanal) and a km or minutes label under each. Copy above, 50 words.
- **Imagery share:** 0%. Mist background.
- **SEO:** H2, `<ul>` styled as a ruler. Confirm distances with drivers.

### 9. Price, rent and what's included — two-column checklist (dense)
- **Job:** the price intent, which is the biggest drop-off point. H2: "5 Bedroom Cottage Price in Kodaikanal", with the H3s "Rent the Whole Cottage" and "Private, With a Kitchen or Chef".
- **Layout:** left: a "From ₹[x] per night" block with what moves the price (season, headcount, dates). Right: two checklists, *Included* and *Extra*. Below: a short rent-terms paragraph.
- **Imagery share:** 5%.
- **CTA:** "Get an exact price for your dates" button, which is the strongest CTA on the page.
- **Open item:** real prices needed. No invented numbers.

### 10. Stay style — home-cooked and host-run (light, optional)
- **Job:** the "5 bedroom homestay" and "group homestay" intent. A short two-image row (kitchen, dining) with 80 words: home-cooked meals, caretaker and chef, house rules.
- **Imagery share:** 50% (`kitchen.webp`, `dining-table-window.webp`).
- **If the page runs long, this folds into section 9.**

### 11. FAQ — accordion (light)
- **Job:** the last objections. H2: "Frequently Asked Questions". Six questions in `<details>`.
- **Imagery share:** 0%. Single column, 720px wide.
- **SEO:** `FAQPage` schema, answers 30 to 40 words.

### 12. Final CTA band + form (heavy colour, short)
- **Job:** capture the visitor who scrolled this far.
- **Layout:** full teal band, 50/50: left, "Tell us your dates and group size, we reply within 24 hours" with call and WhatsApp buttons. Right, the existing `partials/enquiry-form.html` (name, phone, dates, headcount).
- **Imagery share:** 0%. This is the page's terminal point (bottom-right), per the Gutenberg pattern.

## Keyword map (MCP data plus the live page's six phrases)

| Section | Keywords |
|---|---|
| Hero | Brand plus "5 bedroom villa in kodaikanal" (alternate primary), H1 "5 Bedroom Cottages in Kodaikanal" |
| 2 | primary once |
| 3 | kodaikanal cottage booking |
| 4 | cottages in kodaikanal for family |
| 5 | kodaikanal cottage stay, **five bedroom cottage in kodaikanal** (one card), **premium cottages in kodaikanal** (the best-equipped card's description only if true) |
| 6 | best cottage in kodaikanal for family |
| 7 | holiday home in kodaikanal, **large group stay kodaikanal**, cottages in kodaikanal for group stay (one use only) |
| 8 | kodaikanal cottages near lake |
| 9 | kodaikanal cottage price, cottage for rent in kodaikanal, private cottages in kodaikanal, cottages in kodaikanal with kitchen |
| 10 | **5 bedroom homestay in kodaikanal**, **kodaikanal group homestay** |
| 11 FAQ | family stay cottages in kodaikanal with rates, cottages in kodaikanal for 20 persons, kodaikanal cottages lowest price |
| Mention only | **luxury villa in kodaikanal**: one line pointing to the villas page. Do not claim luxury unless a property has it. |

The bold items come from the live page's meta keywords tag. Because the 1.5% cap applies, the live-page phrases replace weaker secondary uses rather than adding to them. A page of about 1,700 words allows about 25 instances in total.

## Visual flow summary

Layout rhythm: split (heavy) → stat band (light) → three-step row (dense) → diagram split (visual) → card grid (dense) → table (dense) → full-bleed photo band (light) → distance ruler (light) → checklist (dense) → photo pair (light) → FAQ (light) → teal band (heavy).
- No three sections in a row repeat a layout. The two dense sections together (5 and 6) are separated from the other dense ones by the light band in 7.
- Eye path: Z-pattern in the hero (CTA at bottom-left of the copy, photo at right), then a vertical F-pattern down the page. The terminal CTA band sits at the bottom right.
- The heaviest moment is the hero. Section 7's photo band is deliberately lower contrast and has no spec card.

## SEO structure summary

```
H1  5 Bedroom Cottages in Kodaikanal
 H2  Booking a 5 Bedroom Cottage in Kodaikanal
 H2  5 Bedroom Cottages in Kodaikanal for Family
 H2  5 Bedroom Cottage Stay Options in Kodaikanal
  H3 Green Hill Resort / Shankar Cottage / Moonlight Cottage / Inba Holidays Villa / Kalpana Cottage / Olive Tree Cottage
 H2  Best 5 Bedroom Cottage in Kodaikanal for Family
 H2  Holiday Home in Kodaikanal for a Large Group Stay
 H2  5 Bedroom Cottages Near Kodaikanal Lake
 H2  5 Bedroom Cottage Price in Kodaikanal
  H3 Rent the Whole Cottage
  H3 Private, With a Kitchen or Chef
 H2  Home-Cooked Meals and Host-Run Stays   (optional)
 H2  Frequently Asked Questions
```

- Schema for the build: `FAQPage`, `ItemList` of the six cottages, `BreadcrumbList` (Home → Villas and Cottages → 5 Bedroom Cottage). Add `LodgingBusiness` only if the details are confirmed.
- The meta keywords tag can go. Google ignores it.
- Alt text: descriptive, one per image, "five bedroom" used in at most two of them.
- Internal links: group-stay page, villas page, home page (for 3 and 4 bedroom sizes), how-to-reach page.

## Self-critique log

- Dropped a "01/02/03 numbered features" idea for the property cards: the cards are not sequential. Numbering stays only in the booking steps, which are.
- Dropped entrance animations, hover lift on cards and the all-caps eyebrow labels above every heading.
- Replaced a repeating image-left, text-right rhythm with the nine different layouts in the flow list.
- Kept the site's teal palette rather than a new "luxury" cream-and-serif palette, because the page has to match the header, footer and the other 30 pages.
- Removed the swimming pool and "luxury cottages" sections from the earlier plan: no property mentions a pool, and "luxury" would overclaim.

## Open items before copy and build

1. Real `From ₹` prices per property, plus extra-guest and tax terms.
2. Confirm the capacities (Green Hill "4 to 100", Shankar "6 to 18") and distances to the lake.
3. Confirm "12 years local", the 24-hour reply promise and any real guest rating.
4. Photos: check `images/properties/` against the six property names and shoot or choose a lake-view and a bedroom shot.
5. Decide whether section 10 stays or folds into section 9.
