<?php

namespace Mha\LaravelModelWatch\Collections;

use Illuminate\Support\Collection;

abstract class BaseWatchCollection
{
    /**
     * Return a collection of Eloquent Models to watch.
     */
    abstract public function getModels(): Collection;
}
