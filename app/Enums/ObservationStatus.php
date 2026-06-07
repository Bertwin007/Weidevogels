<?php

namespace App\Enums;

enum ObservationStatus: string
{
    case PendingAnnotation = 'pending_annotation';
    case Published = 'published';
    case NotPublishable = 'not_publishable';
    case Unpublished = 'unpublished';
}
