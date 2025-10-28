# Mechanic Shop Management Requirements

## Stakeholders & Roles
- **Business Owner / Dispatcher**: Oversees operations, reviews leads, schedules jobs, manages pricing, monitors KPIs.
- **Mobile Technician**: Receives assignments, tracks time, records findings, consumes inventory, captures signatures and payments onsite.
- **Back-Office / Finance**: Handles invoicing, reconciles payments, exports data for accounting, manages customer communications.
- **Marketing / SEO Team**: Publishes localized service content, monitors conversion metrics, optimizes landing pages for search visibility.
- **Customers**: Submit quotes, book appointments, access service history and invoices.

## Core Objectives
1. Centralize lead intake, quoting, scheduling, dispatch, job execution, and invoicing in a single platform.
2. Support mobile-specific workflows (travel windows, van inventory, onsite payment capture) while remaining useful for in-shop operations.
3. Enable national SEO-driven growth (ezmobilemechanic.com) with scalable content, analytics, and lead routing.
4. Provide actionable reporting and KPIs for decision-making and expansion.

## Functional Requirements

### Lead & Quote Management
- Capture quote requests via web, phone, or manual entry with enriched vehicle and repair metadata.
- Auto-generate estimates using the labor/parts catalog, travel multipliers, and configurable surcharges.
- Version quotes, track adjustments, and convert accepted quotes into scheduled jobs.
- Surface SEO-friendly quote confirmation pages with structured data and cross-links.

### Customer & Vehicle Records
- Consolidate customer profiles across channels with contact history, communication preferences, and service history.
- Manage multiple vehicles per customer, storing VIN, trim, mileage, and maintenance records.
- Sync opt-in/consent status for marketing and transactional notifications.

### Scheduling & Dispatch
- Calendar view for dispatchers with drag-and-drop assignments, travel buffers, and route optimization.
- Technician availability management (shifts, PTO) and role-based job assignment.
- Automated alerts for schedule changes, job confirmations, and arrival notifications (SMS/email).

### Technician Mobile App
- Mobile-responsive interface with job checklist, diagnostic steps, photo/video capture, and repair guides.
- Offline-first mode with local caching, syncing when connectivity is restored.
- Time tracking (clock-in/out per job), parts consumption logging, customer signature capture, and payment acceptance.

### Inventory & Parts
- Catalog of parts with supplier info, pricing tiers, and stock levels per van/location.
- Automated deductions when parts are assigned to jobs; threshold alerts for replenishment.
- Support for kits/bundles tied to common services.

### Work Orders, Invoicing & Payments
- Generate work orders from completed jobs with technician notes and used parts.
- Create invoices, apply taxes/fees, and accept payments (card, ACH, cash) with integration hooks (e.g., Stripe).
- Send digital receipts, sync payments to accounting exports, and manage outstanding balances.

### Repair Knowledge Base
- Library of procedures linked to vehicle/service combinations with torque specs, tools, parts, and safety notes.
- Tagging and search to allow technicians to find guides quickly; attach user-generated notes for future jobs.
- Marketing can expose sanitized versions as SEO content (blog/how-to) while keeping internal details secure.

### Analytics & Reporting
- Dashboard for lead volume, quote-to-job conversion, revenue per technician, job duration, inventory usage, and customer satisfaction (NPS/reviews).
- Location-specific metrics to guide expansion and franchise/partner onboarding.
- Attribution tracking tying organic/paid marketing to booked revenue.

### Marketing & SEO Enablement
- CMS-like interface to manage localized landing pages, service area expansions, FAQs, and promotional content.
- Automated schema.org markup for services, local business, FAQs, and reviews.
- XML sitemap and dynamic internal linking updates as new locations/services launch.
- Integration with analytics (GA4), call tracking, and heatmaps.

## Non-Functional Requirements
- **Performance**: Sub-second responses for core API endpoints; public pages optimized for Core Web Vitals (LCP <2.5s, CLS <0.1, FID/INP within guidelines).
- **Security**: Modern authentication (OAuth/JWT), bcrypt/argon2 password hashing, role-based access control, audit logging, encrypted connections.
- **Scalability**: Horizontal API scalability to support nationwide traffic and technician base; CDN-backed static assets.
- **Reliability**: 99.5% uptime target, automated backups (daily), disaster recovery plan with RTO <4h.
- **Compliance**: Adhere to PCI guidelines for payments, TCPA for messaging opt-ins, GDPR/CPRA considerations for customer data.

## Key Performance Indicators
- Organic sessions and keyword rankings per target city/service.
- Quote submission rate, quote-to-job conversion, average revenue per job.
- Technician utilization (billable hours vs capacity), job completion time variance.
- Payment collection time, outstanding receivables, repeat customer rate.
- Customer satisfaction scores (NPS/reviews) and referral volume.

## Dependencies & Integrations
- Payment processors (Stripe/Square) with webhook support.
- Telephony/SMS provider (Twilio) for notifications and call tracking.
- Mapping/route optimization (Google Maps, Mapbox) for dispatch.
- Accounting export (QuickBooks/Xero) via CSV or API.
- Labor time datasets (e.g., MOTOR) and optional third-party repair knowledge sources.

## Risks & Mitigations
- **Data Migration Complexity**: Legacy schema lacks constraints; plan staged imports with validation scripts and dual-running period.
- **Change Management**: Staff training required for new workflows; deliver sandbox environments and documentation.
- **SEO Cannibalization**: Multiple sites/domains (mechanicsaintaugustine.com, ezmobilemechanic.com) need canonicalization and content strategy coordination to avoid duplicate content.
- **Mobile Connectivity**: Technicians may operate in low-signal areas; offline-first design is mandatory.

## Open Questions
- Preferred payment processor and fee structure?
- Are franchise/partner models planned in the first year or later?
- Required integrations with existing call center or CRM systems?
- Scope of internal vs public repair knowledge sharing?
- SLA expectations for support and issue resolution?

## Next Actions
1. Validate requirements with stakeholders; capture answers to open questions.
2. Translate requirements into detailed user stories with acceptance criteria.
3. Produce ERD and API specifications aligned with these requirements.
4. Prioritize MVP scope for Milestone 1 (Foundations) based on effort vs impact.
