# Rollback Guide

This document provides procedures for rolling back the CFH Fund Management application to a previous version.

## Important Warnings

> **WARNING: Always backup before rolling back!**
> 
> - Database changes may not be backwards compatible
> - Rollback may cause data loss if migrations removed fields
> - Always test rollback in a staging environment first
> - Never rollback in production without a tested procedure

> **WARNING: Do not rollback if:**
> - Recent transactions are critical and cannot be re-entered
> - Database schema changes cannot be reversed safely
> - Active users are currently working in the system

---

## Quick Rollback (Git Checkout)

### Checkout a Specific Version

```bash
# See available tags
git tag -l

# Checkout a specific version
git checkout v1.0.0

# Or checkout a branch at a specific tag
git checkout -b rollback-v1.0.0 v1.0.0
```

### Rollback to Commit Hash

```bash
# Find the commit hash
git log --oneline

# Checkout specific commit (creates detached HEAD)
git checkout abc1234

# Or checkout a branch from that commit
git checkout -b emergency-rollback abc1234
```

---

## Safe Rollback Procedure

### Step 1: Backup Everything

```bash
# Backup database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Or using Laravel
php artisan backup:run

# Backup application files
tar -czf app_backup_$(date +%Y%m%d).tar.gz /path/to/application
```

### Step 2: Create Rollback Branch

```bash
# Create a new branch for rollback testing
git checkout -b rollback-to-v1.0.0

# Or from main branch
git checkout -b rollback-to-v1.0.0 main
```

### Step 3: Identify the Target

```bash
# List all versions
cat VERSION.md

# See commit history
git log --oneline --graph --all

# Find the last good commit
git log --oneline -20
```

### Step 4: Cherry-Pick or Revert (Alternative to Full Rollback)

If you need to undo specific changes rather than a full version:

```bash
# Revert a specific commit
git revert abc1234

# Push the revert
git push origin rollback-to-v1.0.0
```

### Step 5: Test in Staging

```bash
# Create staging environment
cp .env.staging .env
php artisan config:cache
php artisan migrate --force
```

### Step 6: Deploy to Production

Only after successful staging test:

```bash
# Merge rollback branch
git checkout main
git merge rollback-to-v1.0.0

# Deploy
php artisan down
git pull origin main
php artisan migrate --force
php artisan config:cache
php artisan up
```

---

## Database Rollback Considerations

### If Using Migrations

```bash
# Rollback last migration
php artisan migrate:rollback --step=1

# Rollback specific migration
php artisan migrate:rollback --path=/database/migrations/specific_file.php

# Check migration status
php artisan migrate:status
```

### Manual Database Rollback

If migrations cannot be safely reversed:

1. Identify the migration that changed the schema
2. Create a manual rollback SQL script
3. Test on staging database
4. Apply to production

```sql
-- Example rollback for adding a column
ALTER TABLE payments DROP COLUMN vendor_name;
```

---

## Rollback Branch Naming Convention

```
rollback-to-v{VERSION}
rollback-hotfix-{DATE}
rollback-{ISSUE_NUMBER}
```

Examples:
- `rollback-to-v1.0.0`
- `rollback-hotfix-20260423`
- `rollback-issue-123`

---

## Verification Checklist

After rollback, verify:

- [ ] Application loads without errors
- [ ] Login works for all users
- [ ] All CRUD operations function
- [ ] Data integrity maintained
- [ ] Filters work correctly
- [ ] Exports/Imports function
- [ ] Theme toggle works
- [ ] No console errors in browser

---

## Emergency Rollback (Production)

If critical issues in production:

```bash
# 1. Put app in maintenance mode
php artisan down --message="Emergency maintenance in progress"

# 2. Quick rollback to last known good
git reset --hard HEAD~1

# 3. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 4. Bring app back online
php artisan up

# 5. Monitor for issues
tail -f storage/logs/laravel.log
```

---

## Contact & Support

For assistance with rollback procedures, contact your system administrator or development team.

---

## Related Documentation

- [RELEASE_WORKFLOW.md](./RELEASE_WORKFLOW.md) - Release procedures
- [VERSION.md](../VERSION.md) - Version history
- [CHANGELOG.md](../CHANGELOG.md) - Change documentation