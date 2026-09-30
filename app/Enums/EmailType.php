<?php

namespace App\Enums;

enum EmailType: string
{
    case AdminNotification = 'admin_notification';
    case CustomerAcknowledgement = 'customer_acknowledgement';

    public function label(): string
    {
        return match ($this) {
            self::AdminNotification => 'Admin notification',
            self::CustomerAcknowledgement => 'Customer acknowledgement',
        };
    }
}
