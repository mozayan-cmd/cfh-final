# Fund Flow Report - Design Specification

**Date**: 2026-05-01  
**Status**: Approved  
**Author**: Brainstorming Session (AI + User)

---

## Overview

Add a new "Fund Flow Report" to the Control Panel that shows all fund movements (inflows and outflows) across all modes (Cash, Bank, GP) for a user-selectable date range or all-time. Report is category-wise with summary, and supports PDF and Excel exports.

---

## Architecture

### Files to Create/Modify

| File | Action | Purpose |
|------|--------|---------|
| `app/Services/FundFlowReportService.php` | New | Query tables separately, combine fund flow data |
| `app/Exports/FundFlowReportExport.php` | New | maatwebsite/excel export class |
| `resources/views/reports/fund-flow.blade.php` | New | Report page with filters and category display |
| `resources/views/reports/fund-flow-pdf.blade.php` | New | Print-optimized PDF template |
| `app/Http/Controllers/ReportController.php` | Modify | Add fundFlow(), fundFlowPdf(), fundFlowExcel() |
| `routes/web.php` | Modify | Add routes for fund flow report |

### Dependencies to Install

- `maatwebsite/excel` (for Excel exports)

---

## Service Class: FundFlowReportService

### Method: getFundFlow($startDate = null, $endDate = null)

**Parameters**:
- `$startDate` (string|null): Start date in Y-m-d format, or null for no filter
- `$endDate` (string|null): End date in Y-m-d format, or null for no filter

**Returns**: Array with structure:
```php
[
    'summary' => [
        'total_inflows' => 123456.78,
        'total_outflows' => 98765.43,
        'net_change' => 24691.35,
    ],
    'categories' => [
        'receipts' => [
            'label' => 'Receipts (Inflow)',
            'transactions' => [...], // Each: date, amount, mode, buyer_name, notes
            'total' => 100000.00,
        ],
        'loans' => [
            'label' => 'Loans (Inflow)',
            'transactions' => [...], // Each: date, amount, source, notes
            'total' => 50000.00,
        ],
        'payments' => [
            'label' => 'Payments (Outflow)',
            'transactions' => [...], // Each: date, amount, mode, payment_for, boat_name, notes
            'total' => 60000.00,
        ],
        'expenses' => [
            'label' => 'Expenses (Outflow)',
            'transactions' => [...], // Each: date, amount, mode, type, vendor_name, boat_name
            'total' => 30000.00,
        ],
        'withdrawals' => [
            'label' => 'Withdrawals (Outflow)',
            'transactions' => [...], // Each: date, amount, notes
            'total' => 1700.00,
        ],
    ],
]
```

**Query Logic**:

1. **Receipts (Inflow)**: Query `receipts` table with `where('user_id', auth()->id())`, filter by date if provided, eager load `buyer`

2. **Payments (Outflow)**: Query `payments` table with `where('user_id', auth()->id())`, filter by date if provided, eager load `boat`

3. **Expenses (Outflow)**: Query `expenses` table with `where('user_id', auth()->id())`, filter by date if provided, eager load `boat`

4. **Loans (Inflow)**: Query `loans` table with `where('user_id', auth()->id())`, filter by date if provided

5. **Withdrawals (Outflow)**: Query `transactions` table with `where('user_id', auth()->id())->where('type', 'Payment')->where('mode', 'Cash')->where('source', 'Cash')->where('notes', 'like', '%Withdrawal%')`, filter by date if provided

6. **Date Filter**: Apply `whereBetween('date', [$startDate, $endDate])` only if both dates provided

---

## Controller: ReportController Modifications

### Method: fundFlow(Request $request)

- Validates `start_date` and `end_date` (nullable dates)
- Calls `FundFlowReportService::getFundFlow()` with dates
- Returns `reports/fund-flow.blade.php` with data

### Method: fundFlowPdf(Request $request)

- Validates dates (same as above)
- Gets fund flow data
- Loads `reports/fund-flow-pdf` view via `Pdf::loadView()`
- Downloads as `fund-flow-report-YYYY-MM-DD.pdf`

### Method: fundFlowExcel(Request $request)

- Validates dates (same as above)
- Gets fund flow data
- Returns `Excel::download(new FundFlowReportExport($data), 'fund-flow-report-YYYY-MM-DD.xlsx')`

---

## Excel Export Class: FundFlowReportExport

**Implements**: `WithMultipleSheets`

**Sheets** (always multiple sheets):
1. **Summary** - Total inflows, outflows, net change
2. **Receipts** - Date, Buyer, Mode, Amount, Notes
3. **Loans** - Date, Source, Amount, Notes
4. **Payments** - Date, Type, Boat, Amount, Notes
5. **Expenses** - Date, Type, Vendor, Boat, Amount
6. **Withdrawals** - Date, Amount, Notes

---

## UI: reports/fund-flow.blade.php

### Filter Section (top)
- Start Date: `<input type="date" name="start_date">` (optional)
- End Date: `<input type="date" name="end_date">` (optional)
- "Generate Report" button (submit form)
- "Export PDF" button (link to `reports.fund-flow.pdf` with same date params)
- "Export Excel" button (link to `reports.fund-flow.excel` with same date params)

### Summary Cards (below filters)
- Total Inflows: displayed in green
- Total Outflows: displayed in red
- Net Change: green if positive, red if negative

### Category Sections (below summary)
Each category in a collapsible `<details>` / `<summary>` HTML element (no JavaScript required):
- **Receipts (Inflow)** - Table: Date, Buyer, Mode, Amount
- **Loans (Inflow)** - Table: Date, Source, Amount
- **Payments (Outflow)** - Table: Date, Type, Boat, Amount
- **Expenses (Outflow)** - Table: Date, Type, Vendor, Boat, Amount
- **Withdrawals (Outflow)** - Table: Date, Amount, Notes

All tables show individual transactions and a "Total" row at the bottom.

---

## Routes to Add (in routes/web.php)

Under existing `reports.` prefix group:
```php
Route::get('/fund-flow', [ReportController::class, 'fundFlow'])->name('fund-flow');
Route::get('/fund-flow/pdf', [ReportController::class, 'fundFlowPdf'])->name('fund-flow.pdf');
Route::get('/fund-flow/excel', [ReportController::class, 'fundFlowExcel'])->name('fund-flow.excel');
```

Add links to these routes in the Control Panel (`reports.index` view).

---

## PDF Template: reports/fund-flow-pdf.blade.php

- Print-optimized (no Tailwind CSS framework, inline styles only)
- Simple table layout for each category
- Summary section at top
- Generated date/time in footer

---

## Key Decisions

1. **Query tables separately** (not using Transaction table) as per user preference
2. **Category-wise display** as per user preference (not chronological)
3. **All fund flow** (Cash + Bank + GP combined) as per user preference
4. **maatwebsite/excel** for Excel exports (industry standard)
5. **Follow existing patterns**: Service class + Controller (like DashboardSummaryService, CashReportController)
6. **Date filter**: If both dates empty, show all-time data (no filter applied)

---

## Success Criteria

- [ ] User can access Fund Flow Report from Control Panel
- [ ] User can filter by date range (start + end) or view all-time
- [ ] Report shows category-wise summary (Receipts, Loans, Payments, Expenses, Withdrawals)
- [ ] Summary cards show Total Inflows, Total Outflows, Net Change
- [ ] PDF export downloads correctly with all categories
- [ ] Excel export downloads correctly with all categories (multiple sheets preferred)
- [ ] Report only shows data for authenticated user (user_id filter)
- [ ] Dark mode / light mode readability maintained
