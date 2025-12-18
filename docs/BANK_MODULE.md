# Bank Account & Balance Module Documentation

## Module Structure

### 1. Banks
Each bank represents a financial institution.

**Fields:**
- `id` (int): Primary key
- `name` (string): Bank name (unique) - e.g., CIH, BMCE, WAFAE BANK
- `description` (string, nullable): Bank description
- `created_at` (timestamp): Record creation date
- `updated_at` (timestamp): Last update date

**Relationship:**
- One bank has many accounts (`HasMany`)

### 2. Bank Accounts
Each account belongs to a bank and tracks financial data.

**Fields:**
- `id` (int): Primary key
- `bank_id` (int): Foreign key to banks table
- `account_number` (string): Unique account identifier
- `currency` (string): Currency code (default: MAD)
- `initial_balance` (decimal 15,2): Starting balance (default: 0)
- `created_at` (timestamp): Record creation date
- `updated_at` (timestamp): Last update date

**Relationships:**
- Belongs to one bank (`BelongsTo`)
- Has many balance records (`HasMany`)

### 3. Account Balances
Historical balance records for each account, tracking **one balance per month**.

**Fields:**
- `id` (int): Primary key
- `bank_account_id` (int): Foreign key to bank_accounts table
- `year` (int): Year of the balance record
- `month` (tinyint unsigned): Month of the balance record (1-12)
- `date` (date): Specific date of the balance record
- `amount` (decimal 15,2): Balance amount
- `created_at` (timestamp): Record creation date
- `updated_at` (timestamp): Last update date

**Unique Constraint:**
- `(bank_account_id, year, month)` - Only one balance per account per month

**Relationships:**
- Belongs to one account (`BelongsTo`)

**Special Feature:**
- The module automatically enforces **one balance per month per account**
- Year and month are automatically extracted from the date field
- When querying, only the last balance of each month is returned

---

## API Endpoints

### Banks

#### List All Banks
```
GET /api/v1/banks
```
**Parameters (Query):**
- `draw` (int): DataTable draw counter
- `start` (int): Pagination start (default: 0)
- `length` (int): Records per page (default: 10)
- `sortBy` (string): Column to sort by (default: id)
- `sortDir` (string): Sort direction - asc/desc (default: asc)
- `id` (int, optional): Filter by bank ID
- `name` (string, optional): Filter by bank name (like search)

**Response:**
```json
{
  "status": "success",
  "message": "Banks loaded successfully.",
  "data": [
    {
      "id": 1,
      "name": "CIH",
      "description": "Crédit Immobilier et Hôtelier",
      "accounts_count": 5,
      "created_at": "2025-12-17T10:00:00Z",
      "updated_at": "2025-12-17T10:00:00Z"
    }
  ],
  "recordsTotal": 3,
  "recordsFiltered": 3,
  "draw": 1,
  "pagination": { ... }
}
```

#### Create Bank
```
GET /api/v1/banks/create
```
**Response:**
```json
{
  "status": "success",
  "message": "Ready to create bank.",
  "data": null
}
```

#### Store Bank
```
POST /api/v1/banks
```
**Request Body:**
```json
{
  "name": "Bank Name",
  "description": "Optional description"
}
```

#### Edit Bank
```
GET /api/v1/banks/edit/{id}
```

#### Update Bank
```
PUT /api/v1/banks/update/{id}
```
**Request Body:**
```json
{
  "name": "Updated Bank Name",
  "description": "Updated description"
}
```

#### Delete Bank
```
DELETE /api/v1/banks/delete/{id}
```
**Note:** Cannot delete if bank has associated accounts (409 Conflict).

---

### Bank Accounts

#### List All Accounts
```
GET /api/v1/bank-accounts
```
**Parameters (Query):**
- `draw`, `start`, `length`, `sortBy`, `sortDir` (same as banks)
- `id` (int, optional): Filter by account ID
- `account_number` (string, optional): Filter by account number
- `bank_id` (int, optional): Filter by bank ID
- `currency` (string, optional): Filter by currency

**Response:**
```json
{
  "status": "success",
  "message": "Bank accounts loaded successfully.",
  "data": [
    {
      "id": 1,
      "bank": {
        "id": 1,
        "name": "CIH"
      },
      "account_number": "ACC-001",
      "currency": "MAD",
      "initial_balance": "1000.00",
      "created_at": "2025-12-17T10:00:00Z",
      "updated_at": "2025-12-17T10:00:00Z"
    }
  ],
  "recordsTotal": 10,
  "recordsFiltered": 10,
  "draw": 1,
  "pagination": { ... }
}
```

#### Create Account (Get Form Data)
```
GET /api/v1/bank-accounts/create
```
**Response:**
```json
{
  "status": "success",
  "message": "Form data retrieved successfully.",
  "data": {
    "banks": [
      {"id": 1, "name": "CIH"},
      {"id": 2, "name": "BMCE"},
      {"id": 3, "name": "WAFAE BANK"}
    ]
  }
}
```

#### Store Account
```
POST /api/v1/bank-accounts
```
**Request Body:**
```json
{
  "bank_id": 1,
  "account_number": "ACC-CIH-001",
  "currency": "MAD",
  "initial_balance": 50000
}
```
**Notes:**
- `currency` defaults to `MAD` if not provided
- `initial_balance` defaults to `0` if not provided

#### Edit Account
```
GET /api/v1/bank-accounts/edit/{id}
```

#### Update Account
```
PUT /api/v1/bank-accounts/update/{id}
```
**Request Body:**
```json
{
  "account_number": "ACC-001-UPDATED",
  "currency": "EUR",
  "initial_balance": 2000.00
}
```

#### Delete Account
```
DELETE /api/v1/bank-accounts/delete/{id}
```
**Note:** Cascades delete all associated balance records.

---

### Account Balances

#### List Account Balances (Monthly)
```
GET /api/v1/account-balances
```
**Parameters (Query):**
- `account_id` (int, **required**): Bank account ID
- `draw`, `start`, `length`, `sortBy`, `sortDir` (same as banks)
- `year` (int, optional): Filter by year
- `month` (int, optional): Filter by month (1-12)
- `date_from` (date, optional): Filter balances from date (YYYY-MM-DD)
- `date_to` (date, optional): Filter balances to date (YYYY-MM-DD)

**Response:**
```json
{
  "status": "success",
  "message": "Account balances loaded successfully.",
  "data": [
    {
      "id": 1,
      "account_id": 1,
      "year": 2025,
      "month": 12,
      "date": "2025-12-31",
      "amount": "5000.50",
      "created_at": "2025-12-31T23:59:59Z",
      "updated_at": "2025-12-31T23:59:59Z"
    },
    {
      "id": 2,
      "account_id": 1,
      "year": 2025,
      "month": 11,
      "date": "2025-11-30",
      "amount": "4500.25",
      "created_at": "2025-11-30T23:59:59Z",
      "updated_at": "2025-11-30T23:59:59Z"
    }
  ],
  "recordsTotal": 12,
  "recordsFiltered": 12,
  "draw": 1,
  "pagination": { ... }
}
```

#### Store Balance
```
POST /api/v1/account-balances
```
**Request Body:**
```json
{
  "bank_account_id": 1,
  "date": "2025-12-31",
  "amount": 5000.50
}
```
**Notes:**
- Year and month are automatically extracted from the date field
- If a balance already exists for the same month, it will be **automatically overridden** with the new values
- Returns 200 status when overriding, 201 when creating new

**Success Response (New Record):**
```json
{
  "status": "success",
  "message": "Balance recorded successfully.",
  "data": { ... }
}
```

**Success Response (Override):**
```json
{
  "status": "success",
  "message": "Balance updated successfully (existing record overridden).",
  "data": { ... }
}
```

#### Edit Balance
```
GET /api/v1/account-balances/edit/{id}
```

#### Update Balance
```
PUT /api/v1/account-balances/update/{id}
```
**Request Body:**
```json
{
  "date": "2025-12-31",
  "amount": 5500.00
}
```
**Notes:**
- If changing the date to a different month, validates no balance exists for the new month

#### Delete Balance
```
DELETE /api/v1/account-balances/delete/{id}
```

---

## Key Features

1. **Hierarchical Structure:** Banks → Accounts → Balances
2. **Monthly Balance Enforcement:** Only one balance per account per month
3. **Automatic Override:** Creating a balance for an existing month automatically updates the previous record
4. **Automatic Year/Month Extraction:** Year and month are extracted from the date field
5. **Cascading Deletes:** Deleting an account automatically deletes related balance records
6. **Unique Constraints:** Prevents duplicate balances for the same month (enforced by override)
7. **DataTable Pagination:** All endpoints support server-side pagination and filtering
8. **Standard Response Format:** Consistent JSON response structure across all endpoints
9. **Default Currency:** MAD (Moroccan Dirham) for bank accounts
10. **Default Initial Balance:** 0 for new accounts

---

## Notes

- All monetary amounts are stored as `decimal(15,2)` for precision
- Dates are stored in `YYYY-MM-DD` format
- The `account_id` parameter is mandatory when querying balances
- Year and month are automatically extracted and stored from the date field
- Balance records are unique per account per month (one balance per month enforced)
- All timestamps use UTC timezone
- Default currency is MAD (Moroccan Dirham)
- Default initial balance is 0

---

## Validation Rules

### Banks
- `name` (required, string, max:255, unique)
- `description` (optional, string)

### Bank Accounts
- `bank_id` (required, exists in banks table)
- `account_number` (required, string, max:255)
- `currency` (optional, string, max:3, defaults to MAD)
- `initial_balance` (optional, numeric, min:0, defaults to 0)

### Account Balances
- `bank_account_id` (required, exists in bank_accounts table)
- `date` (required, valid date)
- `amount` (required, numeric, min:0)
- Monthly uniqueness enforced at database level

---

## User Stories

### User Story 1: Bank Manager Creates a New Bank Account

**As a** Bank Manager  
**I want to** create a new bank account for a specific bank  
**So that** I can track the financial transactions and balances for that account

**Acceptance Criteria:**
- I can access the bank account creation form
- I can select a bank from a dropdown list
- I can enter an account number
- I can specify the currency (defaults to MAD)
- I can set an initial balance (defaults to 0)
- The system validates all required fields
- After creation, I receive a success message with the account details
- The account is immediately visible in the bank accounts list

**Steps:**
1. Navigate to `/api/v1/bank-accounts/create`
2. Receive list of available banks
3. Send POST request to `/api/v1/bank-accounts` with account details
4. System validates and creates the account
5. Receive confirmation with the new account ID

---

### User Story 2: Financial Officer Records Monthly Balance

**As a** Financial Officer  
**I want to** record the balance of a bank account for each month  
**So that** I can maintain a historical record of account balances over time

**Acceptance Criteria:**
- I can only have one balance record per account per month
- I can record a balance with a specific date and amount
- The system automatically extracts year and month from the date
- If I create a balance for a month that already has one, the old balance is automatically overridden
- The system returns a message indicating the balance was updated
- I can explicitly update an existing balance record using the PUT endpoint
- I can delete a balance record if needed
- Balances are displayed in chronological order

**Steps:**
1. Navigate to `/api/v1/account-balances` with `account_id` parameter
2. Review existing monthly balances
3. Send POST request to `/api/v1/account-balances` with balance data
4. If balance exists for that month, system automatically overrides it
5. Receive success message indicating create or override action
6. Balance is updated and visible in the list

---

### User Story 3: Accountant Views Monthly Balance History

**As an** Accountant  
**I want to** view the balance history for a specific account showing one balance per month  
**So that** I can analyze financial trends and verify account statements

**Acceptance Criteria:**
- I can view all monthly balances for a selected account
- Balances are displayed with year and month clearly visible
- I can filter by date range (from/to dates)
- I can filter by specific year or month
- I can sort by date in ascending or descending order
- The system shows pagination with total records count
- Each balance shows the date, amount, and timestamps

**Steps:**
1. Send GET request to `/api/v1/account-balances?account_id=1`
2. Receive paginated list of monthly balances
3. Apply optional filters (year, month, date range)
4. Sort results by date descending
5. View balance trends across months

---

### User Story 4: Bank Administrator Manages Banks

**As a** Bank Administrator  
**I want to** create, view, update, and delete banks  
**So that** I can maintain an accurate list of financial institutions

**Acceptance Criteria:**
- I can create a new bank with name and optional description
- I can view all banks with account counts
- I can filter banks by name or ID
- I can update bank information
- I cannot delete a bank that has associated accounts
- I receive appropriate error messages for validation failures
- Banks are stored with audit timestamps

**Steps:**
1. Send GET request to `/api/v1/banks` to view all banks
2. Send POST request to `/api/v1/banks` to create a new bank
3. Send PUT request to `/api/v1/banks/update/{id}` to update
4. Attempt DELETE request to `/api/v1/banks/delete/{id}` (fails if accounts exist)

---

### User Story 5: User Searches and Filters Bank Accounts

**As a** Bank User  
**I want to** search and filter bank accounts by various criteria  
**So that** I can quickly find specific accounts

**Acceptance Criteria:**
- I can filter by account number
- I can filter by bank ID or bank name
- I can filter by currency
- I can search by account ID
- Multiple filters can be combined
- Results are paginated for performance
- Search is case-insensitive for text fields

**Steps:**
1. Send GET request to `/api/v1/bank-accounts?bank_id=1&currency=MAD`
2. System filters by bank and currency
3. Receive paginated results matching criteria
4. Apply additional sort parameters if needed

---

### User Story 6: System Automatically Overrides Monthly Balances

**As a** System User  
**I want** the system to automatically override existing balance records when I submit a new balance for the same month  
**So that** I don't have to manually check and update existing records

**Acceptance Criteria:**
- Each account can have maximum one balance per month
- When submitting a balance for an existing month, the old record is automatically updated
- System returns 200 status code with override message
- System returns 201 status code when creating new balance
- The updated balance retains the original created_at timestamp
- The updated_at timestamp reflects the override time
- No manual check or DELETE operation is required

**Steps:**
1. Record balance for account 1 on 2025-12-31 (amount: 5000)
2. Submit another balance on 2025-12-15 (amount: 5100, same month)
3. System automatically updates the existing record with new date and amount
4. Receive 200 success response with override message
5. View updated balance in the list

---

### User Story 7: Cascading Deletion of Related Records

**As a** System Administrator  
**I want** the system to automatically delete related balance records when an account is deleted  
**So that** the database remains clean and referential integrity is maintained

**Acceptance Criteria:**
- When a bank account is deleted, all its balance records are automatically deleted
- The deletion is atomic and consistent
- No orphaned records are left in the database
- A success message confirms the deletion
- Users cannot delete a bank with associated accounts (receive error)

**Steps:**
1. Verify account has multiple balance records
2. Send DELETE request to `/api/v1/bank-accounts/delete/{id}`
3. System deletes account and all related balances
4. Verify no orphaned records exist

---

### User Story 8: Default Values for Bank Accounts

**As a** User Creating Bank Accounts  
**I want** the system to provide sensible defaults  
**So that** I don't have to specify every field manually

**Acceptance Criteria:**
- Currency defaults to MAD (Moroccan Dirham)
- Initial balance defaults to 0
- Account number is required (no default)
- Bank selection is required (no default)
- Defaults apply only when fields are not provided
- Defaults are documented in API responses

**Steps:**
1. Send POST request with only required fields (bank_id, account_number)
2. System applies defaults: currency=MAD, initial_balance=0
3. Receive account with all fields populated

---

### User Story 9: DataTable Integration for Frontend

**As a** Frontend Developer  
**I want** the API to support DataTable pagination and sorting  
**So that** I can easily integrate server-side DataTables into the web interface

**Acceptance Criteria:**
- API accepts DataTable parameters (draw, start, length, sortBy, sortDir)
- Response includes recordsTotal and recordsFiltered
- Response includes pagination metadata
- Response is compatible with DataTable server-side processing
- Sorting works on all numeric and text fields
- Pagination handles edge cases (empty results, last page)

**Steps:**
1. Frontend sends DataTable request with parameters
2. API processes pagination and sorting
3. Return formatted response with DataTable metadata
4. Frontend renders table with pagination controls

---

### User Story 10: Audit Trail with Timestamps

**As an** Auditor  
**I want** all records to have creation and update timestamps  
**So that** I can track when changes were made to the data

**Acceptance Criteria:**
- All entities have created_at and updated_at timestamps
- Timestamps are in UTC timezone
- Timestamps are automatically set on creation
- Timestamps are automatically updated on modification
- Timestamps are included in all API responses
- Timestamps help identify data changes over time

**Steps:**
1. Create a bank account
2. Check response includes created_at timestamp
3. Update the account
4. Verify updated_at timestamp changed
5. Compare timestamps to track modification history

---

## Business Rules

1. **One Balance Per Month:** Each bank account can have only one balance record per calendar month
2. **Automatic Override:** Submitting a balance for an existing month automatically updates the previous record
3. **Unique Bank Names:** Bank names must be unique in the system
4. **No Orphaned Balances:** Deleting an account automatically deletes its balances
5. **Prevent Bank Deletion:** Cannot delete a bank that has accounts
6. **Currency Support:** Account currency is stored as a 3-character code
7. **Precision:** All monetary amounts use 2 decimal places (currency subunits)
8. **Required Fields:** Bank ID and Account Number are mandatory for accounts
9. **Defaults:** Currency defaults to MAD, Initial Balance defaults to 0
10. **Audit Trail:** All modifications are tracked with timestamps
11. **Data Validation:** All inputs are validated before storage
12. **Override Transparency:** System clearly indicates when a balance is overridden vs newly created

