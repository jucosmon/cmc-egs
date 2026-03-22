<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Schema;

trait SoftArchive
{
    public function archive(): bool
    {
        if (Schema::hasColumn($this->getTable(), 'is_active')) {
            $this->setAttribute('is_active', false);
        }

        if (Schema::hasColumn($this->getTable(), 'archived_at')) {
            $this->setAttribute('archived_at', now());
        }

        return $this->save();
    }
}
