<?php

namespace Spatie\SiteSearch\Drivers;

interface Driver
{
    public function update(array $properties): self;

    public function delete(): self;

    public function create(): self;
}
