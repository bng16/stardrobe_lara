# Task 24.2: Auction Data Import Functionality

## Overview

This document describes the auction data import functionality implemented for the Creator Marketplace with Sealed Bid Auction system. The feature allows administrators to import auction data from JSON format with comprehensive validation and error handling.

## Implementation

### Components

1. **AuctionImportService** (`app/Services/AuctionImportService.php`)
   - Core service class handling JSON parsing and validation
   - Creates auction records with related data (images, bids)
   - Uses database transactions for data integrity

2. **DashboardController::importAuction** (`app/Http/Controllers/Admin/DashboardController.php`)
   - Admin endpoint for importing auction data
   - Handles HTTP requests and error responses
   - Protected by admin authentication and authorization

3. **Route** (`routes/web.php`)
   - POST `/admin/auctions/import`
   - Requires authentication and admin role

## JSON Structure

The import endpoint accepts JSON in the same format as the export functionality:

```json
{
  "title": "Auction Title",
  "description": "Auction description",
  "category": "Art",
  "reserve_price": 100.00,
  "auction_start": "2024-01-01T00:00:00+00:00",
  "auction_end": "2024-01-08T00:00:00+00:00",
  "status": "active",
  "winning_bid_id": null,
  "creator": {
    "id": "creator-uuid",
    "name": "Creator Name",
    "email": "creator@example.com"
  },
  "bids": [
    {
      "user_id": "user-uuid",
      "amount": 150.00,
      "created_at": "2024-01-02T00:00:00+00:00"
    }
  ],
  "images": [
    {
      "image_path": "path/to/image.jpg",
      "is_primary": true,
      "display_order": 0
    }
  ]
}
```

## Validation Rules

### Required Fields
- `title`: String, max 255 characters
- `description`: String
- `reserve_price`: Numeric, minimum 0.01
- `auction_start`: Valid date
- `auction_end`: Valid date, must be after auction_start
- `status`: One of: draft, active, ended, sold, unsold
- `creator`: Object with id, name, and email

### Optional Fields
- `category`: String, max 100 characters
- `winning_bid_id`: String (UUID)
- `bids`: Array of bid objects
- `images`: Array of image objects

### Business Rules
1. Auction end time must be after start time
2. Reserve price must be at least 0.01
3. Creator must exist in the database (matched by email)
4. Bids for non-existent users are skipped (not failed)
5. All valid auction statuses are accepted

## Error Handling

### JSON Parse Errors (400 Bad Request)
```json
{
  "success": false,
  "message": "Invalid JSON format",
  "error": "Syntax error"
}
```

### Validation Errors (422 Unprocessable Entity)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["Auction title is required"],
    "auction_end": ["Auction end time must be after start time"]
  }
}
```

### Missing Creator (500 Internal Server Error)
```json
{
  "success": false,
  "message": "An error occurred during import",
  "error": "Creator with email 'creator@example.com' not found..."
}
```

### Success Response (201 Created)
```json
{
  "success": true,
  "message": "Auction imported successfully",
  "auction": {
    "id": "product-uuid",
    "title": "Auction Title",
    ...
  }
}
```

## Usage Examples

### Using cURL

```bash
curl -X POST https://example.com/admin/auctions/import \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d @auction-data.json
```

### Using JavaScript/Fetch

```javascript
const auctionData = {
  title: "Imported Auction",
  description: "Description here",
  reserve_price: 100.00,
  auction_start: "2024-01-01T00:00:00Z",
  auction_end: "2024-01-08T00:00:00Z",
  status: "active",
  creator: {
    id: "creator-uuid",
    name: "Creator Name",
    email: "creator@example.com"
  }
};

const response = await fetch('/admin/auctions/import', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken
  },
  body: JSON.stringify(auctionData)
});

const result = await response.json();
```

## Security Considerations

1. **Authentication Required**: Only authenticated admin users can import
2. **Authorization Check**: Uses `admin-dashboard` gate
3. **Transaction Safety**: All imports use database transactions
4. **Validation**: Comprehensive validation prevents invalid data
5. **Creator Verification**: Creator must exist before import

## Testing

### Feature Tests
- `tests/Feature/AuctionDataImportTest.php`
  - Tests complete import workflow
  - Tests validation rules
  - Tests error handling
  - Tests authorization

### Unit Tests
- `tests/Unit/AuctionImportServiceTest.php`
  - Tests JSON parsing logic
  - Tests error messages
  - Tests data structure handling

## Database Transactions

All import operations are wrapped in database transactions to ensure data integrity:

1. If any step fails, the entire import is rolled back
2. No partial data is left in the database
3. Related records (images, bids) are created atomically

## Limitations

1. **Creator Must Exist**: The creator account must be created before importing auctions
2. **User References**: Bids for non-existent users are silently skipped
3. **Image Paths**: Image paths are stored as-is; actual files must exist in storage
4. **No Duplicate Check**: The system does not check for duplicate auctions

## Future Enhancements

1. Add support for batch imports (multiple auctions in one request)
2. Add dry-run mode to validate without importing
3. Add duplicate detection based on title/creator
4. Add support for creating missing users during import
5. Add import history/audit log

## Related Requirements

- **Requirement 20.2**: Parser SHALL parse JSON into Auction objects
- **Requirement 20.3**: Parser SHALL return descriptive error messages for invalid JSON
- **Validates**: Property 36 - Invalid JSON Error Handling

## Related Files

- `app/Services/AuctionImportService.php` - Core import service
- `app/Http/Controllers/Admin/DashboardController.php` - Import endpoint
- `app/Models/Product.php` - Product model with toExportArray method
- `routes/web.php` - Route definition
- `tests/Feature/AuctionDataImportTest.php` - Feature tests
- `tests/Unit/AuctionImportServiceTest.php` - Unit tests
