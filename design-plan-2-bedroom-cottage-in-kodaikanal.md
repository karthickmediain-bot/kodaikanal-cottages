# 2 Bedroom Cottage in Kodaikanal: Design Plan

Page: `2-bedroom-cottage-in-kodaikanal.html`
Date: 2026-10-28
Status: draft, awaiting approval. Content is written after this plan is approved.
Keyword inputs: [keyword analysis/2-bedroom-cottage-in-kodaikanal.md](keyword%20analysis/2-bedroom-cottage-in-kodaikanal.md), plus the live-page keywords listed below.

## Concept: "Find your cottage, then ask for the date"

The page is a decision tool, not a brochure. The visitor is comparing options (commercial investigation) and is close to booking. So the page does three jobs in order: show what a 2-bedroom cottage gives them, let them pick the one that fits their group, and make the enquiry take one tap.

What makes it different from the other cottage and stay pages on the site:
- A **group picker** (Family / Friends / Couples) that re-sorts the cottage cards, instead of a static list of seven cards.
- A **price-first compare table** under the cards, so "with price" is answered on the page and not behind a WhatsApp message.
- A **floor-plan strip** showing the 2-bedroom layout as a diagram (two bedrooms, living, kitchen, porch) instead of another photo row.
- An **enquiry bar that carries the choice**: picking a cottage and group size pre-fills the WhatsApp message.

## Conversion path

1. Hero: one promise and one action ("Check availability"). Phone and WhatsApp are visible without scrolling.
2. Group picker and cottage cards: the visitor sees which cottage fits them.
3. Compare table: price per night, capacity, distance to the lake.
4. Trust and FAQ: remove the last objections (kitchen, pets, distance, price).
5. Final CTA with the same enquiry bar.

Every primary button leads to the same enquiry action. No outbound links in the first three screens.

## Token system (inherited, unchanged)

- Colors: `--color-primary #145066`, `--color-primary-light #1f7592`, `--gradient-cta #22a6d9`, `--color-highlight #4dd9ff`, `--color-steel #cdeef2`, `--color-bg-alt #eaf7fa`, `--color-star #f5a623` (ratings only), `--color-pin #e0483e` (location only), `--color-text #1c1f24`, `--color-text-muted #5c6a6d`, `--color-border #dfeaec`.
- Type: Poppins only. H1 40-48/700, H2 28-32/700, H3 20-22/600, body 16/400.
- Spacing: 8px base, 96px / 56px section padding (desktop / mobile), 1200px container.
- Radii: 12px cards, 999px pills. Motion limited to the entries below.

## Section plan (9 bands)

| # | Band | Construction | Job |
|---|---|---|---|
| 1 | Header | Shared include, unchanged | Navigation |
| 2 | Hero + enquiry card | Full-bleed photo, left-aligned H1, floating enquiry card on the right | Promise and first action |
| 3 | What's included strip | Four-item icon row on `--color-bg-alt`, overlapping the hero edge | Quick answer: beds, kitchen, parking, view |
| 4 | Group picker + cottage cards | Segmented pill control above a card row that re-sorts | Match visitor to cottage |
| 5 | Floor plan + amenities | Two-column: SVG floor plan left, amenity list right | Show the 2-bedroom layout |
| 6 | Price and compare | Table with sticky first column, cheapest row marked | Answer price and capacity |
| 7 | Stay guide (three short blocks) | Three offset text blocks: places to visit, best time, vs homestay | Informational intent without leaving the page |
| 8 | Reviews / trust + FAQ | Review strip, then an accordion | Remove objections |
| 9 | Final CTA + footer | Angled `--color-primary` band, enquiry bar repeated | Close |

### 2. Hero + enquiry card
- Photo of the real cottage (exterior, porch, hills). Dark gradient on the left third for text contrast.
- H1: 2 Bedroom Cottage in Kodaikanal, Private Stay for Families and Groups. One subline: price-from, guests, distance to the lake.
- Enquiry card: check-in date, guests (2 / 4 / 6), cottage dropdown, "Check availability on WhatsApp". Phone button below it.
- Mobile: the card drops under the H1. A sticky bottom bar (Call / WhatsApp) stays on screen for the whole page.
- Trust line under the card: "Prices shown. No booking fee." Only if true. Confirm before building.

### 3. What's included strip
- Four items with icons: two bedrooms with double beds, kitchen, parking, valley view. Final items depend on your amenity list.
- No heading above it. It reads as part of the hero.

### 4. Group picker + cottage cards
- Pills: **Family**, **Friends**, **Couples**. Default is Family (highest search demand).
- Choosing a pill re-sorts the cards and shows a one-line reason on each ("Sleeps 4, kitchen, garden for kids"). Cards that don't fit fade to 50%.
- Cards (7): Happy Point, Zill Breeze, Hillcrest, Apollo, Stone Crest, Blossom Villa, Kodai Mist. Each shows photo, guests, price from, distance to the lake, and a "Choose this cottage" button that fills the enquiry bar.
- Cards are `<article>` elements in the DOM in a fixed order. Sorting only changes the visual order, so the markup stays crawlable.

### 5. Floor plan + amenities
- Simple SVG plan: bedroom 1, bedroom 2, living, kitchen, bathrooms, porch. Hover or tap a room to see its detail.
- Amenity list beside it: kitchen, bonfire, parking, Wi-Fi, geyser, view. Only what the property really has.

### 6. Price and compare
- Table: cottage, guests, bedrooms, price per night (weekday / weekend / peak), distance to the lake. First column sticks on mobile.
- Peak months (April, May) marked, since search demand is 2-3x higher then.
- Row-level "Enquire" button that fills the enquiry bar.
- Owner supplies real rates. Nothing is invented.

### 7. Stay guide
- Three text blocks, deliberately not equal widths: places to visit (short list, link out), best time (small season table), cottage vs homestay (three bullets).
- Each ends with a link to the full page (places, weather, homestay). Links are kept below the booking sections so they don't leak the visitor early.

### 8. Reviews + FAQ
- Review strip with real guest quotes only. If none exist yet, skip it and use the FAQ alone.
- FAQ accordion (6 questions), all answers in the DOM with `hidden` toggled. FAQ schema matches the on-page text.

### 9. Final CTA
- Angled band, same enquiry bar, repeated phone and WhatsApp.
- Static, no motion.

## Interaction inventory

| Element | Interaction |
|---|---|
| Header | Transparent over hero, solid after scroll |
| Enquiry card / bar | Dropdown and date input build the WhatsApp message |
| Group picker | Pill selects, cards re-sort with a 200ms fade |
| Cottage card | "Choose this cottage" sets the cottage in the bar and scrolls to it |
| Floor plan | Room highlight on hover or tap |
| Compare table | Sticky first column, row action fills the bar |
| FAQ | Accordion, one open at a time |
| Mobile bar | Fixed bottom Call / WhatsApp |

## Keyword placement in the design

Inputs from the keyword file and the live page, mapped to bands:

| Band | Keywords |
|---|---|
| Hero | 2 bhk cottage in kodaikanal (alternate primary), brand name |
| Cottage cards | 2 bedroom cottage in kodaikanal (primary), cottage in kodaikanal for family, cottages in kodaikanal for group stay, kodaikanal couple stay |
| Floor plan + amenities | double bed rooms cottage in kodaikanal, cottages with kitchen, bonfire, private cottages in kodaikanal |
| Price and compare | 2 bedroom cottages in kodaikanal with price, budget cottages in kodaikanal, price per night |
| Stay guide | kodaikanal places to visit, best time to visit kodaikanal, kodaikanal homestay, 2 bed rooms homestay in kodaikanal |
| Villa wording | 2 bed room villa in kodaikanal, kodaikanal villa stay (cottage-framed) |
| Final CTA | 2 bedroom cottages in kodaikanal for rent, kodaikanal cottage booking, kodaikanal holiday home |

Live-page terms added to the plan (from the title, meta description and your list):
- **From the live title and meta:** family-friendly stays, villas for rent, homestays, spacious, private, affordable, prices available, cozy getaway.
- **From your list:** 2 bed rooms cottages in kodaikanal, double bed rooms cottage in kodaikanal, 2 bed room villa in kodaikanal, 2 bed rooms homestay in kodaikanal, 2 bedroom cottages in kodaikanal with price, 2 bedroom cottages in kodaikanal for rent.
- "kodiakanal" is a misspelling and is not used as a target.

Density: the added terms bring the count to about 33 instances. The word target rises from about 1,840 to about 2,200 to stay at 1.5%. The extra words go into the compare-table notes, floor-plan labels and FAQ.

## SEO structure

- One H1 (hero). H2s follow the 12-H2 order in the keyword file, grouped under the bands above.
- H3s are cottage names, tab-free FAQ questions and the three stay-guide blocks.
- Schema: `LodgingBusiness` / `VacationRental` per cottage with `Offer` price, `FAQPage`, `BreadcrumbList`.
- Canonical stays as the current live URL. Keep `noindex` off only when the page is ready to launch.

## Cannibalization

- Home page owns "cottages in kodaikanal", bedroom-size cottages, budget cottages and cottage booking. Every H2 touching these carries "2-bedroom".
- Homestay and Villas pages own their head terms. The homestay and villa mentions here stay short and cottage-framed.
- Places and weather pages own the head terms for H2 blocks in band 7. Keep them short and link out.

## Open items

1. Cottage list: confirm all seven cottages are 2-bedroom, with capacity, rate and lake distance for each.
2. Photos: real photos for the hero, each card and the floor plan.
3. Trust line and reviews: only include claims and quotes you can back up.
4. Pool: confirm none of the cottages has one before dropping that keyword.
5. Enquiry: confirm the WhatsApp number and the message template.
