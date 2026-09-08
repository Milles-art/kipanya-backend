<?php

namespace App\Enums\Content;

enum ContentStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';
}
