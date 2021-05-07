<?php
namespace Hyperf\EricTool\Traits;

use Hyperf\EricTool\Exception\ToolException;

/**
 * Trait CreateOrUpdate
 * 新增或更新
 *
 * @package App\Traits
 */
trait CreateOrUpdate
{
    protected $unique_condition = ['id'];

    /**
     * 新增 or 更新数据
     *
     * @param $orgInfo
     *
     * @return bool|\Hyperf\Utils\HigherOrderCollectionProxy|\Hyperf\Utils\HigherOrderTapProxy|int|mixed|void|null
     */
    public function createOrUpdate($orgInfo)
    {
        $info = $this->getInfoByUnique($orgInfo);
        $id   = $info->{$this->primaryKey} ?? null;

        return $this->import($orgInfo, $id);
    }

    public function getInfoByUnique($orgInfo)
    {
        if (method_exists($this, 'getDeletedAtColumn')) {
            $q = static::onlyTrashed();
            foreach ($this->unique_condition as $field) {
                $q->where([$field => $orgInfo[$field]]);
            }
            $softDelete = $q->first();
            if ($softDelete) {
                $softDelete->restore();

                return $softDelete;
            }
        }
        $query = static::query();
        foreach ($this->unique_condition as $field) {
            $query->where([$field => $orgInfo[$field]]);
        }

        return $query->first();
    }

    /**
     * 新增/更新数据
     *
     * @param      $data
     * @param null $id
     *
     * @return bool|\Hyperf\Utils\HigherOrderCollectionProxy|\Hyperf\Utils\HigherOrderTapProxy|int|mixed|void|null
     */
    public function import($data, $id = null)
    {
        $model = $id ? static::query()->where($this->primaryKey, $id)->first() : static::query();
        if (empty($model)) {
            throw new ToolException("查询信息不存在");
        }
        $this->importBefore($data, $id);
        if ($id) {
            if (in_array('updated_user', $this->fillable, true)) {
                $data['updated_user'] = $data['user_name'] ?? 'sys';
            }
            $this->updateBefore($model, $data);
            $model->update($data);
        } else {
            if (in_array('created_user', $this->fillable, true)) {
                $data['created_user'] = $data['user_name'] ?? 'sys';
            }
            $model = static::Create($data);
        }
        $this->importAfter($model, $data);

        return $id ? : $model->{$this->primaryKey};
    }

    protected function updateBefore($model, $data)
    {
        return true;
    }

    protected function importBefore(&$data, $id = null)
    {
        return true;
    }

    protected function importAfter($model, $data)
    {
        return true;
    }
}