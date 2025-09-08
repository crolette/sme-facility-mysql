<?php

namespace App\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class SiteObserver implements ShouldHandleEventsAfterCommit
{
    public function create(Site $site) {}
}
