<?php

function attempt(Closure $closure, mixed $default = null): mixed
{
    try {
        return $closure();
    } catch (Exception) {
        return $default;
    }
}
