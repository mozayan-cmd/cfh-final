<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update payments table - recreate with new enum values
        DB::statement('DROP TABLE IF EXISTS payments_new');
        DB::statement('CREATE TABLE payments_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            boat_id INTEGER,
            landing_id INTEGER,
            date DATE NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            mode VARCHAR(255) NOT NULL,
            source VARCHAR(255) NOT NULL,
            payment_for VARCHAR(255) NOT NULL,
            loan_reference VARCHAR(255) NULL,
            notes TEXT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (boat_id) REFERENCES boats(id),
            FOREIGN KEY (landing_id) REFERENCES landings(id)
        )');

        // Copy data with updated source values
        DB::statement("INSERT INTO payments_new (id, boat_id, landing_id, date, amount, mode, source, payment_for, loan_reference, notes, created_at, updated_at)
            SELECT id, boat_id, landing_id, date, amount, mode, 
                CASE 
                    WHEN source = 'CashFromSales' THEN 'Cash'
                    WHEN source = 'PersonalFund' THEN 'Personal'
                    ELSE source
                END as source,
                payment_for, loan_reference, notes, created_at, updated_at
            FROM payments");

        DB::statement('DROP TABLE payments');
        DB::statement('ALTER TABLE payments_new RENAME TO payments');

        // Update receipts table
        DB::statement('DROP TABLE IF EXISTS receipts_new');
        DB::statement('CREATE TABLE receipts_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            buyer_id INTEGER NOT NULL,
            invoice_id INTEGER NOT NULL,
            boat_id INTEGER NOT NULL,
            landing_id INTEGER NOT NULL,
            date DATE NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            mode VARCHAR(255) NOT NULL,
            source VARCHAR(255) NULL,
            notes TEXT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (buyer_id) REFERENCES buyers(id),
            FOREIGN KEY (invoice_id) REFERENCES invoices(id),
            FOREIGN KEY (boat_id) REFERENCES boats(id),
            FOREIGN KEY (landing_id) REFERENCES landings(id)
        )');

        DB::statement("INSERT INTO receipts_new (id, buyer_id, invoice_id, boat_id, landing_id, date, amount, mode, source, notes, created_at, updated_at)
            SELECT id, buyer_id, invoice_id, boat_id, landing_id, date, amount, mode,
                CASE 
                    WHEN source = 'CashFromSales' THEN 'Cash'
                    WHEN source = 'PersonalFund' THEN 'Personal'
                    ELSE source
                END as source,
                notes, created_at, updated_at
            FROM receipts");

        DB::statement('DROP TABLE receipts');
        DB::statement('ALTER TABLE receipts_new RENAME TO receipts');

        // Update transactions table
        DB::statement('DROP TABLE IF EXISTS transactions_new');
        DB::statement('CREATE TABLE transactions_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type VARCHAR(255) NOT NULL,
            mode VARCHAR(255) NOT NULL,
            source VARCHAR(255) NULL,
            amount DECIMAL(12,2) NOT NULL,
            boat_id INTEGER NULL,
            landing_id INTEGER NULL,
            buyer_id INTEGER NULL,
            invoice_id INTEGER NULL,
            cash_source_receipt_id INTEGER NULL,
            transactionable_type VARCHAR(255) NULL,
            transactionable_id INTEGER NULL,
            date DATE NOT NULL,
            notes TEXT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (boat_id) REFERENCES boats(id),
            FOREIGN KEY (landing_id) REFERENCES landings(id),
            FOREIGN KEY (buyer_id) REFERENCES buyers(id),
            FOREIGN KEY (invoice_id) REFERENCES invoices(id),
            FOREIGN KEY (cash_source_receipt_id) REFERENCES receipts(id)
        )');

        DB::statement("INSERT INTO transactions_new (id, type, mode, source, amount, boat_id, landing_id, buyer_id, invoice_id, cash_source_receipt_id, transactionable_type, transactionable_id, date, notes, created_at, updated_at)
            SELECT id, type, mode,
                CASE 
                    WHEN source = 'CashFromSales' THEN 'Cash'
                    WHEN source = 'PersonalFund' THEN 'Personal'
                    ELSE source
                END as source,
                amount, boat_id, landing_id, buyer_id, invoice_id, cash_source_receipt_id, transactionable_type, transactionable_id, date, notes, created_at, updated_at
            FROM transactions");

        DB::statement('DROP TABLE transactions');
        DB::statement('ALTER TABLE transactions_new RENAME TO transactions');
    }

    public function down(): void
    {
        // Revert payments table
        DB::statement('DROP TABLE IF EXISTS payments_new');
        DB::statement('CREATE TABLE payments_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            boat_id INTEGER,
            landing_id INTEGER,
            date DATE NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            mode VARCHAR(255) NOT NULL,
            source VARCHAR(255) NOT NULL,
            payment_for VARCHAR(255) NOT NULL,
            loan_reference VARCHAR(255) NULL,
            notes TEXT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (boat_id) REFERENCES boats(id),
            FOREIGN KEY (landing_id) REFERENCES landings(id)
        )');

        DB::statement("INSERT INTO payments_new (id, boat_id, landing_id, date, amount, mode, source, payment_for, loan_reference, notes, created_at, updated_at)
            SELECT id, boat_id, landing_id, date, amount, mode,
                CASE 
                    WHEN source = 'Cash' THEN 'CashFromSales'
                    WHEN source = 'Personal' THEN 'PersonalFund'
                    ELSE source
                END as source,
                payment_for, loan_reference, notes, created_at, updated_at
            FROM payments");

        DB::statement('DROP TABLE payments');
        DB::statement('ALTER TABLE payments_new RENAME TO payments');

        // Revert receipts table
        DB::statement('DROP TABLE IF EXISTS receipts_new');
        DB::statement('CREATE TABLE receipts_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            buyer_id INTEGER NOT NULL,
            invoice_id INTEGER NOT NULL,
            boat_id INTEGER NOT NULL,
            landing_id INTEGER NOT NULL,
            date DATE NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            mode VARCHAR(255) NOT NULL,
            source VARCHAR(255) NULL,
            notes TEXT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (buyer_id) REFERENCES buyers(id),
            FOREIGN KEY (invoice_id) REFERENCES invoices(id),
            FOREIGN KEY (boat_id) REFERENCES boats(id),
            FOREIGN KEY (landing_id) REFERENCES landings(id)
        )');

        DB::statement("INSERT INTO receipts_new (id, buyer_id, invoice_id, boat_id, landing_id, date, amount, mode, source, notes, created_at, updated_at)
            SELECT id, buyer_id, invoice_id, boat_id, landing_id, date, amount, mode,
                CASE 
                    WHEN source = 'Cash' THEN 'CashFromSales'
                    WHEN source = 'Personal' THEN 'PersonalFund'
                    ELSE source
                END as source,
                notes, created_at, updated_at
            FROM receipts");

        DB::statement('DROP TABLE receipts');
        DB::statement('ALTER TABLE receipts_new RENAME TO receipts');

        // Revert transactions table
        DB::statement('DROP TABLE IF EXISTS transactions_new');
        DB::statement('CREATE TABLE transactions_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type VARCHAR(255) NOT NULL,
            mode VARCHAR(255) NOT NULL,
            source VARCHAR(255) NULL,
            amount DECIMAL(12,2) NOT NULL,
            boat_id INTEGER NULL,
            landing_id INTEGER NULL,
            buyer_id INTEGER NULL,
            invoice_id INTEGER NULL,
            cash_source_receipt_id INTEGER NULL,
            transactionable_type VARCHAR(255) NULL,
            transactionable_id INTEGER NULL,
            date DATE NOT NULL,
            notes TEXT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (boat_id) REFERENCES boats(id),
            FOREIGN KEY (landing_id) REFERENCES landings(id),
            FOREIGN KEY (buyer_id) REFERENCES buyers(id),
            FOREIGN KEY (invoice_id) REFERENCES invoices(id),
            FOREIGN KEY (cash_source_receipt_id) REFERENCES receipts(id)
        )');

        DB::statement("INSERT INTO transactions_new (id, type, mode, source, amount, boat_id, landing_id, buyer_id, invoice_id, cash_source_receipt_id, transactionable_type, transactionable_id, date, notes, created_at, updated_at)
            SELECT id, type, mode,
                CASE 
                    WHEN source = 'Cash' THEN 'CashFromSales'
                    WHEN source = 'Personal' THEN 'PersonalFund'
                    ELSE source
                END as source,
                amount, boat_id, landing_id, buyer_id, invoice_id, cash_source_receipt_id, transactionable_type, transactionable_id, date, notes, created_at, updated_at
            FROM transactions");

        DB::statement('DROP TABLE transactions');
        DB::statement('ALTER TABLE transactions_new RENAME TO transactions');
    }
};
