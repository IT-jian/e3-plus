<?php


namespace App\Services\Adaptor;


class BaseRepository
{
    protected $table;

    public function db()
    {
        return \DB::connection();
    }

    /**
     * 行
     *
     * @param $where
     * @param array $fields
     * @return mixed
     *
     * @author linqihai
     * @since 2019/12/25 17:34
     */
    public function getRow($where, $fields = ['*'])
    {
        $queryBuilder = $this->db()->table($this->table)->select($fields);
        foreach ($where as $key => $item) {
            if (is_array($item) && 3 == count($item) && 'IN' == strtoupper($item[1])) {
                $queryBuilder = $queryBuilder->whereIn($item[0], $item[1]);
                unset($where[$key]);
            }
        }
        $result = $queryBuilder->where($where)->first();

        return $result;
    }

    public function getAll($where, $fields = ['*'], $order = '', $limit = 0, $skip = 0)
    {
        $queryBuilder = $this->db()->table($this->table)->select($fields);
        foreach ($where as $key => $item) {
            if (is_array($item) && 3 ==count($item) && 'IN' == strtoupper($item[1])) {
                $queryBuilder = $queryBuilder->whereIn($item[0], $item[2]);
                unset($where[$key]);
            }
        }
        $queryBuilder = $queryBuilder->where($where);
        if ($order) {
            $queryBuilder->orderByRaw($order);
        }
        if ($skip) {
            $queryBuilder = $queryBuilder->skip($skip);
        }
        if ($limit) {
            $queryBuilder = $queryBuilder->limit($limit);
        }
        $result = $queryBuilder->get();

        // $result = $this->db()->select($sql);

        return $result;
    }

    public function count($where)
    {
        $queryBuilder = $this->db()->table($this->table);
        foreach ($where as $key => $item) {
            if (is_array($item) && 3 ==count($item) && 'IN' == strtoupper($item[1])) {
                $queryBuilder = $queryBuilder->whereIn($item[0], $item[2]);
                unset($where[$key]);
            }
        }
        $queryBuilder = $queryBuilder->where($where);

        return $queryBuilder->count();
    }

    public function insert($data)
    {
        return $this->db()->table($this->table)->insert($data);
    }

    public function update($data, $where)
    {
        return $this->db()->table($this->table)->where($where)->update($data);
    }

    public function insertMulti($rowArr, $duplicate = [], $ignore = false)
    {
        $rowArr = array_values($rowArr);
        $keyArr = array_keys($rowArr[0]);
        if (!empty($duplicate)) {
            $_duplicate = array();
            foreach ($duplicate as $_key=>$_value) {
                $_duplicate[] = "{$_value}=VALUES({$_value})";
            }
            $duplicate_sql = " ON DUPLICATE KEY UPDATE " . implode(',', $_duplicate);
        } else {
            $duplicate_sql = '';
        }

        $sqlMx = '';
        foreach ($rowArr as $row) {
            $sqlMx .= ",(";
            foreach($keyArr as $key){
                if(is_null($row[$key])){
                    $sqlMx .= "NULL,";
                }else{
                    $sqlMx .= "'".addslashes($row[$key])."',";
                }
            }
            $sqlMx = rtrim($sqlMx, ','). ')';
        }
        $sqlMx = substr($sqlMx, 1);

        $sql = 'INSERT '.($ignore ? 'ignore' : '').' INTO '.$this->table.'('.implode(',', $keyArr).') VALUES'.$sqlMx;
        $sql .= " {$duplicate_sql}";

        return $this->db()->insert($sql);
    }
}