<?php

namespace App\Listeners;

use App\Jobs\ResizeUploadedImage;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class ResizeImageAfterUpload
{
    public function __invoke(MediaHasBeenAddedEvent $event): void
    {
        ResizeUploadedImage::dispatch($event->media);
    }
}
