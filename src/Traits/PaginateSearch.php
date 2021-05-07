<?php
declare(strict_types=1);

namespace Hyperf\EricTool\Traits;

use Hyperf\Contract\LengthAwarePaginatorInterface;

/**
 * User: eric
 * Date: 2020/10/29
 * Time: 4:34 下午
 * 搜索
 */
trait PaginateSearch
{
    public    $orderField      = ['id' => 'desc'];
    protected $searchField     = ['*'];
    protected $searchCondition = ['id', 'created_at'];

    /**
     * author eric
     *
     * @param       $query \Hyperf\Database\Model\Builder
     * @param array $params
     *
     * @return mixed
     */
    public function searchProcess($query, $params = [])
    {
        $this->searchConditionProcess(function ($fieldInfo, $value) use ($query) {
            [$field, $condition] = $fieldInfo;
            switch ($condition) {
                case 'like':
                    $query->where($field, 'like', str_replace($field, $value, $fieldInfo[2]));
                    break;
                case 'in':
                    $query->whereIn($field, $value);
                    break;
                case 'between':
                    $query->whereBetween($field, $value);
                    break;
                case 'not between':
                    $query->whereNotBetween($field, $value);
                    break;
                case 'not in':
                    $query->whereNotIn($field, $value);
                    break;
                case 'null':
                case 'NULL':
                    $query->whereNull($field);
                    break;
                case 'not null':
                case 'NOT NULL':
                    $query->whereNotNull($field);
                    break;
                case 'date':
                    $query->whereDate($field, $value);
                    break;
                case 'month':
                    $query->whereMonth($field, $value);
                    break;
                case 'day':
                    $query->whereDay($field, $value);
                    break;
                case 'time':
                    $query->whereTime($field, '=', $value);
                    break;
                default:
                    $query->where($field, $condition, $value);
                    break;
            }
        }, $params);

        return $query;
    }

    public function searchConditionProcess($callback, $params): void
    {
        if (!empty($this->searchCondition)) {
            foreach ($this->searchCondition as $fieldInfo) {
                if (!is_array($fieldInfo)) {
                    $fieldInfo = [$fieldInfo, '='];
                }
                $field = strpos($fieldInfo[0], '.') !== false ? explode('.', $fieldInfo[0])[1] : $fieldInfo[0];
                if (issetParam($params, $field)) {
                    $callback($fieldInfo, $params[$field]);
                }
            }
        }
    }

    /**
     * author wangwei
     *
     * @param      $params
     * @param bool $isQuery
     *
     * @return array|\Hyperf\Database\Model\Builder|mixed
     */
    public function search($params, $isQuery = false)
    {
        $query = static::query();
        $query = $this->searchProcess($query, $params);
        foreach ($this->orderField as $key => $value) {
            $query->orderBy($key, $value);
        }
        if ($isQuery) {
            return $query;
        }
        return $this->getPaginateData($this->resultProcess($query->paginate($this->getPageSize($params),
            $this->searchField)));
    }

    public function resultProcess(LengthAwarePaginatorInterface $result): LengthAwarePaginatorInterface
    {
        return $result;
    }

    public function getPaginateData(LengthAwarePaginatorInterface $paginateData): array
    {
        return [
            'list'        => $paginateData->items(),
            'currentPage' => $paginateData->currentPage(),
            'lastPage'    => $paginateData->lastPage(),
            'total'       => $paginateData->total(),
            'pageSize'    => $paginateData->perPage(),
        ];
    }

    public function getPageSize($params)
    {
        return $params['pageSize'] ?? 10;
    }
}