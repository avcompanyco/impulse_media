# Payment Registry System

This payment registry system provides comprehensive tracking of all payments made through the streaming app, with support for Stripe and future payment providers.

## Features

- 📊 Complete payment tracking and analytics
- 🔄 Automatic Stripe webhook integration
- 📈 Revenue analytics with multiple time periods
- 🎯 Admin-only access controls
- 🧪 Comprehensive test coverage

## Database Schema

The `payments` table includes:
- User and plan relationships
- Payment amounts and currency
- Status tracking (pending, completed, failed, refunded, cancelled)
- Stripe-specific fields for integration
- Billing period for analytics
- Timestamps for all payment events
- Metadata for additional information

## API Endpoints

### Admin Payment Management
- `GET /admin/payments` - List all payments
- `GET /admin/payments/datatable` - Paginated payments for datatables
- `GET /admin/payments/{payment}` - View payment details
- `GET /admin/payments/revenue` - Revenue analytics

### Revenue Analytics

The revenue endpoint supports multiple query parameters:

```bash
# Monthly revenue for current year
GET /admin/payments/revenue?period=monthly

# Daily revenue for specific month
GET /admin/payments/revenue?period=daily&year=2025&month=1

# Annual revenue
GET /admin/payments/revenue?period=annually
```

#### Response Format

Monthly/Daily revenue returns:
```json
[
  { "month": "Jan", "revenue": 3500 },
  { "month": "Feb", "revenue": 4200 },
  { "month": "Mar", "revenue": 5800 }
]
```

Annual revenue returns:
```json
[
  { "year": 2024, "revenue": 45000 },
  { "year": 2025, "revenue": 52000 }
]
```

## Stripe Integration

The system automatically records payments through Stripe webhooks:

- **invoice.payment_succeeded** - Records completed payments
- **invoice.payment_failed** - Records failed payment attempts

### Webhook Events Handled

1. `checkout.session.completed` - Initial subscription creation
2. `customer.subscription.created` - Subscription setup
3. `customer.subscription.updated` - Subscription changes
4. `customer.subscription.deleted` - Subscription cancellation
5. `invoice.payment_succeeded` - **Payment recording**
6. `invoice.payment_failed` - **Failed payment recording**
7. `customer.subscription.trial_will_end` - Trial expiration

## Models and Relationships

### Payment Model
- Belongs to User and Plan
- Has status management methods
- Includes scopes for filtering
- Provides formatted amount display

### User Model
- `hasMany(Payment::class)` - All user payments

### Plan Model  
- `hasMany(Payment::class)` - All payments for the plan

## Usage Examples

### Creating Payments Programmatically

```php
use App\Models\Payment;
use App\Enums\Payment\PaymentStatus;

$payment = Payment::create([
    'user_id' => $user->id,
    'plan_id' => $plan->id,
    'amount' => 9.99,
    'currency' => 'USD',
    'status' => PaymentStatus::COMPLETED,
    'payment_method' => 'stripe',
    'billing_period' => $plan->billing_period,
    'paid_at' => now(),
]);
```

### Querying Revenue Data

```php
use App\Models\Payment;
use App\Enums\Payment\PaymentStatus;

// Get monthly revenue for current year
$monthlyRevenue = Payment::completed()
    ->whereYear('paid_at', now()->year)
    ->selectRaw('MONTH(paid_at) as month, SUM(amount) as revenue')
    ->groupBy('month')
    ->get();

// Get total revenue for a plan
$planRevenue = Payment::completed()
    ->where('plan_id', $planId)
    ->sum('amount');
```

### Payment Status Management

```php
// Mark payment as completed
$payment->markAsCompleted();

// Mark payment as failed
$payment->markAsFailed();

// Mark payment as refunded
$payment->markAsRefunded();

// Check payment status
if ($payment->isCompleted()) {
    // Handle successful payment
}
```

## Testing

The system includes comprehensive tests:

```bash
php artisan test tests/Feature/Payment/
```

Tests cover:
- Payment model functionality
- Revenue analytics accuracy
- Admin access controls
- Stripe webhook integration
- Factory and relationship testing

## Future Enhancements

The system is designed to be extensible for future payment providers:

1. **PayPal Integration** - Add PayPal-specific fields to metadata
2. **Cryptocurrency** - Add blockchain transaction tracking
3. **Bank Transfers** - Add ACH/wire transfer support
4. **Gift Cards** - Add promotional payment methods

The `payment_method` field and `metadata` JSON column support these future additions without schema changes.