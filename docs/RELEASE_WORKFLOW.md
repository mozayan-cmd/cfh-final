# Release Workflow

This document outlines the release process for the CFH Fund Management application.

## Overview

The release workflow ensures:
- Version consistency across the application
- Proper documentation of changes
- Safe deployment procedures
- Rollback capability

## Version Bump Guidelines

### When to Bump Version

| Change Type | Example | Bump |
|-------------|---------|------|
| Bug fix | Fixed table scroll behavior | PATCH |
| Performance | Improved query speed | PATCH |
| CSS/Styling | Fixed dark mode colors | PATCH |
| New filter | Added vendor filter to Payments | MINOR |
| New page | Added User Management | MINOR |
| New feature | Added import/export functionality | MINOR |
| Breaking change | Changed database schema | MAJOR |
| API change | Modified API endpoints | MAJOR |
| Auth change | New authentication system | MAJOR |

### Version Format

```
v{MAJOR}.{MINOR}.{PATCH}
```

Examples:
- `v1.0.0` - Initial release
- `v1.1.0` - Added new features
- `v1.1.1` - Bug fix release
- `v2.0.0` - Breaking changes

---

## Release Process

### Standard Release (Using Script)

1. **Navigate to project root**
   ```bash
   cd /path/to/cfh-fund-management
   ```

2. **Run release script**
   ```bash
   ./scripts/release.sh
   ```

3. **Follow prompts**
   - Enter version number
   - Add changelog entries
   - Confirm changes
   - Stage files
   - Create commit

4. **Push to remote**
   ```bash
   git push origin main
   ```

5. **Create and push tag**
   ```bash
   git tag v1.2.0
   git push origin v1.2.0
   ```

### Manual Release

1. **Update VERSION.md**
   ```bash
   # Edit VERSION.md and update:
   ## Current Version: v1.2.0
   ```

2. **Update CHANGELOG.md**
   ```markdown
   ## [v1.2.0] - 2026-04-23

   ### Added
   - New feature description

   ### Fixed
   - Bug fix description
   ```

3. **Commit changes**
   ```bash
   git add VERSION.md CHANGELOG.md
   git commit -m "Release: v1.2.0"
   ```

4. **Push and tag**
   ```bash
   git push origin main
   git tag v1.2.0
   git push origin v1.2.0
   ```

---

## Hotfix Release

For critical bug fixes that cannot wait for normal release cycle:

1. **Create hotfix branch**
   ```bash
   git checkout main
   git pull origin main
   git checkout -b hotfix-v1.2.1
   ```

2. **Make targeted fixes**
   ```bash
   # Fix the bug
   # Test thoroughly
   ```

3. **Update version (hotfix bump)**
   ```bash
   # Edit VERSION.md: Current Version: v1.2.1
   # Edit CHANGELOG.md with fix details
   ```

4. **Commit and merge**
   ```bash
   git add VERSION.md CHANGELOG.md
   git commit -m "Hotfix: v1.2.1"
   git push origin hotfix-v1.2.1
   ```

5. **Create PR or merge directly**
   ```bash
   # Option A: Merge to main
   git checkout main
   git merge hotfix-v1.2.1
   
   # Option B: Create PR for review
   # (preferred for production)
   ```

6. **Tag and deploy**
   ```bash
   git tag v1.2.1
   git push origin main --tags
   ```

---

## Tagging

### Tag Format
```
v{MAJOR}.{MINOR}.{PATCH}[-{suffix}]
```

### Tag Examples
- `v1.0.0` - Stable release
- `v1.1.0` - Feature release
- `v1.1.1` - Patch release
- `v1.2.0-beta.1` - Beta release

### Creating Tags

```bash
# Annotated tag (recommended)
git tag -a v1.2.0 -m "Release v1.2.0 with new features"

# Lightweight tag
git tag v1.2.0

# Push specific tag
git push origin v1.2.0

# Push all tags
git push --tags
```

### Listing Tags

```bash
# List all tags
git tag -l

# List tags with details
git tag -l -n

# Search tags
git tag -l "v1.*"
```

---

## Pre-Release Checklist

Before any release, verify:

- [ ] All tests pass
- [ ] No console errors in browser
- [ ] All filters work correctly
- [ ] Theme toggle works
- [ ] Import/Export functions
- [ ] Dark mode displays correctly
- [ ] Mobile responsiveness
- [ ] Database migrations safe
- [ ] VERSION.md updated
- [ ] CHANGELOG.md updated
- [ ] Changes committed
- [ ] Tag created

---

## Deployment

### Development
```bash
# Pull latest
git pull origin main

# Install dependencies
composer install
npm install

# Run migrations
php artisan migrate

# Clear caches
php artisan config:cache
php artisan view:cache
```

### Production
```bash
# Enable maintenance mode
php artisan down

# Pull and update
git pull origin main
composer install --optimize
php artisan migrate --force

# Clear and optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Disable maintenance mode
php artisan up
```

---

## Rollback

If issues occur after release, see [ROLLBACK.md](./ROLLBACK.md) for detailed procedures.

Quick rollback:
```bash
# Revert to previous commit
git revert HEAD
git push origin main

# Or checkout specific tag
git checkout v1.1.0
```

---

## Version Display

The application version is displayed in the footer. To update:

1. **Add version helper function** (if not exists)
   ```php
   // app/Helpers/app_helper.php
   function app_version() {
       $version = trim(file_get_contents(base_path('VERSION.md')));
       preg_match('/## Current Version: (v[\d\.]+)/', $version, $matches);
       return $matches[1] ?? 'v1.0.0';
   }
   ```

2. **Display in footer**
   ```blade
   <footer>
       <p>CFH Fund Management {{ app_version() }}</p>
   </footer>
   ```

---

## Git Flow Summary

```
main (production)
  │
  ├── feature/xxx ── Merge ──► main
  │                        │
  │                        ▼
  │                   Tag v1.x.x
  │                        │
  │                        ▼
  │                   Deploy
  │
  └── hotfix/xxx ── Merge ──► main (if critical)
```

---

## Related Documents

- [VERSION.md](../VERSION.md) - Version rules
- [CHANGELOG.md](../CHANGELOG.md) - Change log
- [ROLLBACK.md](./ROLLBACK.md) - Rollback procedures