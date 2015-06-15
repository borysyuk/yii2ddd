<?php

namespace yii2ddd\utils;

/**
 * Набір функцій-помічників для перетворення даних, обчислення crc та ін.
 *
 * @author Ihor Borysyuk
 */
final class Convert
{

    /**
     * Шаблон для пошуку при перетворенні імені з CamelCase в hyphen_case
     *
     * @var array
     *
     * @ignore
     */
    static protected $search = array(
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z'
    );

    /**
     * Шаблон для заміни при перетворенні імені з CamelCase в hyphen_case
     *
     * @var array
     *
     * @ignore
     */
    static protected $replace = array(
        '_a',
        '_b',
        '_c',
        '_d',
        '_e',
        '_f',
        '_g',
        '_h',
        '_i',
        '_j',
        '_k',
        '_l',
        '_m',
        '_n',
        '_o',
        '_p',
        '_q',
        '_r',
        '_s',
        '_t',
        '_u',
        '_v',
        '_w',
        '_x',
        '_y',
        '_z'
    );

    /**
     * Закешовані значення CRC зі знаком
     *
     * @var array
     *
     * @ignore
     */
    static protected $crc = array();

    /**
     * Перетворює ім'я з MixedCase в underscore_case. Масиви опрацьовуютсья рекурсивно
     *
     * @param string | array
     * @param string Символ, який буде використовуватись для розмежування слів
     * @return string | array
     */
    public static function mixedCaseToUnderscores($data, $underscoreChar = '_')
    {
        if (is_array($data)) {
            foreach ($data as &$item) {
                $item = static::mixedCaseToUnderscores($item);
            }
            unset($item);

            return $data;
        } else {
            $data = str_replace(static::$search, static::$replace, lcfirst($data));
            if ($underscoreChar != '_') {
                $data = str_replace('_', $underscoreChar, $data);
            }

            return $data;
        }
    }

    /**
     * Перетворює ім'я з underscore_case в MixedCase. Масиви опрацьовуютсья рекурсивно
     *
     * @param string | array
     * @param string Символ, який використовується для розмежування слів
     * @return string | array
     */
    public static function underscoresToMixedCase($data, $underscoreChar = '_')
    {
        if (is_array($data)) {
            foreach ($data as &$item) {
                $item = static::underscoresToMixedCase($item);
            }
            unset($item);

            return $data;
        } else {
            return is_numeric($data) ? $data : implode('', array_map('ucfirst', explode($underscoreChar, $data)));
        }
    }

    /**
     * Перетворює ключі масиву з underscore_case в MixedCase. Вкладені масиви опрацьовуються рекурсивно
     *
     * @param mixed
     * @param string Символ, який використовується для розмежування слів
     */
    public static function convertFieldNames($data, $underscoreChar = '_')
    {
        if (!is_array($data)) {
            return $data;
        }

        $result = array();
        foreach ($data as $key => $value) {
            $key = static::underscoresToMixedCase($key, $underscoreChar);
            $result[$key] = static::convertFieldNames($value, $underscoreChar);
        }

        return $result;
    }

    /**
     * Приводить значення змінної до певного типу. Масиви опрацьовуються рекурсивно
     *
     * @param mixed | array
     * @param string | null Тип до якого потрібно привести змінну. Якщо null - перетворення не буде
     */
    public static function cast($values, $type = null)
    {
        switch (strtolower($type)) {
            case 'int':
            case 'integer':
            case 'bool':
            case 'boolean':
            case 'array':
            case 'object':
            case 'string':
            case 'float':
                break;
            case 'str':
                $type = 'string';
                break;
            case 'double':
                $type = 'float';
                break;
            default:
                return $values;
        }

        if (is_array($values)) {
            foreach ($values as &$value) {
                if (is_array($value)) {
                    $value = static::cast($value, $type);
                } else {
                    settype($value, $type);
                }
            }
            unset($value);
        } else {
            settype($values, $type);
        }

        return $values;
    }

    /**
     * Перетворює багатовимірний масив в одновимірний
     *
     * @param mixed | array
     */
    public static function flattenArray($array)
    {
        if (!is_array($array)) {
            return array($array);
        }
        $result = array();
        foreach ($array as $value) {
            if (is_array($value)) {
                $result = array_merge($result, static::flattenArray($value));
            } else {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Об'єднує два масиви, аналогічно до функції array_merge. На відміну від array_merge,
     * перекриваються ключі як літерні, так і числові
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public static function mergeByKeys($array1, $array2)
    {
        $array1 = (array)$array1;
        $array2 = (array)$array2;
        foreach ($array2 as $key => $value) {
            $array1[$key] = $value;
        }

        return $array1;
    }

    /**
     * Вибирає унікальні значення з масиву. Всі значення масиву повинні бути типу string або integer!
     *
     * Для масиву з 10000 елементів працює майже в 10 разів швидше ніж array_unique
     *
     * @param array
     * @returns array
     */
    public static function fastArrayUnique($array)
    {
        return array_keys(array_flip($array));
    }

    /**
     * Видаляє з першого масиву значення, які зустрічаються в другому. Всі значення масивів повинні
     * бути типу string або integer!
     *
     * Для масивів по 10000 елементів працює майже в 20 разів швидше ніж array_diff
     *
     * @param array
     * @param array
     * @returns array
     */
    public static function fastArrayDiff($array1, $array2)
    {
        return array_keys(
            array_diff_key(
                array_flip($array1),
                array_flip($array2)
            )
        );
    }

    /**
     * Знаходить спільні елементи двох масивів. Всі значення масивів повинні бути типу string або integer!
     *
     * Порівняння з array_intersect не проводилось
     *
     * @param array
     * @param array
     * @returns array
     */
    public static function fastArrayIntersect($array1, $array2)
    {
        return array_keys(
            array_intersect_key(
                array_flip($array1),
                array_flip($array2)
            )
        );
    }

    /**
     * Обчислює контролюну суму CRC32 (зі знаком)
     *
     * @param mixed
     * @return integer
     */
    public static function crc($value)
    {
        $result = crc32($value);
        if ($result > 2147483647) {
            $result -= 4294967296;
        }

        return $result;
    }

    /**
     * Повертає поточну дату і час у вигляді рядка
     *
     * @param boolean Якщо true - до результату будуть додані мілісекунди
     * @return string
     */
    public static function now($mode = false)
    {
        // Функція date() не отримує мілісекунд; DateTime::format() - отримує
        $date = new DateTime('now');

        return $date->format('Y-m-d H:i:s' . ($mode ? ' u' : ''));
    }

    /**
     * Приводить змінну до масиву. Якщо змінна вже є масивом - нічого не виконується. Якщо ні - метод поверне масив,
     * єдиним елементом якого буде передана змінна
     *
     * @param mixed Змінна
     * @param string Тип, до якого привести всі елементи масиву (рекурсивно)
     */
    // TODO: По суті досить зробити (array)$value - результат буде той самий
    public static function valueAsArray($value, $type = null)
    {
        $result = is_array($value) ? $value : array($value);
        if (!is_null($type)) {
            $result = static::cast($result, $type);
        }

        return $result;
    }

    /**
     * @todo: Задокументувати цей метод
     */
    // TODO: Задокументувати цей метод (тут взагалі якийсь меджік)
    public static function safeArrayValue($array, $keys, $default = null)
    {
        $result = false;
        $keys = static::valueAsArray($keys);
        $code = '$array[ $keys[' . implode('] ][ $keys[', array_keys($keys)) . '] ]';

        eval('$result = isset(' . $code . ') ? ' . $code . ' : $default;');

        return $result;
    }

    public static function collectItems($items, $indexKey, $valueKey = null)
    {
        // TODO: Цей метод потребує рефакторингу
        if (is_null($indexKey) && is_null($valueKey) || !count($items)) {
            return $items;
        }
        $result = array();
        if (!is_null($indexKey) && count($keys = static::valueAsArray($indexKey))) {
            if (is_null(reset($keys))) {
                throw new \yii\web\UserException('First key in $indexKey can not be null', 500);
            }

            $key = (is_null(end($keys)) && !array_pop($keys)) ? '[]' : '';
            $code =
                '$result[ $item[ $keys[ ' .
                implode(
                    '] ] ][ $item[ $keys[ ',
                    array_keys($keys)
                ) .
                '] ] ]' . $key . ' = is_null( $valueKey ) ? $item : $item[ $valueKey ];';
            foreach ($items as $item) {
                eval($code);
            }
        } else {
            if (is_null($valueKey)) {
                return $items;
            }
            foreach ($items as $item) {
                $result[] = is_null($valueKey) ? $item : $item[$valueKey];
            }
        }

        return $result;
    }

    public static function getRandomString($length, $numbers = true, $specialChars = false)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $num = '0123456789';
        $spec = '[{<!@#$%^&*().,_+=-|/>}]';
        $result = '';

        while (strlen($result) < $length) {
            $result .= (rand(0, 1)) ? substr($chars, rand(0, strlen($chars) - 1), 1) : '';
            $result .= ($numbers && rand(0, 1)) ? substr($num, rand(0, strlen($num) - 1), 1) : '';
            $result .= ($specialChars && rand(0, 1)) ? substr($spec, rand(0, strlen($spec) - 1), 1) : '';
        }

        return substr($result, 0, $length);
    }
}
