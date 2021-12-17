<?php
    /*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register web routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | contains the "web" middleware group. Now create something great!
    |
    */
    Route::pattern('id', '[0-9]+');
    Route::get('/', 'HomeController@index');
    Route::get('/home', 'HomeController@home');
    Route::get('/getUserMenu', 'HomeController@getUserMenu');
    //上传图片
    Route::any('photo/upload', 'PhotoController@upload');
    Route::any('photo/upload_edit', 'PhotoController@editorUpload')->name('upload_edit');
    Route::any('photo/rakutenUpload', 'PhotoController@rakutenUpload');
    Route::group(['namespace' => 'Goods', 'prefix' => 'Goods'], function () {//路由前缀及命名空间
        //本地商品
        Route::any('local/index', 'LocalGoodsController@localIndex');                                   //本地商品首页
        Route::any('local/LocalGoodsSearch', 'LocalGoodsController@LocalGoodsSearch');                  //本地商品首页--搜索
        Route::any('local/addGoodsIndex/{id?}', 'LocalGoodsController@addGoodsIndex');                  //新增商品首页
        Route::any('local/addGoods', 'LocalGoodsController@addGoods');//保存新增商品
        Route::any('local/localGoodsCheck', 'LocalGoodsController@localGoodsCheck');//本地商品审核
        Route::any('local/localGoodsCheckOne', 'LocalGoodsController@localGoodsCheckOne');//编辑页面的商品审核
        Route::any('local/syncGoodsDetail', 'LocalGoodsController@syncGoodsDetail');
        Route::any('local/ajaxGetGoodsDetail', 'LocalGoodsController@ajaxGetGoodsDetail');//详情
        Route::any('local/goodsDel/{id?}', 'LocalGoodsController@localGoodsDel');
        Route::any('local/importGoods', 'LocalGoodsController@importProduct');
        Route::any('local/exportGoods', 'LocalGoodsController@exportLocalGoods');
        Route::any('local/editbGoods', 'LocalGoodsController@editbGoods');
        Route::any('local/synDetail/{id?}', 'LocalGoodsController@synDetail');
        Route::any('local/updatetbGoods', 'LocalGoodsController@updatetbGoods');
        Route::any('local/selectCategory', 'LocalGoodsController@selectCategory');
        Route::any('local/synchroGoods', 'LocalGoodsController@synchroGoods');
        Route::any('local/syncGoodsEdit', 'LocalGoodsController@syncGoodsEdit');
        Route::any('local/syncGoodsList{id?}', 'LocalGoodsController@syncGoodsList');
        Route::any('local/synToDraft', 'LocalGoodsController@synchronizeGoods');
        Route::any('local/selectlev', 'LocalGoodsController@selects');
        //商品分类
        Route::any('category/index', 'GoodsManage@categoryIndex');                                     //商品分类-首页
        Route::any('category/getChildCategoryById', 'GoodsManage@getChildCategoryById');               //根据父级id获取子集分类
        Route::any('category/editCategoryById', 'GoodsManage@editCategoryById');                       //编辑分类
        Route::any('category/addCategory', 'GoodsManage@addCategory');                                 //新增分类
        Route::any('category/delCategoryById', 'GoodsManage@delCategoryById');                         //删除分类
        //亚马逊
        Route::any('amazon/upcIndex', 'AmazonController@upcIndex');                                    //UPC码-首页
        Route::any('amazon/upcSearch', 'AmazonController@upcSearch');                                  //UPC码-搜索
        Route::any('amazon/upcImportIndex', 'AmazonController@upcImportIndex');                        //UPC导入页面
        Route::any('amazon/upcImport', 'AmazonController@upcImport');                                  //UPC导入页面
        Route::any('amazon/useUpcIndex/{id}', 'AmazonController@useUpcIndex');                         //UPC使用页面
        Route::any('amazon/useUpc', 'AmazonController@useUpc');                                        //UPC使用
        Route::any('amazon/editGoods', 'AmazonController@edit');
        Route::any('amazon/editSave', 'AmazonController@editSave');
        Route::any('amazon/checkAmazonGoods', 'AmazonController@checkAmazonGoods');
        Route::get('index', 'GoodsManage@index');
        Route::get('goodsCollect', 'GoodsManage@goodsCollect');
        Route::get('goodsCollect1', 'GoodsManage@goodsCollect1');
        Route::any('collectionGoods', 'GoodsManage@collectionGoods');
        Route::get('ajaxGetAllGoodsByParams', 'GoodsManage@ajaxGetAllGoodsByParams');
        Route::get('ajaxGetFirstCategory', 'GoodsManage@ajaxGetFirstCategory');
        Route::get('ajaxGetChildren', 'GoodsManage@ajaxGetChildren');
        Route::get('claimById', 'GoodsManage@claimById');
        Route::any('claimByIdPost', 'GoodsManage@claimByIdPost');
        Route::get('del', 'GoodsManage@del');
        Route::get('localIndex', 'GoodsManage@localIndex');
        Route::get('ajaxGetAllLocaGoodsByParams', 'GoodsManage@ajaxGetAllLocaGoodsByParams');
        Route::get('home', function () {
            return view('Goods.GoodsManage.home');
        });
        //乐天模块下
        Route::get('lotte/index', 'LotteController@index');
        Route::get('lotte/ajaxGetAllByParams', 'LotteController@ajaxGetAllByParams');
        Route::put('lotte/putOnSale', 'LotteController@putOnSale');//上架
        Route::get('lotte/add', 'LotteController@add');
        Route::get('lotte/edit/{id?}', 'LotteController@edit');
        Route::post('lotte/editSave', 'LotteController@editSave');
        Route::get('lotte/delete', 'LotteController@deleteLotte');
        Route::get('lotte/getCategory', 'LotteController@getCategory');
        Route::get('lotte/checkGoods', 'LotteController@checkGoods');
        //亚马逊的草稿箱
        Route::get('amazon/index', 'AmazonController@index');
        Route::get('amazon/ajaxGetAllByParams', 'AmazonController@ajaxGetAllByParams');
        Route::get('amazon/PutOnSaleById', 'AmazonController@PutOnSaleById');
        Route::get('amazon/edit', 'AmazonController@edit');
        Route::get('amazon/editSave', 'AmazonController@editSave');
        Route::get('amazon/delete/{id?}', 'AmazonController@deleteAmazonDraft');
        Route::get('amazon/add', 'AmazonController@add');
        //上架
        Route::any('amazon/amazonGoodsPutOn', 'AmazonController@amazonGoodsPutOn');
        Route::any('amazon/amazonGoodsSaleOn', 'AmazonController@amazonGoodsSaleOn');
        //商品映射
        Route::get('mapping/index', 'GoodsMappingController@mappingIndex');
        Route::get('mapping/lists', 'GoodsMappingController@mappingLists');
        Route::get('mapping/shops', 'GoodsMappingController@shops');
        Route::get('mapping/product', 'GoodsMappingController@getProduct');
        Route::post('mapping/create', 'GoodsMappingController@createProduct');
        Route::put('mapping/cancel', 'GoodsMappingController@cancelMapping');
        Route::get('mapping/getGoodsMapping', 'GoodsMappingController@getGoodsMapping');
        Route::get('mapping/getGoodsMapping', 'GoodsMappingController@getGoodsMapping');
        Route::delete('mapping/delGoodsMapping', 'GoodsMappingController@delGoodsMapping');
        Route::get('mapping/export', 'GoodsMappingController@exportLists');
        Route::post('mapping/importGoodsMapping', 'GoodsMappingController@importGoodsMapping');
        Route::get('onlineAmazon/index', 'AmazonOnlineGoodsController@index');
        Route::get('onlineAmazon/edit', 'AmazonOnlineGoodsController@editPage');
        Route::any('onlineAmazon/editSave', 'AmazonOnlineGoodsController@editSave');
        Route::get('onlineAmazon/detail', 'AmazonOnlineGoodsController@detail');
        Route::get('onlineAmazon/export', 'AmazonOnlineGoodsController@exportData');
        Route::get('onlineAmazon/PutOnSaleById', 'AmazonOnlineGoodsController@PutOnSaleById');
        Route::get('onlineAmazon/PutOffSaleById', 'AmazonOnlineGoodsController@PutOffSaleById');
        Route::any('onlineAmazon/ajaxGetAllByParams', 'AmazonOnlineGoodsController@ajaxGetAllByParams');
        Route::get('onlineRakuten/index', 'RakutenOnlineGoodsController@index');
        Route::get('onlineRakuten/edit', 'RakutenOnlineGoodsController@editPage');
        Route::get('onlineRakuten/editSave', 'RakutenOnlineGoodsController@edit');
        Route::get('onlineRakuten/detail', 'RakutenOnlineGoodsController@detail');
        Route::get('onlineRakuten/export', 'RakutenOnlineGoodsController@exportData');
        Route::get('onlineRakuten/getCategory', 'RakutenOnlineGoodsController@getCategory');
        Route::any('onlineRakuten/ajaxGetAllByParams', 'RakutenOnlineGoodsController@ajaxGetAllByParams');
        Route::put('onlineRakuten/obtained', 'RakutenOnlineGoodsController@obtained');//下架
        Route::get('onlineAmazon/getCategory', 'AmazonOnlineGoodsController@getCategory');
    });
    //物流
    Route::group(['namespace' => 'Logistics', 'prefix' => 'Logistics'], function () {//路由前缀及命名空间
        Route::get('index', 'LogWorkOrderController@index');
        Route::get('receive', 'LogWorkOrderController@receive');
        Route::get('receive_list', 'LogWorkOrderController@receiveList');
        Route::get('ajax_receive', 'LogWorkOrderController@ajaxReceive');
        Route::any('reply/{priority}', 'LogWorkOrderController@reply');
        Route::any('reply_list', 'LogWorkOrderController@replyList');
        Route::any('have_reply', 'LogWorkOrderController@haveReply');
        Route::any('have_reply_list', 'LogWorkOrderController@haveReplyList');
        Route::any('detail/{id}', 'LogWorkOrderController@detail');
        Route::any('reply_work_order', 'LogWorkOrderController@replyWorkOrder');
        Route::get('index', 'LogWorkOrderController@index');
        Route::get('receive', 'LogWorkOrderController@receive');
        Route::get('receive_list', 'LogWorkOrderController@receiveList');
        Route::get('ajax_receive', 'LogWorkOrderController@ajaxReceive');
        Route::any('reply/{priority}', 'LogWorkOrderController@reply');
        Route::any('reply_list', 'LogWorkOrderController@replyList');
        Route::any('have_reply', 'LogWorkOrderController@haveReply');
        Route::any('have_reply_list', 'LogWorkOrderController@haveReplyList');
        Route::any('detail/{id}', 'LogWorkOrderController@detail');
        Route::any('reply_work_order', 'LogWorkOrderController@replyWorkOrder');
    });
    Route::get('imgsys/{one?}/{two?}/{three?}/{four?}/{five?}/{six?}/{seven?}/{eight?}/{nine?}', 'PhotoController@imageStorageRoute');
    //电商
    Route::any('goods/index', 'Order\OrderController@goodsIndex');
    Route::any('goods/collect', 'Order\OrderController@goodsCollect');
    Route::group(['namespace' => 'BaseInfo', 'prefix' => 'base_info'], function () {

        //仓库管理
        Route::get('warehouse/index', 'WarehouseManagement@index');
        //仓库管理列表
        Route::get('warehouse/list', 'WarehouseManagement@lists')->name('base_info.warehouse.lists');
        //仓库管理删除
        Route::delete('del', 'WarehouseManagement@delete')->name('base_info.warehouse.del');
        //创建仓库页
        Route::get('create/index', 'WarehouseManagement@createIndex')->name('base_info.warehouse.create.index');
        //创建仓库页
        Route::post('create_update', 'WarehouseManagement@createOrUpdate')->name('base_info.warehouse.createOrUpdate');
        //获取仓库json
        Route::get('json', 'WarehouseManagement@getSettingWarehouseList')->name('get.warehouse.results');
        //公告首页
        Route::get('announcement/index', 'Announcement@announcementIndex')->name('base_info.announcement.index');
        //公告编辑页
        Route::get('announcement/edit', 'Announcement@editIndex')->name('base_info.announcement.editIndex');
        //公告创建修改
        Route::post('announcement/create_update', 'Announcement@createOrUpdate')->name('base_info.announcement.create_update');
        //公告列表
        Route::get('announcement/lists', 'Announcement@announcementLists')->name('base_info.announcement.lists');
        //公告删除
        Route::delete('announcement/del', 'Announcement@delete')->name('base_info.announcement.del');
        //仓库授权
        Route::put('warehouse/authorization', 'WarehouseManagement@authorization')->name('base_info.warehouse.authorization');
    });
    Route::group(['namespace' => 'Order', 'prefix' => 'order'], function () {
        Route::get('orderIndex', 'OrderController@orderIndex');//订单列表
        Route::get('orderIndexSearch', 'OrderController@orderIndexSearch');//订单列表搜索
        Route::get('orderDetails/{id}', 'OrderController@orderDetails');//订单详情
        Route::any('afterSales/index', 'afterSalesController@afterSalesIndex');
        Route::any('afterSales/ajaxGetAfterInfo', 'afterSalesController@ajaxGetAfterInfo');
        Route::any('afterSales/createPaymentOrder', 'afterSalesController@createPaymentOrder');
        Route::any('afterSales/addOrder', 'afterSalesController@addOrder');
        Route::any('afterSales/chooseOrder', 'afterSalesController@chooseOrder');
        Route::any('afterSales/Detail', 'afterSalesController@afterOrderDetail');
        Route::get('orderIndex', 'OrderController@orderIndex');//订单列表
        Route::get('orderIndexSearch', 'OrderController@orderIndexSearch');//订单列表搜索
        Route::get('orderDetails/{id}', 'OrderController@orderDetails');//订单详情
        Route::any('orderTroublesMerge', 'OrderController@orderTroublesMerge');//合并订单
        Route::any('removeOrderMerge', 'OrderController@removeOrderMerge');//取消合并
        Route::any('cancelOrderMerge', 'OrderController@cancelOrderMerge');//无需合并
        Route::get('exportOrdersInfo', 'OrderController@exportOrdersInfo');//订单导出
        Route::get('finishProblem/{id}', 'OrderController@finishProblem');//订单导出
        Route::any('saveOrder/{id}', 'OrderController@saveOrder');//订单导出
        Route::put('intercept', 'OrderController@intercept');
        Route::put('cancelOrder', 'OrderController@cancelOrder');
        Route::put('partialRefund', 'OrderController@partialRefund');
        Route::get("originalOrder", "OriginalOrderController@originalOrderIndex");//原始订单列表
        Route::get("originalOrderSearch", "OriginalOrderController@orderIndexSearch");//原始订单列表搜索
        Route::any("originalOrderImport", "OriginalOrderController@importOrder");
        Route::get("getLogistics", "OriginalOrderController@getLogistics");
        Route::get("createOriginalOrder", "OriginalOrderController@createOriginalOrderPage");
        Route::get("searchProductBySku", "OriginalOrderController@searchSku");
        Route::get("getPlatformShop", "OriginalOrderController@getPlatformShop");
        Route::post("createOriginal", "OriginalOrderController@addOne");
        Route::get("originalOrderDetail", "OriginalOrderController@orderDetail");
        Route::get('afterSaleOrder/ajaxCreate', "afterSalesController@ajaxCreateOrder");
        Route::get('afterSaleOrder/createPage', "afterSalesController@addOrder");
        Route::get('afterSaleOrder/confirmReceive', 'afterSalesController@confirmReturnReceive');
        Route::get('afterSaleOrder/cancelOrder', 'afterSalesController@cancel');
        Route::any('afterSaleOrder/getOrderNumber', 'afterSalesController@getOrderNumber');
        //待配货单页
        Route::get('pending/index', 'PendingController@pendingIndex');
        //待配货单页
        Route::get('pending/lists', 'PendingController@lists')->name('order.pending.lists');
        //配货单页
        Route::get('distribution/lists', 'DistributionController@lists')->name('order.distribution.lists');
        //待配货商品页
        Route::get('goods_desc/index', 'PendingController@goodsDescIndex')->name('order.goods_desc.index');
        //配货单商品页
        Route::get('read_goods_desc/index', 'DistributionController@readGoodsDescIndex')->name('order.read_goods_desc.index');
        //导入跟踪号
        Route::post('distribution/importTacking', 'DistributionController@importTacking')->name('order.distribution.import');
        //导出配货单
        Route::get('explode', 'DistributionController@explode')->name('order.distribution.explode');
        //配货单页
        Route::get('distribution/index', 'DistributionController@distributionIndex');
        //获取商铺
        Route::get('shops', 'DistributionController@getShops')->name('order.shops.list');
        // TODO 定时配货给仓库
        Route::get('backhaul_warehouse', 'DistributionController@backhaulWarehouse');
        //获取物流方式
        Route::get('logistics', 'PendingController@getShippingMethod')->name('order.logistics.lists');
        //生成配货单
        Route::patch('generate_distribution_order', 'PendingController@generateDistributionOrder')->name('order.generate_distribution_order.update');
    });
    //店铺管理
    Route::group(['namespace' => 'Shop', 'prefix' => 'shopManage'], function () {
        Route::any('index', 'ShopManagerController@index');
        Route::any('ajaxGetSettingShopData', 'ShopManagerController@ajaxGetSettingShopData');
        Route::any('addDefinedShop/{id?}', 'ShopManagerController@addDefinedShop');
        Route::any('checkDefinedShop/{id?}', 'ShopManagerController@checkDefinedShop');
        Route::any('deleteDefinedShop/{id?}', 'ShopManagerController@deleteDefinedShop');
        Route::any('addAmazonShop/{id?}', 'ShopManagerController@addAmazonShop');
        Route::any('addLotteShop/{id?}', 'ShopManagerController@addLotteShop');
    });
    Route::any('shopManage/deleteDefinedShop/{id}', 'Shop/ShopManagerController@deleteDefinedShop');
    Route::group(['namespace' => 'Logistics', 'prefix' => 'SettingLogistics'], function () {
        Route::get('index', 'SettingLogisticsController@index');
        Route::get('ajaxGetLogistics', 'SettingLogisticsController@ajaxGetLogistics');
        Route::any('addSelfDefinedLogistics/{id?}', 'SettingLogisticsController@addSelfDefineLogistics');
        Route::any('addSmLogistics/{id?}', 'SettingLogisticsController@addSmLogistics');
        Route::any('editSmLogistics/{id?}', 'SettingLogisticsController@editSmLogistics');
        Route::any('checkSelfDefinedLogistics/{id?}', 'SettingLogisticsController@checkSelfDefineLogistics');
    });
    //物流映射
    Route::group(['namespace' => 'Logistics', 'prefix' => 'LogisticsMapping'], function () {
        //物流映射列表
        Route::get('index', 'LogisticsMappingController@index');
        //物流映射搜索
        Route::get('search', 'LogisticsMappingController@LogisticsMappingSearch');
        //添加物流映射
        Route::any('add', 'LogisticsMappingController@addLogisticsMapping');
        //编辑查看物流映射
        Route::any('edit/{id}', 'LogisticsMappingController@editLogisticsMapping');
        //删除物流映射
        Route::any('delete/{id}', 'LogisticsMappingController@deleteLogisticsMapping');
    });

    Route::group(['namespace' => 'Exchange', 'prefix' => 'settingExchange'], function () {
        Route::any('collection', 'ExchangeController@collectionExchange');
        Route::any('exchangeIndex', 'ExchangeController@exchangeIndex');
        Route::any('ajaxGetExchangeData', 'ExchangeController@ajaxGetExchangeData');
        Route::any('exchangeAdd', 'ExchangeController@exchangeAdd');
        Route::any('addSettingCurrencyExchangeMain', 'ExchangeController@addSettingCurrencyExchangeMain');
        Route::any('exportCurrencyHistory', 'ExchangeController@exportCurrencyHistory');
    });
    //采购计划
    Route::group(['namespace' => 'Procurement', 'prefix' => 'procurement'], function () {
        Route::any('index', 'ProcurementController@index');                                            //采购计划初始页面
        Route::any('add', 'ProcurementController@add');                                                //添加采购计划页面
        Route::any('create', 'ProcurementController@create');                                          //添加采购计划
        Route::any('procurementPlanIndexSearch', 'ProcurementController@procurementPlanIndexSearch');  //采购计划搜索
        Route::any('procurementDetail/{id}', 'ProcurementController@procurementDetail');               //采购计划详情
        Route::any('procurementPlanGoods/{id}', 'ProcurementController@procurementPlanGoods');         //采购计划详情-商品信息
        Route::any('checkProcurementPlan', 'ProcurementController@checkProcurementPlan');              //审核采购计划
        Route::any('delProcurementPlan', 'ProcurementController@delProcurementPlan');                  //删除采购计划
        Route::any('getGoodsBySku', 'ProcurementController@getGoodsBySku');                            //新增-根据sku获取商品
        Route::any('getGoodsBySkuEdit', 'ProcurementController@getGoodsBySkuEdit');                    //编辑-根据sku获取商品
        Route::any('procurementPlanToOrder', 'ProcurementController@procurementPlanToOrder');          //采购计划转采购单页面
        Route::any('createProcurementOrder', 'ProcurementController@createProcurementOrder');          //添加采购单
        Route::any('editProcurementPlan/{id}', 'ProcurementController@editProcurementPlan');           //编辑采购计划
        Route::any('updateProcurementPlan', 'ProcurementController@updateProcurementPlan');            //保存编辑采购计划
        Route::any('getProcurementGoods', 'ProcurementController@getProcurementGoods');                //获取采购商品
        Route::any('ajax', 'ProcurementController@ajax');
        Route::get('getLogistics', 'ProcurementController@getLogistics');
    });
    //采购单
    Route::group(['namespace' => 'Procurement', 'prefix' => 'purchase'], function () {
        Route::any('index', 'PurchaseOrderController@index');                                            //采购单初始页面
        Route::any('purchaseOrderIndexSearch', 'PurchaseOrderController@purchaseOrderIndexSearch');      //采购单搜索
        Route::any('purchaseOrderDetail/{id}', 'PurchaseOrderController@purchaseOrderDetail');           //采购单详情
        Route::any('delPurchaseOrder', 'PurchaseOrderController@delPurchaseOrder');                      //作废采购单
        Route::any('exportPurchaseOrder', 'PurchaseOrderController@exportPurchaseOrder');                //导出采购单
        Route::any('checkPurchaseOrder', 'PurchaseOrderController@checkPurchaseOrder');                  //审核采购单
    });
    //供应商管理
    Route::group(['namespace' => 'Procurement', 'prefix' => 'supplier'], function () {
        Route::any('index', 'SupplierController@index');                                                 //供应商初始页面
        Route::any('supplierIndexSearch', 'SupplierController@supplierIndexSearch');                    //供应商搜索
        Route::any('addSupplier', 'SupplierController@addSupplier');                                    //新增供应商页面
        Route::any('createSupplier', 'SupplierController@createSupplier');                              //保存新增供应商
        Route::any('editSupplier/{id}', 'SupplierController@editSupplier');                             //编辑供应商
        Route::any('updateSupplier', 'SupplierController@updateSupplier');                              //保存编辑供应商
        Route::any('changeStatus', 'SupplierController@changeStatus');                                  //启用禁用供应商
    });
    //库存管理
    Route::group(['namespace' => 'Inventory', 'prefix' => 'inventory'], function () {
        Route::any('index', 'InventoryController@inventoryIndex');                                      //库存查询初始页面
        Route::any('inventoryIndexSearch', 'InventoryController@inventoryIndexSearch');                 //库存查询搜索
        Route::any('exportInventory', 'InventoryController@exportInventory');                           //导出采购单
        Route::any('inventoryAllocation', 'InventoryController@inventoryAllocation');                   //库存分配初始页面
        Route::any('inventoryAllocationSearch', 'InventoryController@inventoryAllocationSearch');       //库存分配搜索
        Route::any('addAllocationIndex', 'InventoryController@addAllocationIndex');                     //新增库存分配页面
        Route::any('addAllocation', 'InventoryController@addAllocation');                               //新增库存分配
        Route::any('editAllocationIndex/{id}', 'InventoryController@editAllocationIndex');              //编辑库存分配页面
        Route::any('editAllocation', 'InventoryController@editAllocation');                             //编辑库存分配
        Route::any('importAllocationIndex', 'InventoryController@importAllocationIndex');               //导入库存分配页面
        Route::any('importAllocation', 'InventoryController@importAllocation');                         //导入库存分配
    });

    Route::get('showImage', 'PhotoController@showImage');
    Route::group(['namespace' => 'Rules', 'prefix' => 'rules'], function () {
        Route::any('settingLogisticsRules/addLogicRules/{id?}', 'LogisticsRulesController@addLogicRules');
        Route::any('settingLogisticsRules/logicRulesIndex', 'LogisticsRulesController@logicRulesIndex');
        Route::any('settingLogisticsRules/ajaxGetLogicRules', 'LogisticsRulesController@ajaxGetLogicRules');
        Route::any('settingLogisticsRules/deleteLogicRules', 'LogisticsRulesController@deleteLogicRules');
        Route::any('setMergeOrder/index', 'setMergeRulesController@index');
        Route::any('setMergeOrder/addSetMergeRules', 'setMergeRulesController@addSetMergeRules');
        //订单问题规则
        Route::get('orderTroublesIndex', 'OrdersTroublesController@orderTroublesIndex');
        //订单问题搜索
        Route::get('orderTroublesSearch', 'OrdersTroublesController@orderTroublesSearch');
        //订单问题详页 编辑
        Route::any('orderTroublesDetail/{id}', 'OrdersTroublesController@orderTroublesDetail');
        //订单问题规则添加
        Route::any('orderTroublesCreated', 'OrdersTroublesController@orderTroublesCreated');
        Route::any('orderTroublesPost', 'OrdersTroublesController@orderTroublesCreatedPost');
        //订单问题删除
        Route::get('orderTroublesDelete/{id}', 'OrdersTroublesController@orderTroublesDelete');
        //仓库问题规则
        Route::get('warehouseAllocationIndex', 'WarehouseRulesController@warehouseAllocationIndex');
        //仓库问题搜索
        Route::get('warehouseAllocationSearch', 'WarehouseRulesController@warehouseAllocationSearch');
        //仓库问题详页 编辑
        Route::any('warehouseAllocationDetail/{id}', 'WarehouseRulesController@warehouseAllocationDetail');
        //仓库问题规则添加
        Route::any('warehouseAllocationCreated', 'WarehouseRulesController@warehouseAllocationCreated');
        Route::any('warehouseAllocationPost', 'WarehouseRulesController@warehouseAllocationCreatedPost');
        //仓库问题规则删除
        Route::delete('warehouseAllocationDelete/{id}', 'WarehouseRulesController@delete');
        //物流问题规则
        Route::get('logisticAllocationIndex', 'LogisticRulesController@logisticAllocationIndex');
        //物流问题搜索
        Route::get('logisticAllocationSearch', 'LogisticRulesController@logisticAllocationSearch');
        //物流问题详页 编辑
        Route::any('logisticAllocationDetail/{id}', 'LogisticRulesController@logisticAllocationDetail');
        //物流问题规则添加
        Route::any('logisticAllocationCreated', 'LogisticRulesController@logisticAllocationCreated');
        Route::any('logisticAllocationPost', 'LogisticRulesController@logisticAllocationCreatedPost');
        //物流问题规则删除
        Route::delete('logisticAllocationDelete/{id}', 'LogisticRulesController@delete');
    });

    Route::any('goods/index', 'Order\OrderController@goodsIndex');
    Route::any('goods/collect', 'Order\OrderController@goodsCollect');
    Route::group(['namespace' => 'UnitTesting', 'prefix' => 'UnitTesting'], function () {
        //mongo测试
        Route::get('mongoTest', 'UnitTestingController@mongoTest');
        Route::get('deployCheck', 'UnitTestingController@deployCheck');
        Route::get('linkTest', 'UnitTestingController@linkTest');
        Route::get('dingPush', 'UnitTestingController@dingPush');
    });


