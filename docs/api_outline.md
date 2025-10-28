# API Outline

## Authentication & Authorization
- `POST /v1/auth/login` – Email/password -> JWT + refresh token.
- `POST /v1/auth/refresh`
- `POST /v1/auth/logout`
- `POST /v1/auth/password/reset` – initiate + complete flows.
- Roles: owner, dispatcher, technician, finance, marketing, customer.

## Users & Roles
- `GET /v1/users` – list/filter.
- `POST /v1/users`
- `PATCH /v1/users/{userId}`
- `DELETE /v1/users/{userId}` – soft delete.
- `GET /v1/users/{userId}/time-entries`

## Customers & Vehicles
- `GET /v1/customers`
- `POST /v1/customers`
- `GET /v1/customers/{customerId}`
- `PATCH /v1/customers/{customerId}`
- `GET /v1/customers/{customerId}/vehicles`
- `POST /v1/customers/{customerId}/vehicles`
- `PATCH /v1/vehicles/{vehicleId}`
- `GET /v1/vehicles/{vehicleId}/history`

## Leads & Quotes
- `POST /v1/leads` – intake from public form; automatically creates quote draft.
- `GET /v1/leads`
- `GET /v1/leads/{leadId}`
- `POST /v1/leads/{leadId}/quotes` – create versioned quote.
- `GET /v1/quotes`
- `GET /v1/quotes/{quoteId}`
- `PATCH /v1/quotes/{quoteId}` – adjust pricing, status.
- `POST /v1/quotes/{quoteId}/accept` – converts to job; triggers workflow.
- `GET /v1/quotes/{quoteId}/pdf`

## Catalog & Pricing
- `GET /v1/services`
- `POST /v1/services`
- `PATCH /v1/services/{serviceId}`
- `GET /v1/services/{serviceId}/guides`
- `GET /v1/labor-rates`
- `POST /v1/labor-rates`
- `GET /v1/parts`
- `POST /v1/parts`
- `PATCH /v1/parts/{partId}`
- `POST /v1/catalog/import` – bulk upload labor/parts data.

## Scheduling & Jobs
- `GET /v1/jobs`
- `POST /v1/jobs` – manual job creation.
- `GET /v1/jobs/calendar` – aggregated schedule feed.
- `GET /v1/jobs/{jobId}`
- `PATCH /v1/jobs/{jobId}` – status updates, reschedule.
- `POST /v1/jobs/{jobId}/assignments`
- `DELETE /v1/jobs/{jobId}/assignments/{assignmentId}`
- `POST /v1/jobs/{jobId}/tasks`
- `PATCH /v1/jobs/{jobId}/tasks/{taskId}`
- `POST /v1/jobs/{jobId}/checklist` – update step completion.

## Technician App
- `GET /v1/technicians/me/jobs?date=today`
- `POST /v1/jobs/{jobId}/time-entries`
- `POST /v1/jobs/{jobId}/media` – upload photos/videos.
- `POST /v1/jobs/{jobId}/signatures`
- `POST /v1/jobs/{jobId}/parts-used`
- `POST /v1/jobs/{jobId}/complete`

## Inventory
- `GET /v1/inventory/locations`
- `GET /v1/inventory/items`
- `POST /v1/inventory/items`
- `POST /v1/inventory/transactions`
- `GET /v1/inventory/transactions`
- `POST /v1/inventory/audit`

## Work Orders & Billing
- `GET /v1/work-orders`
- `GET /v1/work-orders/{workOrderId}`
- `GET /v1/invoices`
- `POST /v1/invoices`
- `PATCH /v1/invoices/{invoiceId}`
- `POST /v1/invoices/{invoiceId}/payments`
- `POST /v1/payments/webhook` – handle gateway callbacks.

## Communications & Notifications
- `GET /v1/communications`
- `POST /v1/communications` – send SMS/email.
- `POST /v1/notifications/templates`

## Marketing / SEO
- `GET /v1/marketing/assets`
- `POST /v1/marketing/assets`
- `PATCH /v1/marketing/assets/{assetId}`
- `POST /v1/sitemaps/rebuild`
- `GET /v1/analytics/seo` – key metrics snapshot.

## Reviews & Feedback
- `POST /v1/reviews`
- `GET /v1/reviews`
- `PATCH /v1/reviews/{reviewId}` – publish/hide, respond.

## Reporting & Dashboards
- `GET /v1/reporting/leads`
- `GET /v1/reporting/jobs`
- `GET /v1/reporting/revenue`
- `GET /v1/reporting/utilization`
- `GET /v1/reporting/marketing`

## Public Endpoints
- `POST /public/quotes` – sanitized public intake (rate-limited).
- `GET /public/services` – service catalog teaser with schema markup.
- `GET /public/locations`
- `GET /public/reviews`

## Webhooks & Integrations
- `POST /integrations/twilio/inbound`
- `POST /integrations/accounting/export`
- `POST /integrations/maps/routes`

## Notes
- JSON:API-style payloads; include pagination, filtering, sorting.
- All POST/PATCH endpoints validate against datamodel structs.
- Rate limiting and audit logging for sensitive actions.
- API versioning via `/v1`; plan for `/v2` when major breaking changes occur.
