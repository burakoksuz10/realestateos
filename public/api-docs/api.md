# RE-OS API Documentation

**Base URL:** `https://your-domain.com/api/v1`  
**Format:** JSON  
**Version:** 1.0

---

## Authentication

All protected endpoints require a Bearer token in the `Authorization` header:

```
Authorization: Bearer {token}
```

Tokens are obtained from `POST /auth/login`.

---

## Common Headers

```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}   ← required for protected routes
```

---

## Response Format

### Success
```json
{
  "success": true,
  "message": "İşlem başarılı.",
  "data": { ... }
}
```

### Paginated
```json
{
  "success": true,
  "message": "Success",
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 98
  },
  "links": {
    "first": "https://...",
    "last": "https://...",
    "prev": null,
    "next": "https://...?page=2"
  }
}
```

### Error
```json
{
  "success": false,
  "message": "Hata mesajı.",
  "errors": { "field": ["Validation error detail"] }
}
```

### HTTP Status Codes
| Code | Meaning |
|------|---------|
| 200  | OK |
| 201  | Created |
| 400  | Bad Request |
| 401  | Unauthenticated |
| 403  | Forbidden |
| 404  | Not Found |
| 422  | Validation Error |
| 500  | Server Error |

---

## Health Check

### GET /health
Public. Returns API status.

**Response:**
```json
{
  "status": "ok",
  "timestamp": "2024-01-15T10:30:00.000Z",
  "version": "1.0.0"
}
```

---

## Authentication Endpoints

### POST /auth/login
**Auth:** None

**Request:**
```json
{
  "email": "admin@recrm.com",
  "password": "password",
  "device_name": "WebApp"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Giriş başarılı.",
  "data": {
    "token": "1|abc123...",
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@recrm.com",
      "office": { ... },
      "team": { ... },
      "roles": [ ... ]
    }
  }
}
```

---

### POST /auth/register
**Auth:** None

**Request:**
```json
{
  "name": "Ad Soyad",
  "email": "email@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

**Response (201):** Same as login.

---

### POST /auth/logout
**Auth:** Required

**Response (200):**
```json
{ "success": true, "message": "Çıkış yapıldı.", "data": null }
```

---

### GET /auth/user
**Auth:** Required

Returns the authenticated user with office, team, and roles loaded.

---

### PUT /auth/profile
**Auth:** Required

**Request (multipart/form-data or JSON):**
```json
{
  "name": "Yeni İsim",
  "email": "yeni@email.com",
  "phone": "+905001234567"
}
```
Optionally include `avatar` (image file) as multipart.

---

### PUT /auth/password
**Auth:** Required

**Request:**
```json
{
  "current_password": "eskisifre",
  "password": "yenisifre",
  "password_confirmation": "yenisifre"
}
```

### POST /auth/forgot-password
**Auth:** None

**Request:**
```json
{ "email": "email@example.com" }
```

---

## Dashboard

### GET /dashboard/stats
**Auth:** Required

**Response:**
```json
{
  "data": {
    "listings": 245,
    "active_listings": 180,
    "leads": 320,
    "new_leads": 45,
    "deals": 95,
    "won_deals": 62,
    "revenue": 15750000,
    "agents": 12
  }
}
```

---

### GET /dashboard/charts
**Auth:** Required

Returns last 6 months of lead, deal, and revenue data.

**Response:**
```json
{
  "data": {
    "monthly": [
      { "month": "Aug 2024", "leads": 28, "deals": 8, "revenue": 2100000 },
      ...
    ]
  }
}
```

---

### GET /dashboard/recent-activity
**Auth:** Required

Returns last 10 activities across the system.

---

## Offices

### GET /offices
**Auth:** Required  
**Query params:** none

Returns paginated list of offices with `users_count`.

---

### POST /offices
**Auth:** Required

**Request:**
```json
{
  "name": "İstanbul Ofisi",
  "city": "İstanbul",
  "address": "Bağcılar Mah. No:5",
  "phone": "+902121234567",
  "email": "istanbul@recrm.com"
}
```

---

### GET /offices/{id}
**Auth:** Required — Returns office with users loaded.

---

### PUT /offices/{id}
**Auth:** Required — Same fields as POST.

---

### DELETE /offices/{id}
**Auth:** Required

---

### GET /offices/{id}/users
**Auth:** Required — Paginated list of users in the office.

---

### GET /offices/{id}/stats
**Auth:** Required

**Response:**
```json
{
  "data": {
    "agents": 8,
    "active_listings": 45,
    "total_listings": 72
  }
}
```

---

## Teams

### GET /teams
**Auth:** Required — Paginated list with `users_count`.

### POST /teams
**Auth:** Required

**Request:**
```json
{
  "name": "Satış Takımı A",
  "description": "Anadolu yakası satış grubu",
  "leader_id": 3,
  "office_id": 1
}
```

### GET /teams/{id}
**Auth:** Required — Returns team with users and leader.

### PUT /teams/{id} / DELETE /teams/{id}
**Auth:** Required

### GET /teams/{id}/members
**Auth:** Required — Paginated member list.

---

## Users

### GET /users
**Auth:** Required — Paginated list with office and team.

### POST /users
**Auth:** Required

**Request:**
```json
{
  "name": "Ahmet Yılmaz",
  "email": "ahmet@recrm.com",
  "password": "password123",
  "phone": "+905001234567",
  "office_id": 1,
  "team_id": 2
}
```

### GET /users/{id}
**Auth:** Required — Returns user with office, team, and roles.

### PUT /users/{id} / DELETE /users/{id}
**Auth:** Required

### GET /users/{id}/performance
**Auth:** Required

**Response:**
```json
{
  "data": {
    "total_leads": 45,
    "active_leads": 12,
    "total_deals": 28,
    "won_deals": 19,
    "revenue": 4750000
  }
}
```

---

## Notifications

### GET /notifications
**Auth:** Required — Paginated notifications for authenticated user.

### GET /notifications/unread-count
**Auth:** Required

**Response:**
```json
{ "data": { "count": 7 } }
```

### POST /notifications/{id}/read
**Auth:** Required — Marks a single notification as read.

### POST /notifications/read-all
**Auth:** Required — Marks all notifications as read.

---

## Listings

### GET /listings
**Auth:** Required  
**Query params:**
| Param | Type | Description |
|-------|------|-------------|
| `city` | string | Filter by city |
| `type` | string | `sale` or `rent` |
| `category` | string | e.g. `apartment`, `villa` |
| `min_price` | number | Minimum price |
| `max_price` | number | Maximum price |
| `status` | string | `active`, `sold`, `rented`, `passive` |
| `per_page` | integer | Items per page (default 20) |

---

### POST /listings
**Auth:** Required

**Request:**
```json
{
  "title": "Kadıköy'de 3+1 Satılık Daire",
  "listing_type": "sale",
  "category": "apartment",
  "price": 4500000,
  "city": "İstanbul",
  "district": "Kadıköy",
  "address": "Moda Cad. No:12",
  "gross_sqm": 130,
  "net_sqm": 115,
  "room_count": 3,
  "living_room_count": 1,
  "bathroom_count": 2,
  "floor_number": 4,
  "total_floors": 8,
  "building_age": 5,
  "description": "Manzaralı, ebeveyn banyolu, geniş balkonlu...",
  "features": ["Asansör", "Otopark", "Güvenlik"],
  "agent_id": 2,
  "project_id": null
}
```

---

### GET /listings/{id}
**Auth:** Required — Returns listing with agent, project, and media. Also increments `view_count`.

---

### PUT /listings/{id}
**Auth:** Required — Same fields as POST.

---

### DELETE /listings/{id}
**Auth:** Required

---

### GET /listings/{id}/similar
**Auth:** Required — Returns up to 6 listings in same city and type within ±20% price range.

**Response:**
```json
{
  "data": [ { "id": 14, "title": "...", "price": 4200000, ... }, ... ]
}
```

---

### POST /listings/{id}/view
**Auth:** Required — Increments view counter.

**Response:**
```json
{ "data": { "view_count": 47 } }
```

---

### POST /listings/{id}/favorite
**Auth:** Required — Toggles favorite status for authenticated user.

---

### GET /listings-stats
**Auth:** Required

**Response:**
```json
{
  "data": {
    "total": 245,
    "active": 180,
    "for_sale": 160,
    "for_rent": 85,
    "sold": 42,
    "avg_price_sale": 3850000,
    "avg_price_rent": 18500
  }
}
```

---

## Projects

### GET /projects
**Auth:** Required  
**Query params:** `status` (planning/under_construction/completed), `city`, `featured` (1/0)

---

### POST /projects
**Auth:** Required

**Request:**
```json
{
  "name": "Marina Residences",
  "description": "Deniz manzaralı lüks konut projesi",
  "developer": "ABC İnşaat A.Ş.",
  "city": "İzmir",
  "district": "Alsancak",
  "address": "Kordon Cad. No:45",
  "total_units": 120,
  "available_units": 65,
  "min_price": 5500000,
  "max_price": 18000000,
  "delivery_date": "2025-12-31",
  "status": "under_construction"
}
```

---

### GET /projects/{id}
**Auth:** Required — Returns project with listings and media.

### PUT /projects/{id} / DELETE /projects/{id}
**Auth:** Required

---

### GET /projects/{id}/listings
**Auth:** Required — Paginated listings belonging to the project.

---

### GET /projects/{id}/stats
**Auth:** Required

**Response:**
```json
{
  "data": {
    "total_units": 120,
    "available_units": 65,
    "sold_units": 55,
    "listings_count": 38,
    "active_listings": 22,
    "min_price": 5500000,
    "max_price": 18000000
  }
}
```

---

## CRM — Leads

### GET /leads
**Auth:** Required  
**Query params:** `status`, `assigned_to` (user ID), `priority`

---

### POST /leads
**Auth:** Required

**Request:**
```json
{
  "contact_id": 12,
  "pipeline_id": 1,
  "stage_id": 2,
  "interest_type": "buy",
  "property_type": "apartment",
  "budget_min": 3000000,
  "budget_max": 5000000,
  "priority": "high",
  "assigned_to": 3,
  "notes": "İki çocuklu aile, okula yakın bölge istiyor."
}
```

---

### GET /leads/{id}
**Auth:** Required — Returns lead with contact, assignedTo, pipeline, stage, activities, and tasks.

### PUT /leads/{id}
**Auth:** Required

**Updatable fields:** `interest_type`, `property_type`, `budget_min`, `budget_max`, `priority`, `status`, `score` (0-100), `assigned_to`, `notes`

### DELETE /leads/{id}
**Auth:** Required

---

## CRM — Deals

### GET /deals
**Auth:** Required  
**Query params:** `status` (open/won/lost), `assigned_to`

---

### POST /deals
**Auth:** Required

**Request:**
```json
{
  "title": "Kadıköy Daire Satışı",
  "contact_id": 12,
  "pipeline_id": 1,
  "stage_id": 3,
  "value": 4500000,
  "probability": 75,
  "expected_close_date": "2024-03-31",
  "assigned_to": 2,
  "notes": "Tapu devri Mart ayında planlanıyor."
}
```

---

### GET /deals/{id}
**Auth:** Required — Returns deal with contact, assignedTo, pipeline, stage, activities, and tasks.

### PUT /deals/{id} / DELETE /deals/{id}
**Auth:** Required

---

## CRM — Contacts

### GET /contacts
**Auth:** Required  
**Query params:** `search` (name/email/phone), `status`

---

### POST /contacts
**Auth:** Required

**Request:**
```json
{
  "first_name": "Mehmet",
  "last_name": "Kaya",
  "email": "mehmet.kaya@email.com",
  "phone": "+905321234567",
  "city": "İstanbul",
  "source": "website",
  "assigned_to": 2,
  "notes": "Yatırım amaçlı mülk arıyor."
}
```

---

### GET /contacts/{id}
**Auth:** Required — Returns contact with leads, deals, and activities.

### PUT /contacts/{id} / DELETE /contacts/{id}
**Auth:** Required

---

## Error Examples

### 401 Unauthenticated
```json
{ "message": "Unauthenticated." }
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "Doğrulama hatası.",
  "errors": {
    "email": ["The email field is required."],
    "price": ["The price must be at least 0."]
  }
}
```

### 404 Not Found
```json
{ "message": "No query results for model [Listing] 999" }
```

---

## Frontend Integration Notes

1. Store the token in `localStorage` or `sessionStorage` after login.
2. Attach it to every request as `Authorization: Bearer {token}`.
3. On `401` response, redirect the user to the login page and clear the stored token.
4. Paginated endpoints accept a `page` query parameter (e.g. `?page=2&per_page=20`).
5. All timestamps are in ISO 8601 UTC format. Convert to local time in the frontend.
6. Price values are in Turkish Lira (₺), stored as plain integers (no decimals).
7. `features` fields are JSON arrays — send as `["Asansör", "Otopark"]`.
