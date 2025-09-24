# Quote Form SMS opt-in

The quote widget at `quote/index.html` now supports an SMS follow-up:

- Leads can opt-in by checking “Text me my estimate”.
- Form submissions call `quote/quote_intake_handler.php`, which still mirrors the CRM lead creation flow and now sends a one-time text through Twilio when the opt-in flag is true.
- Visitors can choose from generated appointment slots (skipping Sundays); selections flow through to the CRM notes and are included in the SMS body.
- Configure your messaging number in `api/.env.local.php` by adding `const TWILIO_SMS_FROM = '+1XXXXXXXXXX';` (replace with your Twilio number that can send SMS). The handler also accepts `TWILIO_MESSAGING_SERVICE_SID` when you prefer to use a Twilio Messaging Service.
- If other integrations (Zapier, ad landing pages, etc.) still POST to `/api/quote_intake.php`, update them to hit `/quote/quote_intake_handler.php` or port these changes back into the original endpoint.
- Error details surface in the JSON response under `data.sms` so you can monitor Twilio issues from the UI or logs.

Reply STOP language is included in the outbound message for compliance, but verify your Twilio messaging profile covers this use case.
