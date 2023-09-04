<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class ConvertRequestKeysToSnakeCase
{
    /**
     * Handle an incoming request and change json data keys to snake_case.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get the request data as an array
        $data = $request->all();

        // Convert the keys to snake_case recursively
        $data = $this->convertKeysToSnakeCase($data);

        // Replace the request data with the converted data
        $request->replace($data);

        return $next($request);
    }

    /**
     * Convert an array's keys to snake_case recursively.
     *
     * @param  array  $array
     * @return array
     */
    protected function convertKeysToSnakeCase($array)
    {
        $newArray = [];

        foreach ($array as $key => $value) {
            // Convert the key to snake_case using Laravel's helper method
            $key = Str::snake($key);

            // If the value is an array, convert its keys as well
            if (is_array($value)) {
                $value = $this->convertKeysToSnakeCase($value);
            }

            // Add the converted key and value to the new array
            $newArray[$key] = $value;
        }

        return $newArray;
    }
}