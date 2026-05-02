# Version

## Current Version: v1.0.0

## Semantic Versioning (SemVer)

This project follows [Semantic Versioning 2.0.0](https://semver.org/):

```
MAJOR.MINOR.PATCH
```

### Version Number Format

- **MAJOR** version: Incompatible changes to the API, database schema changes, or major architectural updates
- **MINOR** version: New functionality in a backwards-compatible manner, backwards-compatible feature additions
- **PATCH** version: Backwards-compatible bug fixes and small improvements

### Versioning Rules

1. **Major (X.y.z)**
   - Breaking changes to existing functionality
   - Database migrations that are not backwards-compatible
   - Changes that require users to update their workflow
   - Complete UI redesign
   - Authentication system changes
   - API endpoint changes or removals

2. **Minor (x.Y.z)**
   - New modules or features added
   - New filters or search capabilities
   - New reports or dashboards
   - UI enhancements that add functionality
   - Backwards-compatible database changes (additive)
   - New integration capabilities

3. **Patch (x.y.Z)**
   - Bug fixes
   - Performance improvements
   - Security patches
   - Documentation updates
   - Code refactoring without behavior changes
   - Minor UI tweaks and styling fixes

### Pre-release Versions

Development versions may use pre-release suffixes:
- `v1.0.0-alpha.1` - Alpha release
- `v1.0.0-beta.1` - Beta release
- `v1.0.0-rc.1` - Release candidate

### When to Bump

| Change Type | Version Bump |
|-------------|--------------|
| Add new page/module | MINOR |
| Add new filter/dropdown | MINOR |
| Add new form fields | MINOR |
| Change existing API behavior | MAJOR |
| Database schema change | MAJOR |
| Bug fix | PATCH |
| Performance fix | PATCH |
| CSS/style update | PATCH |
| Documentation only | PATCH |

## Version History

| Version | Date | Description |
|---------|------|-------------|
| v1.0.0 | 2026-04-23 | Initial stable release with core modules |