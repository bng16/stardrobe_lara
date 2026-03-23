<?php

namespace App\Enums;

enum AuctionStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Ended = 'ended';
    case Sold = 'sold';
    case Unsold = 'unsold';
}
