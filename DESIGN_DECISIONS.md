# Architectural & Design Decisions

This document outlines the core architectural and design decisions made while building the Money Transfers API.

## 1. Concurrency and Deadlock Prevention
Handling financial transactions in a concurrent environment (e.g., multiple rapid transfers to the same account) is prone to race conditions and deadlocks. 

**Solution: Database Locking & Deterministic Ordering**
- We utilized Laravel's `DB::transaction()` wrapped around `->lockForUpdate()`. 
- To prevent deadlocks when two transactions attempt to transfer money between the same two accounts simultaneously but in opposite directions, we apply a **deterministic lock order**. 
- In the `TransferService`, accounts are always sorted by their `id` (UUID) ascending before acquiring the locks. This guarantees that no matter the direction of the transfer, the database locks are acquired in the exact same sequence.

## 2. Double-Entry Ledger System
To ensure strict financial auditing, we implemented a double-entry ledger logic via the `transactions` table.
- A single transfer generates **two** records in the `transactions` table.
- One record for the `debit` (money leaving the source account).
- One record for the `credit` (money entering the destination account).
- Both records include the `balance_before` and `balance_after` to create an immutable audit trail.

## 3. Data Integrity & Precision
Floating point precision errors can lead to missing fractions of a cent over time.
- The database stores balances and transaction amounts as `BIGINT` representing **cents**. 
- When calculating $150.50, the system translates it to `15050` internally. 
- The API Resources seamlessly transform the internal cent values back to readable decimals for the client.

## 4. UUIDs for Non-Enumerable Resources
To prevent Insecure Direct Object Reference (IDOR) attacks, we stripped the default auto-incrementing integer IDs.
- Primary keys across all tables utilize **UUID v4**. 
- This makes it mathematically impossible for a malicious actor to guess other user IDs or account IDs and attempt unauthorized transfers.

## 5. Security & Rate Limiting
- Added an API Rate Limiter allowing 60 requests per minute to prevent abuse or denial-of-service (DoS) attempts on the transaction endpoints.
- Email domains are validated against active DNS MX/A records (`rfc,dns` validation rules) to block disposable or fake domain bot signups.

## 6. Architecture Pattern
- The system uses a strict **Service & Repository Pattern**.
- **Controllers** act only as glue code handling HTTP requests and responses.
- **Repositories** handle the database abstraction and Eloquent querying.
- **Services** house the pure business logic (balance validation, database transactions).
