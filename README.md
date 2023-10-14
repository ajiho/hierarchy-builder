## hierarchy-builder(层级关系生成器)

什么？经常开发后台管理系统？需要生成左侧的菜单或者对一些数据进行分类？每次新项目都要从老项目里去复制对应的
代码？然后再微调？不不不，hierarchy-builder(层级关系生成器)可以让你更轻松的生成想要的数据结构。

hierarchy-builder 是一个php实现的非递归高性能的用于将二维数组转换为无限极分类、树状结构数组的生成器

## 特性

- 快速安装
- 灵活高效

## 安装

```bash
composer require ajiho/hierarchy-builder
```

## 用法

```php
$data = [
    ['id' => 8, 'name' => '白云区', 'pid' => 6],
    ['id' => 2, 'name' => '广东省', 'pid' => 1],
    ['id' => 1, 'name' => '中国', 'pid' => 0],
    ['id' => 9, 'name' => '美国', 'pid' => 0],
    ['id' => 6, 'name' => '广州市', 'pid' => 2],
    ['id' => 7, 'name' => '海珠区', 'pid' => 6],
    ['id' => 4, 'name' => '龙岗区', 'pid' => 3],
    ['id' => 10, 'name' => '加利福尼亚州', 'pid' => 9],
    ['id' => 5, 'name' => '南山区', 'pid' => 3],
    ['id' => 11, 'name' => '洛杉矶市', 'pid' => 10],
    ['id' => 3, 'name' => '深圳市', 'pid' => 2],
];


$builder = (new HierarchyBuilder($data));
//或者
$builder = HierarchyBuilder::create($data);

//如果您的关系字段默认不是id和pid
$builder->setIdField('id');
$builder->setPidField('pid');
//也可以指定生成的层级关系字段和表示子集的字段
$builder->setLevelField('level');
$builder->setSonField('children');


$results = $builder->getCateList($pid = 0, $level = 0);
$results = $builder->getTreeList($pid = 0);
$results = $builder->getParents($id, $pid = 0, $include_self = true)


//链式调用
$results = HierarchyBuilder::create($data)->setIdField('id')->setPidField('pid')->getCateList();
```




### 生成无限极分类列表

```php
$results = HierarchyBuilder::create($data)->getCateList();

//结果如下
$results = [
    ['id' => 1, 'name' => '中国', 'pid' => 0, 'level' => 0],
    ['id' => 2, 'name' => '广东省', 'pid' => 1, 'level' => 1],
    ['id' => 6, 'name' => '广州市', 'pid' => 2, 'level' => 2],
    ['id' => 8, 'name' => '白云区', 'pid' => 6, 'level' => 3],
    ['id' => 7, 'name' => '海珠区', 'pid' => 6, 'level' => 3],
    ['id' => 3, 'name' => '深圳市', 'pid' => 2, 'level' => 2],
    ['id' => 4, 'name' => '龙岗区', 'pid' => 3, 'level' => 3],
    ['id' => 5, 'name' => '南山区', 'pid' => 3, 'level' => 3],
    ['id' => 9, 'name' => '美国', 'pid' => 0, 'level' => 0],
    ['id' => 10, 'name' => '加利福尼亚州', 'pid' => 9, 'level' => 1],
    ['id' => 11, 'name' => '洛杉矶市', 'pid' => 10, 'level' => 2],
];



// 获取id为2下面所有的子集分类列表
$results2 = HierarchyBuilder::create($data)->getCateList(2);
//结果如下
$results2 = [
    ['id' => 6, 'name' => '广州市', 'pid' => 2, 'level' => 0],
    ['id' => 8, 'name' => '白云区', 'pid' => 6, 'level' => 1],
    ['id' => 7, 'name' => '海珠区', 'pid' => 6, 'level' => 2],
    ['id' => 3, 'name' => '深圳市', 'pid' => 2, 'level' => 0],
    ['id' => 4, 'name' => '龙岗区', 'pid' => 3, 'level' => 1],
    ['id' => 5, 'name' => '南山区', 'pid' => 3, 'level' => 2],
];



// 设定level的层级从1开始自增
$results3 = HierarchyBuilder::create($data)->getCateList(6,1);

//结果如下
$results3 = [
    ['id' => 8, 'name' => '白云区', 'pid' => 6, 'level' => 1],
    ['id' => 7, 'name' => '海珠区', 'pid' => 6, 'level' => 1],
];
```





### 生成父子级树状结构列表

```php
$results = HierarchyBuilder::create($data)->getTreeList();
//结果如下
$results = [
    ['id' => 1,
        'name' => '中国',
        'pid' => 0,
        'children' => [
            [
                'id' => 2,
                'name' => '广东省',
                'pid' => 1,
                'children' => [
                    [
                        'id' => 6,
                        'name' => '广州市',
                        'pid' => 2,
                        'children' => [
                            [
                                'id' => 8,
                                'name' => '白云区',
                                'pid' => 6,
                                'children' => []
                            ],
                            [
                                'id' => 7,
                                'name' => '海珠区',
                                'pid' => 6,
                                'children' => []
                            ],
                        ]
                    ],
                    [
                        'id' => 3,
                        'name' => '深圳市',
                        'pid' => 2,
                        'children' => [
                            [
                                'id' => 4,
                                'name' => '龙岗区',
                                'pid' => 3,
                                'children' => []
                            ],
                            [
                                'id' => 5,
                                'name' => '南山区',
                                'pid' => 3,
                                'children' => []
                            ],
                        ]
                    ],
                ]
            ],
        ]
    ],
    ['id' => 9,
        'name' => '美国',
        'pid' => 0,
        'children' => [
            [
                'id' => 10,
                'name' => '加利福尼亚州',
                'pid' => 9,
                'children' => [
                    [
                        'id' => 11,
                        'name' => '洛杉矶市',
                        'pid' => 10,
                        'children' => []
                    ],
                ]
            ],
        ]
    ],
];


//更改子节点的字段为son
$results2 = HierarchyBuilder::create($data)->setSonField('son')->getTreeList();
//结果如下
$results2 = [
    ['id' => 1,
        'name' => '中国',
        'pid' => 0,
        'son' => [
            [
                'id' => 2,
                'name' => '广东省',
                'pid' => 1,
                'son' => [
                    [
                        'id' => 6,
                        'name' => '广州市',
                        'pid' => 2,
                        'son' => [
                            [
                                'id' => 8,
                                'name' => '白云区',
                                'pid' => 6,
                                'son' => []
                            ],
                            [
                                'id' => 7,
                                'name' => '海珠区',
                                'pid' => 6,
                                'son' => []
                            ],
                        ]
                    ],
                    [
                        'id' => 3,
                        'name' => '深圳市',
                        'pid' => 2,
                        'son' => [
                            [
                                'id' => 4,
                                'name' => '龙岗区',
                                'pid' => 3,
                                'son' => []
                            ],
                            [
                                'id' => 5,
                                'name' => '南山区',
                                'pid' => 3,
                                'son' => []
                            ],
                        ]
                    ],
                ]
            ],
        ]
    ],
    ['id' => 9,
        'name' => '美国',
        'pid' => 0,
        'son' => [
            [
                'id' => 10,
                'name' => '加利福尼亚州',
                'pid' => 9,
                'son' => [
                    [
                        'id' => 11,
                        'name' => '洛杉矶市',
                        'pid' => 10,
                        'son' => []
                    ],
                ]
            ],
        ]
    ]
];

```


### 获取指定id节点的父级列表

```php
$results = HierarchyBuilder::create($data)->getParents(8);
//结果如下
$results = [
    ['id' => 8, 'name' => '白云区', 'pid' => 6]
    ['id' => 6, 'name' => '广州市', 'pid' => 2],
    ['id' => 2, 'name' => '广东省', 'pid' => 1],
    ['id' => 1, 'name' => '中国', 'pid' => 0],
];

//搜索到pid为1停止
$results2 = HierarchyBuilder::create($data)->getParents(8,1);
//结果如下
$results2 = [
    ['id' => 8, 'name' => '白云区', 'pid' => 6]
    ['id' => 6, 'name' => '广州市', 'pid' => 2],
    ['id' => 2, 'name' => '广东省', 'pid' => 1]
];

//搜索结果不包含当前传入的id节点本身
$results3 = HierarchyBuilder::create($data)->getParents(8,1,false);
//结果如下
$results3 = [
    ['id' => 6, 'name' => '广州市', 'pid' => 2],
    ['id' => 2, 'name' => '广东省', 'pid' => 1]
];


```


