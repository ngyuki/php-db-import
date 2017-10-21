<?php
namespace ngyuki\DbImport\DataSet;

use ngyuki\DbImport\DataRow;

trait DataSetUtil
{
    private static function fixTableName($table)
    {
        // . から後は除去
        list ($table) = explode('.', $table);

        if (strlen($table) === 0) {
            return false;
        }

        // 先頭が @ と ^ なら回転
        if (($table[0] === '@') || ($table[0] === '^')) {
            $table = substr($table, 1);
            return [$table, true];
        }

        return [$table, false];
    }

    private static function arrayToTables($file, $arr)
    {
        $tables = [];

        foreach ($arr as $table => $rows) {
            list ($table, $rotate) = self::fixTableName($table);

            if (strlen($table) === 0) {
                continue;
            }

            if ($rotate) {
                $cols = array_shift($rows);
                $rows = array_map(
                    function ($row) use ($cols) {
                        return array_combine($cols, $row);
                    },
                    $rows
                );
            }
            
            if ($rows instanceof \Traversable) {
                $rows = iterator_to_array($rows);
            }

            if ($file !== null) {
                $rows = array_map(function ($row) use ($file, $table) {
                    return new DataRow($row, sprintf("%s [%s]", $file, $table));
                }, $rows);
            }

            if (!isset($tables[$table])) {
                $tables[$table] = [];
            }
            $tables[$table] = array_merge($tables[$table], $rows);
        }

        return $tables;
    }
}
