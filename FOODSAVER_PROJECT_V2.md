# FoodSaver — Food Waste Reduction Platform
> **AI Agent Instruction File** — Read every section before generating any code.  
> Full-stack web application | PHP · MySQL · Bootstrap 5 · Groq AI API  
> Developer: Ebinesh | Student ID: 20240173 | Module: SDGP 5COSC021C  
> GitHub: https://github.com/Ebinesh2709/Foodsaver-platform  
> Deadline: July 2nd, 2026

---

## AGENT RULES — Read Before Writing Any Code

These rules apply to every single file you generate. No exceptions.

1. **Every PHP file starts with `session_start()`** as the very first line after `<?php`.
2. **Every PHP file that includes `config/db.php` must define the guard first:**
   ```php
   define('APP_RUNNING', true);
   ```
   `db.php` contains `defined('APP_RUNNING') or die('Direct access not permitted');` — if you omit the define, the app dies.
3. **Include path rule:** Files inside a subdirectory (`business/`, `auth/`, `admin/`) must use `../` prefix for includes. Files at root use no prefix.
   - Root file: `require_once 'config/db.php';`
   - `business/add_listing.php`: `require_once '../config/db.php';`
   - `business/add_listing.php`: `require_once '../includes/ai_helper.php';`
4. **All database queries use PDO prepared statements with `?` placeholders.** Never concatenate user input into SQL strings.
5. **All output from the database must be wrapped in `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`.** No raw echo of DB values.
6. **Every POST form must include a hidden CSRF token field** and every POST handler must verify it using `verify_csrf_token()` before processing anything.
7. **Role guards at the top of every protected page.** Check `$_SESSION['role']` immediately after session_start. Redirect to `auth/login.php` if not authenticated. Redirect to appropriate page if wrong role.
8. **Image uploads** from `business/add_listing.php` are saved to the `/uploads/` folder at project root. The relative path from `business/` is `../uploads/`. The `image` column in `food_listings` stores only the filename (e.g., `abc123.jpg`), not the full path. When displaying images in any file, construct the path dynamically relative to root (e.g., `uploads/` + filename).
9. **Do not generate deployment-related code (InfinityFree credentials, production DB config) unless asked.** The `config/db.php` production block is a placeholder only.
10. **PHPUnit test files** must not include `db.php` or make any real database connections. All tested functions must be pure PHP logic that can run without a database.
11. **The `businesses` table row is created immediately after the `users` row in the same registration transaction** when `role = 'business'`. Both inserts are wrapped in a PDO transaction — if either fails, both roll back.
12. **`ai_helper.php` is the only file that calls the Groq API.** No other file makes external HTTP requests.

---

## 1. Project Idea

FoodSaver is a web-based platform that connects food businesses (restaurants, canteens, bakeries) with local individuals to reduce food waste by redistributing surplus food before it expires.

### Problem Being Solved
Every day, restaurants and food businesses discard large quantities of unsold food while people in the same community go without. Existing commercial solutions (Too Good To Go, OLIO) operate in large Western cities and do not serve local communities in South Asian contexts such as Sri Lanka.

### Solution
FoodSaver allows businesses to post surplus food listings with pickup time windows and discounted prices. Customers browse, search, and reserve these listings before they expire. An AI layer automatically scores listing urgency and enables natural language search.

### SDG Alignment
Directly addresses UN SDG 12 (Responsible Consumption and Production).

---

## 2. Tech Stack

| Layer | Technology | Notes |
|---|---|---|
| Backend | PHP 8.0 | Procedural style + PDO only. No mysqli anywhere. |
| Database | MySQL 5.7+ | Via XAMPP locally. |
| Frontend | Bootstrap 5.3, HTML5, CSS3 | CDN links only. No npm build step. |
| AI Integration | **Groq API (primary)** | Free tier. Model: `llama3-8b-8192`. Endpoint: `https://api.groq.com/openai/v1/chat/completions`. API key stored in `config/db.php` as `define('GROQ_API_KEY', 'your-key-here')`. |
| AI Fallback | Rule-based PHP logic | Activates silently if Groq API call fails or returns unexpected output. |
| Local Server | XAMPP (Apache + MySQL on Windows) | Webroot is `htdocs/Foodsaver/`. |
| Version Control | Git + GitHub | |
| CI/CD | GitHub Actions | `.github/workflows/ci.yml` |
| Testing | PHPUnit 9.6 | Tests are pure logic — no DB connection required. |
| Deployment | InfinityFree | Handled separately after local confirmation. |

---

## 3. Database

### Database Name
```
foodsaver_db
```

### SQL — Create All Tables (run in this order)

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('business', 'customer', 'admin') NOT NULL,
  phone VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE businesses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  business_name VARCHAR(150) NOT NULL,
  address TEXT,
  area VARCHAR(100),
  description TEXT,
  logo VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE food_listings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  category ENUM('meals', 'bakery', 'produce', 'dairy', 'other') NOT NULL,
  original_price DECIMAL(10,2) NOT NULL,
  discounted_price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL,
  pickup_start DATETIME NOT NULL,
  pickup_end DATETIME NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  status ENUM('available', 'reserved', 'collected', 'sold_out', 'expired') DEFAULT 'available',
  urgency_score ENUM('high', 'medium', 'low') DEFAULT 'low',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
);

CREATE TABLE reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  listing_id INT NOT NULL,
  user_id INT NOT NULL,
  status ENUM('pending', 'confirmed', 'collected', 'cancelled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (listing_id) REFERENCES food_listings(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Listing Status Lifecycle
```
available → reserved     (when a customer reserves it)
reserved  → collected    (when business marks it collected)
reserved  → available    (when customer cancels the reservation)
available → expired      (future feature: cron job after pickup_end passes)
available → sold_out     (future: if quantity tracking is added)
```

### Key Relationships
- `businesses.user_id` → `users.id` (one-to-one: each business user has one business profile)
- `food_listings.business_id` → `businesses.id` (one business has many listings)
- `reservations.listing_id` → `food_listings.id`
- `reservations.user_id` → `users.id`
- A listing can only have one active reservation at a time (enforced by checking `status = 'available'` before insert)

---

## 4. Complete Folder Structure

```
Foodsaver/                          ← project root (htdocs/Foodsaver/)
│
├── index.php                       ← public landing page (no login required)
├── browse_listings.php             ← customer browse + AI natural language search
├── reserve_listing.php             ← POST handler: create reservation (customer only)
├── my_reservations.php             ← customer: view own reservations
├── cancel_reservation.php          ← POST handler: cancel a reservation (customer only)
├── logout.php                      ← destroy session and redirect to index.php
│
├── auth/
│   ├── login.php                   ← login form + POST handler
│   └── register.php                ← registration form + POST handler (all roles)
│
├── business/
│   ├── dashboard.php               ← business home after login (summary stats)
│   ├── add_listing.php             ← create new food listing + trigger AI urgency scoring
│   ├── my_listings.php             ← view/manage own listings
│   ├── edit_listing.php            ← edit a listing (ownership verified, re-scores urgency)
│   ├── delete_listing.php          ← POST handler: delete a listing (ownership verified)
│   └── manage_reservations.php     ← view reservations on own listings, confirm/collect
│
├── admin/
│   └── dashboard.php               ← admin home (view all users and listings)
│
├── config/
│   └── db.php                      ← PDO connection + GROQ_API_KEY constant + APP_RUNNING guard
│
├── includes/
│   ├── ai_helper.php               ← all Groq API calls + fallback logic
│   ├── csrf_helper.php             ← CSRF token generate + verify functions
│   ├── header.php                  ← shared HTML <head> + Bootstrap nav (role-aware)
│   └── footer.php                  ← shared HTML footer + Bootstrap JS CDN
│
├── tests/
│   ├── UrgencyTest.php             ← PHPUnit: 4 tests for urgency fallback logic
│   └── ValidationTest.php          ← PHPUnit: 12 tests for input validation functions
│
├── uploads/                        ← food listing images (gitignored, must exist on server)
│
├── .github/
│   └── workflows/
│       └── ci.yml                  ← GitHub Actions: run PHPUnit on every push
│
├── composer.json                   ← PHPUnit dependency declaration
├── .gitignore                      ← excludes: uploads/, vendor/, config secrets
└── README.md
```

---

## 5. File-by-File Specification

### 5.0 `config/db.php`
**Purpose:** PDO database connection. Environment-aware. Holds the Groq API key.

**Logic:**
- `defined('APP_RUNNING') or die('Direct access not permitted');` — first line after `<?php`
- Detect environment: if `$_SERVER['HTTP_HOST']` is `localhost` or `127.0.0.1`, use local credentials. Otherwise use production placeholder.
- Local credentials: host=`localhost`, dbname=`foodsaver_db`, user=`root`, pass=`''`
- Production credentials: host, dbname, user, pass are placeholder strings — leave as `'YOUR_PROD_*'`
- Create PDO with options: `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`, `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`
- Define constant: `define('GROQ_API_KEY', 'your-groq-api-key-here');`
- Expose `$pdo` variable — every file that includes `db.php` gets `$pdo` ready to use.

---

### 5.1 `includes/csrf_helper.php`
**Purpose:** Generate and verify CSRF tokens stored in the session.

**Functions to implement:**

`generate_csrf_token()`:
- If `$_SESSION['csrf_token']` is not set, generate one using `bin2hex(random_bytes(32))` and store it.
- Return `$_SESSION['csrf_token']`.

`verify_csrf_token($token)`:
- Compare `$token` (from POST) against `$_SESSION['csrf_token']` using `hash_equals()`.
- If mismatch, call `http_response_code(403)`, echo `'Invalid CSRF token'`, and `exit`.

**Usage pattern in every POST form:**
```html
<input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
```
**Usage pattern in every POST handler:**
```php
verify_csrf_token($_POST['csrf_token'] ?? '');
```

---

### 5.2 `includes/ai_helper.php`
**Purpose:** All AI API logic in one file. Two public functions only.

**Dependencies:** Requires `config/db.php` to already be included (for `GROQ_API_KEY`). Do NOT include `db.php` inside this file — the caller is responsible.

**Function 1: `get_urgency_score(string $description, string $pickup_end): string`**

Returns: `'high'`, `'medium'`, or `'low'`.

Logic:
1. Calculate `$hours_until_end` = difference between `$pickup_end` (datetime string) and `now()` in hours.
2. If `$hours_until_end <= 0`, return `'high'` immediately (already expired or expiring now).
3. Build Groq API request:
   - Endpoint: `https://api.groq.com/openai/v1/chat/completions`
   - Method: POST with `Authorization: Bearer ` + `GROQ_API_KEY`
   - Model: `llama3-8b-8192`
   - System message: `"You are a food urgency classifier. Respond with exactly one word: high, medium, or low. Nothing else."`
   - User message: `"Food item: {$description}. Hours until pickup deadline: {$hours_until_end}. Classify urgency."`
   - `max_tokens`: 5
   - `temperature`: 0
4. Make request using PHP `curl`.
5. Parse JSON response. Extract `choices[0].message.content`. Strip whitespace and lowercase.
6. If result is not one of `['high','medium','low']`, fall through to fallback.
7. On any curl error, JSON parse failure, or unexpected value → **fallback:**
   - `$hours_until_end <= 12` → `'high'`
   - `$hours_until_end <= 48` → `'medium'`
   - Otherwise → `'low'`

**Function 2: `parse_natural_language_search(string $query): array`**

Returns: associative array with keys `category`, `min_quantity`, `urgency`, `keyword`. Any key can be `null`.

Logic:
1. Build Groq API request:
   - Same endpoint and model as above.
   - System message: `"You are a food search filter extractor. Extract search filters from the user query and return ONLY a JSON object with these keys: category (one of: meals, bakery, produce, dairy, other, or null), min_quantity (integer or null), urgency (one of: high, medium, low, or null), keyword (string or null). Return only the JSON object, no other text."`
   - User message: `$query`
   - `max_tokens`: 100
   - `temperature`: 0
2. Parse response. Extract JSON from `choices[0].message.content`.
3. Use `json_decode()`. If it fails or returns unexpected structure → **fallback:**
   - Return `['category' => null, 'min_quantity' => null, 'urgency' => null, 'keyword' => $query]`
   - This causes a simple keyword search against title and description.

---

### 5.3 `includes/header.php`
**Purpose:** Shared HTML `<head>` and Bootstrap navigation bar. Included at the top of every page that renders HTML.

**Requirements:**
- `session_start()` is NOT called here — the calling page calls it.
- Bootstrap 5.3 CSS via CDN link.
- Nav brand: "🍱 FoodSaver"
- Nav links change based on `$_SESSION['role']`:
  - **Not logged in:** Home (`index.php`), Browse (`browse_listings.php`), Login (`auth/login.php`), Register (`auth/register.php`)
  - **role = customer:** Browse, My Reservations (`my_reservations.php`), Logout (`logout.php`) — show username
  - **role = business:** Dashboard (`business/dashboard.php`), Add Listing (`business/add_listing.php`), My Listings (`business/my_listings.php`), Reservations (`business/manage_reservations.php`), Logout — show username
  - **role = admin:** Dashboard (`admin/dashboard.php`), Logout — show username
- The `$page_title` variable is set by the calling page before including header. Use it in `<title>FoodSaver — {$page_title}</title>`.
- Active nav link highlighting: accept optional `$active_page` variable from calling page.

**Include path note:** Files in subdirectories must use the correct relative path. Header itself uses `$_SESSION` directly — no include path issue. But the calling file must set `$page_title` before `require_once`.

---

### 5.4 `includes/footer.php`
**Purpose:** Closing HTML tags + Bootstrap JS CDN.

**Contents:**
- Bootstrap 5.3 bundle JS via CDN.
- Simple footer bar: "© 2025 FoodSaver — Reducing food waste in our community | SDG 12"
- Close `</body></html>`.

---

### 5.5 `index.php` (Landing Page)
**Purpose:** Public homepage. No login required.

**Logic:**
- If user is already logged in (`isset($_SESSION['user_id'])`), redirect to appropriate dashboard based on role.
- Otherwise show landing page.

**Page content:**
- Hero section: headline "Save Food, Feed Community", subtext about the platform, two CTA buttons: "Browse Listings" and "Register as Business".
- How it works: 3-step visual (Business posts → Customer finds → Food saved).
- SDG 12 badge/mention.

---

### 5.6 `logout.php`
**Purpose:** Destroy session and redirect.

**Logic:**
- `session_start()` → `session_destroy()` → `header('Location: index.php')` → `exit`.
- No HTML output.

---

### 5.7 `auth/register.php`
**Purpose:** Registration form and POST handler for all user roles.

**GET (show form):**
- Fields: Full Name, Email, Password (min 8 chars), Phone, Role (radio or select: customer / business)
- If role = business: show additional fields: Business Name, Address, Area, Description (these only submit when business is selected — use JS to show/hide)
- CSRF token hidden field.

**POST (process registration):**
1. `verify_csrf_token()`.
2. Validate all fields server-side:
   - Name: not empty, max 100 chars.
   - Email: `filter_var(FILTER_VALIDATE_EMAIL)`, check duplicate against `users` table.
   - Password: min 8 chars.
   - Role: must be `'customer'` or `'business'` (not admin — admins are created manually in DB).
3. Hash password: `password_hash($password, PASSWORD_DEFAULT)`.
4. If role = `'business'`:
   - Wrap in PDO transaction.
   - Insert into `users` table.
   - Get last insert ID.
   - Insert into `businesses` table using that user ID, with business_name, address, area, description.
   - Commit. If any step fails, rollback and show error.
5. If role = `'customer'`:
   - Single insert into `users`.
6. On success: redirect to `auth/login.php` with success query param `?registered=1`.
7. On error: re-show form with inline error messages above the relevant field.

---

### 5.8 `auth/login.php`
**Purpose:** Login form and POST handler.

**GET (show form):**
- Email and Password fields.
- Show success message if `$_GET['registered'] == 1`.
- CSRF token hidden field.

**POST (process login):**
1. `verify_csrf_token()`.
2. Find user by email: `SELECT * FROM users WHERE email = ?`.
3. If no user found OR `password_verify()` fails → show generic error "Invalid email or password" (same message for both cases).
4. Set session variables: `$_SESSION['user_id']`, `$_SESSION['name']`, `$_SESSION['role']`.
5. Redirect based on role:
   - `business` → `../business/dashboard.php` (note: login.php is in `auth/` so use `../business/`)
   - `customer` → `../browse_listings.php`
   - `admin` → `../admin/dashboard.php`

---

### 5.9 `business/dashboard.php`
**Purpose:** Business user home page after login. Shows a summary.

**Role guard:** `$_SESSION['role'] !== 'business'` → redirect to `../auth/login.php`.

**Logic:**
- Look up `businesses` row for this user: `SELECT * FROM businesses WHERE user_id = ?` using `$_SESSION['user_id']`.
- Count total active listings: `SELECT COUNT(*) FROM food_listings WHERE business_id = ? AND status = 'available'`.
- Count pending reservations: `SELECT COUNT(*) FROM reservations r JOIN food_listings fl ON r.listing_id = fl.id WHERE fl.business_id = ? AND r.status = 'pending'`.

**Display:**
- Welcome message with business name.
- Stat cards: Total Active Listings, Pending Reservations.
- Quick action buttons: Add New Listing, View My Listings, Manage Reservations.

---

### 5.10 `business/add_listing.php`
**Purpose:** Form to create a new food listing. Triggers AI urgency scoring on submit.

**Role guard:** `$_SESSION['role'] !== 'business'` → redirect.

**Include paths (from `business/` subdirectory):**
```php
require_once '../config/db.php';
require_once '../includes/csrf_helper.php';
require_once '../includes/ai_helper.php';
require_once '../includes/header.php';
```

**GET (show form):**
- Fields: Title, Description (textarea), Category (select: meals/bakery/produce/dairy/other), Quantity (number, min 1), Original Price, Discounted Price, Pickup Start (datetime-local), Pickup End (datetime-local), Image (file, optional).
- CSRF token.

**POST (process):**
1. `verify_csrf_token()`.
2. Look up `$business_id`: `SELECT id FROM businesses WHERE user_id = ?` using `$_SESSION['user_id']`. If not found, show error.
3. Validate all fields server-side:
   - Title: not empty, max 200 chars.
   - Description: not empty.
   - Category: must be one of `['meals','bakery','produce','dairy','other']`.
   - Quantity: `is_numeric()` and > 0.
   - Original price and discounted price: `is_numeric()` and >= 0.
   - Pickup start and pickup end: valid datetimes. Pickup end must be after pickup start.
4. Handle image upload (optional):
   - If file uploaded, check extension is in `['jpg','jpeg','png','webp']`.
   - Generate filename: `uniqid('food_', true) . '.' . $ext`.
   - Move to `../uploads/` (relative from `business/`).
   - Store filename only in `$image_filename`.
   - If no file, `$image_filename = null`.
5. Insert into `food_listings` with `urgency_score = 'low'` as placeholder.
6. Get the new listing's `id` via `$pdo->lastInsertId()`.
7. Call `get_urgency_score($description, $pickup_end)` from `ai_helper.php`.
8. Update the row: `UPDATE food_listings SET urgency_score = ? WHERE id = ?`.
9. Redirect to `my_listings.php` with success message `?added=1`.
10. On validation error: re-show form with error messages, preserving field values.

---

### 5.11 `business/my_listings.php`
**Purpose:** Show the logged-in business's own listings.

**Role guard:** `$_SESSION['role'] !== 'business'` → redirect.

**Logic:**
- Look up `$business_id` from `businesses` table.
- Query: `SELECT * FROM food_listings WHERE business_id = ? ORDER BY created_at DESC`.

**Display:**
- Table or card grid of listings with: title, category, urgency badge, status badge, quantity, discounted price, pickup end, actions.
- Actions per listing: Edit button (link to `edit_listing.php?id=X`), Delete button (POST form to `delete_listing.php`).
- Show success flash if `$_GET['added'] == 1` or `$_GET['updated'] == 1`.
- If no listings, show friendly empty state message with link to Add Listing.

---

### 5.12 `business/edit_listing.php`
**Purpose:** Edit an existing listing. Ownership is verified. Re-scores urgency on save.

**Role guard:** `$_SESSION['role'] !== 'business'` → redirect.

**GET (load form):**
- Get `$id` from `$_GET['id']`.
- Query: `SELECT fl.* FROM food_listings fl JOIN businesses b ON fl.business_id = b.id WHERE fl.id = ? AND b.user_id = ?` using listing id and `$_SESSION['user_id']`.
- If no row returned, redirect to `my_listings.php` (not their listing).
- Pre-fill all form fields including datetime-local fields (format: `Y-m-d\TH:i`).

**POST (save):**
1. `verify_csrf_token()`.
2. Re-verify ownership with same query above.
3. Validate all fields same as add_listing.
4. Handle optional new image upload same as add_listing. If no new file uploaded, keep existing `$image_filename`.
5. Call `get_urgency_score($description, $pickup_end)` to re-score urgency.
6. Update `food_listings` row.
7. Redirect to `my_listings.php?updated=1`.

---

### 5.13 `business/delete_listing.php`
**Purpose:** POST handler only. Delete a listing after verifying ownership.

**Role guard:** `$_SESSION['role'] !== 'business'` → redirect.

**POST only** (reject GET requests).

**Logic:**
1. `verify_csrf_token()`.
2. Get `$id` from `$_POST['listing_id']`.
3. Look up `$business_id`.
4. Delete: `DELETE FROM food_listings WHERE id = ? AND business_id = ?`. This double condition prevents cross-business deletion.
5. Redirect to `my_listings.php`.

---

### 5.14 `business/manage_reservations.php`
**Purpose:** Business views all reservations on their listings. Can confirm or mark collected.

**Role guard:** `$_SESSION['role'] !== 'business'` → redirect.

**GET (display):**
- Look up `$business_id`.
- Query:
  ```sql
  SELECT r.id, r.status, r.created_at,
         fl.title, fl.id AS listing_id,
         u.name AS customer_name, u.phone AS customer_phone
  FROM reservations r
  JOIN food_listings fl ON r.listing_id = fl.id
  JOIN users u ON r.user_id = u.id
  WHERE fl.business_id = ?
  ORDER BY r.created_at DESC
  ```
- Display as table: food item, customer name, customer phone, reservation date, status badge, action buttons.
- Action buttons:
  - If `status = 'pending'` → "Confirm" POST form button.
  - If `status = 'confirmed'` → "Mark Collected" POST form button.
  - Each form includes `reservation_id`, `action` (`confirm` or `collect`), and CSRF token.

**POST (handle action):**
1. `verify_csrf_token()`.
2. Get `$reservation_id` and `$action` from POST.
3. Verify the reservation belongs to this business (re-run JOIN query with business_id check).
4. If `$action === 'confirm'` and current status is `pending`: update `reservations.status = 'confirmed'`.
5. If `$action === 'collect'` and current status is `confirmed`:
   - Use PDO transaction.
   - Update `reservations.status = 'collected'`.
   - Update `food_listings.status = 'collected'` for the listing.
   - Commit.
6. Redirect to same page (PRG pattern).

---

### 5.15 `browse_listings.php`
**Purpose:** Main customer page. Shows all available listings. AI natural language search.

**Access:** No role restriction — but Reserve button only shown if `$_SESSION['role'] === 'customer'`. Guests see a "Login to reserve" prompt.

**Includes (root-level file):**
```php
require_once 'config/db.php';
require_once 'includes/csrf_helper.php';
require_once 'includes/ai_helper.php';
require_once 'includes/header.php';
```

**GET — default (no search):**
- Query:
  ```sql
  SELECT fl.*, b.business_name, b.area
  FROM food_listings fl
  JOIN businesses b ON fl.business_id = b.id
  WHERE fl.status = 'available'
  ORDER BY
    FIELD(fl.urgency_score, 'high', 'medium', 'low'),
    fl.pickup_end ASC
  ```
- Display as Bootstrap card grid (3 columns desktop, 1 mobile).

**GET — with search (`$_GET['q']` not empty):**
1. Call `parse_natural_language_search($_GET['q'])`.
2. Build dynamic SQL with conditions:
   - `category = ?` if `$filters['category']` is not null.
   - `quantity >= ?` if `$filters['min_quantity']` is not null.
   - `urgency_score = ?` if `$filters['urgency']` is not null.
   - `(title LIKE ? OR description LIKE ?)` if `$filters['keyword']` is not null.
   - Always include `status = 'available'`.
3. Display active filter badges above results so user sees what AI interpreted.
4. Show "X results found" count.

**Card contents:**
- Food image (`uploads/` + filename) if not null, else placeholder image.
- Urgency badge: `high` = red badge, `medium` = yellow badge, `low` = secondary badge.
- Title, category badge, business name + area, description (truncated to 100 chars), quantity, discounted price (bold green) with strikethrough original price, pickup end formatted as human-readable.
- Reserve button → POST form to `reserve_listing.php` with `listing_id` hidden field + CSRF token. Only shown if logged in as customer.

**Search form:**
- Input: `name="q"`, placeholder "e.g. rice meals for tonight, cheap bakery items..."
- Search button, Clear button (link to `browse_listings.php`).
- Search is GET method (bookmarkable).

---

### 5.16 `reserve_listing.php`
**Purpose:** POST handler only. Creates a reservation atomically.

**Role guard:** `$_SESSION['role'] !== 'customer'` → redirect to `auth/login.php`.

**POST only** (reject GET).

**Logic:**
1. `verify_csrf_token()`.
2. Get `$listing_id` from `$_POST['listing_id']`.
3. Begin PDO transaction.
4. Select listing with lock: `SELECT * FROM food_listings WHERE id = ? AND status = 'available' FOR UPDATE`.
5. If no row returned (listing is no longer available), rollback and redirect to `browse_listings.php?error=unavailable`.
6. Insert into `reservations`: `(listing_id, user_id, status)` = `($listing_id, $_SESSION['user_id'], 'pending')`.
7. Update `food_listings.status = 'reserved'` WHERE `id = $listing_id`.
8. Commit.
9. Redirect to `my_reservations.php?reserved=1`.
10. On any exception, rollback and redirect to `browse_listings.php?error=failed`.

---

### 5.17 `my_reservations.php`
**Purpose:** Customer views their own reservations.

**Role guard:** `$_SESSION['role'] !== 'customer'` → redirect.

**Logic:**
- Query:
  ```sql
  SELECT r.id, r.status, r.created_at,
         fl.title, fl.category, fl.discounted_price,
         fl.pickup_end, fl.urgency_score,
         b.business_name, b.area
  FROM reservations r
  JOIN food_listings fl ON r.listing_id = fl.id
  JOIN businesses b ON fl.business_id = b.id
  WHERE r.user_id = ?
  ORDER BY r.created_at DESC
  ```

**Display:**
- Flash success message if `$_GET['reserved'] == 1`.
- Card or table for each reservation.
- Status badge colours:
  - `pending` → yellow (Bootstrap `warning`)
  - `confirmed` → green (Bootstrap `success`) + text "Ready for pickup!"
  - `collected` → grey (Bootstrap `secondary`)
  - `cancelled` → red (Bootstrap `danger`)
- Cancel button shown only if `status = 'pending'`. It is a POST form (not a link) to `cancel_reservation.php` with `reservation_id` hidden field + CSRF token.

---

### 5.18 `cancel_reservation.php`
**Purpose:** POST handler only. Cancel a pending reservation atomically.

**Role guard:** `$_SESSION['role'] !== 'customer'` → redirect.

**POST only.**

**Logic:**
1. `verify_csrf_token()`.
2. Get `$reservation_id` from `$_POST['reservation_id']`.
3. Begin PDO transaction.
4. Select reservation: `SELECT * FROM reservations WHERE id = ? AND user_id = ? AND status = 'pending'` using `$_SESSION['user_id']`.
5. If no row (not their reservation, or not pending), rollback and redirect.
6. Get `$listing_id` from the reservation row.
7. Update `reservations.status = 'cancelled'`.
8. Update `food_listings.status = 'available'` WHERE `id = $listing_id`.
9. Commit.
10. Redirect to `my_reservations.php`.

---

### 5.19 `admin/dashboard.php`
**Purpose:** Admin-only overview page.

**Role guard:** `$_SESSION['role'] !== 'admin'` → redirect to `../auth/login.php`.

**Include paths (from `admin/` subdirectory):**
```php
require_once '../config/db.php';
require_once '../includes/header.php';
```

**Display:**
- Total users count.
- Total businesses count.
- Total listings count.
- Total reservations count.
- Recent 10 users table (id, name, email, role, created_at).
- Recent 10 listings table (id, title, business, urgency, status, created_at).

---

## 6. Security Implementation

Every measure below must be present in every relevant file. Not optional.

| Measure | Implementation |
|---|---|
| Password hashing | `password_hash($password, PASSWORD_DEFAULT)` on register. `password_verify($input, $hash)` on login. Never store or compare plain text. |
| SQL injection | PDO prepared statements with `?` everywhere. No string concatenation into SQL except for dynamic WHERE clauses — use an array of conditions and bind values. |
| XSS | `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')` on every variable echoed into HTML. |
| CSRF | `csrf_helper.php` token in every POST form. Verified at top of every POST handler. |
| Role-based access | Session role check at top of every protected page before any logic runs. |
| Ownership | All business edit/delete queries include `AND business_id = ?` (derived from session, not POST). |
| Direct file access | `defined('APP_RUNNING') or die()` in `config/db.php`. |
| Input validation | `trim()` all strings, `is_numeric()` for numbers, `filter_var(FILTER_VALIDATE_EMAIL)` for email, min/max length checks. |
| File upload | Extension whitelist `['jpg','jpeg','png','webp']`. Use `pathinfo()` to extract extension. Use `uniqid()` to rename file. Check `is_uploaded_file()` before moving. |
| Config guard | Production DB credentials are placeholder strings in `db.php`. API key is a constant, never echoed to page. |

---

## 7. Urgency Badge Rendering Helper

Use this logic consistently wherever urgency badges are displayed (browse_listings.php, my_listings.php, my_reservations.php):

```
urgency = 'high'   → Bootstrap class: badge bg-danger       → text: "High Urgency"
urgency = 'medium' → Bootstrap class: badge bg-warning text-dark → text: "Medium"
urgency = 'low'    → Bootstrap class: badge bg-secondary    → text: "Low"
```

A PHP helper function `get_urgency_badge_html(string $urgency): string` can be defined in `header.php` for reuse.

---

## 8. Testing

### PHPUnit Setup
- `composer.json` must declare:
  ```json
  {
    "require-dev": {
      "phpunit/phpunit": "^9.6"
    },
    "autoload": {
      "psr-4": {
        "App\\": "src/"
      }
    }
  }
  ```
- Run: `./vendor/bin/phpunit tests/ --testdox`

### Critical Rule for Test Files
Test files **must not** include `config/db.php` or make any database connections. Extract pure logic functions into testable form:

- Create `includes/validation_helpers.php` with pure functions: `validate_email()`, `validate_password()`, `validate_quantity()`, `validate_pickup_window()`, `sanitize_output()`.
- Create `includes/urgency_fallback.php` with `calculate_urgency_fallback(float $hours): string` — pure function, no API call.
- Both test files import only these pure helper files.

### `tests/UrgencyTest.php` — 4 Tests
1. `testHighUrgencyWithin12Hours` — `calculate_urgency_fallback(8)` returns `'high'`
2. `testMediumUrgencyWithin48Hours` — `calculate_urgency_fallback(24)` returns `'medium'`
3. `testLowUrgencyBeyond48Hours` — `calculate_urgency_fallback(72)` returns `'low'`
4. `testExpiredListingIsHigh` — `calculate_urgency_fallback(0)` returns `'high'`

### `tests/ValidationTest.php` — 12 Tests
1. `testEmptyTitleIsInvalid`
2. `testValidTitlePasses`
3. `testNegativeQuantityIsInvalid`
4. `testZeroQuantityIsInvalid`
5. `testValidQuantityPasses`
6. `testPickupEndBeforeStartIsInvalid`
7. `testValidPickupWindowPasses`
8. `testInvalidEmailFails`
9. `testValidEmailPasses`
10. `testShortPasswordIsInvalid` (less than 8 chars)
11. `testValidPasswordPasses`
12. `testXssInputIsEscaped` — `sanitize_output('<script>alert(1)</script>')` does not contain `<script>`

---

## 9. CI/CD Pipeline

**File:** `.github/workflows/ci.yml`

**Trigger:** Push or pull request to `main` branch.

**Job 1 — `test`:**
- runs-on: ubuntu-latest
- PHP version: 8.0 (use `shivammathur/setup-php@v2`)
- Steps: checkout → setup PHP → `composer install --no-progress` → `./vendor/bin/phpunit tests/ --testdox`
- No MySQL service needed (tests are pure logic, no DB connection).

**Job 2 — `deploy-check`:**
- Depends on `test` job passing.
- Checks that all critical files exist in the repository:
  - `config/db.php`, `includes/ai_helper.php`, `includes/csrf_helper.php`, `includes/header.php`, `includes/footer.php`
  - `auth/login.php`, `auth/register.php`
  - `business/dashboard.php`, `business/add_listing.php`, `business/my_listings.php`
  - `browse_listings.php`, `reserve_listing.php`, `my_reservations.php`
- Use bash `test -f filename || exit 1` for each file.

---

## 10. Complete User Journey

```
BUSINESS FLOW:
index.php → auth/register.php (role=business)
  → inserts users row + businesses row in transaction
  → auth/login.php → business/dashboard.php
  → business/add_listing.php (fill form, submit)
    → AI urgency scoring via ai_helper.php
    → row inserted in food_listings (status=available)
  → business/my_listings.php (see all own listings)
  → business/edit_listing.php (re-scores urgency on save)
  → business/manage_reservations.php
    → Confirm pending reservation (status → confirmed)
    → Mark Collected (reservation → collected, listing → collected)

CUSTOMER FLOW:
index.php → auth/register.php (role=customer)
  → auth/login.php → browse_listings.php
  → Search with natural language → AI extracts filters
    → dynamic SQL query → filtered results displayed
  → Reserve a listing (POST to reserve_listing.php)
    → PDO transaction: reservation inserted + listing status → reserved
  → my_reservations.php (see status badges)
    → pending: cancel option available
    → confirmed: "Ready for pickup" message
    → collected: completed badge
  → cancel_reservation.php (POST)
    → PDO transaction: reservation → cancelled, listing → available

ADMIN FLOW:
auth/login.php (admin account created manually in DB)
  → admin/dashboard.php (view counts + recent data)
```

---

## 11. `.gitignore`

```
/uploads/
/vendor/
*.env
config/secrets.php
```

---

## 12. Known Limitations (Do Not Fix Unless Asked)

- No real-time notifications — users must manually refresh to see status updates.
- No admin CRUD UI — admin dashboard is read-only display only.
- No email confirmation on registration.
- Image storage is local filesystem — not suitable for multi-server deployment.
- No automatic expiry cron job — listings do not auto-expire when `pickup_end` passes.
- Groq API requires a valid API key; demo must have key configured in `config/db.php`.

---

## 13. Git Repository

- URL: https://github.com/Ebinesh2709/Foodsaver-platform
- Branch: main
- Commit style: descriptive imperative messages, e.g. `"Add CSRF protection helper"`, `"Implement natural language search with Groq fallback"`
- Target: 25+ commits across all features.
