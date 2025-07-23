<?php

namespace App\Observers;

use App\Models\ProductTranslation;
use App\Traits\HandlesEditorUploads;

class ProductTranslationObserver
{
    use HandlesEditorUploads;

    /**
     * Handle the ProductTranslation "created" event.
     */
    public function created(ProductTranslation $productTranslation): void
    {
        //
        $this->handleEditorUploadsFromHtml($productTranslation->description);
    }

    /**
     * Handle the ProductTranslation "updating" event.
     */
    public function updating(ProductTranslation $productTranslation): void
    {
        //
        $oldDescription = $productTranslation->getOriginal('description');
        $newDescription = $productTranslation->description;

        $this->syncEditorUploadsFromHtml($oldDescription, $newDescription);
    }

    /**
     * Handle the ProductTranslation "updated" event.
     */
    public function updated(ProductTranslation $productTranslation): void
    {
        //
    }

    public function deleting(ProductTranslation $productTranslation)
    {
        $this->deleteEditorUploadsFromHtml($productTranslation->description);
    }

    /**
     * Handle the ProductTranslation "deleted" event.
     */
    public function deleted(ProductTranslation $productTranslation): void
    {
        //
    }
}
