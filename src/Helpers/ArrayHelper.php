<?php

namespace PlacetoPay\Kount\Helpers;

class ArrayHelper
{
    public static function get(array $array, string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);

        foreach ($keys as $key) {
            if (is_array($array) && array_key_exists($key, $array)) {
                $array = $array[$key];
            } else {
                return $default;
            }
        }

        return $array;
    }

    public static function filterValues(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::filterValues($value);
            }
        }

        return array_filter($array, fn ($v) => !(
            $v === null ||
            $v === '' ||
            (is_array($v) && empty($v))
        ));
    }
}
