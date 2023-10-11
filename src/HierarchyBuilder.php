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

    protected function setIdField($fieldName)
    {
        $this->idField = $fieldName;
        return $this;
    }

    protected function setLevelField($fieldName)
    {
        $this->levelField = $fieldName;
        return $this;
    }

    protected function setSonField($fieldName)
    {
        $this->sonField = $fieldName;
        return $this;
    }

    protected function setPidField($fieldName)
    {
        $this->pidField = $fieldName;
        return $this;
    }


    /**
     * 获取无限级分类列表
     */
    protected function getCateList($pid = 0, $level = 0, $include_self = true)
    {
        $sortedNodes = [];
        $parentIdMap = [];
        $idMap = [];

        // 构建父节点ID映射表和id节点映射表
        foreach ($this->items as $item) {
            $parentId = $item[$this->pidField];
            $id = $item[$this->idField];

            if (!isset($parentIdMap[$parentId])) {
                $parentIdMap[$parentId] = [];
            }
            if (!isset($idMap[$id])) {
                $idMap[$id] = [];
            }
            $parentIdMap[$parentId][] = $item;
            $idMap[$id][] = $item;
        }



        if (isset($idMap[$pid]) && $include_self === true) {
            $selfNode = $idMap[$pid][0];
            $selfNode[$this->levelField] = $level;
            $sortedNodes[$pid] = $selfNode;
        }


        // 遍历根节点下的所有子节点
        $stack = $parentIdMap[$pid];



        while (!empty($stack)) {
            $node = array_shift($stack);

            $id = $node[$this->idField];
            $parentId = $node[$this->pidField];


            // 计算当前节点的层级
            if ($parentId === $pid) {
                $node[$this->levelField] = isset($sortedNodes[$parentId][$this->levelField]) ? $sortedNodes[$parentId][$this->levelField] + 1 : $level;
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

        return array_values($sortedNodes);
    }



    protected function getTreeList($pid = 0)
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

}
