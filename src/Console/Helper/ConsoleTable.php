<?php
/**
 * Created by PhpStorm.
 * User: eValor
 * Date: 2018/10/30
 * Time: 下午12:20
 */

namespace EasySwoole\EasySwoole\Console\Helper;

/**
 * 根据数组创建一个便于阅读的表格
 * Class ConsoleTable
 * @package EasySwoole\EasySwoole\Console\Helper
 */
class ConsoleTable
{
    protected $tableHeader = array();       // 表头字段
    protected $tableRows = array();         // 表格内容
    protected $tableColumnsWidth = array(); // 表格列宽
    protected $tableContent = '';           // 表格内容

    /**
     * 设置表头字段
     * @param array $header [ $item ]
     * @author: eValor < master@evalor.cn >
     */
    public function setTableHeader(array $header)
    {
        $this->tableHeader = $header;
    }

    /**
     * 设置表格数据
     * @param array $rows [ [$item] ]
     * @author: eValor < master@evalor.cn >
     */
    public function setTableRows(array $rows)
    {
        $this->tableRows = $rows;
    }

    /**
     * 计算最小列宽
     * @author: eValor < master@evalor.cn >
     * @return void
     */
    private function calculateColumnWidth(): void
    {
        foreach (range(0, count($this->tableHeader) - 1) as $tableColumnIndex) {
            $currentRow = array_column($this->tableRows, $tableColumnIndex);
            array_push($currentRow, $this->tableHeader[$tableColumnIndex]);
            $currentLen = 0;
            foreach ($currentRow as $item) {
                $len = mb_strlen($item);
                if ($len > $currentLen) {
                    $currentLen = $len;
                }
            }
            $this->tableColumnsWidth[$tableColumnIndex] = $currentLen + 1;
        }
    }

    /**
     * 生产表头
     * @author: eValor < master@evalor.cn >
     */
    private function buildTableHeaders()
    {
        $this->buildSeparatorLine();
        foreach ($this->tableHeader as $index => $header) {
            $this->tableContent .= '| ' . str_pad($header, $this->tableColumnsWidth[$index], ' ', STR_PAD_BOTH);
        }
        $this->tableContent .= '|' . PHP_EOL;
        $this->buildSeparatorLine();
    }

    /**
     * 生产表格行
     * @author: eValor < master@evalor.cn >
     */
    private function buildTableRows()
    {
        foreach ($this->tableRows as $tableRow) {
            if (count($tableRow) < count($this->tableHeader)) {
                for ($i = 0; $i < (count($this->tableHeader) - count($tableRow)); $i++) {
                    array_push($tableRow, ' ');
                }
            }
            foreach ($tableRow as $index => $row) {
                $this->tableContent .= '| ' . str_pad($row, $this->tableColumnsWidth[$index], ' ', STR_PAD_BOTH);
            }
            $this->tableContent .= '|' . PHP_EOL;
            $this->buildSeparatorLine();
        }
    }

    /**
     * 生产行分割线
     * @author: eValor < master@evalor.cn >
     */
    private function buildSeparatorLine()
    {
        $this->tableContent .= str_repeat('-', array_sum($this->tableColumnsWidth) + count($this->tableColumnsWidth) * 2 + 2) . PHP_EOL;
    }

    /**
     * 生产表格字符串
     * @author: eValor < master@evalor.cn >
     */
    private function buildTable()
    {
        if (empty($this->tableHeader)) {
            throw new \InvalidArgumentException('table header is empty!');
        }
        $this->calculateColumnWidth();
        $this->buildTableHeaders();
        $this->buildTableRows();
    }

    public function __toString()
    {
        $this->buildTable();
        return $this->tableContent;
    }
}