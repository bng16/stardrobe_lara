# Requirements Document

## Introduction

The Creator Marketplace with Sealed Bid Auction system is a Laravel 11 application that enables creators to sell products through time-bound sealed bid auctions. The system maintains strict bid confidentiality, showing only rank positions to buyers while allowing admins full visibility. The platform supports multi-role authentication (admin, creator, buyer), automated auction closing, Stripe payment processing, and a social follow system with personalized feeds.

## Glossary

- **System**: The Creator Marketplace application
- **Admin**: A user with administrative privileges who can create creator accounts and view all bids
- **Creator**: A user who can list products for auction and manage their shop
- **Buyer**: A user who can place bids on products and follow creators
- **Sealed_Bid**: A bid amount that is hidden from other buyers during and after the auction
- **Auction**: A time-bound sale period for a product where buyers submit sealed bids
- **Leaderboard**: A post-auction display showing rank positions without revealing bid amounts
- **Reserve_Price**: The minimum acceptable bid amount set by the creator
- **Auction_Scheduler**: A Laravel scheduled task that closes auctions every minute
- **Payment_Window**: A 48-hour period after auction close for the winner to complete payment
- **Creator_Shop**: A public-facing page displaying a creator's products and profile
- **Follow_System**: A mechanism allowing buyers to follow creators for personalized content
- **For_You_Feed**: A personalized feed showing products from followed creators
- **Open_Market_Feed**: A public feed showing all available products with filters
- **Onboarding_Flow**: A multi-step process for creators to complete their profile and shop setup
- **Invite_Email**: An email sent by admins to new creators with account credentials
- **Product_Image**: An image file uploaded to S3 or Cloudflare R2 storage
- **Bid_Rank**: The position of a buyer's bid relative to other bids (1st, 2nd, 3rd, etc.)
- **Winner**: The buyer with the highest bid when the auction closes
- **Order**: A record of a completed transaction after payment
- **Notification_System**: Email-based notifications for auction events
- **Rate_Limiter**: A mechanism to prevent excessive bid submissions
- **UUID**: A universally unique identifier used as primary key
- **Creator_Private_Info**: Encrypted payout and tax information accessible only to the creator and admin
- **Middleware**: Laravel middleware components that control access to routes
- **Queue_Job**: A background task processed by Laravel Queue with Redis
- **Stripe_Cashier**: Laravel Cashier integration for Stripe payment processing

## Requirements

### Requirement 1: Multi-Role Authentication System

**User Story:** As a system administrator, I want a multi-role authentication system, so that admins, creators, and buyers have appropriate access levels.

#### Acceptance Criteria

1. THE System SHALL support three user roles: admin, creator, and buyer
2. WHEN a user logs in, THE System SHALL authenticate them using Laravel Breeze
3. THE System SHALL store the user role in the users table as an enum field
4. THE System SHALL restrict route access based on user role using middleware
5. WHEN an unauthorized user attempts to access a protected route, THE System SHALL redirect them to an appropriate page

### Requirement 2: Admin Creator Account Management

**User Story:** As an admin, I want to create creator accounts and send invite emails, so that I can onboard new creators to the platform.

#### Acceptance Criteria

1. THE System SHALL provide an admin interface for creating creator accounts
2. WHEN an admin creates a creator account, THE System SHALL generate a secure temporary password
3. WHEN a creator account is created, THE System SHALL send an invite email with login credentials
4. THE System SHALL use Laravel Mail with Mailtrap for development and Resend or Mailgun for production
5. THE System SHALL queue invite emails using Laravel Queue with Redis

### Requirement 3: Creator Onboarding Flow

**User Story:** As a creator, I want a guided onboarding process, so that I can set up my shop and start selling products.

#### Acceptance Criteria

1. WHEN a creator logs in for the first time, THE System SHALL redirect them to the onboarding flow
2. THE System SHALL require creators to complete profile information before accessing other features
3. THE System SHALL use middleware to gate access until onboarding is complete
4. THE System SHALL collect shop name, bio, and profile image during onboarding
5. WHEN onboarding is complete, THE System SHALL mark the creator account as active

### Requirement 4: Creator Shop Setup

**User Story:** As a creator, I want to set up my shop with public and private information, so that buyers can discover my products while my payout details remain secure.

#### Acceptance Criteria

1. THE System SHALL store public shop information in the creator_shops table
2. THE System SHALL store private payout information in the creator_private_info table with encryption
3. THE System SHALL never load Creator_Private_Info on public routes
4. WHEN a creator updates shop information, THE System SHALL validate all input fields
5. THE System SHALL allow creators to upload a shop banner image to S3 or Cloudflare R2

### Requirement 5: Product Listing with Image Upload

**User Story:** As a creator, I want to list products with images and auction parameters, so that buyers can bid on my items.

#### Acceptance Criteria

1. THE System SHALL allow creators to create product listings with title, description, and reserve price
2. WHEN a creator uploads a product image, THE System SHALL validate file type and size
3. THE System SHALL store Product_Images in S3 or Cloudflare R2 using Laravel Storage
4. THE System SHALL require creators to set auction start time, end time, and reserve price
5. THE System SHALL use UUIDs as primary keys for all product records
6. WHEN a product is created, THE System SHALL validate that end time is after start time

### Requirement 6: Public Marketplace with Filters

**User Story:** As a buyer, I want to browse available products with filtering options, so that I can find items I'm interested in bidding on.

#### Acceptance Criteria

1. THE System SHALL display all active auctions in the Open_Market_Feed
2. THE System SHALL provide filters for category, price range, and auction end time
3. THE System SHALL show auction countdown timers for each product
4. THE System SHALL display product images, titles, and creator information
5. THE System SHALL hide Reserve_Price from buyers until after auction close

### Requirement 7: Sealed Bid Submission

**User Story:** As a buyer, I want to submit sealed bids on products, so that I can participate in auctions without revealing my bid amount to others.

#### Acceptance Criteria

1. WHEN a buyer submits a bid, THE System SHALL store it as a Sealed_Bid in the bids table
2. THE System SHALL allow only one bid per buyer per product
3. WHEN a buyer submits a second bid, THE System SHALL update their existing bid
4. THE System SHALL apply rate limiting to the bid submission endpoint
5. THE System SHALL validate that bid amount meets or exceeds the Reserve_Price
6. THE System SHALL prevent bid submission after the auction end time
7. THE System SHALL never expose Sealed_Bid amounts to other buyers

### Requirement 8: Bid Authorization and Privacy

**User Story:** As a buyer, I want my bid amount to remain confidential, so that other buyers cannot see what I bid.

#### Acceptance Criteria

1. THE System SHALL use gate-based authorization for viewing bid amounts
2. THE System SHALL allow only the bid owner and admins to view specific bid amounts
3. WHEN a buyer views their own bid, THE System SHALL display the bid amount
4. WHEN a buyer views the leaderboard, THE System SHALL display only Bid_Rank without amounts
5. THE System SHALL never include bid amounts in API responses to unauthorized users

### Requirement 9: Automated Auction Closing

**User Story:** As a system operator, I want auctions to close automatically at the scheduled time, so that the process is fair and consistent.

#### Acceptance Criteria

1. THE System SHALL run the Auction_Scheduler every minute using Laravel Task Scheduling
2. WHEN the current time exceeds an auction's end time, THE Auction_Scheduler SHALL close the auction
3. THE System SHALL process auction closing logic in a queued job
4. WHEN an auction closes, THE System SHALL determine the Winner based on highest bid
5. WHEN an auction closes with no bids meeting Reserve_Price, THE System SHALL mark it as unsold
6. THE System SHALL send notification emails to the Winner and creator when an auction closes

### Requirement 10: Post-Auction Leaderboard

**User Story:** As a buyer, I want to see my rank after an auction closes, so that I know how my bid compared without seeing others' bid amounts.

#### Acceptance Criteria

1. WHEN an auction closes, THE System SHALL generate a Leaderboard showing Bid_Rank for all bidders
2. THE System SHALL display Winner status to the winning buyer
3. THE System SHALL display rank position (1st, 2nd, 3rd, etc.) to all bidders
4. THE System SHALL never display bid amounts on the Leaderboard to buyers
5. THE System SHALL allow admins to view all bid amounts in the admin dashboard

### Requirement 11: Winner Payment Flow

**User Story:** As a winning buyer, I want to complete payment via Stripe, so that I can purchase the product I won.

#### Acceptance Criteria

1. WHEN a buyer wins an auction, THE System SHALL provide a payment link valid for 48 hours
2. THE System SHALL process payments using Stripe via Laravel Cashier
3. WHEN payment is completed, THE System SHALL create an Order record
4. WHEN payment is completed, THE System SHALL send confirmation emails to buyer and creator
5. IF payment is not completed within the Payment_Window, THE System SHALL mark the order as expired
6. THE System SHALL transfer funds to the creator's payout account after successful payment

### Requirement 12: Creator Public Shop Pages

**User Story:** As a buyer, I want to visit creator shop pages, so that I can see all products from a specific creator.

#### Acceptance Criteria

1. THE System SHALL provide a public URL for each Creator_Shop
2. WHEN a buyer visits a shop page, THE System SHALL display the creator's profile and active products
3. THE System SHALL show creator bio, profile image, and shop banner
4. THE System SHALL display follower count on the shop page
5. THE System SHALL never expose Creator_Private_Info on public shop pages

### Requirement 13: Follow System and Personalized Feeds

**User Story:** As a buyer, I want to follow creators and see their products in a personalized feed, so that I can easily track creators I'm interested in.

#### Acceptance Criteria

1. THE System SHALL allow buyers to follow and unfollow creators
2. THE System SHALL store follow relationships in the follows table
3. THE System SHALL provide a For_You_Feed showing products from followed creators
4. THE System SHALL provide an Open_Market_Feed showing all available products
5. WHEN a buyer is not following any creators, THE System SHALL display a message in the For_You_Feed

### Requirement 14: Admin Dashboard with Full Visibility

**User Story:** As an admin, I want to view all bids and auction data, so that I can monitor platform activity and resolve disputes.

#### Acceptance Criteria

1. THE System SHALL provide an admin dashboard with full bid visibility
2. THE System SHALL allow admins to view all Sealed_Bid amounts for any auction
3. THE System SHALL display auction statistics including total bids and highest bid
4. THE System SHALL allow admins to view creator payout information
5. THE System SHALL restrict admin dashboard access to users with admin role

### Requirement 15: Notification System

**User Story:** As a user, I want to receive email notifications for important events, so that I stay informed about auction activity.

#### Acceptance Criteria

1. WHEN an auction closes, THE System SHALL send email notifications to the Winner and creator
2. WHEN payment is completed, THE System SHALL send confirmation emails to buyer and creator
3. WHEN a followed creator lists a new product, THE System SHALL send notification to followers
4. THE System SHALL log all sent notifications in the notification_logs table
5. THE System SHALL queue all notification emails using Laravel Queue with Redis

### Requirement 16: Security and Data Protection

**User Story:** As a system operator, I want robust security measures, so that user data and bid information remain protected.

#### Acceptance Criteria

1. THE System SHALL use UUIDs as primary keys for all database tables
2. THE System SHALL encrypt Creator_Private_Info using Laravel encryption
3. THE System SHALL apply rate limiting to bid submission endpoints
4. THE System SHALL validate all file uploads for type, size, and malicious content
5. THE System SHALL use gate-based authorization for all sensitive data access
6. THE System SHALL never expose Sealed_Bid amounts in API responses to unauthorized users
7. THE System SHALL use HTTPS for all production traffic

### Requirement 17: File Upload Validation

**User Story:** As a system operator, I want strict file upload validation, so that malicious files cannot be uploaded to the platform.

#### Acceptance Criteria

1. WHEN a user uploads an image, THE System SHALL validate file type against allowed extensions
2. THE System SHALL validate file size does not exceed 5MB for product images
3. THE System SHALL validate file size does not exceed 2MB for profile images
4. THE System SHALL scan uploaded files for malicious content
5. IF validation fails, THE System SHALL return a descriptive error message

### Requirement 18: Database Schema and Relationships

**User Story:** As a developer, I want a well-structured database schema, so that data relationships are clear and maintainable.

#### Acceptance Criteria

1. THE System SHALL use UUID primary keys for users, creator_shops, products, bids, and orders tables
2. THE System SHALL enforce foreign key constraints for all relationships
3. THE System SHALL use soft deletes for users, products, and orders
4. THE System SHALL index frequently queried fields including auction end times and user roles
5. THE System SHALL use enum types for user roles and auction status fields

### Requirement 19: Queue and Background Job Processing

**User Story:** As a system operator, I want background job processing, so that time-intensive tasks don't block user requests.

#### Acceptance Criteria

1. THE System SHALL use Laravel Queue with Redis for background job processing
2. THE System SHALL queue email sending operations
3. THE System SHALL queue auction closing operations
4. THE System SHALL queue payment processing operations
5. WHEN a queued job fails, THE System SHALL retry up to 3 times before marking as failed

### Requirement 20: Parser and Serializer for Auction Data

**User Story:** As a developer, I want reliable parsing and serialization of auction data, so that data integrity is maintained across the system.

#### Acceptance Criteria

1. WHEN auction data is exported, THE System SHALL serialize it to JSON format
2. WHEN auction data is imported, THE Parser SHALL parse JSON into Auction objects
3. IF invalid JSON is provided, THE Parser SHALL return a descriptive error message
4. THE Pretty_Printer SHALL format Auction objects back into valid JSON
5. FOR ALL valid Auction objects, parsing then printing then parsing SHALL produce an equivalent object (round-trip property)
