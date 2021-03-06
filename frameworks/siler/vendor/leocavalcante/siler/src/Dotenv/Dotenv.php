<?php

declare(strict_types=1);

/*
 * Helpers functions to work with vlucas/phpdotenv.
 */

namespace Siler\Dotenv;

use Dotenv\Dotenv;
use function Siler\array_get;

/**
 * Load the .env file contents into the environment.
 *
 * @param string $path Directory name of the .env file location
 *
 * @return array
 */
function init(string $path): array
{
    $dotenv = Dotenv::create($path);

    return $dotenv->load();
}

/**
 * Get an environment value or fallback to the given default.
 *
 * @param string|null $key
 * @param mixed $default A default when the key do not exists
 *
 * @return mixed
 */
function env(?string $key = null, $default = null)
{
    return array_get($_SERVER, $key, $default);
}
