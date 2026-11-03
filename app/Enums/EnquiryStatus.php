<?php

namespace App\Enums;

enum EnquiryStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Closed = 'closed';
    case Spam = 'spam';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
