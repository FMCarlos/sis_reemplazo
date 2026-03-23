<?php

namespace App\Enums;

enum FormSubmissionStatus: string
{
    case DRAFT = 'DRAFT';
    case SUBMITTED = 'SUBMITTED';
    case CANCELLED = 'CANCELLED';
}
