# Mechanic Shop Management Blueprint

## Overview
- **Vision**: Deliver an end-to-end shop management experience for mobile and in-shop mechanics, unifying lead intake, quoting, scheduling, dispatch, repair execution, invoicing, and follow-up in the existing mechanicsaintaugustine.com property.
- **Primary Users**: Owners/dispatchers, mobile technicians, back-office staff, and customers accessing portals for quotes and history.
- **Non-Goals (for now)**: Deep accounting integrations beyond exports, inventory purchases from vendors, multi-location franchising workflows, or OEM-level diagnostics tooling.
- **Success Measures**: All jobs flow through the new system, quotes reflect accurate labor/parts data, dispatch and technician UIs work on mobile, the CRM/MySQL legacy paths are deprecated without data loss, and the content footprint grows rankings and lead volume in target geographies.

## Architecture Direction
- **Backend**: Stand up a Go service that adopts `github.com/shopmonkeyus/go-datamodel/v3` for core entities (customer, vehicle, job, invoice). Expose REST/JSON (GraphQL optional later). Handle auth, validation, business rules, and integrations.
- **Database**: Move from the legacy `mm` schema to a normalized Postgres or MySQL database owned by the Go service. Use migrations (go-migrate or similar) and seed scripts for labor catalog and repair guides.
- **Frontends**:
  - Public site remains largely PHP/HTML, but quote submission calls the Go API instead of local scripts.
  - Internal admin SPA (React/Vue/Svelte) served from the site or CDN, consuming the API for scheduling, quoting, and inventory.
  - Mobile technician interface as responsive web (PWA) using the same API, focused on job details, checklists, photos, and invoicing.
- **Integrations**: Optional adapters for Twilio (SMS/voice), payment processor (Stripe/Square), accounting export (QuickBooks), and map/routing services.
- **Transition Strategy**: Keep current PHP endpoints running while the Go service is developed. Gradually swap endpoints (e.g., quote intake) to API calls. Migrate data in phases to avoid outages.

## SEO & Growth Strategy
- **Content Architecture**: Structure public pages around service, city, and vehicle/problem clusters using schema.org markup, localized landing pages, and internal linking to maximize organic visibility.
- **Technical SEO**: Ensure fast Core Web Vitals, clean URL structure, XML sitemaps, and structured data output from the Go API so new features launch crawl-ready.
- **Location Expansion**: Support scalable city/state targeting (ezmobilemechanic.com) with templated content, dynamic hours/service availability, and CRM tagging for franchise/partner routing.
- **Conversion Tracking**: Instrument quote requests, calls, SMS, and bookings with analytics, and feed performance data back into lead routing and pricing refinement.
- **Content Operations**: Build tools for marketing to manage landing pages, FAQs, and repair guides without engineering, tying each asset to measurable KPIs.

## Data Model Foundations
- **Core Entities**: Customers, Vehicles, Appointments/Jobs, Quotes, Work Orders, Invoices, Payments, Technicians, Dispatch Zones, Parts, Inventory Items.
- **Quoting Catalog**: Services with default labor hours, labor rate tables, travel surcharges, diagnostic fees, recommended parts, and warranty info. Supports overrides per job.
- **Repair Knowledge Base**: Guides linked to vehicles and services, containing procedures, checklists, torque specs, required tools, media references, and common diagnostics; similar to charm.li but owned locally.
- **Relations**:
  - A Customer owns many Vehicles; Vehicles have many Jobs.
  - Each Job references Quote line items, assigned Technician(s), and results in Work Orders and Invoices.
  - Inventory tracks stock per location/van; Jobs consume inventory and trigger reorder thresholds.
  - Audit tables maintain status history (quote→scheduled→in-progress→completed→paid).
- **Data Migration**: Map fields from `Mobile-mechanic/DB/mm.sql` into the new schema, clean duplicates, and import historical service requests and user accounts.

## Feature Roadmap
1. **Foundations**
   - Formal requirements doc, ERD, and API spec.
   - Go service scaffold with auth (JWT + role-based access), migrations, and initial entities (users, customers, vehicles).
   - Replace MD5 login in `Mobile-mechanic/login.php` with API-backed auth flow.
2. **Quoting & Catalog**
   - Build labor/parts catalog admin UI and API endpoints.
   - Swap quote intake (`quote/quote_intake_handler.php`) to call the Go estimator, produce structured estimates, and log revisions.
   - Introduce PDF/email quote outputs and price adjustment rules.
3. **Dispatch & Operations**
   - Job board with calendar/route planning, technician assignment, travel windows, notifications.
   - Technician mobile interface for job steps, time tracking, media uploads, and parts usage.
   - Inventory tracking tied to jobs and vans.
4. **Commerce & Customer Experience**
   - Work order completion workflow, invoicing, and payment capture.
   - Customer portal for upcoming appointments, history, and receipts.
   - Automated reminders (SMS/email) and follow-up sequences.
5. **Enhancements & Integrations**
   - Analytics dashboards (conversion rate, utilization, revenue per tech).
   - Accounting/export adapters and advanced reporting.
   - Optional marketplace/API integrations once core system is stable.

## Execution Approach
- **Project Management**: Maintain a Kanban or backlog with clear acceptance criteria. Lock scope per milestone before development to avoid derailment.
- **Delivery Loop**: For each feature batch: refine requirements → update ERD/API schemas → implement backend → wire UI → seed or migrate data → write integration/unit tests → run end-to-end validation.
- **Testing & QA**: Adopt automated tests in Go (unit + integration), contract tests for API/client interactions, and Cypress/Playwright for UI. Stage environment mirrors production data shape.
- **Deployment**: Containerize the Go service, use CI/CD (GitHub Actions) for lint/test/build/deploy. Target phased rollout (shadow mode, limited users) before full cutover.
- **Security & Compliance**: Enforce hashed passwords (bcrypt/argon2), HTTPS everywhere, least-privilege roles, audit logging, and data backups. Review PII handling and retention.
- **Open Questions**: Payment provider choice, exact inventory granularity, offline/mobile requirements (caching), third-party data imports (labor guides), and CRM sunset timeline.

## Immediate Next Steps
1. Validate this blueprint with stakeholders and adjust scope if needed.
2. Elaborate the requirements doc (user stories, roles, success metrics).
3. Produce the ERD and migration plan for the new database.
4. Scaffold the Go service repository/module and set up CI basics.
5. Plan the quote intake handoff (PHP → API) as the first integration point.
