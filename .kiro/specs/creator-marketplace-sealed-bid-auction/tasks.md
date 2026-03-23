# Implementation Plan: Creator Marketplace with Sealed Bid Auction

## Overview

This implementation plan breaks down the Creator Marketplace with Sealed Bid Auction feature into discrete, sequential coding tasks. The system is built on Laravel 11 with Inertia.js + Vue 3, featuring multi-role authentication, sealed bid auctions with strict privacy controls, automated auction closing, Stripe payment processing, and a social follow system.

The implementation follows a layered approach: database foundation → core models → authentication & authorization → business logic → frontend components → automation & jobs → testing.

## Tasks

- [x] 1. Database foundation and migrations
  - [x] 1.1 Create database migrations for core tables
    - Create migration for users table with role enum (admin, creator, buyer)
    - Create migration for creator_shops table with UUID primary key
    - Create migration for creator_private_info table with encrypted fields
    - Create migration for products table with auction timestamps and status enum
    - Create migration for product_images table
    - Create migration for bids table with unique constraint on user_id + product_id
    - Create migration for follows table with unique constraint on follower_id + creator_id
    - Create migration for orders table with payment tracking fields
    - Create migration for notification_logs table
    - Add all necessary indexes for performance (role, auction_end, status, etc.)
    - _Requirements: 18.1, 18.2, 18.4, 18.5_

  - [ ]* 1.2 Write property test for database constraints
    - **Property 10: Auction Time Constraint**
    - **Validates: Requirements 5.6**

- [x] 2. Core enums and base models
  - [x] 2.1 Create enum classes
    - Create UserRole enum (Admin, Creator, Buyer)
    - Create AuctionStatus enum (Draft, Active, Ended, Sold, Unsold)
    - Create OrderStatus enum (Pending, Completed, Expired, Refunded)
    - _Requirements: 1.1, 1.3, 18.5_

  - [x] 2.2 Set up User model with role casting
    - Update User model to cast role field to UserRole enum
    - Add relationships: creatorShop (hasOne), bids (hasMany), follows (belongsToMany), orders (hasMany)
    - Add query scopes: scopeCreators, scopeBuyers
    - _Requirements: 1.1, 1.3_


- [x] 3. Creator models and relationships
  - [x] 3.1 Create CreatorShop model
    - Set up UUID primary key with $keyType and $incrementing properties
    - Define fillable fields: user_id, shop_name, bio, profile_image, banner_image, is_onboarded
    - Add relationships: creator (belongsTo User), products (hasMany), followers (belongsToMany)
    - Add business logic methods: getFollowerCount(), getActiveProductCount()
    - _Requirements: 4.1, 12.1, 12.4_

  - [x] 3.2 Create CreatorPrivateInfo model
    - Set up with encrypted tax_id field using Laravel encryption
    - Define fillable fields: creator_shop_id, stripe_account_id, tax_id, payout_email
    - Add relationship: creatorShop (belongsTo)
    - _Requirements: 4.2, 16.2_

  - [ ]* 3.3 Write property test for private info encryption
    - **Property 6: Creator Private Info Encryption**
    - **Validates: Requirements 4.2, 16.2**

  - [ ]* 3.4 Write property test for private info exclusion on public routes
    - **Property 7: Private Info Exclusion on Public Routes**
    - **Validates: Requirements 4.3, 12.5**

- [x] 4. Product and bid models
  - [x] 4.1 Create Product model
    - Set up UUID primary key
    - Define fillable fields and cast auction_start, auction_end to datetime, status to AuctionStatus enum
    - Add relationships: creator (belongsTo User), bids (hasMany), images (hasMany)
    - Add query scopes: scopeActive, scopeEnded, scopeNeedsClosure
    - Add business logic methods: isActive(), hasEnded(), getWinningBid()
    - _Requirements: 5.1, 5.4, 5.5, 9.2_

  - [x] 4.2 Create ProductImage model
    - Set up UUID primary key
    - Define fillable fields: product_id, image_path, is_primary, display_order
    - Add relationship: product (belongsTo)
    - _Requirements: 5.3_

  - [x] 4.3 Create Bid model with privacy scopes
    - Set up UUID primary key
    - Define fillable fields: product_id, user_id, amount (cast to decimal)
    - Add relationships: product (belongsTo), user (belongsTo)
    - Add CRITICAL privacy scopes: scopeForUser, scopeWithAmountIfAuthorized
    - Add business logic methods: getRank(), isWinning()
    - _Requirements: 7.1, 7.2, 8.1, 8.2_

  - [ ]* 4.4 Write property test for bid storage and uniqueness
    - **Property 13: Bid Storage and Uniqueness**
    - **Validates: Requirements 7.1, 7.2**

  - [ ]* 4.5 Write property test for sealed bid privacy
    - **Property 17: Sealed Bid Privacy**
    - **Validates: Requirements 7.7, 8.2, 8.4, 8.5, 10.4, 16.6**

- [x] 5. Follow system and order models
  - [x] 5.1 Set up follows pivot relationship
    - Configure belongsToMany relationship on User model for follows
    - Configure belongsToMany relationship on CreatorShop model for followers
    - _Requirements: 13.1, 13.2_

  - [x] 5.2 Create Order model
    - Set up UUID primary key
    - Define fillable fields: user_id, product_id, bid_id, amount, stripe_payment_id, status, payment_deadline
    - Cast status to OrderStatus enum
    - Add relationships: user (belongsTo), product (belongsTo), bid (belongsTo)
    - _Requirements: 11.3_

  - [x] 5.3 Create NotificationLog model
    - Set up UUID primary key
    - Define fillable fields: user_id, type, subject, sent_at
    - Add relationship: user (belongsTo)
    - _Requirements: 15.4_

- [x] 6. Authentication and role-based middleware
  - [x] 6.1 Create RoleMiddleware
    - Implement handle method to check user role against required role
    - Return 403 Forbidden if role doesn't match
    - Register middleware in bootstrap/app.php or Kernel
    - _Requirements: 1.4, 1.5_

  - [ ]* 6.2 Write property test for role-based route protection
    - **Property 1: Role-Based Route Protection**
    - **Validates: Requirements 1.4, 14.5**

  - [x] 6.3 Create EnsureCreatorOnboarded middleware
    - Check if creator user has is_onboarded = true
    - Redirect to onboarding route if not onboarded
    - _Requirements: 3.2, 3.3_

  - [ ]* 6.4 Write property test for non-onboarded creator access restriction
    - **Property 4: Non-Onboarded Creator Access Restriction**
    - **Validates: Requirements 3.2**

- [x] 7. Authorization gates
  - [x] 7.1 Define authorization gates in AppServiceProvider
    - Define 'view-bid-amount' gate (owner or admin only)
    - Define 'place-bid' gate (buyer role, active auction, not ended)
    - Define 'manage-creator-shop' gate (shop owner or admin)
    - Define 'admin-dashboard' gate (admin role only)
    - _Requirements: 8.1, 8.2, 14.5_

  - [ ]* 7.2 Write property test for bid authorization
    - **Property 17: Sealed Bid Privacy** (authorization layer)
    - **Validates: Requirements 8.1, 8.2**


- [x] 8. Admin creator management
  - [x] 8.1 Create Admin/CreatorController
    - Implement index method to list all creators
    - Implement store method to create creator accounts with secure password generation
    - Apply 'auth' and 'role:admin' middleware
    - _Requirements: 2.1, 2.2_

  - [ ]* 8.2 Write property test for secure password generation
    - **Property 2: Secure Password Generation**
    - **Validates: Requirements 2.2**

  - [x] 8.3 Create SendCreatorInviteEmail job
    - Implement queued job to send invite email with credentials
    - Use Laravel Mail with Mailtrap for dev, Resend/Mailgun for prod
    - _Requirements: 2.3, 2.4, 2.5_

  - [ ]* 8.4 Write property test for creator invite email queuing
    - **Property 3: Creator Invite Email Queuing**
    - **Validates: Requirements 2.3**

  - [x] 8.5 Create Inertia view for admin creator management
    - Create Vue component: Pages/Admin/Creators/Index.vue
    - Display creator list with invite form
    - Show creator status (onboarded/not onboarded)
    - _Requirements: 2.1_

- [x] 9. Creator onboarding flow
  - [x] 9.1 Create CreatorOnboardingController
    - Implement show method to display onboarding form
    - Implement store method to save shop information and mark as onboarded
    - Apply 'auth' and 'role:creator' middleware
    - Validate shop_name uniqueness, bio length, image uploads
    - _Requirements: 3.1, 3.4, 4.4_

  - [ ]* 9.2 Write property test for onboarding completion state transition
    - **Property 5: Onboarding Completion State Transition**
    - **Validates: Requirements 3.5**

  - [ ]* 9.3 Write property test for shop information validation
    - **Property 8: Shop Information Validation**
    - **Validates: Requirements 4.4**

  - [x] 9.4 Create Inertia view for creator onboarding
    - Create Vue component: Pages/Creator/Onboarding.vue
    - Multi-step form for shop name, bio, profile image, banner image
    - Image upload with preview
    - _Requirements: 3.1, 3.4_

- [x] 10. Product listing and image upload
  - [x] 10.1 Create ProductController for creators
    - Implement index method to list creator's products
    - Implement create method to show product creation form
    - Implement store method to create product with validation
    - Implement update and destroy methods
    - Apply 'auth', 'role:creator', and 'creator-onboarded' middleware
    - _Requirements: 5.1, 5.4_

  - [x] 10.2 Implement image upload to S3/R2
    - Configure Laravel Storage for S3 or Cloudflare R2
    - Validate file type (jpeg, png, jpg, webp) and size (max 5MB)
    - Store ProductImage records with paths
    - _Requirements: 5.2, 5.3, 17.1, 17.2_

  - [ ]* 10.3 Write property test for file upload validation
    - **Property 9: File Upload Validation**
    - **Validates: Requirements 5.2, 16.4, 17.1, 17.2, 17.3, 17.4, 17.5**

  - [ ]* 10.4 Write property test for auction time constraint
    - **Property 10: Auction Time Constraint**
    - **Validates: Requirements 5.6**

  - [x] 10.5 Create Inertia views for product management
    - Create Vue component: Pages/Creator/Products/Index.vue (product list)
    - Create Vue component: Pages/Creator/Products/Create.vue (product form)
    - Create Vue component: Pages/Creator/Products/Edit.vue (edit form)
    - Multi-image upload with drag-and-drop
    - Date/time pickers for auction start and end
    - _Requirements: 5.1, 5.4_

- [x] 11. Public marketplace with filters
  - [x] 11.1 Create MarketplaceController
    - Implement index method for Open Market Feed with filters (category, price range, end time)
    - Implement forYou method for personalized feed from followed creators
    - Load products with creator.shop and images relationships
    - Apply query scopes for active auctions
    - Paginate results (20 per page)
    - _Requirements: 6.1, 6.2, 13.3, 13.4_

  - [ ]* 11.2 Write property test for marketplace filter accuracy
    - **Property 11: Marketplace Filter Accuracy**
    - **Validates: Requirements 6.2**

  - [ ]* 11.3 Write property test for reserve price privacy during active auction
    - **Property 12: Reserve Price Privacy During Active Auction**
    - **Validates: Requirements 6.5**

  - [ ]* 11.4 Write property test for personalized feed filtering
    - **Property 29: Personalized Feed Filtering**
    - **Validates: Requirements 13.3**

  - [x] 11.5 Create Inertia views for marketplace
    - Create Vue component: Pages/Marketplace/Index.vue (open market)
    - Create Vue component: Pages/Marketplace/ForYou.vue (personalized feed)
    - Display product cards with images, title, creator info
    - Implement filter UI (category dropdown, price range slider, date picker)
    - Add countdown timers for auctions
    - Hide reserve price from buyers
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_


- [x] 12. Product detail page and bid submission
  - [x] 12.1 Add show method to MarketplaceController
    - Load product with creator.shop, images, and user's bid (if exists)
    - Use gate to conditionally include bid amount (only for owner/admin)
    - Return only rank for unauthorized users
    - _Requirements: 6.4, 8.3, 8.4_

  - [x] 12.2 Create BidController
    - Implement store method to create or update bid
    - Apply 'auth' and 'role:buyer' middleware
    - Use 'place-bid' gate authorization
    - Validate bid amount >= reserve_price
    - Apply rate limiting to prevent spam
    - Return rank position after bid submission
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 16.3_

  - [ ]* 12.3 Write property test for bid update on resubmission
    - **Property 14: Bid Update on Resubmission**
    - **Validates: Requirements 7.3**

  - [ ]* 12.4 Write property test for bid amount validation
    - **Property 15: Bid Amount Validation**
    - **Validates: Requirements 7.5**

  - [ ]* 12.5 Write property test for post-auction bid prevention
    - **Property 16: Post-Auction Bid Prevention**
    - **Validates: Requirements 7.6**

  - [x] 12.6 Create Inertia view for product detail
    - Create Vue component: Pages/Marketplace/Show.vue
    - Display product images in gallery/carousel
    - Show product description, creator info, auction countdown
    - Bid submission form with amount input
    - Display user's current rank (not amount to others)
    - Show "Auction Ended" state when applicable
    - _Requirements: 6.4, 7.1, 8.3_

- [x] 13. Checkpoint - Core functionality validation
  - Ensure all tests pass, ask the user if questions arise.

- [x] 14. Automated auction closing system
  - [x] 14.1 Create CloseAuctionsCommand
    - Implement handle method to query products with scopeNeedsClosure
    - Dispatch CloseAuctionJob for each ended auction
    - Log number of jobs dispatched
    - _Requirements: 9.1, 9.2_

  - [x] 14.2 Create CloseAuctionJob
    - Implement handle method with database transaction
    - Determine winning bid using getWinningBid()
    - Check if winning bid meets reserve price
    - Update product status to 'sold' or 'unsold'
    - Set winning_bid_id if sold
    - Dispatch notification jobs for winner and creator
    - _Requirements: 9.3, 9.4, 9.5, 9.6_

  - [ ]* 14.3 Write property test for automated auction closing
    - **Property 18: Automated Auction Closing**
    - **Validates: Requirements 9.2**

  - [ ]* 14.4 Write property test for winner determination
    - **Property 19: Winner Determination**
    - **Validates: Requirements 9.4**

  - [ ]* 14.5 Write property test for unsold auction handling
    - **Property 20: Unsold Auction Handling**
    - **Validates: Requirements 9.5**

  - [ ]* 14.6 Write property test for auction close notifications
    - **Property 21: Auction Close Notifications**
    - **Validates: Requirements 9.6, 15.1**

  - [x] 14.7 Register scheduled task
    - Add Schedule::command('auctions:close')->everyMinute() in routes/console.php or Kernel
    - _Requirements: 9.1_

- [x] 15. Post-auction leaderboard
  - [x] 15.1 Add leaderboard method to ProductController
    - Load all bids for closed auction
    - Calculate ranks for all bidders
    - Use gate to conditionally show amounts (admin only)
    - Return rank-only data for buyers
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

  - [ ]* 15.2 Write property test for leaderboard rank generation
    - **Property 22: Leaderboard Rank Generation**
    - **Validates: Requirements 10.1, 10.3**

  - [ ]* 15.3 Write property test for admin bid visibility
    - **Property 30: Admin Bid Visibility**
    - **Validates: Requirements 14.2**

  - [x] 15.4 Create Inertia view for leaderboard
    - Create Vue component: Pages/Products/Leaderboard.vue
    - Display ranks without amounts for buyers
    - Highlight winner
    - Show amounts only for admin users
    - _Requirements: 10.1, 10.3, 10.4_

- [x] 16. Winner payment flow with Stripe
  - [x] 16.1 Install and configure Laravel Cashier
    - Install laravel/cashier package
    - Run cashier migrations
    - Configure Stripe keys in .env
    - Add Billable trait to User model
    - _Requirements: 11.2_

  - [x] 16.2 Create PaymentController
    - Implement show method to display payment form for winner
    - Check payment deadline (48 hours from auction close)
    - Implement store method to process payment via Cashier
    - Dispatch ProcessPaymentJob
    - Apply 'auth' and 'role:buyer' middleware
    - _Requirements: 11.1, 11.2_

  - [ ]* 16.3 Write property test for payment link generation with deadline
    - **Property 23: Payment Link Generation with Deadline**
    - **Validates: Requirements 11.1**

  - [x] 16.3 Create ProcessPaymentJob
    - Charge user via Cashier: $user->charge($amount, $paymentMethodId)
    - Create Order record with stripe_payment_id
    - Transfer funds to creator's Stripe Connect account
    - Dispatch payment confirmation emails
    - _Requirements: 11.3, 11.4, 11.6_

  - [ ]* 16.4 Write property test for order creation on payment completion
    - **Property 24: Order Creation on Payment Completion**
    - **Validates: Requirements 11.3**

  - [ ]* 16.5 Write property test for payment confirmation notifications
    - **Property 25: Payment Confirmation Notifications**
    - **Validates: Requirements 11.4, 15.2**

  - [x] 16.6 Create order expiration command
    - Create ExpireOrdersCommand to mark unpaid orders as expired after 48 hours
    - Schedule to run hourly
    - _Requirements: 11.5_

  - [ ]* 16.7 Write property test for order expiration
    - **Property 26: Order Expiration**
    - **Validates: Requirements 11.5**

  - [ ]* 16.8 Write property test for fund transfer to creator
    - **Property 27: Fund Transfer to Creator**
    - **Validates: Requirements 11.6**

  - [x] 16.9 Create Inertia view for payment
    - Create Vue component: Pages/Payment/Show.vue
    - Display order summary with product details
    - Integrate Stripe Elements for card input
    - Show payment deadline countdown
    - Handle payment success/failure states
    - _Requirements: 11.1, 11.2_


- [x] 17. Creator public shop pages
  - [x] 17.1 Create CreatorShopController
    - Implement show method to display public shop page
    - Load creator with shop, active products, and follower count
    - Never load CreatorPrivateInfo on this route
    - _Requirements: 12.1, 12.2, 12.3, 12.5_

  - [ ]* 17.2 Write property test for follower count accuracy
    - **Property 28: Follower Count Accuracy**
    - **Validates: Requirements 12.4**

  - [x] 17.3 Create Inertia view for creator shop
    - Create Vue component: Pages/CreatorShop/Show.vue
    - Display shop banner, profile image, bio
    - Show follower count and follow/unfollow button
    - List active products in grid layout
    - _Requirements: 12.1, 12.2, 12.3, 12.4_

- [x] 18. Follow system implementation
  - [x] 18.1 Create FollowController
    - Implement store method to create follow relationship
    - Implement destroy method to remove follow relationship
    - Apply 'auth' middleware
    - Dispatch notification to creator when followed
    - _Requirements: 13.1, 13.2_

  - [x] 18.2 Update MarketplaceController forYou method
    - Query products from followed creators only
    - Show empty state message if not following anyone
    - _Requirements: 13.3, 13.4, 13.5_

  - [x] 18.3 Create SendNewProductNotification job
    - Queue notification emails to all followers when creator lists new product
    - _Requirements: 15.3_

  - [ ]* 18.4 Write property test for new product follower notifications
    - **Property 32: New Product Follower Notifications**
    - **Validates: Requirements 15.3**

- [x] 19. Admin dashboard with full visibility
  - [x] 19.1 Create Admin/DashboardController
    - Implement index method with auction statistics
    - Load all auctions with bid counts and highest bids
    - Apply 'auth' and 'role:admin' middleware
    - _Requirements: 14.1, 14.3_

  - [x] 19.2 Create Admin/BidController
    - Implement index method to view all bids for any auction
    - Show all bid amounts (admin has full visibility)
    - _Requirements: 14.2_

  - [ ]* 19.3 Write property test for auction statistics accuracy
    - **Property 31: Auction Statistics Accuracy**
    - **Validates: Requirements 14.3**

  - [x] 19.4 Create Admin/CreatorPrivateInfoController
    - Implement show method to view creator payout information
    - Apply 'admin-dashboard' gate
    - _Requirements: 14.4_

  - [x] 19.5 Create Inertia views for admin dashboard
    - Create Vue component: Pages/Admin/Dashboard.vue (overview)
    - Create Vue component: Pages/Admin/Auctions/Show.vue (auction details with all bids)
    - Create Vue component: Pages/Admin/Creators/Show.vue (creator private info)
    - Display statistics, charts, and full bid visibility
    - _Requirements: 14.1, 14.2, 14.3, 14.4_

- [x] 20. Notification system implementation
  - [x] 20.1 Create notification job classes
    - Create SendAuctionWonEmail job
    - Create SendAuctionSoldEmail job
    - Create SendPaymentConfirmationEmail job
    - Create SendSaleConfirmationEmail job
    - All jobs should implement ShouldQueue
    - _Requirements: 15.1, 15.2_

  - [x] 20.2 Create notification mailable classes
    - Create AuctionWonMail mailable
    - Create AuctionSoldMail mailable
    - Create PaymentConfirmationMail mailable
    - Create SaleConfirmationMail mailable
    - _Requirements: 15.1, 15.2_

  - [x] 20.3 Implement notification logging
    - Add NotificationLog::create() calls in all notification jobs
    - Log user_id, type, subject, and sent_at timestamp
    - _Requirements: 15.4, 15.5_

  - [ ]* 20.4 Write property test for notification logging
    - **Property 33: Notification Logging**
    - **Validates: Requirements 15.4**

  - [x] 20.5 Configure queue and email settings
    - Configure Redis queue driver in config/queue.php
    - Set up Mailtrap for development in .env
    - Document production email service setup (Resend/Mailgun)
    - _Requirements: 2.4, 2.5, 15.5_

- [x] 21. Checkpoint - Integration validation
  - Ensure all tests pass, ask the user if questions arise.

- [x] 22. Security hardening and validation
  - [x] 22.1 Implement rate limiting
    - Add rate limiting to bid submission endpoint (10 requests per minute)
    - Add rate limiting to API routes (60 requests per minute)
    - _Requirements: 7.4, 16.3_

  - [x] 22.2 Add HTTPS enforcement for production
    - Configure TrustProxies middleware
    - Add HTTPS redirect in production environment
    - _Requirements: 16.7_

  - [x] 22.3 Implement file upload security
    - Add MIME type validation for all image uploads
    - Add file size limits (5MB for products, 2MB for profiles)
    - Implement malicious file scanning (optional: ClamAV integration)
    - _Requirements: 17.1, 17.2, 17.3, 17.4, 17.5_

  - [x] 22.4 Review and test all authorization gates
    - Test 'view-bid-amount' gate with various user roles
    - Test 'place-bid' gate with expired auctions
    - Test 'manage-creator-shop' gate with unauthorized users
    - Test 'admin-dashboard' gate with non-admin users
    - _Requirements: 8.1, 8.2, 8.5, 16.5, 16.6_

- [x] 23. Queue job configuration and error handling
  - [x] 23.1 Configure queue job retry logic
    - Set max attempts to 3 for all jobs
    - Implement exponential backoff strategy
    - Configure failed_jobs table
    - _Requirements: 19.5_

  - [ ]* 23.2 Write property test for job retry logic
    - **Property 34: Job Retry Logic**
    - **Validates: Requirements 19.5**

  - [x] 23.3 Implement job failure handling
    - Add failed() method to critical jobs (CloseAuctionJob, ProcessPaymentJob)
    - Log failures with context
    - Send admin alerts for critical job failures
    - _Requirements: 19.1, 19.2, 19.3, 19.4_


- [x] 24. Data serialization and parsing (optional enhancement)
  - [x] 24.1 Create auction data export functionality
    - Implement JSON serialization for Auction objects
    - Add export endpoint in admin dashboard
    - _Requirements: 20.1_

  - [x] 24.2 Create auction data import functionality
    - Implement JSON parser for Auction objects
    - Validate JSON structure and data types
    - Handle parsing errors gracefully
    - _Requirements: 20.2, 20.3_

  - [ ]* 24.3 Write property test for serialization round-trip
    - **Property 35: Auction Data Serialization Round-Trip**
    - **Validates: Requirements 20.1, 20.2, 20.4, 20.5**

  - [ ]* 24.4 Write property test for invalid JSON error handling
    - **Property 36: Invalid JSON Error Handling**
    - **Validates: Requirements 20.3**

- [x] 25. Frontend UI polish and accessibility
  - [x] 25.1 Implement responsive design
    - Ensure all pages work on mobile, tablet, and desktop
    - Use Tailwind CSS responsive utilities
    - Test on various screen sizes
    - _Requirements: General UX_

  - [x] 25.2 Add loading states and error handling
    - Show loading spinners during async operations
    - Display user-friendly error messages
    - Implement toast notifications for success/error feedback
    - _Requirements: General UX_

  - [x] 25.3 Implement accessibility features
    - Add ARIA labels to interactive elements
    - Ensure keyboard navigation works
    - Test with screen readers
    - Add focus indicators
    - _Requirements: General UX_

  - [x] 25.4 Add countdown timers
    - Implement real-time countdown for auction end times
    - Show "Auction Ended" when time expires
    - Update UI dynamically without page refresh
    - _Requirements: 6.3_

- [x] 26. Database seeders and factories
  - [x] 26.1 Create model factories
    - Create UserFactory with role variations
    - Create CreatorShopFactory
    - Create ProductFactory with realistic auction times
    - Create BidFactory
    - Create OrderFactory
    - _Requirements: Testing infrastructure_

  - [x] 26.2 Create database seeder
    - Seed admin user
    - Seed 10 creator users with shops
    - Seed 20 buyer users
    - Seed 30 products with various statuses
    - Seed 100 bids across products
    - Seed follow relationships
    - _Requirements: Testing infrastructure_

- [x] 27. Documentation and deployment preparation
  - [x] 27.1 Create .env.example updates
    - Document all required environment variables
    - Add Stripe keys, S3 credentials, email service config
    - Add Redis and queue configuration
    - _Requirements: Deployment_

  - [x] 27.2 Write deployment documentation
    - Document Laravel Scheduler setup (cron job)
    - Document queue worker setup (supervisor)
    - Document S3/R2 bucket configuration
    - Document Stripe Connect setup for creators
    - _Requirements: Deployment_

  - [x] 27.3 Create API documentation
    - Document all public routes
    - Document authentication requirements
    - Document request/response formats
    - _Requirements: Developer experience_

- [x] 28. Final checkpoint and integration testing
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional property-based tests and can be skipped for faster MVP delivery
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation at key milestones
- Property tests validate universal correctness properties from the design document
- Unit tests (not marked with `*`) validate specific examples and edge cases
- The implementation follows a bottom-up approach: database → models → controllers → jobs → frontend
- All sensitive operations (bid privacy, payment processing, auction closing) have corresponding property tests
- Queue jobs should be tested with Queue::fake() in unit tests
- External services (Stripe, S3, Email) should be mocked in tests using Laravel's testing utilities

## Testing Strategy

This implementation plan includes both unit tests and property-based tests:

- **Unit Tests**: Test specific examples, edge cases, and integration points
- **Property-Based Tests**: Test universal properties that hold for all inputs (marked with `*`)
- **Coverage Goal**: Minimum 80% line coverage, 100% property coverage
- **Testing Tools**: PHPUnit, PestPHP, Laravel testing utilities (Queue::fake(), Mail::fake(), Storage::fake())

## Execution Instructions

To begin implementation:

1. Open this tasks.md file in Kiro
2. Click "Start task" next to any task item to begin
3. Kiro will guide you through each step with context from requirements and design documents
4. Optional tasks (marked with `*`) can be skipped if you want to move faster
5. Checkpoints provide natural break points to validate progress

## Dependencies

- Laravel 11 (PHP 8.3)
- MySQL 8.0
- Redis (for queues)
- Node.js and npm (for frontend build)
- Composer (for PHP dependencies)
- S3 or Cloudflare R2 account (for file storage)
- Stripe account (for payment processing)
- Email service account (Mailtrap for dev, Resend/Mailgun for prod)
