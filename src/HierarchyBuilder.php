<?php

namespace ajiho;

class HierarchyBuilder
{

    private $items;

    private $levelField = 'level';
    private $idField = 'id';
    private $sonField = 'children';
    private $pidField = 'pid';


    public function __construct($items)
    {
        $this->items = $items;
    }

    /**
     * 传递数据得到实例
     * @param $items
     * @return self
     */
    public static function create($items)
    {
        return new self($items);
    }


    public function setIdField($fieldName)
    {
        $this->idField = $fieldName;
        return $this;
    }

    public function setLevelField($fieldName)
    {
        $this->levelField = $fieldName;
        return $this;
    }

    public function setSonField($fieldName)
    {
        $this->sonField = $fieldName;
        return $this;
    }

    public function setPidField($fieldName)
    {
        $this->pidField = $fieldName;
        return $this;
    }


    /**
     * 获取无限级分类列表
     */
    public function getCateList($pid = 0, $level = 0)
    {
        $result = [];
        $parentIdMap = [];


        // 构建父节点ID映射表和id节点映射表
        foreach ($this->items as $item) {
            $parentId = $item[$this->pidField];

            if (!isset($parentIdMap[$parentId])) {
                $parentIdMap[$parentId] = [];
            }
            $parentIdMap[$parentId][] = $item;
        }

        if (!isset($parentIdMap[$pid])) {
            return $result;
        }


        // 遍历根节点下的所有子节点
        $stack = $parentIdMap[$pid];


        while (!empty($stack)) {
            $node = array_shift($stack);


            $id = $node[$this->idField];
            $parentId = $node[$this->pidField];


            // 计算当前节点的层级
            if ($parentId === $pid) {
                $node[$this->levelField] = $level;
            } else {
                $node[$this->levelField] = $result[$parentId][$this->levelField] + 1;
            }

            // 添加当前节点到排序结果中
            $result[$id] = $node;

            if (isset($parentIdMap[$id])) {
                // 把当前节点的子节点放入堆栈
                array_unshift($stack, ...$parentIdMap[$id]);
            }
        }

        return array_values($result);
    }

    /**
     * 获取父子级树状结构
     * @param $pid
     * @return array
     */
    public function getTreeList($pid = 0)
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

        return $temp[$pid][$this->sonField] ?? [];
    }


    /**
     * 获取指定节点的父级节点列表
     * @param $id
     * @param mixed $pid
     * @return array
     */
    public function getParents($id, $pid = 0, $include_self = true)
    {

        $temp = [];

        // 判断传递进来的id是否存在,不存在直接返回结果,避免陷入死循环
        $idExists = isset(array_flip(array_column($this->items, $this->idField))[$id]);
        $pidExists = isset(array_flip(array_column($this->items, $this->pidField))[$pid]);

        if (!$idExists || !$pidExists) {
            return $temp;
        }


        while ($id != $pid) {
            foreach ($this->items as $item) {

                if ($item[$this->idField] == $id) {
                    $temp[] = $item;

                    $id = $item[$this->pidField];
                    break;
                }
            }
        }


        if ($include_self === false) {
            array_shift($temp);
        }

        return $temp;
    }


}
