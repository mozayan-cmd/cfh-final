# April 2025 Fixes

## Fix 1: Expense - Boat Name Optional

**Date:** 25 April 2025

**Issue:** When recording expenses, boat name was required. Users have expenses unrelated to specific boats.

**Files Modified:**

1. `app/Http/Requests/StoreExpenseRequest.php`
   - Changed `boat_id` validation from `'required|exists:boats,id'` to `'nullable|exists:boats,id'`

2. `app/Http/Requests/UpdateExpenseRequest.php`
   - Changed `boat_id` validation from `'required|exists:boats,id'` to `'nullable|exists:boats,id'`

3. `resources/views/expenses/index.blade.php`
   - Create form: Removed `required` attribute, changed label to "Boat (Optional)", added "No Boat" option
   - Edit form: Removed `required` attribute, added "No Boat" option
   - Table display: Changed `$expense->boat->name` to `$expense->boat->name ?? '-'` for safe null handling

4. `app/Http/Controllers/ExpenseController.php`
   - `store()` method: Updated boat validation to only check if boat_id is provided, not required
   - `update()` method: Updated validation to handle nullable boat_id
   - Landing logic: Only process landing assignments when boat_id is provided

**Result:** Users can now record expenses without selecting a boat name.

---

## Fix 2: Cash Payment - Available Balance Error

**Date:** 25 April 2025

**Issue:** When recording payments unrelated to boat name (Cash mode), error appeared: "Payment amount exceeds available cash balance. Maximum allowed: ₹0.00" despite having cash balance of ₹54,400.

**Root Cause:** Two issues combined:

1. **Dashboard vs Payment calculation mismatch:**
   - Dashboard `getCashInHand()` includes loan receipts (Basheer, Personal, Others) as cash
   - Payment form `getAvailableCashReceipts()` only counted Receipt records, ignoring loan receipts

2. **Transaction linkage requirement:**
   - Old code filtered receipts requiring linked Transaction records
   - Receipts for rishad user were created from loans (not linked to receipts), so they were invisible

**Files Modified:**

1. `app/Services/CashSourceTrackingService.php`
   - `getAvailableCashReceipts()`: Rewrote to match dashboard calculation including:
     - Cash receipts (Receipt records)
     - Loan receipts (Transactions from Basheer/Personal/Others in Cash mode)
     - Cash withdrawals
     - Minus: Cash payments and cash deposits to bank
   - Creates a virtual receipt entry when user has cash but no Cash receipts

2. Cache cleared: `php artisan cache:clear` and `php artisan view:clear`

**Result:** Cash payment form now shows ₹54,400 available for rishad user (matching dashboard), allowing payments to be recorded correctly.