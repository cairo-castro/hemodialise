#!/bin/sh
# ==============================================
# Environment Variables Validation Script
# Validates that all required environment variables are set
# ==============================================

set -e

echo "🔍 Validating environment variables..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

ERRORS=0
WARNINGS=0

# Function to check required variable
check_required() {
    VAR_NAME=$1
    VAR_VALUE=$(eval echo \$$VAR_NAME)

    if [ -z "$VAR_VALUE" ]; then
        echo "${RED}❌ ERROR: $VAR_NAME is not set${NC}"
        echo "   Configure it in Dokploy: Projects > qualidade > Environment Variables"
        ERRORS=$((ERRORS + 1))
        return 1
    else
        echo "${GREEN}✅ $VAR_NAME is set${NC}"
        return 0
    fi
}

# Function to check optional variable
check_optional() {
    VAR_NAME=$1
    VAR_VALUE=$(eval echo \$$VAR_NAME)

    if [ -z "$VAR_VALUE" ]; then
        echo "${YELLOW}⚠️  WARNING: $VAR_NAME is not set (optional)${NC}"
        WARNINGS=$((WARNINGS + 1))
        return 1
    else
        echo "${GREEN}✅ $VAR_NAME is set${NC}"
        return 0
    fi
}

# Function to validate value
check_value() {
    VAR_NAME=$1
    EXPECTED=$2
    VAR_VALUE=$(eval echo \$$VAR_NAME)

    if [ "$VAR_VALUE" != "$EXPECTED" ]; then
        echo "${RED}❌ ERROR: $VAR_NAME=$VAR_VALUE (expected: $EXPECTED)${NC}"
        ERRORS=$((ERRORS + 1))
        return 1
    else
        echo "${GREEN}✅ $VAR_NAME=$VAR_VALUE${NC}"
        return 0
    fi
}

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "   Application Configuration"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
check_required "APP_NAME"
check_value "APP_ENV" "production"
check_required "APP_KEY"
check_value "APP_DEBUG" "false"
check_required "APP_URL"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "   Database Configuration"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
check_required "DB_CONNECTION"
check_required "DB_HOST"
check_required "DB_PORT"
check_required "DB_DATABASE"
check_required "DB_USERNAME"
check_required "DB_PASSWORD"

# Validate DB_CONNECTION is MariaDB/MySQL
if [ "$DB_CONNECTION" != "mariadb" ] && [ "$DB_CONNECTION" != "mysql" ]; then
    echo "${RED}❌ ERROR: DB_CONNECTION=$DB_CONNECTION${NC}"
    echo "   Expected: mariadb or mysql"
    echo "   Current SQLite will cause migration failures!"
    ERRORS=$((ERRORS + 1))
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "   Security & Authentication"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
check_optional "JWT_SECRET"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "   Cache & Session"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
check_optional "CACHE_STORE"
check_optional "SESSION_DRIVER"
check_optional "QUEUE_CONNECTION"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "   Validation Summary"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Errors: $ERRORS"
echo "Warnings: $WARNINGS"
echo ""

if [ $ERRORS -gt 0 ]; then
    echo "${RED}❌ VALIDATION FAILED!${NC}"
    echo ""
    echo "Required environment variables are missing."
    echo "Please configure them in Dokploy:"
    echo "  1. Access: http://212.85.1.175:3000"
    echo "  2. Go to: Projects > qualidade > qualidadehd"
    echo "  3. Click: Environment tab"
    echo "  4. Add all required variables"
    echo ""
    echo "See DOKPLOY-ENV-GUIDE.md for complete instructions"
    echo ""
    exit 1
fi

echo "${GREEN}✅ All required environment variables are set!${NC}"
echo ""

exit 0
