<?php

namespace App\Enums\Plan;

enum BillingPeriod: string
{
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
    case DAILY = 'daily';
}
