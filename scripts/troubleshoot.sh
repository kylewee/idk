#!/bin/bash
# Troubleshooting script for Mechanic Saint Augustine website
# This script checks the configuration and helps diagnose common issues

set -e

echo "================================================"
echo "Mechanic Saint Augustine - System Diagnostics"
echo "================================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Determine the site root directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SITE_ROOT="$(dirname "$SCRIPT_DIR")"

echo "Site root: $SITE_ROOT"
echo ""

# Check 1: Configuration file exists
echo "1. Checking configuration file..."
if [ -f "$SITE_ROOT/api/.env.local.php" ]; then
    echo -e "${GREEN}✓ Configuration file exists${NC}"
    
    # Check for placeholder values
    if grep -q "const TWILIO_ACCOUNT_SID = '';" "$SITE_ROOT/api/.env.local.php" 2>/dev/null; then
        echo -e "${YELLOW}⚠ Warning: TWILIO_ACCOUNT_SID appears to be empty${NC}"
    fi
    
    if grep -q "const CRM_USERNAME = '';" "$SITE_ROOT/api/.env.local.php" 2>/dev/null; then
        echo -e "${YELLOW}⚠ Warning: CRM_USERNAME appears to be empty${NC}"
    fi
else
    echo -e "${RED}✗ Configuration file missing${NC}"
    echo "  Expected: $SITE_ROOT/api/.env.local.php"
    echo "  Action: Copy .env.local.php.example to .env.local.php and configure"
fi
echo ""

# Check 2: Voice system files
echo "2. Checking voice system files..."
if [ -f "$SITE_ROOT/voice/incoming.php" ]; then
    echo -e "${GREEN}✓ Voice incoming handler exists${NC}"
else
    echo -e "${RED}✗ Voice incoming handler missing${NC}"
fi

if [ -f "$SITE_ROOT/voice/recording_callback.php" ]; then
    echo -e "${GREEN}✓ Recording callback handler exists${NC}"
else
    echo -e "${RED}✗ Recording callback handler missing${NC}"
fi
echo ""

# Check 3: API files
echo "3. Checking API files..."
if [ -f "$SITE_ROOT/api/quote_intake.php" ]; then
    echo -e "${GREEN}✓ Quote intake endpoint exists${NC}"
else
    echo -e "${RED}✗ Quote intake endpoint missing${NC}"
fi
echo ""

# Check 4: Log files and permissions
echo "4. Checking log files..."
if [ -f "$SITE_ROOT/voice/voice.log" ]; then
    LINES=$(wc -l < "$SITE_ROOT/voice/voice.log")
    echo -e "${GREEN}✓ Voice log exists (${LINES} entries)${NC}"
    echo "  Last entry: $(tail -1 "$SITE_ROOT/voice/voice.log" 2>/dev/null || echo 'N/A')"
else
    echo -e "${YELLOW}⚠ Voice log doesn't exist yet (will be created on first call)${NC}"
fi

if [ -f "$SITE_ROOT/api/quote_intake.log" ]; then
    LINES=$(wc -l < "$SITE_ROOT/api/quote_intake.log")
    echo -e "${GREEN}✓ Quote intake log exists (${LINES} entries)${NC}"
    
    # Check for recent CRM errors
    if tail -5 "$SITE_ROOT/api/quote_intake.log" 2>/dev/null | grep -q '"username is required"'; then
        echo -e "${RED}  ✗ Recent CRM authentication errors detected${NC}"
        echo "    Action: Set CRM_USERNAME and CRM_PASSWORD in .env.local.php"
    fi
else
    echo -e "${YELLOW}⚠ Quote intake log doesn't exist yet (will be created on first quote)${NC}"
fi
echo ""

# Check 5: CRM connectivity (if running)
echo "5. Checking CRM endpoint..."
CRM_URL="${CRM_URL:-https://mechanicstaugustine.com/crm/api/rest.php}"
if command -v curl &> /dev/null; then
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$CRM_URL" --max-time 5 2>/dev/null || echo "000")
    if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "405" ]; then
        echo -e "${GREEN}✓ CRM endpoint is accessible (HTTP $HTTP_CODE)${NC}"
    elif [ "$HTTP_CODE" = "000" ]; then
        echo -e "${YELLOW}⚠ CRM endpoint not reachable (timeout or connection refused)${NC}"
        echo "  This is normal for local development if site isn't running"
    else
        echo -e "${RED}✗ CRM endpoint returned HTTP $HTTP_CODE${NC}"
    fi
else
    echo -e "${YELLOW}⚠ curl not available, skipping connectivity check${NC}"
fi
echo ""

# Check 6: Docker environment (if applicable)
echo "6. Checking Docker environment..."
if [ -f "$SITE_ROOT/compose.yaml" ]; then
    echo -e "${GREEN}✓ Docker Compose file exists${NC}"
    
    if command -v docker &> /dev/null; then
        if docker ps --format "table {{.Names}}" 2>/dev/null | grep -q "idk"; then
            echo -e "${GREEN}✓ Docker containers are running${NC}"
            docker ps --format "table {{.Names}}\t{{.Status}}" | grep "idk" || true
        else
            echo -e "${YELLOW}⚠ Docker containers not running${NC}"
            echo "  Run: docker compose up -d --build"
        fi
    else
        echo -e "${YELLOW}⚠ Docker not available${NC}"
    fi
else
    echo -e "${YELLOW}⚠ Docker Compose file not found${NC}"
fi
echo ""

# Check 7: Common issues summary
echo "7. Common Issues & Solutions:"
echo "----------------------------"
echo ""

if [ -f "$SITE_ROOT/api/quote_intake.log" ]; then
    # Check for CRM auth issues
    if tail -10 "$SITE_ROOT/api/quote_intake.log" 2>/dev/null | grep -q "username is required\|No match for Username"; then
        echo -e "${RED}Issue: CRM Authentication Failures${NC}"
        echo "  Solution: Update CRM_USERNAME and CRM_PASSWORD in api/.env.local.php"
        echo ""
    fi
    
    # Check for Twilio issues
    if tail -10 "$SITE_ROOT/api/quote_intake.log" 2>/dev/null | grep -q "twilio_http_4"; then
        echo -e "${RED}Issue: Twilio SMS Failures${NC}"
        echo "  Solution: Update TWILIO_ACCOUNT_SID and TWILIO_AUTH_TOKEN in api/.env.local.php"
        echo ""
    fi
fi

# Generic recommendations
echo "Recommendations:"
echo "  1. Review the setup guide: docs/SETUP_GUIDE.md"
echo "  2. Check the runbook: docs/runbook.md"
echo "  3. Verify Twilio webhook configuration"
echo "  4. Test with a quote submission"
echo ""

echo "================================================"
echo "Diagnostics complete!"
echo "================================================"
echo ""
echo "For detailed setup instructions, see:"
echo "  $SITE_ROOT/docs/SETUP_GUIDE.md"
echo ""
