# Service Booking API

Production-Quality Service Booking RESTful API built with Laravel 12. This API serves a multi-user platform for managing service provider availability, enabling real-time booking by customers, and providing administrators with powerful reporting tools.

## Features

- **Multi-role Authentication** (Customer, Provider, Admin) with Laravel Sanctum
- **Service Management** - Providers can create and manage their services
- **Availability System** - Recurring weekly schedules with custom overrides
- **Real-time Booking** - Conflict detection and slot validation
- **Admin Reporting** - Comprehensive analytics and CSV exports
- **Rate Limiting** - Protection against abuse
- **Event-driven Notifications** - Queued jobs for async processing
- **Production Ready** - Comprehensive testing, error handling, and documentation

## Quick Start

### Installation

```bash
# Clone and install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Set up database (MySQL recommended)
# Configure database credentials in .env file
php artisan migrate

# Seed with comprehensive test data
php artisan db:seed

# Generate API documentation
php artisan l5-swagger:generate
```

### API Documentation

**Interactive Swagger UI:** Visit `/api/documentation` when the application is running

**Quick Links:**
- Full API Documentation: `http://localhost:8000/api/documentation`
- API JSON Schema: `http://localhost:8000/api/docs.json`

### Test Data & Authentication

**Pre-loaded Test Accounts** (password: `password123`):
- **Admin**: `admin@booking.com` - Access all reports and admin features
- **Provider**: `test.provider@example.com` - Manage services and availability  
- **Customer**: `test.customer@example.com` - Create and manage bookings

**Quick Authentication Test**:
```bash
# Login as test customer
POST /api/v1/login
{
  "email": "test.customer@example.com",
  "password": "password123"
}

# Use token in subsequent requests
Authorization: Bearer {your-token-here}
```

**Database includes**:
- 13 users (1 admin, 6 providers, 6 customers)
- 10 service categories (Beauty, Health, Fitness, etc.)
- 20-30 realistic services with pricing
- Comprehensive availability schedules
- 70+ bookings (past, current, future) for testing

See [TESTING_DATA.md](TESTING_DATA.md) for detailed testing scenarios.

## API Endpoints Overview

### Authentication
- `POST /api/v1/register` - Register new user
- `POST /api/v1/login` - Authenticate and get token
- `POST /api/v1/logout` - Invalidate token

### Services (Provider Only)
- `GET /api/v1/provider/services` - List provider's services
- `POST /api/v1/provider/services` - Create new service
- `PUT /api/v1/provider/services/{id}` - Update service
- `DELETE /api/v1/provider/services/{id}` - Delete service

### Availability (Provider Only)
- `GET /api/v1/provider/availability` - Get recurring schedule
- `POST /api/v1/provider/availability` - Set weekly availability

### Public Service Browsing
- `GET /api/v1/services` - Browse published services (filterable)
- `GET /api/v1/services/{id}/availability` - Get next 7 days of available slots

### Bookings
- `GET /api/v1/bookings` - List user's bookings
- `POST /api/v1/bookings` - Create booking (customers only, rate limited)
- `POST /api/v1/bookings/{id}/confirm` - Confirm booking (providers only)
- `POST /api/v1/bookings/{id}/cancel` - Cancel booking

### Admin Reports
- `GET /api/v1/admin/reports/bookings-summary` - Booking statistics
- `GET /api/v1/admin/reports/bookings-summary/export` - Export as CSV
- `GET /api/v1/admin/reports/peak-hours` - Peak booking times

## Architecture

### Clean Architecture Pattern
- **Controllers** - Handle HTTP requests, validation, responses
- **Services** - Business logic and application rules  
- **Repositories** - Data access layer with contracts
- **Models** - Eloquent models with relationships
- **Policies** - Authorization rules
- **Resources** - API response transformation
- **Form Requests** - Input validation and authorization

### Key Components
- **Events/Listeners** - `BookingCreated` event triggers notifications
- **Jobs** - Queued notification processing
- **Rate Limiting** - 10 requests/minute on booking creation
- **Exception Handling** - Standardized API error responses
- **Scheduled Commands** - Daily cleanup of expired bookings

## Database Schema

### Core Tables
- `users` - Multi-role users (admin, provider, customer)
- `categories` - Service categories
- `services` - Provider services with pricing
- `availabilities` - Recurring weekly schedules
- `availability_overrides` - One-time schedule changes
- `bookings` - Service bookings with status tracking

### Features
- **ULID Primary Keys** - Sortable, secure identifiers
- **Soft Deletes** - Booking history preservation
- **Proper Relationships** - Foreign key constraints
- **Timezone Support** - Provider timezone handling

## Development

### Running Tests
```bash
php artisan test
```

### Testing with Real Data
```bash
# Refresh database with fresh test data
php artisan migrate:fresh --seed

# Run individual seeders
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=ServiceSeeder

# Test booking cleanup with seeded data
php artisan bookings:cleanup
```

### Scheduled Commands
```bash
# Run booking cleanup manually
php artisan bookings:cleanup

# View scheduled tasks
php artisan schedule:list
```

### Queue Processing
```bash
# Process queued notification jobs
php artisan queue:work
```

## Production Deployment

1. Configure production database
2. Set up queue workers
3. Configure task scheduler
4. Enable error logging
5. Set up monitoring

For detailed deployment instructions, see the Laravel 12 documentation.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).