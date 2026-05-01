# Money Transfer System Overview

This document provides a comprehensive explanation of how the core business logic operates within the Money Transfers API. It is designed to explain the system from both a technical and financial perspective.

---

## 1. The Core Entities

The system relies on four primary entities to function securely:

1. **Users**: The entity that owns accounts.
2. **Accounts**: The digital wallets holding the actual funds. An account always belongs to one user and maintains an internal `balance`.
3. **Transfers**: The overarching request to move money from Point A to Point B. It records the intent, the amount, and the ultimate `status` (success or failed).
4. **Transactions (The Ledger)**: The explicit, granular movement of money. This is the financial audit trail.

---

## 2. Understanding Debit vs. Credit

In our system, we use a classic **Double-Entry Ledger**. This means money never simply "disappears" from one place and "appears" in another. Every single transfer produces exactly two synchronized transaction records:

*   **Debit**: Money going **OUT** of an account. 
    *   *Example:* If Fares sends $100, his account is **debited**. His balance decreases.
*   **Credit**: Money coming **IN** to an account.
    *   *Example:* If Ahmed receives that $100, his account is **credited**. His balance increases.

**Why do we do this?** 
In a robust financial system, the sum of all debits must perfectly match the sum of all credits. If a system crashes mid-transfer, an audit would immediately reveal if a debit occurred without a matching credit. Our architecture prevents this via database locking.

---

## 3. The Lifecycle of a Transfer

When a user requests a money transfer via the `POST /api/v1/transfers` endpoint, the system executes the following flow:

### Step 1: Input Validation
The system first validates the incoming request (e.g., ensuring the source and destination are different, the amount is greater than zero, and the UUIDs are valid).

### Step 2: Database Locking & Concurrency Check
If multiple users try to access the same account at the exact same millisecond (e.g., two people sending money to the same account at once), the database could get confused and write the wrong balance. 

To solve this, the system opens a **Database Transaction** and requests a `lockForUpdate()`. 
*   It grabs both accounts and temporarily "locks" them. No other process can touch these accounts until our transfer is finished. 
*   **Deadlock Prevention:** To ensure two simultaneous transfers don't lock each other out in an endless loop, the system always sorts the accounts alphabetically by their UUIDs before locking them.

### Step 3: Financial Verification
With the accounts securely locked, the system checks the math. 
*   Does the source account have enough money? 
*   If **No**: The system aborts. It logs the transfer attempt as `failed` with the reason "Insufficient balance" and immediately unlocks the accounts.

### Step 4: The Transfer Execution
If there are sufficient funds, the system:
1.  **Updates Balances:** Subtracts the amount from the Source Account, and adds the amount to the Destination Account.
2.  **Writes the Ledger (Double-Entry):**
    *   Creates a `debit` transaction for the Source Account, permanently logging the `balance_before` and `balance_after`.
    *   Creates a `credit` transaction for the Destination Account, also logging its `balance_before` and `balance_after`.
3.  **Logs the Transfer:** Marks the overarching Transfer record as `success`.

### Step 5: Commit & Unlock
Once all mathematical operations and database writes are successfully completed, the system "commits" the database transaction. The locks are released, and the accounts are free to process their next transfers.

---

## 4. Why Cents? (Integer Math)

Computers are notoriously bad at floating-point mathematics (decimals). For example, `0.1 + 0.2` in many programming languages equals `0.30000000000000004`. Over millions of transactions, these microscopic errors result in missing money.

To guarantee perfect accuracy, our database has no concept of dollars or decimals. **Everything is stored as an integer representing cents.**
*   `$150.50` is stored as `15050`.
*   The system performs all addition and subtraction using whole integers.
*   Only at the very end, when the API sends the data back to the user, does it format the integer back into a readable decimal format.
