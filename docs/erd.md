# Data Model Overview

## Core Entities
- **User**: System users with roles (owner, dispatcher, technician, back-office, marketing). Stores authentication credentials, permissions, and profile details.
- **Customer**: Individuals or businesses requesting service. Tracks contact info, preferred communication channels, billing details, and consent flags.
- **Vehicle**: Tied to a customer; includes VIN, year, make, model, trim, mileage, tags, and maintenance notes.
- **Lead**: Initial contact or quote request. Contains source, campaign data, and raw intake payload.
- **Quote**: Structured estimate composed of line items referencing catalog services/parts. Versioned and linked to a lead and customer.
- **QuoteLineItem**: References ServiceCatalog entries, labor hours, rates, parts, discounts, and technician notes.
- **ServiceCatalog**: Canonical services with default labor time, pricing rules, required parts, recommended upsells, and warranty policies.
- **LaborRate**: Defines labor pricing per service type, technician tier, or geography.
- **Part**: Inventory item with SKU, supplier, cost, price, stock level, reorder thresholds, and van/location tracking.
- **InventoryTransaction**: Movement of parts (allocation to job, restock, adjustment) with audit trail.
- **Job**: Scheduled work derived from an accepted quote. Stores status, scheduled window, assigned technicians, travel requirements, and checklists.
- **JobTask**: Sub-steps within a job (diagnostics, labor operations) with completion status and time tracking.
- **DispatchZone**: Geographic area definitions for routing and scheduling constraints.
- **TechnicianAssignment**: Association between technicians and jobs with roles (lead tech, helper) and time entries.
- **TimeEntry**: Clock-in/out records for technicians per job or general labor.
- **RepairGuide**: Procedural content linked to services/vehicles, including instructions, torque specs, tools, media.
- **WorkOrder**: Documentation of completed work, capturing actual labor, parts used, technician notes, inspection results, signatures.
- **Invoice**: Billing record referencing a work order, with line items, taxes, fees, discounts, and payment status.
- **Payment**: Transaction records (method, amount, gateway details, status) tied to invoices.
- **CommunicationLog**: Record of SMS/email/call interactions with timestamps and outcomes.
- **MarketingAsset**: SEO content items (landing pages, blog posts, FAQs) with metadata, canonical URLs, and publishing status.
- **Review**: Customer feedback captured post-service with rating, comments, and publication status.

## Relationships (High-Level)
- User 1..* TechnicianAssignment (for technician role)
- Customer 1..* Vehicle
- Customer 1..* Lead
- Lead 1..1 Quote (initial); Quote versions maintain history
- Quote 1..* QuoteLineItem
- QuoteLineItem *..1 ServiceCatalog and 0..* Part
- ServiceCatalog 0..* RepairGuide
- Quote 0..1 Job (upon acceptance)
- Job 1..* JobTask
- Job 1..* TechnicianAssignment; TechnicianAssignment 1..* TimeEntry
- Job 1..* InventoryTransaction (for parts usage)
- Job 1..1 WorkOrder
- WorkOrder 1..1 Invoice; Invoice 0..* Payment
- Customer 1..* Invoice; Customer 1..* CommunicationLog
- MarketingAsset linked to DispatchZone and ServiceCatalog for SEO targeting
- Review linked to Customer, Job, and MarketingAsset (for testimonials)

## Notes
- Employ UUIDs for public identifiers, integer primary keys internally.
- Maintain audit tables for Quote, Job, Invoice state changes.
- Implement soft deletes for Customer, Vehicle, MarketingAsset to preserve history.
- Support multi-location by associating Jobs, Inventory, and Marketing assets with DispatchZones.
