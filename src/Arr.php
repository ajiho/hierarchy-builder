<?php

namespace ajiho\ArrayHierarchy;

class Arr
{
    protected $items;
    protected $id;
    protected $level;

    protected $levelField = 'level';
    protected $idField = 'id';
    protected $sonField = 'children';
    protected $pidField = 'pid';


    //1:默认的树行解构 2:无限极分类列表 3:根据id返回父级的无限极分类列表
    private $returnType = 1;
    private $includeSelf = false;
    private $limit = null;

    private $pluckValue = null;
    private $pluckKey = null;


    public function __construct($items, $pid = 0, $level = 0, $idField = 'id', $pidField = 'pid', $sonField = 'children', $levelField = 'level')
    {
        $this->items = $items;
        $this->pid = $pid;
        $this->level = $level;
        $this->idField = $idField;
        $this->pidField = $pidField;
        $this->sonField = $sonField;
        $this->levelField = $levelField;
    }


    /**
     *
     * 根据id获取子集
     * @param $id mixed
     * @param $includeSelf
     * @return $this
     */
    public function child($id, $includeSelf = false)
    {
        $this->includeSelf = $includeSelf;
        $this->pid = $id;
        return $this;
    }


    /**
     * 根据id查找出所有的父级节点
     * @param $id
     * @param $includeSelf
     * @return $this
     */
    public function parents($id, $includeSelf = false)
    {
        $this->returnType = 3;
        $this->id = $id;
        $this->includeSelf = $includeSelf;
        return $this;
    }


    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }


    /**
     *
     * @param array|string $value
     * @param string|null $key
     * @return $this
     */
    public function pluck($value, $key = null)
    {
        $this->pluckValue = $value;
        $this->pluckKey = $key;
        return $this;
    }


    public function sort($isSort)
    {

        if ($isSort === true) {
            $this->returnType = 2;
        }

        return $this;
    }


    public function get()
    {
        switch ($this->returnType) {
            case 2:
                return $this->toSort();
            case 3:
                return $this->getParents();
            default:
                return $this->toTree();
        }

    }


    private function getParents()
    {

        $result = [];

        // 找到指定 ID 的节点
        $targetNode = null;
        foreach ($this->items as $item) {
            if ($item[$this->idField] === $this->id) {
                $targetNode = $item;
                break;
            }
        }

        if (is_null($targetNode)) {
            return $result;
        }

        // 迭代获取父级节点
        while (!is_null($targetNode[$this->pidField])) {
            $result[] = $targetNode;

            $parentId = $targetNode[$this->pidField];
            $targetNode = null;

            foreach ($this->items as $item) {
                if ($item[$this->idField] === $parentId) {
                    $targetNode = $item;
                    break;
                }
            }

            if (is_null($targetNode)) {
                break;
            }
        }

        // 最后加入根节点
        if ($this->includeSelf === true) {
            $result[] = $targetNode;
        }

        return $result;

    }


    private function toTree()
    {
        // 将每条数据中的id值作为其下标
        $temp = [];

        foreach ($this->items as $item) {

            $item[$this->sonField] = [];
            $temp[$item[$this->idField]] = $item;
        }

        // 获取分类树
        foreach ($temp as $v) {
            $temp[$v[$this->pidField]][$this->sonField][] = &$temp[$v[$this->idField]];
        }

        return $temp[$this->pid][$this->sonField] ?? [];
    }


    /**
     * 无限极排序
     */
    private function toSort()
    {
        $result = [];
        $sortedNodes = [];
        $parentIdMap = [];
        $idMap = [];

        // 构建父节点ID映射表
        foreach ($this->items as $item) {
            if ($this->includeSelf === true) {
                $id = $item[$this->idField];
                if (!isset($idMap[$id])) {
                    $idMap[$id] = [];
                }
                $idMap[$id][] = $item;
            }
            $parentId = $item[$this->pidField];
            if (!isset($parentIdMap[$parentId])) {
                $parentIdMap[$parentId] = [];
            }
            $parentIdMap[$parentId][] = $item;
        }


        if (isset($idMap[$this->pid]) && $this->includeSelf === true) {
            $selfNode = reset($idMap[$this->pid]);
            $selfNode[$this->levelField] = $this->level;
            $sortedNodes[$this->pid] = $selfNode;
        }


        // 遍历根节点下的所有子节点
        if (!isset($parentIdMap[$this->pid])) {
            return $result;
        }

        $stack = $parentIdMap[$this->pid];
        while (!empty($stack)) {
            $node = array_shift($stack);
            $id = $node[$this->idField];
            $parentId = $node[$this->pidField];

            // 计算当前节点的层级
            if ($parentId === $this->pid) {
                $node[$this->levelField] = isset($sortedNodes[$parentId][$this->levelField]) ? $sortedNodes[$parentId][$this->levelField] + 1 : $this->level;
            } else {
                $node[$this->levelField] = isset($sortedNodes[$parentId][$this->levelField]) ?? $sortedNodes[$parentId][$this->levelField] + 1;
            }

            // 添加当前节点到排序结果中
            $sortedNodes[$id] = $node;

            if (isset($parentIdMap[$id])) {
                // 把当前节点的子节点放入堆栈
                array_unshift($stack, ...$parentIdMap[$id]);
            }
        }

        $result = array_values($sortedNodes);

        if (is_int($this->limit) && $this->limit > 0) {
            $result = array_slice(array_values($sortedNodes), 0, $this->limit);
        }

        if (!is_null($this->pluckValue)) {

            if (!is_null($this->pluckKey)) {
                $result = array_column($result, $this->pluckValue, $this->pluckKey);
            } else {
                $result = array_column($result, $this->pluckValue);
            }

        }

        return $result;
    }
}
