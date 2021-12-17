<?php
namespace App\Auth\Services;

/**
 * 路由权限服务层
 * Class RoutePermissionsService
 * @package App\Auth\Services
 */
class RoutePermissionsService
{
    /**
     * 获取所有节点id和路由
     * @author zt6768
     * @param int id        节点id
     * @param int parent_id 父节点id
     * @param int sort      排序
     * @param string name   名称
     * @param string url    路由
     * @param boolean navigation true-菜单，false-功能
     * @param string icon   图标
     * @return array
     */
    public static function getRoute()
    {
        return [
            0 => [
                0 => [
                    'id' => 1,
                    'parent_id' => 0,
                    'sort' => 1,
                    'name' => '电商首页',
                    'url'  => 'ECommerce/index',
                    'navigation' => true,
                    'icon' => 'ic1'
                ],
                1 => [
                    'id' => 2,
                    'parent_id' => 0,
                    'sort' => 2,
                    'name' => '客诉单管理',
                    'url'  => '',
                    'navigation' => true,
                    'icon' => 'ic2',
                    'child' => [
                        0 => [
                            'id' => 20,
                            'parent_id' => 2,
                            'sort' => 1,
                            'name' => '客诉单信息',
                            'url'  => 'ECommerce/CCIndex',
                            'navigation' => true,
                            'child' => [
                                0 => [
                                    'id' => 200,
                                    'parent_id' => 20,
                                    'sort' => 1,
                                    'name' => '查询',
                                    'url'  => 'ECommerce/CCSearch',
                                    'navigation' => false,
                                ],
                                1 => [
                                    'id' => 201,
                                    'parent_id' => 20,
                                    'sort' => 2,
                                    'name' => '提交',
                                    'url'  => 'ECommerce/submitCC',
                                    'navigation' => false,
                                ],
                                2 => [
                                    'id' => 202,
                                    'parent_id' => 20,
                                    'sort' => 3,
                                    'name' => '新增',
                                    'url'  => 'ECommerce/create',
                                    'navigation' => false,
                                ],
                                3 => [
                                    'id' => 203,
                                    'parent_id' => 20,
                                    'sort' => 4,
                                    'name' => '编辑',
                                    'url'  => 'ECommerce/editCC',
                                    'navigation' => false,
                                ],
                                5 => [
                                    'id' => 205,
                                    'parent_id' => 20,
                                    'sort' => 6,
                                    'name' => '查看',
                                    'url'  => 'ECommerce/filingDetail',
                                    'navigation' => false,
                                ],
                                6 => [
                                    'id' => 206,
                                    'parent_id' => 20,
                                    'sort' => 7,
                                    'name' => '补充',
                                    'url'  => 'ECommerce/supplement',
                                    'navigation' => false,
                                ],
                                7 => [
                                    'id' => 207,
                                    'parent_id' => 20,
                                    'sort' => 8,
                                    'name' => '提交补充描述',
                                    'url'  => 'ECommerce/editCCDescription',
                                    'navigation' => false,
                                ],
                                8 => [
                                    'id' => 208,
                                    'parent_id' => 20,
                                    'sort' => 9,
                                    'name' => '关闭',
                                    'url'  => 'ECommerce/closeCC',
                                    'navigation' => false,
                                ],
                                9 => [
                                    'id' => 209,
                                    'parent_id' => 20,
                                    'sort' => 10,
                                    'name' => '同步仓库接口',
                                    'url'  => 'ECommerce/ajaxGetOrderProductByOrderNumber',
                                    'navigation' => false,
                                ],
                                10 => [
                                    'id' => 2010,
                                    'parent_id' => 20,
                                    'sort' => 11,
                                    'name' => '商品编辑',
                                    'url'  => 'ECommerce/goodsEditOld',
                                    'navigation' => false,
                                ],
                                11 => [
                                    'id' => 2011,
                                    'parent_id' => 20,
                                    'sort' => 12,
                                    'name' => '商品删除',
                                    'url'  => 'ECommerce/goodsDelOld',
                                    'navigation' => false,
                                ],
                                12 => [
                                    'id' => 1003,
                                    'parent_id' => 20,
                                    'sort' => 13,
                                    'name' => '图片删除',
                                    'url'  => 'ECommerce/delPic',
                                    'navigation' => false
                                ]
                            ]
                        ],
                        1 => [
                            'id' => 21,
                            'parent_id' => 2,
                            'sort' => 2,
                            'name' => '客诉单归档',
                            'url'  => 'ECommerce/filing',
                            'navigation' => true,
                            'child' => [
                                0 => [
                                    'id' => 210,
                                    'parent_id' => 21,
                                    'sort' => 1,
                                    'name' => '查询',
                                    'url'  => 'ECommerce/searchFiling',
                                    'navigation' => false,
                                ],
                                1 => [
                                    'id' => 211,
                                    'parent_id' => 21,
                                    'sort' => 2,
                                    'name' => '批量确认',
                                    'url'  => 'ECommerce/batchConfirm',
                                    'navigation' => false,
                                ],
                                2 => [
                                    'id' => 212,
                                    'parent_id' => 21,
                                    'sort' => 3,
                                    'name' => '导出',
                                    'url'  => 'ECommerce/export',
                                    'navigation' => false,
                                ],
                                3 => [
                                    'id' => 213,
                                    'parent_id' => 21,
                                    'sort' => 4,
                                    'name' => '查看',
                                    'url'  => 'ECommerce/filingDetail',
                                    'navigation' => false,
                                ],
                                4 => [
                                    'id' => 214,
                                    'parent_id' => 21,
                                    'sort' => 5,
                                    'name' => '确认',
                                    'url'  => 'ECommerce/confirm',
                                    'navigation' => false,
                                ],
                                5 => [
                                    'id' => 215,
                                    'parent_id' => 21,
                                    'sort' => 6,
                                    'name' => '详情',
                                    'url'  => 'ECommerce/ECDetail',
                                    'navigation' => false,
                                ],
                            ]
                        ]
                    ]
                ]
            ],
            1 => [
                0 => [
                    'id' => 3,
                    'parent_id' => 0,
                    'sort' => 3,
                    'name' => '售后首页',
                    'url'  => 'AfterSale/index',
                    'navigation' => true,
                    'icon' => 'ic1'
                ],
                1 => [
                    'id' => 4,
                    'parent_id' => 0,
                    'sort' => 4,
                    'name' => '客诉单管理',
                    'url'  => '',
                    'navigation' => true,
                    'icon' => 'ic2',
                    'child' => [
                        0 => [
                            'id' => 40,
                            'parent_id' => 4,
                            'sort' => 1,
                            'name' => '客诉单信息',
                            'url'  => 'AfterSale/customerComplaintManager/customerComplaint',
                            'navigation' => true,
                            'child' => [
                                0 => [
                                    'id' => 400,
                                    'parent_id' => 40,
                                    'sort' => 1,
                                    'name' => '列表',
                                    'url'  => 'AfterSale/customerComplaintManager/customerComplaintList',
                                    'navigation' => false,
                                ],
                                1 => [
                                    'id' => 401,
                                    'parent_id' => 40,
                                    'sort' => 2,
                                    'name' => '查询',
                                    'url'  => 'AfterSale/customerComplaintManager/searchCustomerComplaintList',
                                    'navigation' => false,
                                ],
                                2 => [
                                    'id' => 402,
                                    'parent_id' => 40,
                                    'sort' => 3,
                                    'name' => '新增',
                                    'url'  => 'AfterSale/customerComplaintManager/customerComplaint',
                                    'navigation' => false,
                                ],
                                3 => [
                                    'id' => 403,
                                    'parent_id' => 40,
                                    'sort' => 4,
                                    'name' => '提交保存',
                                    'url'  => 'AfterSale/customerComplaintManager/addCustomerComplaint',
                                    'navigation' => false,
                                ],
                                4 => [
                                    'id' => 404,
                                    'parent_id' => 40,
                                    'sort' => 5,
                                    'name' => '处理',
                                    'url'  => 'AfterSale/customerComplaintManager/customerComplaintHandle',
                                    'navigation' => false,
                                ],
                                5 => [
                                    'id' => 405,
                                    'parent_id' => 40,
                                    'sort' => 6,
                                    'name' => '转交客诉单',
                                    'url'  => 'AfterSale/customerComplaintManager/ajaxHandle',
                                    'navigation' => false,
                                ],
                                6 => [
                                    'id' => 406,
                                    'parent_id' => 40,
                                    'sort' => 7,
                                    'name' => '获取协助人',
                                    'url'  => 'AfterSale/customerComplaintManager/ajaxGetHelpers',
                                    'navigation' => false,
                                ],
                                7 => [
                                    'id' => 407,
                                    'parent_id' => 40,
                                    'sort' => 8,
                                    'name' => '查看',
                                    'url'  => 'AfterSale/customerComplaintManager/customerComplaintDetail',
                                    'navigation' => false,
                                ],
                                8 => [
                                    'id' => 208,
                                    'parent_id' => 40,
                                    'sort' => 9,
                                    'name' => '同步仓库接口',
                                    'url'  => 'ECommerce/ajaxGetOrderProductByOrderNumber',
                                    'navigation' => false,
                                ]
                            ]
                        ]
                    ]
                ],
                2 => [
                    'id' => 5,
                    'parent_id' => 0,
                    'sort' => 5,
                    'name' => '工单管理',
                    'url'  => '',
                    'navigation' => true,
                    'icon' => 'ic4',
                    'child' => [
                        0 => [
                            'id' => 50,
                            'parent_id' => 5,
                            'sort' => 1,
                            'name' => '工单信息',
                            'url'  => 'AfterSale/workOrderManager/workOrder',
                            'navigation' => true,
                            'child' => [
                                0 => [
                                    'id' => 500,
                                    'parent_id' => 50,
                                    'sort' => 1,
                                    'name' => '列表',
                                    'url'  => 'AfterSale/workOrderManager/workOrderList',
                                    'navigation' => false,
                                ],
                                1 => [
                                    'id' => 501,
                                    'parent_id' => 50,
                                    'sort' => 2,
                                    'name' => '查询',
                                    'url'  => 'AfterSale/workOrderManager/searchWorkOrderList',
                                    'navigation' => false,
                                ],
                                2 => [
                                    'id' => 502,
                                    'parent_id' => 50,
                                    'sort' => 3,
                                    'name' => '高级查询',
                                    'url'  => 'AfterSale/workOrderManager/advancedSearch',
                                    'navigation' => false,
                                ],
                                3 => [
                                    'id' => 503,
                                    'parent_id' => 50,
                                    'sort' => 4,
                                    'name' => '工单处理详情',
                                    'url'  => 'AfterSale/ticketManage',
                                    'navigation' => false,
                                ],
                                4 => [
                                    'id' => 504,
                                    'parent_id' => 50,
                                    'sort' => 5,
                                    'name' => '工单处理',
                                    'url'  => 'afterSale/ticketHandle',
                                    'navigation' => false,
                                ],
                                5 => [
                                    'id' => 505,
                                    'parent_id' => 50,
                                    'sort' => 6,
                                    'name' => '确认工单',
                                    'url'  => 'afterSale/ticketConfirm',
                                    'navigation' => false,
                                ],
                                6 => [
                                    'id' => 506,
                                    'parent_id' => 50,
                                    'sort' => 7,
                                    'name' => '关闭工单',
                                    'url'  => 'afterSale/ticketClose',
                                    'navigation' => false,
                                ],
                                7 => [
                                    'id' => 507,
                                    'parent_id' => 50,
                                    'sort' => 8,
                                    'name' => '备注工单',
                                    'url'  => 'afterSale/ticketRemake',
                                    'navigation' => false,
                                ],
                                8 => [
                                    'id' => 508,
                                    'parent_id' => 50,
                                    'sort' => 9,
                                    'name' => '仓库咨询-物流咨询',
                                    'url'  => 'afterSale/warehouseConsult',
                                    'navigation' => false,
                                ],
                                9 => [
                                    'id' => 2010,
                                    'parent_id' => 20,
                                    'sort' => 10,
                                    'name' => '商品编辑',
                                    'url'  => 'ECommerce/goodsEditOld',
                                    'navigation' => false,
                                ],
                                10 => [
                                    'id' => 509,
                                    'parent_id' => 50,
                                    'sort' => 11,
                                    'name' => '工单咨询查看',
                                    'url'  => 'afterSale/consultDetail',
                                    'navigation' => false,
                                ],
                                11 => [
                                    'id' => 5010,
                                    'parent_id' => 50,
                                    'sort' => 12,
                                    'name' => '工单的历史记录',
                                    'url'  => 'afterSale/viewStory',
                                    'navigation' => false,
                                ],
                                12 => [
                                    'id' => 5011,
                                    'parent_id' => 50,
                                    'sort' => 13,
                                    'name' => '商品的历史记录',
                                    'url'  => 'afterSale/viewProductStory',
                                    'navigation' => false,
                                ],
                                13 => [
                                    'id' => 5012,
                                    'parent_id' => 50,
                                    'sort' => 14,
                                    'name' => '工单详情',
                                    'url'  => 'afterSale/ticketDetail',
                                    'navigation' => false,
                                ]
                            ]
                        ],
                        1 => [
                            'id' => 51,
                            'parent_id' => 5,
                            'sort' => 2,
                            'name' => '工单归档',
                            'url'  => 'AfterSale/workOrderManager/workOrderFile',
                            'navigation' => true,
                            'child' => [
                                0 => [
                                    'id' => 510,
                                    'parent_id' => 51,
                                    'sort' => 1,
                                    'name' => '查询',
                                    'url'  => 'AfterSale/workOrderManager/workOrderFileList',
                                    'navigation' => false,
                                ],
                                1 => [
                                    'id' => 511,
                                    'parent_id' => 51,
                                    'sort' => 2,
                                    'name' => '高级查询',
                                    'url'  => 'AfterSale/workOrderManager/workOrderFileListSenior',
                                    'navigation' => false,
                                ],
                                2 => [
                                    'id' => 512,
                                    'parent_id' => 51,
                                    'sort' => 3,
                                    'name' => '导出',
                                    'url'  => 'AfterSale/workOrderManager/workOrderFileExport',
                                    'navigation' => false,
                                ],
                                3 => [
                                    'id' => 513,
                                    'parent_id' => 51,
                                    'sort' => 4,
                                    'name' => '详情',
                                    'url'  => 'AfterSale/detail',
                                    'navigation' => false,
                                ],
                                4 => [
                                    'id' => 514,
                                    'parent_id' => 51,
                                    'sort' => 5,
                                    'name' => '确认归档',
                                    'url'  => 'AfterSale/confirmArchive',
                                    'navigation' => false,
                                ],
                                5 => [
                                    'id' => 515,
                                    'parent_id' => 51,
                                    'sort' => 6,
                                    'name' => '工单咨询查看',
                                    'url'  => 'AfterSale/look',
                                    'navigation' => false,
                                ],
                            ]
                        ]
                    ]
                ],
                3 => [
                    'id' => 6,
                    'parent_id' => 0,
                    'sort' => 6,
                    'name' => '基础数据管理',
                    'url'  => '',
                    'navigation' => true,
                    'icon' => 'ic5',
                    'child' => [
                        0 => [
                            'id' => 60,
                            'parent_id' => 6,
                            'sort' => 1,
                            'name' => '仓库管理',
                            'url'  => 'AfterSale/baseData/wareManage',
                            'navigation' => true,
                            'child' => [
                                0 => [
                                    'id' => 600,
                                    'parent_id' => 60,
                                    'sort' => 1,
                                    'name' => '新增',
                                    'url'  => 'AfterSale/baseData/wareManageCreate',
                                    'navigation' => false,
                                ],
                                1 => [
                                    'id' => 601,
                                    'parent_id' => 60,
                                    'sort' => 2,
                                    'name' => '编辑',
                                    'url'  => 'AfterSale/baseData/wareManageEdit',
                                    'navigation' => false,
                                ],
                                2 => [
                                    'id' => 602,
                                    'parent_id' => 60,
                                    'sort' => 3,
                                    'name' => '查询',
                                    'url'  => 'AfterSale/baseData/wareManageList',
                                    'navigation' => false,
                                ],
                            ]
                        ],
                        1 => [
                            'id' => 61,
                            'parent_id' => 6,
                            'sort' => 2,
                            'name' => '物流管理',
                            'url'  => 'AfterSale/baseData/logManage',
                            'navigation' => true,
                            'child' => [
                                0 => [
                                    'id' => 610,
                                    'parent_id' => 61,
                                    'sort' => 1,
                                    'name' => '新增',
                                    'url'  => 'AfterSale/baseData/logManageCreate',
                                    'navigation' => false
                                ],
                                1 => [
                                    'id' => 611,
                                    'parent_id' => 61,
                                    'sort' => 2,
                                    'name' => '编辑',
                                    'url'  => 'AfterSale/baseData/logManageEdit',
                                    'navigation' => false
                                ],
                                2 => [
                                    'id' => 612,
                                    'parent_id' => 61,
                                    'sort' => 3,
                                    'name' => '查询',
                                    'url'  => 'AfterSale/baseData/logManageList',
                                    'navigation' => false
                                ]
                            ]
                        ],
                        2 => [
                            'id' => 62,
                            'parent_id' => 6,
                            'sort' => 3,
                            'name' => '电商平台管理',
                            'url'  => 'AfterSale/baseData/platformManage',
                            'navigation' => true,
                            'child' => [
                                0 => [
                                    'id' => 620,
                                    'parent_id' => 62,
                                    'sort' => 1,
                                    'name' => '新增',
                                    'url'  => 'AfterSale/baseData/platformManageCreate',
                                    'navigation' => false
                                ],
                                1 => [
                                    'id' => 621,
                                    'parent_id' => 62,
                                    'sort' => 2,
                                    'name' => '编辑',
                                    'url'  => 'AfterSale/baseData/platformManageEdit',
                                    'navigation' => false
                                ],
                                2 => [
                                    'id' => 622,
                                    'parent_id' => 62,
                                    'sort' => 3,
                                    'name' => '查询',
                                    'url'  => 'AfterSale/baseData/platformManageList',
                                    'navigation' => false
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            2 => [
                0 => [
                    'id' => 7,
                    'parent_id' => 0,
                    'sort' => 7,
                    'name' => '物流首页',
                    'url'  => 'Logistics/index',
                    'navigation' => true,
                    'icon' => 'ic1'
                ],
                1 => [
                    'id' => 8,
                    'parent_id' => 0,
                    'sort' => 7,
                    'name' => '工单管理',
                    'url'  => '',
                    'navigation' => true,
                    'icon' => 'ic4',
                    'child' => [
                        0 => [
                            'id' => 80,
                            'parent_id' => 8,
                            'sort' => 1,
                            'name' => '待接收工单',
                            'url'  => 'Logistics/receive',
                            'navigation' => true,
                            'child' => [
                                0 => [
                                    'id' => 800,
                                    'parent_id' => 80,
                                    'sort' => 1,
                                    'name' => '查询',
                                    'url'  => 'Logistics/receive_list',
                                    'navigation' => false
                                ],
                                1 => [
                                    'id' => 801,
                                    'parent_id' => 80,
                                    'sort' => 2,
                                    'name' => '接收工单',
                                    'url'  => 'Logistics/ajax_receive',
                                    'navigation' => false
                                ],
                                2 => [
                                    'id' => 802,
                                    'parent_id' => 80,
                                    'sort' => 3,
                                    'name' => '详情',
                                    'url'  => 'Logistics/detail',
                                    'navigation' => false
                                ],
                                3 => [
                                    'id' => 1003,
                                    'parent_id' => 80,
                                    'sort' => 4,
                                    'name' => '图片删除',
                                    'url'  => 'ECommerce/delPic',
                                    'navigation' => false
                                ]
                            ]
                        ],
                        1 => [
                            'id' => 81,
                            'parent_id' => 8,
                            'sort' => 2,
                            'name' => '待回复工单(紧急)',
                            'url'  => 'Logistics/reply/3',
                            'navigation' => true,
                            'child' => [
                                0 => [
                                    'id' => 810,
                                    'parent_id' => 81,
                                    'sort' => 1,
                                    'name' => '查询',
                                    'url'  => 'Logistics/reply_list',
                                    'navigation' => false
                                ],
                                1 => [
                                    'id' => 811,
                                    'parent_id' => 81,
                                    'sort' => 2,
                                    'name' => '回复工单',
                                    'url'  => 'Logistics/reply_work_order',
                                    'navigation' => false
                                ]
                            ]
                        ],
                        2 => [
                            'id' => 82,
                            'parent_id' => 8,
                            'sort' => 3,
                            'name' => '待回复工单(重要)',
                            'url'  => 'Logistics/reply/2',
                            'navigation' => true
                        ],
                        3 => [
                            'id' => 83,
                            'parent_id' => 8,
                            'sort' => 4,
                            'name' => '待回复工单(普通)',
                            'url'  => 'Logistics/reply/1',
                            'navigation' => true
                        ],
                        4 => [
                            'id' => 84,
                            'parent_id' => 8,
                            'sort' => 5,
                            'name' => '已回复工单',
                            'url'  => 'Logistics/have_reply',
                            'navigation' => true,
                            'child' => [
                                0 => [
                                    'id' => 840,
                                    'parent_id' => 84,
                                    'sort' => 1,
                                    'name' => '查询',
                                    'url'  => 'Logistics/have_reply_list',
                                    'navigation' => false
                                ]
                            ]
                        ],
                    ]
                ]
            ],
            3 => [
                0 => [
                    'id' => 9,
                    'parent_id' => 0,
                    'sort' => 8,
                    'name' => '仓库首页',
                    'url'  => 'Warehouse/index',
                    'navigation' => true,
                    'icon' => 'ic1',
                ],
                1 => [
                    'id' => 10,
                    'parent_id' => 0,
                    'sort' => 9,
                    'name' => '工单管理',
                    'url'  => '',
                    'navigation' => true,
                    'icon' => 'ic4',
                    'child' => [
                        0 => [
                            'id' => 100,
                            'parent_id' => 10,
                            'sort' => 1,
                            'name' => '待接收工单',
                            'url'  => 'Warehouse/ware_receive',
                            'navigation' => true,
                            'child' => [
                                0 => [
                                    'id' => 1000,
                                    'parent_id' => 100,
                                    'sort' => 1,
                                    'name' => '查询',
                                    'url'  => 'Warehouse/ware_receive_list',
                                    'navigation' => false
                                ],
                                1 => [
                                    'id' => 1001,
                                    'parent_id' => 100,
                                    'sort' => 2,
                                    'name' => '接收',
                                    'url'  => 'Warehouse/ware_ajax_receive',
                                    'navigation' => false
                                ],
                                2 => [
                                    'id' => 1002,
                                    'parent_id' => 100,
                                    'sort' => 3,
                                    'name' => '详情',
                                    'url'  => 'Warehouse/ware_detail',
                                    'navigation' => false
                                ],
                                3 => [
                                    'id' => 1003,
                                    'parent_id' => 100,
                                    'sort' => 4,
                                    'name' => '图片删除',
                                    'url'  => 'ECommerce/delPic',
                                    'navigation' => false
                                ]

                            ]
                        ],
                        1 => [
                            'id' => 101,
                            'parent_id' => 10,
                            'sort' => 2,
                            'name' => '待回复工单(紧急)',
                            'url'  => 'Warehouse/ware_reply/3',
                            'navigation' => true,
                            'child' => [
                                0 => [
                                    'id' => 1010,
                                    'parent_id' => 101,
                                    'sort' => 1,
                                    'name' => '回复工单',
                                    'url'  => 'Warehouse/ware_reply_work_order',
                                    'navigation' => false
                                ],
                                1 => [
                                    'id' => 1011,
                                    'parent_id' => 101,
                                    'sort' => 2,
                                    'name' => '查询',
                                    'url'  => 'Warehouse/ware_reply_list',
                                    'navigation' => false
                                ]
                            ]
                        ],
                        2 => [
                            'id' => 102,
                            'parent_id' => 10,
                            'sort' => 3,
                            'name' => '待回复工单(重要)',
                            'url'  => 'Warehouse/ware_reply/2',
                            'navigation' => true,
                        ],
                        3 => [
                            'id' => 103,
                            'parent_id' => 10,
                            'sort' => 4,
                            'name' => '待回复工单(普通)',
                            'url'  => 'Warehouse/ware_reply/1',
                            'navigation' => true,
                        ],
                        4 => [
                            'id' => 104,
                            'parent_id' => 10,
                            'sort' => 5,
                            'name' => '已回复工单',
                            'url'  => 'Warehouse/ware_have_reply',
                            'navigation' => true,
                            'child' => [
                                0 => [
                                    'id' => 1040,
                                    'parent_id' => 104,
                                    'sort' => 1,
                                    'name' => '查询',
                                    'url'  => 'Warehouse/ware_have_reply_list',
                                    'navigation' => false
                                ]
                            ]
                        ],
                    ]
                ]
            ],
            4 => [
                0 => [
                    'id' => 11,
                    'parent_id' => 0,
                    'sort' => 10,
                    'name' => '用户管理',
                    'url'  => '',
                    'navigation' => true,
                    'icon' => 'ic6',
                    'child' => [
                        0 => [
                            'id' => 12,
                            'parent_id' => 11,
                            'sort' => 1,
                            'name' => '账号管理',
                            'url'  => 'user/index',
                            'navigation' => true,
                            'child' => [
                                0 => [
                                    'id' => 120,
                                    'parent_id' => 12,
                                    'sort' => 1,
                                    'name' => '新增',
                                    'url'  => 'user/add',
                                    'navigation' => false,
                                ],
                                1 => [
                                    'id' => 121,
                                    'parent_id' => 12,
                                    'sort' => 2,
                                    'name' => '编辑',
                                    'url'  => 'user/edit',
                                    'navigation' => false,
                                ],
                                2 => [
                                    'id' => 123,
                                    'parent_id' => 12,
                                    'sort' => 3,
                                    'name' => '提交',
                                    'url'  => 'user/store',
                                    'navigation' => false,
                                ],
                                3 => [
                                    'id' => 124,
                                    'parent_id' => 12,
                                    'sort' => 4,
                                    'name' => '查询',
                                    'url'  => 'user/search',
                                    'navigation' => false,
                                ]
                            ]
                        ],
                        1 => [
                            'id' => 13,
                            'parent_id' => 11,
                            'sort' => 2,
                            'name' => '角色管理',
                            'url'  => 'role/index',
                            'navigation' => true,
                            'child' => [
                                0 => [
                                    'id' => 130,
                                    'parent_id' => 13,
                                    'sort' => 1,
                                    'name' => '新增',
                                    'url'  => 'role/add',
                                    'navigation' => false,
                                ],
                                1 => [
                                    'id' => 131,
                                    'parent_id' => 13,
                                    'sort' => 2,
                                    'name' => '编辑',
                                    'url'  => 'role/edit',
                                    'navigation' => false,
                                ],
                                2 => [
                                    'id' => 132,
                                    'parent_id' => 13,
                                    'sort' => 3,
                                    'name' => '提交',
                                    'url'  => 'role/store',
                                    'navigation' => false,
                                ],
                                3 => [
                                    'id' => 133,
                                    'parent_id' => 13,
                                    'sort' => 4,
                                    'name' => '查询',
                                    'url'  => 'role/search',
                                    'navigation' => false,
                                ],
                                4 => [
                                    'id' => 134,
                                    'parent_id' => 13,
                                    'sort' => 5,
                                    'name' => '分配权限',
                                    'url'  => 'role/permissions',
                                    'navigation' => false,
                                ],
                                5 => [
                                    'id' => 135,
                                    'parent_id' => 13,
                                    'sort' => 6,
                                    'name' => '适用对象',
                                    'url'  => 'role/apply',
                                    'navigation' => false,
                                ],
                                6 => [
                                    'id' => 136,
                                    'parent_id' => 13,
                                    'sort' => 7,
                                    'name' => '保存权限',
                                    'url'  => 'role/stroePermissions',
                                    'navigation' => false,
                                ]
                            ]
                        ],
                        2 => [
                            'id' => 14,
                            'parent_id' => 11,
                            'sort' => 3,
                            'name' => '密码修改',
                            'url'  => 'user/editPassword',
                            'navigation' => true
                        ]
                    ]
                ],
            ]
        ];
    }

    /**
     * 根据角色获取默认权限
     * @author zt6768
     * @param int $roleId 角色id
     * @return array
     */
    public static function getRoleDefaultPermissionsByRoleId($roleId)
    {
        $data = [];
        if ($roleId == 1) { //管理员
            $data = [208,2010,3,4,40,400,401,402,403,404,405,406,407,5,50,500,501,502,503,504,505,506,507,508,509,5010,5011,5012,51,510,511,512,513,514,515,6,60,600,601,602,61,610,611,612,62,620,621,622,11,12,120,121,123,124,13,130,131,132,133,134,135,136,14];
        }
        if ($roleId == 2) { //电商
            $data = [1,2,20,200,201,202,203,205,206,207,208,209,2010,2011,1003,21,210,211,212,213,214,215,11,14];
        }
        if ($roleId == 3) { //售后
            $data = [208,2010,3,4,40,400,401,402,403,404,405,406,407,5,50,500,501,502,503,504,505,506,507,508,509,5010,5011,5012,51,510,511,512,513,514,515,6,60,600,601,602,61,610,611,612,62,620,621,622,11,14];
        }
        if ($roleId == 4) { //仓库
            $data = [1003,9,10,100,1000,1001,1002,101,1010,1011,102,103,104,1040,11,14];
        }
        if ($roleId == 5) { //物流
            $data = [1003,7,8,80,800,801,802,81,810,811,82,83,84,840,11,14];
        }
        if ($roleId == 6) { //其它
            $data = [];
        }
        return $data;
    }

    /**
     * 根据节点id获取权限和菜单
     * @author zt6768
     * @param array $ids 节点id
     * @return array
     */
    public static function getPermissionsById($ids)
    {
        $permissions = [];
        $navigation = [];
        $route = self::getRoute();
        foreach ($route as $p) {
            foreach ($p as $p1) {
                if (in_array($p1['id'], $ids)) {
                    //权限
                    $permissions[$p1['id']] = $p1['url'];
                    //菜单
                    $navigation[$p1['id']]['id'] = $p1['id'];
                    $navigation[$p1['id']]['parent_id'] = $p1['parent_id'];
                    $navigation[$p1['id']]['sort'] = $p1['sort'];
                    $navigation[$p1['id']]['name'] = $p1['name'];
                    $navigation[$p1['id']]['url'] = $p1['url'];
                    $navigation[$p1['id']]['icon'] = isset($p1['icon']) ? $p1['icon'] : '';
                }
                if (isset($p1['child'])) {
                    foreach ($p1['child'] as $p2) {
                        if (in_array($p2['id'], $ids)) {
                            //权限
                            $permissions[$p2['id']] = $p2['url'];
                            //菜单
                            $navigation[$p2['id']]['id'] = $p2['id'];
                            $navigation[$p2['id']]['parent_id'] = $p2['parent_id'];
                            $navigation[$p2['id']]['sort'] = $p2['sort'];
                            $navigation[$p2['id']]['name'] = $p2['name'];
                            $navigation[$p2['id']]['url'] = $p2['url'];
                        }
                        if (isset($p2['child'])) {
                            foreach ($p2['child'] as $p3) {
                                if (in_array($p3['id'], $ids)) {
                                    //权限
                                    $permissions[$p3['id']] = $p3['url'];
                                }
                            }
                        }
                    }
                }
            }
        }
        $data = [];
        $data['permissions'] = $permissions;
        $data['navigation'] = self::listToTree($navigation, 'id', 'parent_id', 'child', 0);
        return $data;
    }

    /**
     * 返回的数据集转换成Tree
     * @author zt6768
     * @param array  $list 要转换的数据集
     * @param string $pid  标记字段
     * @param string $child 标记字段
     * @param int $root 0-父级
     * @return array
     */
    public static function listToTree($list, $pk = 'id', $pid = 'parent_id', $child = 'child', $root = 0)
    {
        $tree = array();
        if (is_array($list)) {
            //创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] = & $list[$key];
            }
            foreach ($list as $key => $data) {
                //判断是否存在parent
                $parentId =  $data[$pid];
                if ($root == $parentId) {
                    $tree[] = & $list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = & $refer[$parentId];
                        $parent[$child][] = & $list[$key];
                    } else {
                        $tree[] = & $list[$key];
                    }
                }
            }
        }
        return $tree;
    }

}