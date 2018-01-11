<?php
namespace ngyuki\DbImport\DataSet;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use ngyuki\DbImport\DataRow;
use ngyuki\DbImport\Exception\IOException;
use ngyuki\DbImport\Importer;
use \PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use \PhpOffice\PhpSpreadsheet\Shared\Date;

class ExcelDataSet implements DataSetInterface
{
    use DataSetUtil;

    private $file;

    public function __construct($file)
    {
        $this->file = realpath($file);

        if ($this->file === false) {
            throw new IOException("File not found ... $file");
        }

        if (is_readable($this->file) === false) {
            throw new IOException("File not readable ... $this->file");
        }
    }

    public function getData(Importer $importer)
    {
        if (preg_match('/\~\$/', basename($this->file))) {
            // excel の一時ファイルは除外
            return [];
        }

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setIncludeCharts(false);
        $reader->setReadDataOnly(true);
        $excel = $reader->load($this->file);

        $tables = [];

        foreach ($excel->getSheetNames() as $name) {
            $arr = $excel->getSheetByName($name)->toArray();

            list ($name, $rotate) = self::fixTableName($name);

            if (strlen($name) === 0) {
                continue;
            }

            if ($rotate) {
                $arr = array_map(null, ...$arr);
            }

            $columns = $this->parseColumns($importer->getConnection(), $name, array_shift($arr));

            foreach ($arr as $line => $row) {
                $assoc = $this->applyColumns($columns, $row);

                // すべての列が空の行はスキップ
                if (!array_filter($assoc, function ($v) {
                    return strlen($v);
                })) {
                    continue;
                }

                $tables[$name][] = new DataRow($assoc, sprintf("%s [%s] (%d)", $this->file, $name, $line + 1));
            }
        }

        return $tables;
    }

    private function parseColumns(Connection $conn, $table, array $names)
    {
        $columns = $conn->getSchemaManager()->listTableColumns($table);
        $results = [];

        foreach ($names as $i => $name) {
            $modifiers = explode('|', $name);
            $name = trim(array_shift($modifiers));
            if (strlen($name) === 0) {
                continue;
            }
            $modifiers = array_merge($modifiers, $this->getModifier($columns, $name));
            $results[$i] = [$name, $modifiers];
        }

        return $results;
    }

    private function applyColumns($columns, $row)
    {
        $assoc = [];

        foreach ($columns as $i => list ($column, $modifiers)) {
            $val = $row[$i] ?? null;
            foreach ($modifiers as $modifier) {
                $method = 'modifier_' . $modifier;
                if (method_exists($this, $method) === false) {
                    throw new \LogicException("Excel modified $modifier not found");
                }
                $val = $this->$method($val);
            }
            $assoc[$column] = $val;
        }

        return $assoc;
    }

    /**
     * @param Column[] $columns
     * @param string $name
     * @return array
     */
    private function getModifier($columns, $name)
    {
        $modifiers = [];

        if (isset($columns[$name]) === false) {
            return $modifiers;
        }

        $column = $columns[$name];

        // NOT NULL かつ自動採番ではないなら NOT NULL 修飾子を適用
        if ($column->getNotnull() && !$column->getAutoincrement()) {
            $modifiers[] = 'not_null';
        }

        $type = strtolower($column->getType()->getName());
        $method = 'modifier_' . $type;

        if (method_exists($this, $method)) {
            $modifiers[] = $type;
        }

        return $modifiers;
    }

    protected function modifier_not_null($val)
    {
        if ($val === null) {
            return '';
        }
        return $val;
    }

    protected function modifier_datetime($val)
    {
        if ($val !== null) {
            return gmdate('Y/m/d H:i:s', (int)\PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($val));
        }
        return $val;
    }

    protected function modifier_date($val)
    {
        if ($val !== null) {
            if (is_int($val) || is_float($val)) {
                return gmdate('Y/m/d H:i:s', (int)\PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($val));
            }
        }
        return $val;
    }

    protected function modifier_time($val)
    {
        if ($val !== null) {
            if (is_int($val) || is_float($val)) {
                return gmdate('H:i:s', (int)\PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($val));
            }
        }
        return $val;
    }
}
