# furqandev.com: SEO and AEO plan

Audit date: 2026-10-24. Scope: the live site at https://furqandev.com, the code in this repo, and public SERP checks.

## 1. Read this first

**Keyword volumes are not in this document.** Both paid tools failed:

- Ahrefs returned "Insufficient plan" on every Site Explorer and Keywords Explorer call.
- Semrush returned "no API units".

The keyword section in §5 is built from live SERP observation and search intent. Every keyword is **unvalidated** until someone with working access, or the free Google Keyword Planner, fills in volume and difficulty. I have not invented any numbers.

**What I could not measure:** Google Search Console data, backlinks, domain rating, indexed pages, and rankings. The web search tool I have is US-only and ignores `site:`, so a search for `furqandev.com` returning nothing is weak evidence that the site is unindexed or unranked. Don't read more into it than that.

**Where I'm pushing back on the premise.** SEO is a 6 to 12 month channel for a new domain. This offer has a capacity of 5 builds a month, so it needs perhaps 10 to 20 qualified leads a month, not traffic. The head term, "AI chatbot for real estate", is owned by funded no-code vendors (Dante AI, Wonderchat, Boei, Chat Data, Chatbot.com, Voiceflow, CustomGPT). A new single-page site will not win it, and chasing it would waste the budget. The plan below leans on long-tail pages, entity building and AEO, and treats Upwork/Contra/LinkedIn as part of the SEO surface, because those pages outrank personal sites for "hire a RAG developer" queries.

## 2. Where things stand

### What's working

| Item | Evidence |
|---|---|
| Content is in raw HTML, no JS needed to read it | `index.html` is fully static, FAQ answers are in the DOM. AI crawlers that don't run JS can read the page. |
| HTTPS with http→https 301 | `curl -I http://furqandev.com/` returns 301 to `https://furqandev.com/`. |
| Nothing blocks AI crawlers | `robots.txt` is a 404, so the default is allow. This is accidental, not intentional. |
| Valid `sitemap.xml` exists | Returns 200 `application/xml`. |
| Clear offer, fixed price, guarantee, named testimonial | Good raw material for answer engines to quote. |

### What's broken or missing

Ranked by impact.

| # | Finding | Evidence | Severity |
|---|---|---|---|
| 1 | **`www` does not redirect to the apex, and there is no canonical tag.** Both `furqandev.com` and `www.furqandev.com` return 200 with the same page. | `curl -I https://www.furqandev.com/` returns 200; `grep canonical index.html` returns 0 | Critical, 10-minute fix |
| 2 | **Title and H1 contain none of the keywords the page is selling.** Title is "Furqan — Agentic AI Engineer". "Furqan" is a very common name (my brand search returned 10+ unrelated Furqans on Fiverr, Hashnode and elsewhere). "Agentic AI Engineer" is a job title, not something buyers search. | `index.html:6`, `:47` | Critical |
| 3 | **Zero structured data.** No JSON-LD of any kind, so there is no Person, ProfessionalService, Offer or FAQPage markup. Answer engines have to infer who you are and what you charge from prose. | grep for `ld+json` returns 0 | High |
| 4 | **No social/Open Graph tags, no favicon.** Links shared on LinkedIn/Slack/WhatsApp render as bare URLs. `favicon.ico` returns 404. | grep returns 0; curl returns 404 | High (for outbound, since your leads come via shared links) |
| 5 | **One URL for everything.** A single page cannot rank for "real estate chatbot", "RAG chatbot developer", "chatbot cost" and "agentic AI" at once. The page's main promise is the real-estate angle, but the 4th problem card and the About section drift into e-commerce and support. | `index.html` structure | High (structural) |
| 6 | **No `robots.txt`, no `llms.txt`.** Sitemap is not declared. | both 404 | Medium |
| 7 | **Unverifiable proof.** The 62% case study names no client. The only named proof is "WiseWorkman" (a marketplace-style handle) and a demo at `svpremier.furqandev.com`, which is `noindex` and titled "Chatbot demo". Search engines and AI answers weight verifiable authors, clients and sources. | `index.html:260-272`; curl of svpremier | Medium to High |
| 8 | **The headshot filename suggests it is AI-generated.** `uploads/Gemini_Generated_Image_…jpeg` is byte-identical (585,417 B) to `assets/furqan-headshot.jpeg`, and both are publicly served. If it is generated, a personal brand whose pitch is trust ("no made-up answers") is undermined by a made-up portrait. Please confirm; if it's a real photo, rename and delete the duplicate. | `ls -la`; both URLs return 200 | Medium (trust) |
| 9 | **Performance.** 585 KB 1024×1024 JPEG used for two images, no `srcset`, no WebP. Full Bootstrap CSS+JS and jQuery loaded from a CDN for a one-page site. No cache headers observed on the HTML. Core Web Vitals not measured; this is based on weights, not a Lighthouse run. | `assets/`, `index.html:11,394-395` | Medium |
| 10 | **Sitemap is thin and stale.** One URL, `lastmod` 2026-09-09, and it references a third-party stylesheet (`xml-sitemaps.com`). | `sitemap.xml` | Low |
| 11 | **Draft marker shipped live.** The HTML comment "DRAFT: draft-offer-redesign … search for CONFIRM" is in production source. | `index.html:16-19` | Low |

### Outside SEO but worth a flag

`chat-widget.js` posts to `https://automate.teknexuss.com/webhook/furqandev-chat`. Anyone can call that endpoint directly. Check that it has rate limiting and a cost cap, because the demo bot calls a paid LLM. I'm raising it because it affects the site, not because it is an SEO issue.

## 3. Strategy

**Positioning to build the site around:** "AI chatbot for real-estate websites, trained only on your listings, fixed price, 7 days." It's specific, the price is a genuine differentiator (the market quotes $5k to $25k for custom RAG builds, and no-code tools sell at $19 to $150 a month), and the guarantee is checkable.

Three tracks, in this order:

1. **Fix the foundations** (week 1). Cheap, and everything else depends on them.
2. **Build the entity and answer-engine layer** (weeks 1 to 4). Makes the facts about Furqan consistent, machine-readable and quotable.
3. **Add a few intent-specific pages** (weeks 3 to 10), then earn off-site mentions.

Not in the plan on purpose: a high-volume blog, guest-post link buying, or any attempt to "get recommended by ChatGPT". Nobody can make an AI recommend a business. What can be done is making the site readable, accurate and citable.

## 4. Action plan

### Phase 0: Technical foundations (week 1)

| # | Task | Done when |
|---|---|---|
| 0.1 | 301 `www.furqandev.com` → `https://furqandev.com` in `.htaccess` (the server is LiteSpeed, so Apache rules work) | `curl -I https://www.furqandev.com/` returns 301 |
| 0.2 | Add `<link rel="canonical" href="https://furqandev.com/">` | present in `<head>` |
| 0.3 | Rewrite `<title>` and meta description (drafts in §6) | live |
| 0.4 | Keep `<h1>` emotional, but add the keyword phrase to the eyebrow as `<p>` text and to the first sentence of the hero paragraph. Do not stuff. | the first 100 words say "AI chatbot", "real-estate website" and "your own listings" |
| 0.5 | Add `robots.txt` (with sitemap line) | returns 200 |
| 0.6 | Add JSON-LD: `ProfessionalService` + `Person`, `Offer` for the $699 build, `FAQPage` for the 5 FAQs (drafts in §6) | validates in Google Rich Results Test and schema.org validator |
| 0.7 | Add Open Graph + Twitter card tags and a 1200×630 OG image; add favicon | link preview works in LinkedIn Post Inspector |
| 0.8 | Compress headshot (WebP + JPEG fallback, ~800px wide, `srcset`), delete the duplicate in `uploads/`, set `fetchpriority="high"` on the hero image | image under ~100 KB |
| 0.9 | Remove the DRAFT comment from production HTML | gone |
| 0.10 | Replace Bootstrap CSS/JS and jQuery with only the CSS/JS the page uses, or at minimum self-host with cache headers. Confirm `script.js` doesn't need jQuery. | Lighthouse mobile ≥ 90 performance; LCP < 2.5 s |
| 0.11 | Add cache headers for static assets (1 year, versioned filenames already in use) | `cache-control` visible |
| 0.12 | Create and verify Google Search Console and Bing Webmaster Tools properties for the domain; submit the sitemap. **This is the single most important measurement step.** | both verified, sitemap accepted |
| 0.13 | Update `sitemap.xml` (drop the third-party stylesheet line, correct `lastmod`, add new pages as they ship) | validates |

### Phase 1: Entity and AEO layer (weeks 1 to 4)

AEO here means: be easy to find, read and quote accurately.

| # | Task | Done when |
|---|---|---|
| 1.1 | Settle one canonical fact sheet (full name, role, location or "remote", price, delivery time, guarantee wording, capacity, stack) and use it identically on the site, LinkedIn, Upwork, Contra, GitHub and Instagram. Inconsistent facts get quoted wrong. | fact sheet agreed; all profiles match |
| 1.2 | Add an `llms.txt` at the site root (draft in §6). It is a convention, not a ranking factor; it costs 10 minutes and helps agents that read it. | returns 200 |
| 1.3 | State crawler policy explicitly in `robots.txt`: allow GPTBot, ClaudeBot, PerplexityBot, Google-Extended, OAI-SearchBot. The default is already allow, so this makes the choice visible and protects against a future security plugin or host default blocking them. | in robots.txt |
| 1.4 | Add a visible "Facts at a glance" block near the top of the page (price, timeline, what's included, guarantee, capacity, stack) in plain HTML. Short, scannable, no marketing language. This is the paragraph an answer engine will lift. | live |
| 1.5 | Rewrite the FAQ as real customer questions with 40 to 70 word direct answers (list in §5.3). Add five more. | 10 FAQs live with FAQPage schema |
| 1.6 | Resolve the proof problem: get written permission to name the e-commerce client behind the 62% claim, or reword it as what it is. Link the WiseWorkman testimonial to its source (the Upwork/Fiverr review URL). Show the real author name if they consent. | every claim traceable |
| 1.7 | Confirm the portrait is a real photo (see finding 8); replace if not. Add a short About paragraph with real, checkable background. **I will not write credentials for you. Send me the facts.** | done |
| 1.8 | Claim and complete off-site profiles that answer engines and Google actually surface for these queries: Upwork, Contra, LinkedIn (headline and About use the same phrases), GitHub README, Clutch/GoodFirms if eligible, Instagram bio link. Each gets the same one-sentence description and a link to furqandev.com. | 5+ profiles consistent and linked |
| 1.9 | Add `sameAs` links in the Person schema to all of those profiles | in JSON-LD |
| 1.10 | Baseline AEO test: run the prompt set in §7 across ChatGPT, Claude, Perplexity, Gemini and Google AI Overviews. Record whether furqandev.com or "Furqan" appears and which sources are cited. Save results in `docs/aeo-baseline-2026-11.md`. | baseline saved |

### Phase 2: Intent pages and content (weeks 3 to 10)

Don't build all of these at once. Ship in this order, then measure in Search Console before writing more.

| Order | URL | Targets | Why this order |
|---|---|---|---|
| 1 | `/real-estate-ai-chatbot/` | AI chatbot for real estate website, real estate chatbot trained on listings, 24/7 lead capture for agents | Matches the core offer. Move the real-estate content here and make `/` a shorter hub. |
| 2 | `/rag-chatbot-development/` | RAG chatbot developer, custom chatbot trained on your data, hire RAG developer | Serves the non-real-estate buyer without diluting page 1. |
| 3 | `/pricing/` (or `/ai-chatbot-cost/`) | how much does a custom AI chatbot cost, RAG chatbot cost | Market sources range from $5k to $25k+; your $699 fixed price is the most distinctive fact on the site. A transparent cost page is also the type of page AI answers cite. Be honest about what $699 does and does not include. |
| 4 | `/case-studies/<client>/` | named proof | Only after §1.6; a named case study with real numbers, scope and quote is worth more than ten blog posts. |
| 5 | `/blog/` with 4 to 6 posts, no more at first | see §5.3 | Only the ones with a real question behind them. |

Page rules: one primary query per page, answer it in the first 100 words, one H1, descriptive H2s phrased as questions where natural, internal links between the pages, FAQ block with schema, a single clear CTA (free demo).

### Phase 3: Authority (month 2 onward)

| # | Task |
|---|---|
| 3.1 | Ask the named client(s) for a link to furqandev.com from their site or from a case-study mention. |
| 3.2 | Publish 1 to 2 build write-ups (how the guarantee test works, the 50-question evaluation method). Real engineering detail is what gets cited, and it supports the "no made-up answers" claim. |
| 3.3 | Real-estate-side placements: local realtor groups, brokerage newsletters, podcasts, and Reddit/Facebook-group answers where the link is relevant and disclosed. No fake reviews, no hidden text, no private blog networks. |
| 3.4 | Reuse the Instagram content as short posts on LinkedIn, linking to the matching page. |
| 3.5 | Monthly: refresh the AEO prompt test (§7), review Search Console queries, and update the pages that already get impressions before writing new ones. |

### Timeline

| Weeks | Focus |
|---|---|
| 1 | Phase 0 (everything), start 1.1 and 1.8 |
| 2 to 4 | Phase 1 complete, AEO baseline recorded |
| 3 to 6 | Pages 1 and 2 live |
| 6 to 10 | Pages 3 and 4, first 2 blog posts |
| 10 to 12 | First real review of Search Console data and AEO re-test |

Search and AI visibility move slowly. Don't expect a measurable change before about 30 days after changes ship, and rankings for competitive terms take longer. I'm not promising a position or a lead count.

## 5. Keyword research

**Method:** seed terms from the page copy and offer, then checked who ranks (SERP type) for each cluster via web search. **No volume or difficulty data**; see §1. "Winnable?" is my judgment from who ranks, not a metric.

### 5.1 Clusters

| Cluster | Example queries | Intent | Who ranks (observed) | Winnable? | Target |
|---|---|---|---|---|---|
| A. Real-estate chatbot, head | ai chatbot for real estate, real estate chatbot | Commercial | No-code vendors: Dante AI, Wonderchat, Boei, Chat Data, Chatbot.com, Voiceflow | **No** in year 1 | Don't target directly |
| B. Real-estate chatbot, specific | chatbot trained on my real estate listings, ai chatbot for realtor website, answer buyer questions after hours, real estate website chatbot that doesn't hallucinate | Commercial | Same vendors plus a few freelancer gigs | **Maybe**, via long-tail | `/real-estate-ai-chatbot/` |
| C. Hire a RAG developer | hire rag chatbot developer, rag chatbot developer freelance, custom rag chatbot for business | Transactional | Toptal, Upwork, Freelancer, Fiverr, MentorCruise | Not on your own site yet. **Yes via Upwork/Contra profile pages**, and long-tail on `/rag-chatbot-development/` | Off-site + page 2 |
| D. Cost | how much does a custom ai chatbot cost, rag chatbot cost, ai chatbot for small business price | Commercial research | Agency blogs: RaftLabs, SoluLab, Kellton, Wonderchat | **Maybe.** Your $699 fixed price is unusual, so the angle is differentiated | `/pricing/` |
| E. Speed to lead | real estate lead response time, after-hours leads real estate | Informational | Vendor blogs (HyperLeap, MindStudio, Qobrix, Noform) | Low value, low fit. The statistics are mostly vendor-sourced and inconsistently attributed. Only write about it with primary sources. | Skip for now |
| F. Brand | furqan ai engineer, furqandev | Navigational | Fiverr profiles of other Furquans | **Yes**, once the full name and schema are set. Brand disambiguation is the cheapest win on this list. | Home + schema + profiles |
| G. Generic jargon | agentic ai engineer, rag chatbot, what is agentic rag | Informational | Coursera, vendor docs, big publishers | **No** | Don't target |

### 5.2 Priority keywords (to validate)

Tier 1, build pages for these:

1. ai chatbot for real estate website that answers from your listings
2. real estate website chatbot after hours
3. rag chatbot for real estate
4. how much does a custom rag chatbot cost
5. custom ai chatbot for small business price
6. hire rag chatbot developer
7. chatbot trained on your own data (non-technical buyers)

Tier 2, answer on the site, support with FAQ:

8. how to stop an ai chatbot making up answers
9. real estate chatbot lead qualification
10. chatbot vs live chat for real estate
11. n8n chatbot for website

**Validation step (free):** put the Tier 1 list into Google Keyword Planner and Search Console's Performance → Queries once data exists. Drop anything under roughly 10 searches a month that doesn't have strong commercial intent. For a service that needs ~10 to 20 leads a month, low-volume, high-intent queries are fine.

### 5.3 Question set (use for FAQ, page sections and the AEO test)

Phrase answers directly, 40 to 70 words each, with the fact first.

- How much does a custom AI chatbot for a real estate website cost?
- How long does it take to build a chatbot trained on my listings?
- Will the chatbot make up prices or details? (How "answers only from your content" works, and how the 50-question test is run)
- What data do you need from me?
- What happens to my listings and client data?
- Does it connect to my CRM or MLS feed? (Currently: custom tier only, say so)
- What does the free demo include, and what's the catch?
- What happens if it fails the 90% test? (Quote the guarantee exactly)
- How is this different from Chatbot.com / Wonderchat / a no-code builder? (Honest comparison: when no-code is the right call. This is a trust-builder and a strong AEO candidate.)
- Do I need to be technical?

## 6. Drafts to review (nothing published)

Placeholders in `[BRACKETS]` need facts only you can supply. I have not made up any.

### 6.1 Title and meta description

```
<title>AI Chatbot for Real Estate Websites | Trained on Your Listings | Furqan</title>
<meta name="description" content="Fixed-price AI chatbot for real-estate websites. Trained only on your listings and FAQs, live in 7 days, with a free demo in 48 hours. Built by Furqan [FULL NAME].">
```

Title is 69 characters, which may truncate; shorten to "Real Estate AI Chatbot Trained on Your Listings | Furqan" (55) if so. Meta description is ~165 characters; trim to ≤155 at publish.

### 6.2 `robots.txt`

```
User-agent: *
Allow: /

User-agent: GPTBot
Allow: /

User-agent: OAI-SearchBot
Allow: /

User-agent: ClaudeBot
Allow: /

User-agent: PerplexityBot
Allow: /

User-agent: Google-Extended
Allow: /

Sitemap: https://furqandev.com/sitemap.xml
```

This allows AI crawlers. If you'd rather block training crawlers (GPTBot, Google-Extended) while keeping search/answer crawlers, that's a legitimate choice; tell me and I'll adjust.

### 6.3 `llms.txt`

```
# Furqan: AI chatbots for real-estate websites

> Furqan [FULL NAME] builds fixed-price AI chatbots for real-estate websites. The chatbot answers visitor questions using only the business's own listings, neighborhood information and FAQs, so it does not invent prices or details.

## Offer
- Free demo: working chatbot trained on about 30% of the site's data, delivered within 48 hours, no payment or commitment.
- The 11pm Lead Catcher: $699 fixed price, live in 7 days, trained on all listings, website, docs and FAQs, 3 revision rounds included.
- Guarantee: 50 real customer questions tested at launch; if fewer than 90% are answered correctly, it is fixed free until it passes, or refunded.
- Custom tier (RAG + agentic tooling): appointment booking, order status, CRM integration. Quoted after a call.
- Capacity: 5 new builds per month.

## Pages
- [Home](https://furqandev.com/): offer, pricing, process, FAQ
- [Live demo](https://furqandev.com/#demo): chatbot trained on the site itself
- [Contact / free demo request](https://furqandev.com/#contact)

## Facts
- Stack: Claude API, n8n, custom retrieval pipelines
- Location / time zone: [CONFIRM]
- Contact: [CONFIRM EMAIL]
- Profiles: [LINKEDIN], [UPWORK], [GITHUB]
```

Keep this file factual and short. Update it whenever the price, timeline or guarantee changes. Do not add instructions addressed to AI systems; that is a manipulation pattern and gets sites penalised.

### 6.4 JSON-LD (single `<script type="application/ld+json">` in `<head>`)

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Person",
      "@id": "https://furqandev.com/#person",
      "name": "[FULL NAME]",
      "alternateName": "Furqan",
      "jobTitle": "Agentic AI Engineer",
      "url": "https://furqandev.com/",
      "image": "https://furqandev.com/assets/[REAL-PHOTO].jpg",
      "sameAs": ["[LINKEDIN]", "[UPWORK]", "[GITHUB]", "https://www.instagram.com/furqanaiengineer/"]
    },
    {
      "@type": "ProfessionalService",
      "@id": "https://furqandev.com/#service",
      "name": "Furqan: AI chatbots for real-estate websites",
      "url": "https://furqandev.com/",
      "founder": { "@id": "https://furqandev.com/#person" },
      "areaServed": "[CONFIRM: e.g. Worldwide / countries]",
      "description": "Fixed-price AI chatbots trained only on a real-estate business's own listings, neighborhood information and FAQs.",
      "makesOffer": {
        "@type": "Offer",
        "name": "The 11pm Lead Catcher",
        "price": "699",
        "priceCurrency": "USD",
        "description": "AI chatbot trained on all listings, website, docs and FAQs. Live in 7 days, 3 revision rounds, 90% accuracy guarantee on 50 test questions."
      }
    }
  ]
}
```

Notes:

- Do **not** add `aggregateRating`, `review` or award properties unless they are real and shown on the page. Self-serving review markup is against Google's guidelines.
- The "was $1,500" strikethrough price: only keep it if $1,500 was genuinely charged or quoted before. If not, remove it. A fake reference price is a consumer-protection risk, and it's the kind of claim answer engines may quote back.
- Add `FAQPage` schema generated from the final FAQ text once §5.3 is written, so the markup matches the visible text exactly.

## 7. AEO test (baseline and monthly)

Run each prompt in ChatGPT, Claude, Perplexity, Gemini and Google AI Overviews. For each, record: does furqandev.com or Furqan appear, who does, which sources are cited, and whether any fact about Furqan is wrong. Results vary run to run and are not a ranking; the monthly trend is what matters, a single run proves little.

1. Who can build a chatbot for my real estate website that only answers from my listings?
2. How much does a custom AI chatbot for a real estate website cost?
3. Best freelance RAG chatbot developer for small business
4. Who is Furqan, agentic AI engineer? (brand and fact check)
5. Is furqandev.com legit?
6. How do I stop an AI chatbot from making up property details?
7. AI chatbot for realtors with a money-back guarantee
8. Cheapest way to get a custom AI chatbot trained on my own website

Also test the free-text variants customers actually type, with typos and no punctuation.

## 8. Measurement

| Metric | Source | Target (to be set after baseline) |
|---|---|---|
| Impressions, clicks, queries, indexed pages | Google Search Console | baseline at week 2 |
| Branded query ranking ("furqan ai engineer") | Search Console / manual | page 1 for brand + disambiguator by week 8 |
| Demo requests from organic | form submissions with referrer/UTM | log source on every lead |
| AI answer appearances and citation accuracy | §7 monthly test | trend only |
| Core Web Vitals | PageSpeed Insights | pass mobile |
| Referring domains | Search Console links / any backlink tool | track, no target in year 1 |

**Lead attribution gap:** `submit.php` doesn't record where the visitor came from. Add a hidden `referrer` and UTM capture to the form so you can tell SEO leads from Instagram or Upwork leads. Without that, none of the above can be tied to revenue.

## 9. Open questions (I need these from you)

1. Full name for the Person schema and title tag. "Furqan" alone will not disambiguate you.
2. Is the headshot a real photograph? (Filename says Gemini-generated.)
3. Can the e-commerce client behind the 62% figure be named? If not, the claim should be reworded.
4. Can "WiseWorkman" be linked to the original review?
5. Was $1,500 ever the actual price?
6. Target geography (US only? worldwide?). It affects `areaServed`, the cost-page currency and which listings sites matter.
7. Do you want AI training crawlers (GPTBot, Google-Extended) allowed or blocked?
8. Can you grant Search Console access, or run a Keyword Planner pull on §5.2, so the keyword list gets numbers? Or restore API access on Ahrefs/Semrush and I'll re-run it.
9. Which profiles exist today: LinkedIn, Upwork, GitHub, Contra, Clutch?

## 10. Recommended first moves

If only five things get done this week:

1. `www` → apex 301 and canonical tag (0.1, 0.2)
2. New title, meta description, and keyword-bearing hero copy (0.3, 0.4)
3. Verify Search Console and Bing, submit sitemap (0.12)
4. JSON-LD, OG tags, favicon, `robots.txt`, `llms.txt` (0.5 to 0.7, 1.2)
5. Answer the open questions in §9 so the proof and entity work can start
