# API Documentation: Creator Marketplace with Sealed Bid Auction

This document describes the API routes and endpoints for the Creator Marketplace application.

## Overview

The application uses **Inertia.js** for seamless SPA-like experience, which means most routes return Inertia responses rather than JSON. However, some endpoints return JSON for AJAX requests.

### Base URL
- Development: `http://localhost`
- Production: `https://your-domain.com`

### Authentication
The application uses Laravel Breeze session-based authentication with CSRF protection.

**CSRF Token**: All POST, PUT, PATCH, DELETE requests require a CSRF token in the `X-CSRF-TOKEN` header or `_token` field.

### Response Formats

**Inertia Response** (most routes):
```javascript
{
  component: 'ComponentName',
  props: { /* data */ },
  url: '/current/url',
  version: 'asset-version'
}
```

**JSON Response** (API endpoints):
```json
{
  "data": { /* response data */ },
  "message": "Success message"
}
```

**Error Response**:
```json
{
  "message": "Error message",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

## Authentication Routes

### Register
```
POST /register
```

**Request Body**:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response**: Redirect to dashboard

---

### Login
```
POST /login
```

**Request Body**:
```json
{
  "email": "john@example.com",
  "password": "password123",
  "remember": true
}
```

**Response**: Redirect to appropriate dashboard based on role

---

### Logout
```
POST /logout
```

**Response**: Redirect to home page

---

## Public Routes

### Marketplace - Open Market Feed
```
GET /marketplace
```

**Query Parameters**:
- `category` (optional): Filter by product category
- `max_price` (optional): Maximum reserve price
- `ending_soon` (optional): Show auctions ending within 24 hours
- `page` (optional): Pagination page number

**Response**: Inertia page with products list

**Example**:
```
GET /marketplace?category=art&max_price=500&page=1
```

---

### Marketplace - For You Feed
```
GET /marketplace/for-you
```

**Authentication**: Required (Buyer role)

**Response**: Inertia page with products from followed creators

---

### Product Detail
```
GET /products/{product_id}
```

**Response**: Inertia page with product details, images, and user's bid (if authenticated)

**Response Data**:
```javascript
{
  product: {
    id: "uuid",
    title: "Product Title",
    description: "Product description",
    reserve_price: 100.00, // Hidden from buyers during auction
    auction_start: "2024-01-01T00:00:00Z",
    auction_end: "2024-01-07T23:59:59Z",
    status: "active",
    creator: {
      id: "uuid",
      name: "Creator Name",
      shop: {
        shop_name: "Shop Name",
        profile_image: "url"
      }
    },
    images: [
      { id: "uuid", image_path: "url", is_primary: true }
    ]
  },
  userBid: {
    rank: 3, // Only rank shown to buyers
    amount: 150.00 // Only shown to bid owner and admin
  }
}
```

---

### Creator Shop Page
```
GET /shops/{shop_id}
```

**Response**: Inertia page with creator shop details and products

**Response Data**:
```javascript
{
  shop: {
    id: "uuid",
    shop_name: "Shop Name",
    bio: "Shop bio",
    profile_image: "url",
    banner_image: "url",
    follower_count: 42,
    is_followed: true // If authenticated
  },
  products: [
    { /* product objects */ }
  ]
}
```

---

## Buyer Routes

All buyer routes require authentication with `buyer` role.

### Submit Bid
```
POST /bids
```

**Request Body**:
```json
{
  "product_id": "uuid",
  "amount": 150.00
}
```

**Validation Rules**:
- `product_id`: required, exists in products table
- `amount`: required, numeric, >= reserve_price

**Rate Limit**: 10 requests per minute

**Response**:
```json
{
  "message": "Your bid is currently ranked #3",
  "rank": 3
}
```

**Error Responses**:
- `403`: Auction has ended or not started
- `422`: Bid amount below reserve price
- `429`: Too many requests

---

### View Bid
```
GET /products/{product_id}/bid
```

**Response**:
```json
{
  "bid": {
    "amount": 150.00,
    "rank": 3,
    "created_at": "2024-01-05T10:30:00Z",
    "updated_at": "2024-01-05T15:45:00Z"
  }
}
```

---

### Follow Creator
```
POST /follow
```

**Request Body**:
```json
{
  "creator_id": "uuid"
}
```

**Response**:
```json
{
  "message": "Successfully followed creator"
}
```

---

### Unfollow Creator
```
DELETE /follow/{creator_id}
```

**Response**:
```json
{
  "message": "Successfully unfollowed creator"
}
```

---

### View Leaderboard
```
GET /products/{product_id}/leaderboard
```

**Authentication**: Required

**Response**: Inertia page with leaderboard

**Response Data**:
```javascript
{
  product: { /* product details */ },
  leaderboard: [
    {
      rank: 1,
      user_name: "Winner Name",
      is_winner: true,
      is_current_user: false,
      amount: 200.00 // Only visible to admin
    },
    {
      rank: 2,
      user_name: "Your Name",
      is_current_user: true,
      amount: 150.00 // Only visible to bid owner and admin
    }
  ]
}
```

---

### Payment Page
```
GET /payment/{order_id}
```

**Authentication**: Required (Winner only)

**Response**: Inertia page with payment form

**Response Data**:
```javascript
{
  order: {
    id: "uuid",
    amount: 200.00,
    payment_deadline: "2024-01-09T23:59:59Z",
    product: { /* product details */ }
  },
  stripe_key: "pk_live_..."
}
```

---

### Process Payment
```
POST /payment/{order_id}
```

**Request Body**:
```json
{
  "payment_method_id": "pm_..." // Stripe payment method ID
}
```

**Response**:
```json
{
  "message": "Payment successful",
  "order": {
    "id": "uuid",
    "status": "completed"
  }
}
```

---

## Creator Routes

All creator routes require authentication with `creator` role and completed onboarding.

### Creator Onboarding
```
GET /creator/onboarding
POST /creator/onboarding
```

**POST Request Body**:
```json
{
  "shop_name": "My Shop",
  "bio": "Shop bio",
  "profile_image": "file", // multipart/form-data
  "banner_image": "file"
}
```

**Validation Rules**:
- `shop_name`: required, unique, max 255 characters
- `bio`: optional, max 1000 characters
- `profile_image`: optional, image, max 2MB
- `banner_image`: optional, image, max 5MB

**Response**: Redirect to creator dashboard

---

### List Products
```
GET /creator/products
```

**Response**: Inertia page with creator's products

---

### Create Product
```
GET /creator/products/create
POST /creator/products
```

**POST Request Body** (multipart/form-data):
```json
{
  "title": "Product Title",
  "description": "Product description",
  "category": "art",
  "reserve_price": 100.00,
  "auction_start": "2024-01-01T00:00:00Z",
  "auction_end": "2024-01-07T23:59:59Z",
  "images": ["file1", "file2", "file3"] // 1-5 images
}
```

**Validation Rules**:
- `title`: required, max 255 characters
- `description`: required, max 5000 characters
- `category`: optional, max 100 characters
- `reserve_price`: required, numeric, min 0.01, max 999999.99
- `auction_start`: required, date, after now
- `auction_end`: required, date, after auction_start
- `images`: required, array, min 1, max 5
- `images.*`: image, mimes: jpeg,png,jpg,webp, max 5MB

**Response**: Redirect to product list

---

### Edit Product
```
GET /creator/products/{product_id}/edit
PUT /creator/products/{product_id}
```

**PUT Request Body**: Same as create product

**Response**: Redirect to product list

---

### Delete Product
```
DELETE /creator/products/{product_id}
```

**Response**: Redirect to product list

---

## Admin Routes

All admin routes require authentication with `admin` role.

### Admin Dashboard
```
GET /admin/dashboard
```

**Response**: Inertia page with statistics

**Response Data**:
```javascript
{
  stats: {
    total_auctions: 150,
    active_auctions: 42,
    total_bids: 1250,
    total_revenue: 50000.00
  },
  recent_auctions: [
    { /* auction details with bid counts */ }
  ]
}
```

---

### List Creators
```
GET /admin/creators
```

**Response**: Inertia page with creators list

---

### Create Creator Account
```
POST /admin/creators
```

**Request Body**:
```json
{
  "name": "Creator Name",
  "email": "creator@example.com"
}
```

**Response**: Redirect to creators list

**Side Effect**: Sends invite email with temporary password

---

### View Creator Details
```
GET /admin/creators/{creator_id}
```

**Response**: Inertia page with creator details including private info

**Response Data**:
```javascript
{
  creator: {
    id: "uuid",
    name: "Creator Name",
    email: "creator@example.com",
    shop: {
      shop_name: "Shop Name",
      follower_count: 42
    },
    private_info: {
      stripe_account_id: "acct_...",
      payout_email: "payout@example.com",
      tax_id: "***-**-1234" // Partially masked
    }
  },
  products: [ /* creator's products */ ]
}
```

---

### View Auction Details with All Bids
```
GET /admin/auctions/{product_id}
```

**Response**: Inertia page with full bid visibility

**Response Data**:
```javascript
{
  product: { /* product details */ },
  bids: [
    {
      id: "uuid",
      user: { name: "Bidder Name", email: "bidder@example.com" },
      amount: 200.00, // Admin can see all amounts
      rank: 1,
      created_at: "2024-01-05T10:30:00Z"
    }
  ],
  stats: {
    total_bids: 15,
    highest_bid: 200.00,
    average_bid: 125.50
  }
}
```

---

### Export Auction Data
```
GET /admin/auctions/{product_id}/export
```

**Response**: JSON file download

**Response Format**:
```json
{
  "product": {
    "id": "uuid",
    "title": "Product Title",
    "reserve_price": 100.00,
    "auction_start": "2024-01-01T00:00:00Z",
    "auction_end": "2024-01-07T23:59:59Z",
    "status": "sold"
  },
  "bids": [
    {
      "user_id": "uuid",
      "amount": 200.00,
      "created_at": "2024-01-05T10:30:00Z"
    }
  ]
}
```

---

### Import Auction Data
```
POST /admin/auctions/import
```

**Request Body** (multipart/form-data):
```json
{
  "file": "auction_data.json"
}
```

**Response**:
```json
{
  "message": "Auction data imported successfully",
  "imported_count": 5
}
```

---

## Webhook Endpoints

### Stripe Webhook
```
POST /stripe/webhook
```

**Authentication**: Stripe signature verification

**Handled Events**:
- `payment_intent.succeeded`: Mark order as completed
- `payment_intent.payment_failed`: Mark order as failed
- `charge.refunded`: Mark order as refunded

**Response**: 200 OK

---

## Rate Limiting

### Bid Submission
- **Limit**: 10 requests per minute per user
- **Response**: 429 Too Many Requests

### API Routes
- **Limit**: 60 requests per minute per user
- **Response**: 429 Too Many Requests

### Public Routes
- **Limit**: 100 requests per minute per IP
- **Response**: 429 Too Many Requests

---

## Error Codes

### 400 Bad Request
Invalid request format or parameters

### 401 Unauthorized
Authentication required or invalid credentials

### 403 Forbidden
User does not have permission to access resource

**Common Scenarios**:
- Non-admin accessing admin routes
- Buyer viewing other buyers' bid amounts
- Bidding on ended auction

### 404 Not Found
Resource does not exist

### 422 Unprocessable Entity
Validation failed

**Example Response**:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "amount": ["The amount must be at least 100.00"],
    "auction_end": ["The auction end must be after auction start"]
  }
}
```

### 429 Too Many Requests
Rate limit exceeded

**Response Headers**:
```
X-RateLimit-Limit: 10
X-RateLimit-Remaining: 0
Retry-After: 60
```

### 500 Internal Server Error
Server error occurred

---

## Data Models

### User
```typescript
interface User {
  id: string; // UUID
  name: string;
  email: string;
  role: 'admin' | 'creator' | 'buyer';
  created_at: string; // ISO 8601
  updated_at: string;
}
```

### Product
```typescript
interface Product {
  id: string; // UUID
  creator_id: string;
  title: string;
  description: string;
  category?: string;
  reserve_price: number; // Hidden from buyers during auction
  auction_start: string; // ISO 8601
  auction_end: string; // ISO 8601
  status: 'draft' | 'active' | 'ended' | 'sold' | 'unsold';
  winning_bid_id?: string;
  created_at: string;
  updated_at: string;
  
  // Relationships
  creator?: User;
  images?: ProductImage[];
  bids?: Bid[];
}
```

### ProductImage
```typescript
interface ProductImage {
  id: string; // UUID
  product_id: string;
  image_path: string; // Full URL
  is_primary: boolean;
  display_order: number;
  created_at: string;
}
```

### Bid
```typescript
interface Bid {
  id: string; // UUID
  product_id: string;
  user_id: string;
  amount: number; // Only visible to owner and admin
  rank?: number; // Calculated field
  created_at: string;
  updated_at: string;
  
  // Relationships
  product?: Product;
  user?: User;
}
```

### CreatorShop
```typescript
interface CreatorShop {
  id: string; // UUID
  user_id: string;
  shop_name: string;
  bio?: string;
  profile_image?: string; // Full URL
  banner_image?: string; // Full URL
  is_onboarded: boolean;
  follower_count?: number; // Calculated field
  created_at: string;
  updated_at: string;
  
  // Relationships
  creator?: User;
  products?: Product[];
}
```

### Order
```typescript
interface Order {
  id: string; // UUID
  user_id: string;
  product_id: string;
  bid_id: string;
  amount: number;
  stripe_payment_id: string;
  status: 'pending' | 'completed' | 'expired' | 'refunded';
  payment_deadline: string; // ISO 8601
  created_at: string;
  updated_at: string;
  
  // Relationships
  user?: User;
  product?: Product;
  bid?: Bid;
}
```

---

## Testing

### Test Credentials

**Admin**:
```
Email: admin@example.com
Password: password
```

**Creator**:
```
Email: creator@example.com
Password: password
```

**Buyer**:
```
Email: buyer@example.com
Password: password
```

### Stripe Test Cards

**Successful Payment**:
```
Card Number: 4242 4242 4242 4242
Expiry: Any future date
CVC: Any 3 digits
```

**Payment Declined**:
```
Card Number: 4000 0000 0000 0002
```

**Requires Authentication**:
```
Card Number: 4000 0025 0000 3155
```

---

## Best Practices

### Authentication
- Always include CSRF token in POST/PUT/DELETE requests
- Session cookies are httpOnly and secure in production
- Logout users after 2 hours of inactivity

### Bid Privacy
- Never expose bid amounts to unauthorized users
- Always check authorization before returning bid data
- Use gates for authorization checks

### File Uploads
- Validate file types and sizes
- Scan for malicious content
- Store files in S3/R2, not local filesystem

### Rate Limiting
- Respect rate limits to avoid 429 errors
- Implement exponential backoff for retries
- Cache responses when possible

### Error Handling
- Always check response status codes
- Display user-friendly error messages
- Log errors for debugging

---

## Support

For API questions or issues:
- Review this documentation
- Check application logs
- Contact development team

## Changelog

### Version 1.0.0 (2024-01-01)
- Initial API release
- Multi-role authentication
- Sealed bid auction system
- Stripe payment integration
- Creator shop management
- Follow system
