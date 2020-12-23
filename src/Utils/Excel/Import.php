<?php
declare(strict_types=1);

namespace Hyperf\EricTool\Utils\Excel;

use Hyperf\HttpMessage\Upload\UploadedFile;
use Vtiful\Kernel\Excel;

/**
 * 文件导入
 * User: wangwei
 * Date: 2020/12/8
 * Time: 上午10:35
 */
class Import
{
    /**
     * @var \Vtiful\Kernel\Excel
     */
    private $excel;
    private $file;
    private $startRow;//开始行
    private $endVerifyLine;//判断空就结束的列
    private $sheetName;

    public function __construct(UploadedFile $file, $startRow = 0, $endVerifyLine = 0, $sheetName = 'Sheel1')
    {
        $config              = ['path' => $file->getPath()];
        $this->file          = $file;
        $this->excel         = new Excel($config);
        $this->startRow      = $startRow;
        $this->endVerifyLine = $endVerifyLine;
        $this->sheetName     = $sheetName;
    }

    public function process($callback, $mapField): void
    {
        $this->excel->openFile($this->file->getBasename())->openSheet();
        $line = -1;
        while (($row = $this->excel->nextRow()) !== null) {
            ++$line;
            if ($line < $this->startRow) {
                continue;
            }
            if (empty($row[$this->endVerifyLine])) {
                break;
            }
            $saveData = [];
            foreach ($mapField as $key => $val) {
                if (is_array($val)) {
                    $saveData[$key] = $val['function']($row[$val['line']]);
                } else {
                    $saveData[$key] = $row[$val] ?? '未知';
                }
                unset($val);
            }
            unset($item);
            $callback($saveData);
            unset($saveData);
        }
    }
}