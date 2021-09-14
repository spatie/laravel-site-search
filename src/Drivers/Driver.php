<?php

namespace Spatie\SiteSearch\Drivers;

interface Driver
{
    public function update(array $properties): self;
}
