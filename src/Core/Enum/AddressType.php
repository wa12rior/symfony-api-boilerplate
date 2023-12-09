<?php

declare(strict_types=1);

namespace App\Core\Enum;

enum AddressType: string
{
    case DEFAULT = 'DEFAULT';

    case COMPANY = 'COMPANY';

    case DELIVERY = 'DELIVERY';

    case RETURN = 'RETURN';
}
