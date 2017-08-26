<?php
namespace ngyuki\DbImport;

/**
 * Excel などで空文字列と NULL が区別できないときの値
 *
 * テーブルへの列定義を元にどちらが妥当か判断する
 */
class EmptyValue
{
    public static function val()
    {
        static $val;
        if (isset($val) === false) {
            $val = new self;
        }
        return $val;
    }
}
