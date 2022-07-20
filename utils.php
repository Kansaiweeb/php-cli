<?php

class Utils
{
    static function calc_factorial_by_loop(int $number): int
    {
        if ($number <= 0) {
            return 0;
        }

        $factorial = 1;
        for ($i = 1; $i <= $number; $i++) {
            $factorial = $factorial * $i;
        }

        return $factorial;
    }

    static function calc_factorial_by_recursion(int $number): int
    {

        if ($number <= 0) {
            return 0;
        } elseif ($number == 1) {
            return 1;
        }

        $factorial = $number * Utils::calc_factorial_by_recursion($number - 1);

        return $factorial;
    }

    static function sort_array(array $array): array
    {
        if (count($array) < 2) return $array;

        $left = $right = array();

        reset($array);
        $pivot_key = key($array);
        $pivot = array_shift($array);

        foreach ($array as $k => $v) {
            if ($v < $pivot)
                $left[$k] = $v;
            else
                $right[$k] = $v;
        }

        return array_merge(Utils::sort_array($left), array($pivot_key => $pivot), Utils::sort_array($right));
    }



    static function get_10_newest_titles(): array
    {
        $homepage = file('http://www.opennet.ru/opennews/opennews_1.txt');
        $titles = array();
        for ($i = 2; $i <= 40; $i += 4) {
            array_push($titles, $homepage[$i]);
        }
        return $titles;
    }
}
