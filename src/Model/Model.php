<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\EricTool\Model;

use Hyperf\EricTool\Exception\ToolException;
use Hyperf\EricTool\Traits\CreateOrUpdate;
use Hyperf\EricTool\Traits\PaginateSearch;
use Hyperf\DbConnection\Model\Model as BaseModel;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

abstract class Model extends BaseModel implements CacheableInterface
{
    use Cacheable;
    use CreateOrUpdate;
    use PaginateSearch;

    public $timestamps = false;

    /**
     * author wangwei
     *
     * @param      $id
     * @param bool $isExc
     *
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Builder[]|\Hyperf\Database\Model\Collection|\Hyperf\Database\Model\Model
     */
    public static function getInfoById($id, $isExc = true)
    {
        $info = static::query()->find($id);
        if ($isExc && empty($info)) {
            throw new ToolException('信息不存在');
        }

        return $info;
    }

    public static function deleteById($id)
    {
        return self::getInfoById($id)->delete();
    }

    public static function editStatus($id, $status)
    {
        return self::getInfoById($id)->update(['status' => $status]);
    }

    public static function firstOrError($where)
    {
        if (!$model = static::query()->where($where)->first()) {
            throw new ToolException('信息不存在');
        }

        return $model;
    }
}
