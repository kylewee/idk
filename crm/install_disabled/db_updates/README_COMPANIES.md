# Companies Entity Migration

## Overview
This migration adds a new "Companies" entity to the Rukovoditel CRM system. The Companies entity allows tracking and managing company information including contact details, industry, and status.

## Problem Statement
"shere is sod.company" - This cryptic message led to the creation of a comprehensive Companies entity for the CRM system.

## What's Added

### Entity Configuration
- **Entity ID**: 25
- **Entity Name**: Companies
- **Menu Display**: Enabled
- **Parent Entity**: None (top-level entity)

### Fields
The Companies entity includes the following fields:

1. **Company Name** (required, searchable) - Primary heading field
2. **Status** (required) - Dropdown with options: Active, Inactive, Prospect
3. **Industry** - Dropdown with options: Automotive, Technology, Healthcare, Manufacturing, Retail, Service, Other
4. **Website** - URL field
5. **Phone** - Text input
6. **Email** - Email field (searchable)
7. **Address** - Textarea
8. **Notes** - Textarea
9. **Date Added** - Auto-populated
10. **Created By** - Auto-populated

### Access Rights
The entity includes three access level configurations:
- **Group 6**: View assigned items
- **Group 5**: View, create, update, and reports access
- **Group 4**: Full access (view, create, update, delete, reports)

## Installation

### Manual Installation
To install this migration manually, run the SQL file against your Rukovoditel database:

```bash
mysql -u your_username -p your_database < add_companies_entity.sql
```

### Docker Installation
If using the Docker setup:

```bash
docker compose exec db mysql -u mechanic -pmechanic mechaniccrm < crm/install_disabled/db_updates/add_companies_entity.sql
```

## Usage
After installation, the Companies entity will appear in the CRM menu. Users can:
- Add new companies
- Track company status and industry
- Store contact information
- View and filter companies based on various criteria
- Generate reports on companies

## Notes
- The entity uses ID 25 (next sequential ID after existing entities)
- All default CRM field types are utilized
- Comments are enabled for the Companies entity
- The entity follows the standard Rukovoditel entity structure
