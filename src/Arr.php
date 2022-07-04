<?php

namespace DeathSatan\ArrayHelpers;

use ArrayAccess;
use Closure;
use Generator;

class Arr
{
    /**
     * 使用“点”符号将数组项设置为给定的值。
     *
     * 如果没有给方法指定键，整个数组将被替换。
     *
     * @param array $array
     * @param string|null $key
     * @param  mixed  $value
     * @return array
     */
    public static function set(array &$array, ?string $key, $value): array
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        foreach ($keys as $i => $key) {
            if (count($keys) === 1) {
                break;
            }

            unset($keys[$i]);

            //如果键在这个深度不存在，我们将创建一个空数组
            //存放下一个值，允许我们创建存放final值的数组
            //在正确的深度值。然后我们会继续挖掘这个数组。
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * 打乱给定数组并返回结果。
     *
     * @param array $array
     * @param  int|null  $seed
     * @return array
     */
    public static function shuffle(array $array, $seed = null): array
    {
        if (is_null($seed)) {
            shuffle($array);
        } else {
            mt_srand($seed);
            shuffle($array);
            mt_srand();
        }

        return $array;
    }

    /**
     * 使用给定的回调函数过滤数组。
     *
     * @param array $array
     * @param  callable  $callback
     * @return array
     */
    public static function where(array $array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * 将数组转换为http查询字符串。
     *
     * @param array $array
     * @return string
     */
    public static function query(array $array): string
    {
        return http_build_query($array, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * 确定给定键是否存在于所提供的数组中。
     *
     * @param ArrayAccess|array  $array
     * @param  string|int  $key
     * @return bool
     */
    public static function exists($array, $key): bool
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * 使用“点”表示法从给定数组中删除一个或多个数组项。
     *
     * @param array $array
     * @param  array|string  $keys
     * @return void
     */
    public static function forget(array &$array, $keys):void
    {
        $original = &$array;

        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (static::exists($array, $key)) {
                unset($array[$key]);

                continue;
            }

            $parts = explode('.', $key);

            // clean up before each pass
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

    /**
     * 确定给定的值是否为数组可访问。
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function accessible($value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * 使用“点”表示法从数组中获取项。
     *
     * @param ArrayAccess|array  $array
     * @param  string|int|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if (! static::accessible($array)) {
            return $default;
        }

        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (strpos($key, '.') === false) {
            return $array[$key] ?? $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * 将一个数组分成两个数组。一个键名，另一个键值。
     *
     * @param array $array
     * @return array
     */
    public static function divide(array $array): array
    {
        return [array_keys($array), array_values($array)];
    }

    /**
     * 使用“点”符号检查数组中是否存在一个或多个项。
     *
     * @param ArrayAccess|array  $array
     * @param  string|array  $keys
     * @return bool
     */
    public static function has($array, $keys): bool
    {
        $keys = (array) $keys;

        if (! $array || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (static::exists($array, $key)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (static::accessible($subKeyArray) && static::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 从给定数组中获取项的子集。
     *
     * @param array $array
     * @param  array|string  $keys
     * @return array
     */
    public static function only(array $array, $keys): array
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * 将给定的值转换为json
     * @param array|ArrayAccess $array
     * @param mixed $flags
     * @return false|string
     */
    public static function toJson($array,$flags=true)
    {
        return json_encode($array,$flags);
    }

    /**
     * 将给定的字符串转换为数组或对象
     * @param string $json
     * @param bool $assoc
     * @param integer $option
     * @param integer $depth
     * @return object|array
     */
    public static function reverseJson(string $json,bool $assoc = true,int $option=JSON_UNESCAPED_UNICODE,int $depth=512)
    {
        return json_decode($json,$assoc,$depth,$option);
    }

    /**
     * 通过yield关键字和array_chunk函数分割数组来进行操作
     * @param array $array
     * @param int $size
     * @param callable|null $callable
     * @return Generator
     */
    public static function chunk(array $array,int $size,callable $callable = null): Generator
    {
        $chunk = array_chunk($array,$size);
        if (!empty($callable)){
            yield $chunk;
        }
        foreach ($chunk as $i=>$item){
            $chunk[$i] = $callable($item,$i);
        }
        yield $chunk;
    }

    /**
     * 获取二维数组下某一列值的总和
     * @param array $array
     * @param string $column
     * @return float|int
     */
    public static function column_sum(array $array,string $column)
    {
        return array_sum(array_column($array,$column));
    }

    /**
     * 对二维数组做闭包处理
     * @param array $array
     * @param Closure $closure
     * @return array
     */
    public static function each(array $array,Closure $closure): array
    {
        return array_map($closure,$array);
    }
}