<?php

namespace App\Enums;

enum ProductStockTransferType: string
{
    case PURCHASE = 'purchase';

    // ── Stock IN ─────────────────────────────────────────────
    case OPENING        = 'opening';        // first time stock setup for branch
    case STOCK_IN       = 'stock_in';       // direct stock added to branch manually
    case TRANSFER_IN    = 'transfer_in';    // received from another branch

    // ── Stock OUT ────────────────────────────────────────────
    case TRANSFER_OUT   = 'transfer_out';   // sent to another branch
    case SALE           = 'sale';           // sold to customer (order deduction)
    case DAMAGE         = 'damage';         // damaged / write-off

    // ── Neutral ──────────────────────────────────────────────
    case ADJUSTMENT     = 'adjustment';     // manual correction (count mismatch)

}

