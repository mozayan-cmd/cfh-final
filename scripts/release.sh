#!/bin/bash

# CFH Fund Management - Release Script
# =====================================
# This script handles versioning and changelog updates for releases.
# It will NOT push or create tags automatically (manual for safety).

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Paths
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
VERSION_FILE="$PROJECT_DIR/VERSION.md"
CHANGELOG_FILE="$PROJECT_DIR/CHANGELOG.md"

echo -e "${BLUE}======================================${NC}"
echo -e "${BLUE}  CFH Fund Management Release Script${NC}"
echo -e "${BLUE}======================================${NC}"
echo ""

# Function to validate version format
validate_version() {
    local version=$1
    if [[ ! $version =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo -e "${RED}Error: Invalid version format. Use MAJOR.MINOR.PATCH (e.g., 1.2.3)${NC}"
        return 1
    fi
    return 0
}

# Function to get current version
get_current_version() {
    grep -E "^## Current Version: v[0-9]+\.[0-9]+\.[0-9]+" "$VERSION_FILE" | sed 's/## Current Version: v//'
}

# Function to compare versions
version_gt() {
    local v1=$1
    local v2=$2
    local gt=$(printf '%s\n%s\n' "$v1" "$v2" | sort -V | tail -n 1)
    [[ "$gt" == "$v1" && "$v1" != "$v2" ]]
}

# Get current version
current_version=$(get_current_version)
echo -e "${YELLOW}Current version: ${GREEN}v$current_version${NC}"
echo ""

# Prompt for new version
read -p "Enter new version number (MAJOR.MINOR.PATCH): " new_version

# Validate version format
if ! validate_version "$new_version"; then
    exit 1
fi

# Validate version is greater than current
if ! version_gt "$new_version" "$current_version"; then
    echo -e "${RED}Error: New version must be greater than current version (v$current_version)${NC}"
    exit 1
fi

echo ""
echo -e "${YELLOW}Preparing release: v$new_version${NC}"
echo ""

# Prompt for release notes
echo -e "${BLUE}Enter release notes (one per line, empty line to finish):${NC}"
echo -e "${YELLOW}(Prefix lines with ### Added, ### Changed, ### Fixed, ### Removed)${NC}"
echo ""

release_notes=""
while true; do
    read -p "> " line
    if [[ -z "$line" ]]; then
        if [[ -n "$release_notes" ]]; then
            break
        fi
    else
        release_notes="${release_notes}${line}"$'\n'
    fi
done

# Confirm before proceeding
echo ""
echo -e "${YELLOW}======================================${NC}"
echo -e "${YELLOW}Release Summary:${NC}"
echo -e "${YELLOW}======================================${NC}"
echo -e "  Current: ${RED}v$current_version${NC}"
echo -e "  New:     ${GREEN}v$new_version${NC}"
echo ""
echo -e "${BLUE}Changelog entries:${NC}"
echo "$release_notes"
echo ""

read -p "Proceed with release? (y/n): " confirm
if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
    echo -e "${RED}Release cancelled.${NC}"
    exit 0
fi

echo ""
echo -e "${BLUE}Updating files...${NC}"

# Update VERSION.md
sed -i.bak "s/## Current Version: v[0-9]\.[0-9]\.[0-9]*/## Current Version: v$new_version/" "$VERSION_FILE"
sed -i.bak "s/| v$new_version | $(date +%Y-%m-%d) |.*|/| v$new_version | $(date +%Y-%m-%d) | Initial stable release |/" "$VERSION_FILE"
rm -f "$VERSION_FILE.bak"

# Update CHANGELOG.md
today=$(date +%Y-%m-%d)
cat > /tmp/changelog_header.txt << EOF
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [$new_version] - $today

EOF

# Get existing changelog content (from ## [Unreleased] onwards)
existing_content=$(grep -A 1000 "## \[Unreleased\]" "$CHANGELOG_FILE" || echo "")

# Create new changelog with release section
cat > /tmp/changelog_new.txt << EOF
$(cat /tmp/changelog_header.txt)
$release_notes

### Changed
- Version bumped to v$new_version

### Technical
- Release v$new_version

$existing_content
EOF

mv /tmp/changelog_new.txt "$CHANGELOG_FILE"

echo -e "${GREEN}Updated VERSION.md${NC}"
echo -e "${GREEN}Updated CHANGELOG.md${NC}"

# Git staging
echo ""
echo -e "${BLUE}Git Operations:${NC}"
echo ""

echo "Files to be staged:"
echo "  - VERSION.md"
echo "  - CHANGELOG.md"
echo ""

read -p "Stage changes with git add? (y/n): " stage_confirm
if [[ "$stage_confirm" == "y" || "$stage_confirm" == "Y" ]]; then
    cd "$PROJECT_DIR"
    git add VERSION.md CHANGELOG.md
    echo -e "${GREEN}Changes staged.${NC}"
else
    echo -e "${YELLOW}Skipped staging. You can manually run: git add VERSION.md CHANGELOG.md${NC}"
fi

echo ""

# Commit
echo ""
read -p "Commit changes with message 'Release: v$new_version'? (y/n): " commit_confirm
if [[ "$commit_confirm" == "y" || "$commit_confirm" == "Y" ]]; then
    cd "$PROJECT_DIR"
    git commit -m "Release: v$new_version"
    echo -e "${GREEN}Commit created.${NC}"
else
    echo -e "${YELLOW}Skipped commit.${NC}"
fi

echo ""
echo -e "${GREEN}======================================${NC}"
echo -e "${GREEN}Release v$new_version Complete!${NC}"
echo -e "${GREEN}======================================${NC}"
echo ""
echo -e "${YELLOW}Next steps (manual for safety):${NC}"
echo "  1. Review the changes: git diff"
echo "  2. Push to remote: git push"
echo "  3. Create tag: git tag v$new_version"
echo "  4. Push tag: git push origin v$new_version"
echo ""