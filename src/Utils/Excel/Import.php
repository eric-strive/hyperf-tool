<?php
declare(strict_types=1);

namespace Hyperf\EricTool\Utils\Excel;

use Hyperf\EricTool\Exception\ToolException;
use Hyperf\EricTool\Utils\Excel\Constants\ErrorCode;
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
    /**
     * @var \Hyperf\HttpMessage\Upload\UploadedFile UploadedFile
     */
    private $file;
    private $startRow;//开始行
    private $endVerifyLine;//判断空就结束的列
    private $sheetName;

    //允许上传的文件后缀
    public $allowExtension = ['csv', 'xls', 'xlsx'];

    //允许上传的mime类型
    public $allowMime = [
        'text/csv',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    //允许上传的大小
    private $maxSize = 2 * 1024 * 1024;

    public function __construct(UploadedFile $file, $startRow = 0, $endVerifyLine = 0, $sheetName = 'Sheel1')
    {
        $this->file = $file;
        $this->fileCheck();
        $config              = ['path' => $file->getPath()];
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

    private function fileCheck()
    {
        if (!in_array($this->file->getExtension(), $this->allowExtension, true)) {
            throw new UtilsException(sprintf('文件后缀只能是%s', explode(',', $this->allowExtension)),
                ErrorCode::FILE_EXTENSION_ERROR);
        }
        if (!in_array($this->file->getMimeType(), $this->allowMime, true)) {
            $this->exceptionProcess(ErrorCode::FILE_MIME_ERROR);
        }
        if ($this->file->getSize() > $this->maxSize) {
            $this->exceptionProcess(ErrorCode::FILE_SIZE_OVERRUN);
        }
    }

    private function exceptionProcess($code): void
    {
        throw new ToolException($code,ErrorCode::getMessage($code));
    }
}