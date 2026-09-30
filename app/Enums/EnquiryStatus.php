<?php

namespace App\Enums;

enum EnquiryStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Closed = 'closed';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
