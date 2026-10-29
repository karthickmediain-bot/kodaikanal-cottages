# 4 Bedroom Cottage in Kodaikanal — Design Plan

Page: `4-bedroom-cottage-in-kodaikanal.html` (live today, about 800 words, thin copy). Keyword data: `4-bedroom-cottage-in-kodaikanal-keywords.md` (Keyword Planner, India).

## Concept: "Pick by headcount"

The visitor is a family or group organiser with one question: which cottage fits my people, my dates and my budget? The page is built around answering that in the first screen, then proving it, then taking the enquiry. Every section either narrows the choice or removes a doubt. Primary action is a WhatsApp or call enquiry with the group size already filled in.

## Tokens (inherited from `css/styles.css`, no new colours)

- Colours: primary `#145066`, primary-dark `#103e4e`, primary-light `#1f7592`, CTA `#22a6d9`, highlight `#4dd9ff`, steel `#cdeef2`, alt surface `#eaf7fa`, text `#1c1f24`, muted `#3f4f54`, border `#dfeaec`, success `#2f7a4d`. `#f5a623` is reserved for the single "peak season" accent.
- Type: Poppins only. H1 40–48/700, H2 28–32/700, H3 20–22/600, body 16/400.
- Radius 12px cards, 999px pills. Container 1200px. Section gap 50px mobile, 100px desktop.
- Restraint: one accent colour per section, no looping animation.

## Conversion mechanics

| Mechanic | Where | Detail |
|---|---|---|
| Headcount picker | Directly under the hero | Chips: 6–8, 9–12, 13–16 guests. Picking one highlights the matching cottage cards below and rewrites the enquiry button message. |
| Pre-filled WhatsApp | Every CTA | `wa.me/917502345777?text=` with guests, dates field and "4 bedroom cottage" in the message. Page-specific text, like the other pages. |
| Sticky enquiry bar | Mobile, after the hero | Call and WhatsApp buttons pinned to the bottom, hidden while the footer is visible. |
| Proof near every CTA | Beside buttons | "Reply within the hour", "Every property checked in person", "No booking fee". These claims come from the homepage copy; confirm before launch. |
| Rate clarity | Price section | Season bands, no fixed prices (the site quotes on enquiry), plus a "get an exact quote" button. |
| Book-early strip | Booking section | Twelve bars of "kodaikanal cottage booking" searches. April–May is the peak (720) with a December bump. The peak bars use the amber accent. |
| Thank-you tracking | After the form | Existing `thank-you.html` flow; fire the same GA event as other pages. |

## Layout DNA: one construction per section

| # | Section | Construction |
|---|---|---|
| 1 | Hero | Full-bleed photo, bottom-left text block, H1, 60-word paragraph, two buttons (WhatsApp, Call) |
| 2 | Headcount picker | Overlapping card that straddles the hero edge: three chips, a result line, one button |
| 3 | Family | Text left, tall image right, "fit strip" of four icons beneath (10–16 guests, 4 bedrooms, kitchen, parking) |
| 4 | Group stay | Text with a mini table, "pick by headcount", beside a bed-configuration list |
| 5 | Best for family | Comparison table, six rows: guests, location, lake distance, kitchen, pool, fire camp, enquire. Horizontal scroll on mobile with a sticky first column. |
| 6 | Villa, homestay or cottage | Three side-by-side columns with a one-line verdict each; the cottage column is highlighted |
| 7 | Near the lake | Text with a distance list (lake, Coaker's Walk, Pillar Rocks, market) |
| 8 | Vattakanal | Dark primary band with image; the only reversed-colour section before the CTA |
| 9 | Private cottages | Short text with three trust ticks (own compound, own entrance, caretaker) |
| 10 | Amenities | Icon grid, 2×3 desktop, 2×2 plus scroll mobile |
| 11 | Property cards | Six cards in a staggered two-column layout, each with a capacity badge and an Enquire button; the picker highlights matches |
| 12 | Budget and low price | Two-column "save money" list with a call-out card |
| 13 | Price and rates | Season-band table (peak, shoulder, off-season) with a quote button |
| 14 | How to book | Four-step horizontal strip with the book-early bars below |
| 15 | FAQ | Accordion, all answers in the DOM, FAQPage schema |
| 16 | Closing CTA | Solid primary band, one line, WhatsApp and Call |

## Keywords: MCP set plus live-page terms

Live-page terms below come from the current meta keywords tag. They have no Keyword Planner volume data, so use them for coverage, not as targets.

| Live-page term | Placed in |
|---|---|
| four bedroom cottage in Kodaikanal | Property cards (once; already in the live property copy) |
| 4 bedroom villa in Kodaikanal | Villa, homestay or cottage (section 6) |
| 4 bedroom homestay in Kodaikanal | Section 6 |
| family villas in Kodaikanal | Section 6 |
| large group stay Kodaikanal | Group stay (section 4) |
| Kodaikanal group stay | Section 4 |
| big cottages in Kodaikanal | Family (section 3) |

Section 6 also answers the "homestay vs cottage" question, which has no volume data but is a real search.
"kodaikanal group stay cottage" (90) is dropped from the body to stay under the density cap; use it in an FAQ answer if there is room.

## Section budget

| # | Section | Words | Keyword instances |
|---|---|---|---|
| 1 | Hero | 60 | 1 |
| 2 | Headcount picker | 40 | 0 |
| 3 | Family | 130 | 2 |
| 4 | Group stay | 130 | 3 |
| 5 | Best for family (table) | 120 | 1 |
| 6 | Villa, homestay or cottage | 130 | 3 |
| 7 | Near the lake | 100 | 1 |
| 8 | Vattakanal | 130 | 2 |
| 9 | Private cottages | 90 | 1 |
| 10 | Amenities | 120 | 3 |
| 11 | Property cards | 360 | 2 |
| 12 | Budget | 90 | 1 |
| 13 | Low price | 100 | 1 |
| 14 | Price and rates | 120 | 2 |
| 15 | How to book | 120 | 1 |
| 16 | FAQ (7 answers, 30–40 words) | 245 | 7 |
| 17 | Closing CTA | 40 | 0 |
| | **Total** | **about 2,125** | **31 (about 1.46%)** |

Note: the table numbers sections 1–17 because the headcount picker and fit strip sit as their own bands. Section numbers in "Layout DNA" above merge the picker and the hero overlap into one view.

## Page meta

- Title (51 chars): 4 Bedroom Cottage in Kodaikanal for Family & Groups
- Description: Book a 4 bedroom cottage in Kodaikanal for family or group stays. Private kitchen, lake and Vattakanal options, budget to luxury. Get rates today.
- H1: 4 Bedroom Cottage in Kodaikanal for Family & Group Stays
- Remove the meta keywords tag; fix the "�" encoding fault in the description.

## Accessibility and performance

- All chart numbers also in a visually hidden table; chips and accordion are keyboard-operable.
- Hero image under 150 KB WebP, `fetchpriority="high"`, explicit width and height; every other image lazy-loaded.
- Comparison table is real `<table>` markup, not divs.
- Schema: FAQPage, plus LodgingBusiness or Product for the six cards.

## Open items

- Confirm the three trust claims (reply within the hour, checked in person, no booking fee) and the 10–16 guest range.
- Confirm lake and attraction distances per cottage before they go in the table.
- No local copy of the live page exists in the repo; the build starts as a new file.
- NLP terms were not researched (web search was declined); the list in the earlier plan is unverified.
