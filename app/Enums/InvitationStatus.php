<?php
namespace App\Enums;

enum InvitationStatus: int {
    case Valid   = 0;
    case Invalid = 1;
    case Expired = 2;
};
