# Architecture & Codebase Map

This document serves as a map to understand how the codebase satisfies the technical requirements, including the relational database structure, the clean architecture pattern, and the separation of business and persistence logic.

---

## 1. Database Schema
We used Laravel migrations to construct a relational database that enforces data integrity, uses UUIDs for security against IDOR, and stores financial amounts in cents to eliminate floating-point precision issues.

```text
users
├── id (PK, CHAR(36), UUID v4)           -- non-guessable
├── name (VARCHAR 255, NOT NULL)
├── email (VARCHAR 255, UNIQUE, NOT NULL)
├── created_at
└── updated_at

accounts
├── id (PK, CHAR(36), UUID v4)           -- non-guessable
├── user_id (FK → users.id, ON DELETE CASCADE)
├── name (VARCHAR 255, NOT NULL)          -- e.g. "Savings", "Main"
├── balance (BIGINT, NOT NULL, DEFAULT 0) -- stored in cents
├── created_at
└── updated_at

transfers
├── id (PK, CHAR(36), UUID v4)           -- non-guessable
├── source_account_id (FK → accounts.id)
├── destination_account_id (FK → accounts.id)
├── amount (BIGINT UNSIGNED, NOT NULL)    -- in cents, always positive
├── status (ENUM: 'pending','success','failed', NOT NULL, DEFAULT 'pending')
├── failure_reason (VARCHAR 255, NULLABLE)
├── created_at
└── updated_at

transactions (Double-Entry Ledger)
├── id (PK, CHAR(36), UUID v4)           -- non-guessable
├── transfer_id (FK → transfers.id)
├── account_id (FK → accounts.id)
├── type (ENUM: 'debit','credit', NOT NULL)
├── amount (BIGINT UNSIGNED, NOT NULL)    -- in cents
├── balance_before (BIGINT, NOT NULL)     -- snapshot for auditability
├── balance_after (BIGINT, NOT NULL)      -- snapshot for auditability
├── created_at
└── updated_at
```

---

## 2. Codebase Structure (Repository Pattern)
The codebase uses a strict Repository/Service pattern to cleanly separate the HTTP delivery mechanism, business logic, and database persistence.

```text
app/
├── Http/
│   ├── Controllers/             -- (Thin layers handling HTTP responses)
│   │   ├── UserController.php
│   │   ├── AccountController.php
│   │   ├── TransferController.php
│   │   └── TransactionController.php
│   ├── Requests/                -- (FormRequest validation logic)
│   │   ├── CreateUserRequest.php
│   │   ├── CreateAccountRequest.php
│   │   └── TransferMoneyRequest.php
│   └── Resources/               -- (API JSON Transformers)
│       ├── UserResource.php
│       ├── AccountResource.php
│       ├── TransferResource.php
│       └── TransactionResource.php
├── Models/                      -- (Eloquent ORM)
│   ├── User.php
│   ├── Account.php
│   ├── Transfer.php
│   └── Transaction.php
├── Repositories/                -- (Database Queries / Persistence Logic)
│   ├── Contracts/
│   │   ├── UserRepositoryInterface.php
│   │   ├── AccountRepositoryInterface.php
│   │   ├── TransferRepositoryInterface.php
│   │   └── TransactionRepositoryInterface.php
│   ├── UserRepository.php
│   ├── AccountRepository.php
│   ├── TransferRepository.php
│   └── TransactionRepository.php
├── Services/                    -- (Business Logic & Transaction Handling)
│   ├── UserService.php
│   ├── AccountService.php
│   └── TransferService.php
├── Traits/
│   └── ApiResponse.php
└── Providers/                   -- (Dependency Injection Bindings)
    └── RepositoryServiceProvider.php
```

---

## 3. Execution Flow
Every request in the application follows a predictable, highly-decoupled execution path:

```text
HTTP Request
    ↓
Route (routes/api.php)
    ↓
FormRequest (Validation - e.g., TransferMoneyRequest)
    ↓
Controller (Thin - delegates to Service layer)
    ↓
Service (Business logic, mathematical checks, DB::transaction management)
    ↓
Repository (Persistence, executing DB queries via Eloquent)
    ↓
Eloquent Model + Database (MySQL)
```
