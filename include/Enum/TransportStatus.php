<?php

declare(strict_types=1);

namespace LiquidRazor\TransportContracts\Enum;

enum TransportStatus: string
{
    case Success = 'success';
    case Failed = 'failed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case TimedOut = 'timed_out';
    case Deferred = 'deferred';
    case NoOutput = 'no_output';
}
