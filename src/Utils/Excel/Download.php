<?php
declare(strict_types=1);

namespace Hyperf\EricTool\Utils\Excel;

use Hyperf\EricTool\Exception\ToolException;
use Hyperf\EricTool\Utils\Excel\Constants\ErrorCode;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Vtiful\Kernel\Excel;
use Vtiful\Kernel\Format;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\ApplicationContext;

/**
 * User: wangwei
 * Date: 2020/12/8
 * Time: 上午10:37
 */
class Download
{
    private const ARRAY_CONVERT = [
        'created_at',
        'updated_at',
    ];

    /**
     * @param                                            $fileName
     * @param array|\Hyperf\Database\Model\Builder|mixed $query
     * @param                                            $formatData
     *
     * @param null                                       $temPath 临时地址
     *
     * @return mixed
     */
    public static function downloadCsv($fileName, $query, $formatData, $temPath = null)
    {

        $config      = [
            'path' => self::getPath($temPath),
        ];
        $excelObject = new Excel($config);
        $fileName    = sprintf('%s.csv', $fileName);
        $fileObject  = $excelObject->constMemory($fileName);
        // Init File
        $heardData = [];
        $lineNum   = 0;
        foreach ($formatData as $item) {
            $heardData[] = $item['label'];
            if (isset($item['width'])) {
                $alp = Excel::stringFromColumnIndex($lineNum);
                $fileObject->setColumn(sprintf('%s:%s', $alp, $alp), (int)$item['width']);
            }
            $lineNum++;
        }
        // 创建边框样式
        $fileHandle  = $fileObject->getHandle();
        $format      = new Format($fileHandle);
        $borderStyle = $format->bold()->border(Format::BORDER_THIN)->toResource();
        $fileObject->setRow('A1', 20, $borderStyle)->freezePanes(1, 0)->header($heardData);
        $callback = static function ($datas) use ($formatData, $fileObject) {
            $excelData = [];
            foreach ($datas as $queryItem) {
                $row = [];
                foreach ($formatData as $item) {
                    $formatFunction = $item['value'];
                    if (is_string($formatFunction)) {
                        $value = in_array($formatFunction, self::ARRAY_CONVERT,
                            true) ? $queryItem->toArray()[$formatFunction] : $queryItem[$formatFunction];
                    } else {
                        $value = $formatFunction($queryItem);
                    }
                    $row[] = $value;
                    unset($value);
                }
                $excelData[] = $row;
                unset($row);
            }
            $fileObject->data($excelData);
            unset($excelData);
        };
        if (is_object($query)) {
            $query->chunk(1000, $callback);
        } else {
            $callback($query);
        }

        $filePath = $fileObject->output();

        return self::excelResponse($fileName, $filePath);
    }

    /**
     * 返回响应excel
     *
     * @param $fileName
     * @param $filePath
     *
     * @return mixed
     */
    public static function excelResponse($fileName, $filePath)
    {
        //后期建议异步删除文件
        $response = ApplicationContext::getContainer()
            ->get(ResponseInterface::class)
            ->withHeader('content-description', 'File Transfer')
            ->withHeader('content-type', 'text/csv')
            ->withHeader('Content-Disposition', "attachment; filename={$fileName}")
            ->withHeader('Content-Length', filesize($filePath))
            ->withHeader('content-transfer-encoding', 'binary')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Cache-Control', 'max-age=0')
            ->withHeader('pragma', 'public')
            ->withBody(new SwooleStream(file_get_contents($filePath)));
        @unlink($filePath);

        return $response;
    }

    private static function getPath($temPath): string
    {
        $temPath = $temPath ?? '/tmp';
        //文件夹不存在或者不是目录。创建文件夹
        if (self::checkPath($temPath)) {
            return $temPath;
        }
        throw new ToolException(ErrorCode::FILE_PATH_ERROR,ErrorCode::getMessage(ErrorCode::FILE_PATH_ERROR));
    }

    private static function checkPath($temPath)
    {
        if (!file_exists($temPath) || !is_dir($temPath)) {
            return mkdir($temPath, 0777, true);
        }
        if (!is_writable($temPath)) {
            return chmod($temPath, 0777);
        }

        return true;
    }
}