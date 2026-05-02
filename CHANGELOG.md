# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [v1.0.0] - 2026-04-23

### Added

#### Core Modules
- **Dashboard** - Overview with financial metrics, recent activity, quick navigation
- **Boats** - CRUD operations for fishing vessel management
- **Landings** - Track individual fishing trips with financial settlement
- **Buyers** - Manage buyer accounts with aggregated statistics
- **Invoices** - Create and manage buyer invoices
- **Receipts** - Record buyer payments against invoices
- **Expenses** - Track expenses with payment status
- **Payments** - Record owner, expense, and loan payments
- **Cash Management** - Track cash utilization and deposits
- **Loans** - Track loans from various sources
- **Bank Management** - Track bank balance and withdrawals
- **Control Panel** - Access reports and backup functionality
- **User Management** - Admin-only user CRUD
- **Unlinked Expenses** - Admin-only unlinked expense management

#### Features
- **Dark/Light Theme Toggle** - Persistent theme with localStorage
- **Sticky Table Headers** - Scrollable tables with fixed headers
- **Filter Auto-Submit** - Dropdowns auto-submit on change
- **CSV Export** - Export data from all list pages
- **CSV Import** - Import data with paste mode or file upload
- **Delete Confirmation** - Modal shows related records before deletion
- **Inline Modals** - Create/Edit forms in modals
- **Responsive Design** - Works on mobile and desktop

#### Filters
- **Payments**: Boat, Landing Date, Payment Mode, Vendor
- **Expenses**: Boat, Type, Vendor Status (Paid/Pending groups)
- **Invoices**: Buyer, Boat, Landing Date, Status
- **Receipts**: Buyer, Boat, Landing Date, Payment Mode
- **Buyers**: Sort by Name, Purchased, Received, Pending

#### Calculated Fields
- Invoice status (Pending, Partial, Paid)
- Expense payment status (Pending, Partial, Paid)
- Landing settlement status (Pending, Partial, Settled)
- Buyer pending amounts
- Loan outstanding amounts
- Cash balance per receipt

#### Security
- User-scoped data isolation (all queries filter by user_id)
- Admin role for elevated access
- CSRF protection on all forms
- Payment balance validation (cannot exceed available)

### Changed

#### UI/UX
- Light mode as default theme
- Consistent card-based layout
- Consistent table styling with scrollable containers
- Dark mode support for all form elements
- Improved text contrast for readability

#### Filters (Improvements)
- **Removed**: Payment Source filter from Payments page
- **Removed**: Payment For filter from Payments page
- **Removed**: Status filter from Expenses page
- **Added**: Vendor filter to Payments page (dynamic from existing data)

### Fixed

- Table scroll behavior consistency across all pages
- Delete confirmation shows related records
- Receipt deletion properly reverses invoice balance
- Payment deletion properly reverses allocations
- Import validation with proper error messages
- Dark mode form styling consistency

### Technical

#### Database Schema
- Payment allocations for multi-expense payments
- Transaction logging for all financial operations
- Morph relationships for polymorphic associations

#### Services
- PaymentPostingService - Handles payment creation with allocations
- ExpenseSettlementService - Manages expense payment tracking
- LandingSummaryService - Calculates landing financials
- InvoicePostingService - Handles receipt posting
- InvoiceImportService - Parses and imports invoices
- CashSourceTrackingService - Tracks cash utilization

[Unreleased]: https://github.com/example/cfh-fund-management/compare/v1.0.0...HEAD
[v1.0.0]: https://github.com/example/cfh-fund-management/releases/tag/v1.0.0