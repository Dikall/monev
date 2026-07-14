<?php

declare(strict_types=1);

/**
 * Vercel PHP Function entry point.
 *
 * Static files are handled by Vercel routes; all dynamic requests reach
 * Laravel through its standard public front controller.
 */
require __DIR__.'/../public/index.php';