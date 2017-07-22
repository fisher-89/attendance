<?php

/**
 * 插件数据交互服务
 * create by Fisher 2016/8/28 <fisher9389@sina.com>
 */

namespace App\Services;

use Illuminate\Http\Request;
use DB;

class PluginService {

    public function dataTables(Request $request, $tableName, $basicSql = 1) {
        /* 数据表 */
        if (is_object($tableName)) {
            $table = $tableName;
        } else if (preg_match('/^\w+$/', $tableName)) {
            $table = DB::table($tableName);
        } else {
            $table = new $tableName;
        }
        $joinTable = [];
        /* 字段 */
        foreach ($request->columns as $v) {
            $columnTmp = explode('.', $v['data']);
            $columns[] = $columnTmp;
            if ($v['searchable'] == "true") {
                $search_column[] = explode('.', $v['data']);
            }
            array_pop($columnTmp);
            if (!empty($columnTmp)) {
                $joinTable[] = implode('.', $columnTmp);
            }
        }
        /* 获取Datatables发送的参数 必要 */
        $draw = $request->draw; //这个值直接返回给前台
        /* 排序 */
        $order_column = $request->order['0']['column']; //排序字段序号
        $order_column = !empty($request->order) ? $columns[$order_column] : [$table->getKeyName()]; //获取排序字段，若没有，默认为主键
        $order_dir = $request->order['0']['dir'] ? $request->order['0']['dir'] : 'asc'; //asc desc 升序或者降序
        /* 搜索 */
        $search = $request->search['value']; //获取前台传过来的过滤条件
        /* 筛选 */
        $filter = $request->filter ? $request->filter : [];
        /* 分页 */
        $start = $request->start ? $request->start : 0; //从多少开始
        $length = $request->length ? $request->length : 0; //数据长度
        /* 查询初始化 */
        $dbInit = $table->whereRaw($basicSql);
        /* 表的总记录数 必要 */
        $recordsTotal = $dbInit->count();
        /* 定义过滤条件查询过滤后的记录数 */
        $recordsFiltered = $dbInit
                ->when(strlen($search) > 0, function($query)use($search_column, $search) {
                    return $this->dataTablesSearch($query, $search_column, $search);
                })
                ->when(!empty($filter), function($query)use($filter) {
                    return $this->dataTablesFilter($query, $filter);
                })
                ->count();
        /* 数据 */
        $infos = $dbInit
                        ->when(!empty($joinTable), function($query)use($joinTable) {
                            return $query->with($joinTable);
                        })
                        ->when(strlen($search) > 0, function($query)use($search_column, $search) {
                            return $this->dataTablesSearch($query, $search_column, $search);
                        })
                        ->when($filter, function($query)use( $filter) {
                            return $this->dataTablesFilter($query, $filter);
                        })
                        ->when(count($order_column) == 1, function($query)use($order_column, $order_dir) {
                            return $query->orderBy($order_column[0], $order_dir);
                        })
                        ->when(count($order_column) == 2, function($query)use($order_column, $order_dir) {
                            return $query->orderBy(head($order_column) . '_id', $order_dir);
                        })
                        ->when($length > 0, function($query)use($start, $length) {
                            return $query->skip($start)->take($length);
                        })
                        ->get()->toArray();
        /*
         * Output 包含的是必要的
         */
        return [
            "draw" => intval($draw),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $infos
        ];
    }

    /* --filter start-- */

    private function dataTablesFilter($query, $filterOrigin) {
        $filter = [];
        foreach ($filterOrigin as $v) {
            $value = $v['value'];
            if (empty($value))
                continue;
            $names = explode('[', $v['name']);
            if (count($names) > 1) {
                $key = $names[0];
                $subkey = trim($names[1], ']');
                $filter[$key][$subkey] = $value;
            } else {
                $key = $names[0];
                $filter[$key]['is'] = $value;
            }
        }
        foreach ($filter as $k => $v) {
            $column = explode('.', $k);
            $this->filterByTree($query, $column, $v);
        }
        return $query;
    }

    private function filterByTree(&$query, $column, $filter) {
        $first = array_shift($column);
        if (count($column) > 0) {
            $query->whereHas($first, function ($q) use ($column, $filter) {
                $this->filterByTree($q, $column, $filter);
            });
        } else {
            if (!empty($filter['min'])) {
                $query->where($first, '>', $filter['min']);
            }
            if (!empty($filter['max'])) {
                $query->where($first, '<', $filter['max']);
            }
            if (!empty($filter['in'])) {
                $query->whereIn($first, $filter['in']);
            }
            if (!empty($filter['like'])) {
                $query->where($first, 'like', '%' . $filter['like'] . '%');
            }
            if (!empty($filter['is'])) {
                $query->where($first, '=', $filter['is']);
            }
        }
    }

    /* --filter end-- */

    /**
     * 生成搜索Sql
     * @param object $query
     * @param array $columns
     * @param string $search
     * @return object
     */
    private function dataTablesSearch($query, $columns, $search) {
        return $query->where(function($q)use($columns, $search) {
                    foreach ($columns as $column) {
                        $this->searchByTree($q, $column, $search);
                    }
                });
    }

    /**
     * 递归拼接搜索Sql语句
     * @param object $query
     * @param array $column
     * @param string $search
     */
    private function searchByTree(&$query, $column, $search) {
        $first = array_shift($column);
        if (count($column) > 1) {
            $query->orWhereHas($first, function ($q) use ($column, $search) {
                $q->where(function($qq)use($column, $search) {
                    $this->searchByTree($qq, $column, $search);
                });
            });
        } else if (count($column) > 0) {
            $query->orWhereHas($first, function ($q) use ($column, $search) {
                $q->where([[head($column), 'like', '%' . $search . '%']]);
            });
        } else {
            $query->orWhere([[$first, 'like', '%' . $search . '%']]);
        }
    }

}
