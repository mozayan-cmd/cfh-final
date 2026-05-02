# CFH Fund Management System - Android App Prompt

## Project Overview

Build a **native Android application** for managing a fishing agency fund management system. This app tracks the complete financial lifecycle of fish landing operations - from boats returning with catch, to selling fish to buyers, collecting payments, paying boat owners, managing expenses, handling loans, and maintaining cash/bank balances.

**Project Name:** CFH Fund Manager  
**Platform:** Android (Native Kotlin)  
**Database:** SQLite (Room Database)  
**Min SDK:** API 24 (Android 7.0)  
**Target SDK:** API 34 (Android 14)  
**Architecture:** MVVM with Clean Architecture  
**UI:** Material Design 3 with Dark Theme

---

## Core Business Domain: Fishing Agency Operations

### What is a Fishing Agency?

A fishing agency operates boats that go out to sea to catch fish. When boats return (called "landing"), the agency:
1. Buys the fish catch from the boat owner at a negotiated price (gross value)
2. Sells the fish to buyers (fish merchants, markets, restaurants)
3. Collects payments from buyers
4. Pays the boat owner their share after deducting expenses and other dues

### Key Players

| Role | Description |
|------|-------------|
| **Boat Owner** | Owns the fishing boat, receives payment for fish catch |
| **Buyer** | Purchases fish from the agency (fish merchants, markets, restaurants) |
| **Agency** | Mediates between boats and buyers, manages all transactions |
| **Loan Sources** | External parties (Basheer, Personal, Others, etc.) who lend money |

---

## Core Business Workflow

### 1. Landing Process (Boat Returns)

```
Boat Returns → Create Landing Record
                    ↓
            Record Gross Value (price paid to boat owner)
                    ↓
            Add Expenses (Diesel, Ice, Ration, etc.)
                    ↓
            Create Invoices (sales to buyers)
                    ↓
            Record Receipts (collections from buyers)
                    ↓
            Make Payments (pay boat owner, settle expenses)
                    ↓
            Settlement Complete (when owner is fully paid)
```

### 2. Invoice & Receipt Cycle

```
Create Landing (gross_value = amount paid to boat)
        ↓
Create Invoice for each Buyer (what buyers owe)
        ↓
Buyer Pays (receipt recorded)
        ↓
Invoice Updated (received_amount, pending_amount)
        ↓
Invoice Status: Pending → Partial → Paid
```

### 3. Expense Management

```
Create Expense (Diesel, Ice, etc.)
        ↓
Link to Boat/Landing
        ↓
Make Payment (partial or full)
        ↓
Expense Updated (paid_amount, pending_amount)
        ↓
Status: Pending → Partial → Paid
```

### 4. Payment to Boat Owner

```
Landing Created (net_owner_payable = gross_value - expenses)
        ↓
Make Payments to Owner (cash or bank transfer)
        ↓
Landing Updated (owner_paid, owner_pending)
        ↓
Status: Open → Partial → Settled
```

### 5. Loan Management

```
Need Funds → Record Loan from Sources
        ↓
Loan Tracked Separately (not part of landing settlement)
        ↓
Repay Loan → Mark as Repaid (partial or full)
```

---

## Database Schema (Room SQLite)

### Entities

#### 1. Boat
```kotlin
@Entity(tableName = "boats")
data class Boat(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val name: String,
    val ownerPhone: String? = null,
    val createdAt: Long = System.currentTimeMillis(),
    val updatedAt: Long = System.currentTimeMillis()
)
```

#### 2. Buyer
```kotlin
@Entity(tableName = "buyers")
data class Buyer(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val name: String,
    val phone: String? = null,
    val address: String? = null,
    val notes: String? = null,
    val createdAt: Long = System.currentTimeMillis(),
    val updatedAt: Long = System.currentTimeMillis()
)
```

#### 3. Landing
```kotlin
@Entity(tableName = "landings")
data class Landing(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val boatId: Long,
    val date: Long, // Unix timestamp
    val grossValue: Double,
    val notes: String? = null,
    val status: String = "Open", // Open, Partial, Settled
    val createdAt: Long = System.currentTimeMillis(),
    val updatedAt: Long = System.currentTimeMillis()
)
```

#### 4. Expense
```kotlin
@Entity(tableName = "expenses")
data class Expense(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val boatId: Long,
    val landingId: Long? = null,
    val date: Long,
    val type: String, // Diesel, Ice, Ration, Petty Cash Advance, Unloading, Toll, Salary, Other
    val vendorName: String? = null,
    val amount: Double,
    val paidAmount: Double = 0.0,
    val pendingAmount: Double,
    val paymentStatus: String = "Pending", // Pending, Partial, Paid
    val notes: String? = null,
    val createdAt: Long = System.currentTimeMillis(),
    val updatedAt: Long = System.currentTimeMillis()
)
```

#### 5. Invoice
```kotlin
@Entity(tableName = "invoices")
data class Invoice(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val buyerId: Long,
    val boatId: Long,
    val landingId: Long,
    val invoiceDate: Long,
    val originalAmount: Double,
    val receivedAmount: Double = 0.0,
    val pendingAmount: Double,
    val status: String = "Pending", // Pending, Partial, Paid
    val notes: String? = null,
    val createdAt: Long = System.currentTimeMillis(),
    val updatedAt: Long = System.currentTimeMillis()
)
```

#### 6. Receipt
```kotlin
@Entity(tableName = "receipts")
data class Receipt(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val buyerId: Long,
    val invoiceId: Long,
    val boatId: Long,
    val landingId: Long,
    val date: Long,
    val amount: Double,
    val mode: String, // Cash, GP, Bank
    val source: String? = null, // Cash, Personal, Bank, Other
    val notes: String? = null,
    val createdAt: Long = System.currentTimeMillis(),
    val updatedAt: Long = System.currentTimeMillis()
)
```

#### 7. Payment
```kotlin
@Entity(tableName = "payments")
data class Payment(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val boatId: Long? = null,
    val landingId: Long? = null,
    val date: Long,
    val amount: Double,
    val mode: String, // Cash, GP, Bank
    val source: String? = null, // Cash, Personal, Bank, Basheer, Others
    val paymentFor: String, // Owner, Expense, Loan, Basheer, Personal, Mixed
    val loanReference: Long? = null,
    val notes: String? = null,
    val createdAt: Long = System.currentTimeMillis(),
    val updatedAt: Long = System.currentTimeMillis()
)
```

#### 8. PaymentAllocation (Polymorphic)
```kotlin
@Entity(tableName = "payment_allocations")
data class PaymentAllocation(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val paymentId: Long,
    val allocatableType: String, // Expense, Landing
    val allocatableId: Long,
    val amount: Double,
    val createdAt: Long = System.currentTimeMillis(),
    val updatedAt: Long = System.currentTimeMillis()
)
```

#### 9. Transaction (Ledger)
```kotlin
@Entity(tableName = "transactions")
data class Transaction(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val type: String, // Receipt, Payment
    val mode: String, // Cash, GP, Bank
    val source: String? = null,
    val amount: Double,
    val boatId: Long? = null,
    val landingId: Long? = null,
    val buyerId: Long? = null,
    val invoiceId: Long? = null,
    val transactionableType: String? = null, // Receipt, Payment, Loan
    val transactionableId: Long? = null,
    val date: Long,
    val notes: String? = null,
    val createdAt: Long = System.currentTimeMillis(),
    val updatedAt: Long = System.currentTimeMillis()
)
```

#### 10. Loan
```kotlin
@Entity(tableName = "loans")
data class Loan(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val source: String, // Basheer, Personal, Others, or custom
    val amount: Double,
    val repaidAmount: Double = 0.0,
    val date: Long,
    val mode: String = "Cash", // Cash, GP, Bank
    val notes: String? = null,
    val repaidAt: Long? = null,
    val createdAt: Long = System.currentTimeMillis(),
    val updatedAt: Long = System.currentTimeMillis()
)
```

#### 11. LoanSource
```kotlin
@Entity(tableName = "loan_sources")
data class LoanSource(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val name: String,
    val createdAt: Long = System.currentTimeMillis(),
    val updatedAt: Long = System.currentTimeMillis()
)
```

#### 12. ExpenseType
```kotlin
@Entity(tableName = "expense_types")
data class ExpenseType(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val name: String,
    val createdAt: Long = System.currentTimeMillis(),
    val updatedAt: Long = System.currentTimeMillis()
)
```

#### 13. PaymentType
```kotlin
@Entity(tableName = "payment_types")
data class PaymentType(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val name: String,
    val createdAt: Long = System.currentTimeMillis(),
    val updatedAt: Long = System.currentTimeMillis()
)
```

---

## Key Business Calculations

### Cash in Hand
```
Cash in Hand = Cash Receipts + Loan Receipts (Cash) + Cash Withdrawals - Cash Payments - Cash Deposited to Bank
```

### Cash at Bank
```
Cash at Bank = Bank/GP Receipts + Cash Deposited + Loan Receipts (Bank) - Bank/GP Payments - Cash Withdrawals
```

### Landing Net Owner Payable
```
Net Owner Payable = Gross Value - Total Expenses
```

### Landing Owner Pending
```
Owner Pending = Net Owner Payable - Owner Paid
```

### Invoice Pending
```
Invoice Pending = Original Amount - Received Amount
```

### Expense Pending
```
Expense Pending = Total Amount - Paid Amount
```

### Loan Outstanding
```
Loan Outstanding = Amount - Repaid Amount
```

---

## Features List

### Core Modules
1. **Dashboard** - Summary cards and recent activity
2. **Boats** - CRUD, list, detail view
3. **Buyers** - CRUD, list, detail view, buyer-wise totals
4. **Landings** - CRUD, detail with financial breakdown
5. **Invoices** - CRUD, link to buyer/landing
6. **Receipts** - CRUD, cascade selection (Boat → Landing → Buyer → Invoice)
7. **Expenses** - CRUD, type management
8. **Payments** - CRUD, allocation to expenses/landings
9. **Cash Management** - Utilization tracking, deposits, withdrawals
10. **Loans** - CRUD, source management, repayment
11. **Bank Management** - Balance, withdrawals

### Special Features
1. **Loan Sources** - Add/manage loan source types (Basheer, Personal, Others, etc.)
2. **Expense Types** - Add/manage expense types
3. **Payment Types** - Add/manage payment types
4. **Cash Source Tracking** - Link payments to specific receipts
5. **PDF Reports** - Settlement, Cash, Bank reports
6. **Backup/Restore** - Export/import database
7. **Authentication** - Login screen

### Dashboard Cards
1. Cash in Hand
2. Cash at Bank
3. Buyer Pending (total outstanding)
4. Boat Owner Pending
5. Expense Pending
6. Outstanding Loans

---

## UI/UX Requirements

### Theme
- Dark theme with glass-morphism cards
- Material Design 3 components
- Sidebar navigation (NavigationView/Drawer)
- Bottom navigation for quick access

### Screens

#### Main Navigation
- Dashboard (Home)
- Boats
- Landings
- Buyers
- Invoices (+ Import)
- Receipts
- Expenses
- Payments
- Cash Management
- Loans
- Bank Management
- Control Panel (Reports + Backups)

#### Forms
- Cascade dropdowns (Boat → Landing → Buyer → Invoice)
- Expense allocation to multiple targets
- Payment allocation to expenses

#### Status Badges
- Green: Settled/Paid
- Yellow: Partial
- Gray: Open/Pending
- Red: Overdue/Error

#### Landing Detail Page
- Full financial breakdown
- Receipt Summary (Cash vs Bank/GP)
- Payment Summary (Cash vs Bank/GP)
- Invoice table with status
- Expense table with status

---

## Technical Requirements

### Database
- **Room Persistence Library** for SQLite
- DAOs for each entity
- TypeConverters for dates
- Pre-populated tables: expense_types, payment_types, loan_sources

### Architecture
- **MVVM** (Model-View-ViewModel)
- **Clean Architecture** layers:
  - Presentation (UI, ViewModels)
  - Domain (Use Cases)
  - Data (Repositories, Room)
- **Hilt** for Dependency Injection
- **Kotlin Coroutines + Flow** for async operations

### UI Framework
- **Jetpack Compose** with Material 3
- **Navigation Compose** for routing
- ViewModels with StateFlow

### PDF Generation
- **iText** or **Android PDF Document API**
- Generate settlement reports, cash reports, bank reports

### Authentication
- SharedPreferences or EncryptedSharedPreferences
- Session-based login (keep user logged in)

### Data Export/Import
- CSV export for receipts, payments
- CSV import with preview
- Database backup as SQLite file

---

## Sample Data (Pre-populate)

### Default Expense Types
- Diesel
- Ice
- Ration
- Petty Cash Advance
- Unloading
- Toll
- Salary
- Other

### Default Payment Types
- Owner
- Expense
- Loan
- Basheer
- Personal
- Mixed

### Default Loan Sources
- Basheer
- Personal
- Others

### Demo Data (Optional)
- 3 Boats
- 5 Buyers
- Multiple Landings with various statuses
- Sample receipts and payments

---

## File Structure (Android Project)

```
app/
├── src/main/
│   ├── java/com/cfh/fundmanager/
│   │   ├── di/                    # Hilt modules
│   │   ├── data/
│   │   │   ├── local/           # Room database, DAOs
│   │   │   ├── repository/     # Repository implementations
│   │   │   └── model/        # Entity classes
│   │   ├── domain/
│   │   │   ├── model/         # Domain models
│   │   │   └── usecase/      # Business logic
│   │   ├── ui/
│   │   │   ├── theme/        # Material theme
│   │   │   ├── components/   # Reusable composables
│   │   │   ├── navigation/  # Nav graph
│   │   │   └── screens/    # Screen composables + ViewModels
│   │   └── CFHApplication.kt
│   └── res/
│       ├── values/
│       ├── drawable/
│       └── xml/
└── build.gradle.kts
```

---

## Implementation Notes

### 1. Cascade Dropdown Flow
When creating a receipt:
- Select Boat → Filtered landings for that boat appear
- Select Landing → Filtered buyers with pending invoices appear
- Select Buyer → Filtered pending invoices appear
- Select Invoice → Enter amount (validated against pending)

### 2. Payment Allocation
When paying expenses:
- Select boat and landing
- Show pending expenses for that boat/landing
- Allow multiple expense selection
- Payment amount distributed across selected expenses

### 3. Loan System
- Loans recorded manually with source selection
- Mode determines which balance increases
- Partial repayment supported
- Transaction created automatically

### 4. Cash Tracking
- Link cash payments to specific receipts
- Track utilization (used/deposited/remaining)
- Deposit cash to bank from specific receipt

### 5. Status Computations
- Invoice status: computed from received_amount
- Expense status: computed from paid_amount
- Landing status: computed from owner_pending
- Loan status: computed from repaid_amount

---

## Success Criteria

The app should:
1. Track complete landing cycle from boat return to owner settlement
2. Manage buyer invoices and receipt collection
3. Handle all expense payments with allocation
4. Track cash and bank balances accurately
5. Manage external loans with dynamic sources
6. Generate reports
7. Enable database backup/restore
8. Provide dashboard overview of all financial metrics
9. Work offline with local SQLite database

---

## Similar Web App Reference

This Android app should replicate the functionality of the Laravel web app at:
- **Repository:** CFH Refined (Laravel 12)
- **Database:** SQLite
- **Features:** Fully documented in this project's memory.md, prompt.md, project.md files

The web app has been tested and all features work correctly. Use it as reference for business logic and calculations.

---

*This prompt is for building a native Android equivalent of the CFH Fund Management System.*