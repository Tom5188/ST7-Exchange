<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User model class
    |--------------------------------------------------------------------------
    */

    'user_model' => 'App\Models\AdminUser',

    /*
    |--------------------------------------------------------------------------
    | Nova User resource tool class
    |--------------------------------------------------------------------------
    */

    'user_resource' => 'App\Nova\AdminUser',

    /*
    |--------------------------------------------------------------------------
    | The group associated with the resource
    |--------------------------------------------------------------------------
    */

    'role_resource_group' => 'Other',

    /*
    |--------------------------------------------------------------------------
    | Database table names
    |--------------------------------------------------------------------------
    | When using the "HasRoles" trait from this package, we need to know which
    | table should be used to retrieve your roles. We have chosen a basic
    | default value but you may easily change it to any table you like.
    */

    'table_names' => [
        'roles' => 'admin_role',

        'role_permission' => 'admin_role_permission',

        'role_user' => 'admin_role_user',
        
        'users' => 'admin_users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Permissions
    |--------------------------------------------------------------------------
    */

    'permissions' => [
        'update site-settings' => ['display_name' => '编辑站点设置', 'description' => '', 'group' => 'General'],
        
        'update nova-settings' => ['display_name' => '编辑常规设置', 'description' => '', 'group' => 'Site'],

        'view users' => ['display_name' => '查看会员账号', 'description' => '允许查看会员账号', 'group' => 'User'],
        'create users' => ['display_name' => '创建会员账号', 'description' => '允许创建会员账号', 'group' => 'User'],
        'update users' => ['display_name' => '编辑会员账号', 'description' => '允许编辑会员账号', 'group' => 'User'],
        'delete users' => ['display_name' => '删除会员账号', 'description' => '允许删除会员账号', 'group' => 'User'],
        
        'view user-reals' => ['display_name' => '查看实名管理', 'description' => '', 'group' => 'IsRealsName'],
        'create user-reals' => ['display_name' => '创建实名管理', 'description' => '', 'group' => 'IsRealsName'],
        'update user-reals' => ['display_name' => '编辑实名管理', 'description' => '', 'group' => 'IsRealsName'],
        'delete user-reals' => ['display_name' => '删除实名管理', 'description' => '', 'group' => 'IsRealsName'],

        'view user-usdtinfo' => ['display_name' => '查看会员地址', 'description' => '', 'group' => 'UserUsdtInfo'],
        'create user-usdtinfo' => ['display_name' => '创建会员地址', 'description' => '', 'group' => 'UserUsdtInfo'],
        'update user-usdtinfo' => ['display_name' => '编辑会员地址', 'description' => '', 'group' => 'UserUsdtInfo'],
        'delete user-usdtinfo' => ['display_name' => '删除会员地址', 'description' => '', 'group' => 'UserUsdtInfo'],

        // 'view user-levels' => ['display_name' => '查看会员等级', 'description' => '', 'group' => 'UserLevels'],
        // 'create user-levels' => ['display_name' => '创建会员等级', 'description' => '', 'group' => 'UserLevels'],
        // 'update user-levels' => ['display_name' => '编辑会员等级', 'description' => '', 'group' => 'UserLevels'],
        // 'delete user-levels' => ['display_name' => '删除会员等级', 'description' => '', 'group' => 'UserLevels'],

        'view currencies' => ['display_name' => '查看币种管理', 'description' => '', 'group' => 'Currency'],
        'create currencies' => ['display_name' => '创建币种管理', 'description' => '', 'group' => 'Currency'],
        'update currencies' => ['display_name' => '编辑币种管理', 'description' => '', 'group' => 'Currency'],
        'delete currencies' => ['display_name' => '删除币种管理', 'description' => '', 'group' => 'Currency'],

        'view currency-types' => ['display_name' => '查看币种类型', 'description' => '', 'group' => 'CurrencyType'],
        'create currency-types' => ['display_name' => '创建币种类型', 'description' => '', 'group' => 'CurrencyType'],
        'update currency-types' => ['display_name' => '编辑币种类型', 'description' => '', 'group' => 'CurrencyType'],
        'delete currency-types' => ['display_name' => '删除币种类型', 'description' => '', 'group' => 'CurrencyType'],

        'view currency-matches' => ['display_name' => '查看交易对', 'description' => '', 'group' => 'Counterparty'],
        'create currency-matches' => ['display_name' => '创建交易对', 'description' => '', 'group' => 'Counterparty'],
        'update currency-matches' => ['display_name' => '编辑交易对', 'description' => '', 'group' => 'Counterparty'],
        'delete currency-matches' => ['display_name' => '删除交易对', 'description' => '', 'group' => 'Counterparty'],

        'view currency-openings' => ['display_name' => '查看开盘时间', 'description' => '', 'group' => 'CurrencyOpening'],
        'create currency-openings' => ['display_name' => '创建开盘时间', 'description' => '', 'group' => 'CurrencyOpening'],
        'update currency-openings' => ['display_name' => '编辑开盘时间', 'description' => '', 'group' => 'CurrencyOpening'],
        'delete currency-openings' => ['display_name' => '删除开盘时间', 'description' => '', 'group' => 'CurrencyOpening'],

        'view currency-floatings' => ['display_name' => '查看调控设置', 'description' => '', 'group' => 'CurrencyFloating'],
        'create currency-floatings' => ['display_name' => '创建调控设置', 'description' => '', 'group' => 'CurrencyFloating'],
        'update currency-floatings' => ['display_name' => '编辑调控设置', 'description' => '', 'group' => 'CurrencyFloating'],
        'delete currency-floatings' => ['display_name' => '删除调控设置', 'description' => '', 'group' => 'CurrencyFloating'],

        'view news' => ['display_name' => '查看信息管理', 'description' => '', 'group' => 'News'],
        'create news' => ['display_name' => '创建信息管理', 'description' => '', 'group' => 'News'],
        'update news' => ['display_name' => '编辑信息管理', 'description' => '', 'group' => 'News'],
        'delete news' => ['display_name' => '删除信息管理', 'description' => '', 'group' => 'News'],

        'view messages' => ['display_name' => '查看站内消息', 'description' => '', 'group' => 'Message'],
        'create messages' => ['display_name' => '创建站内消息', 'description' => '', 'group' => 'Message'],
        'update messages' => ['display_name' => '编辑站内消息', 'description' => '', 'group' => 'Message'],
        'delete messages' => ['display_name' => '删除站内消息', 'description' => '', 'group' => 'Message'],

        'view lever-multiples' => ['display_name' => '查看倍数设置', 'description' => '', 'group' => 'LeverMultiple'],
        'create lever-multiples' => ['display_name' => '创建倍数设置', 'description' => '', 'group' => 'LeverMultiple'],
        'update lever-multiples' => ['display_name' => '编辑倍数设置', 'description' => '', 'group' => 'LeverMultiple'],
        'delete lever-multiples' => ['display_name' => '删除倍数设置', 'description' => '', 'group' => 'LeverMultiple'],

        'view lever-transactions' => ['display_name' => '查看合约订单', 'description' => '', 'group' => 'LeverTransaction'],
        'create lever-transactions' => ['display_name' => '创建合约订单', 'description' => '', 'group' => 'LeverTransaction'],
        'update lever-transactions' => ['display_name' => '编辑合约订单', 'description' => '', 'group' => 'LeverTransaction'],
        'delete lever-transactions' => ['display_name' => '删除合约订单', 'description' => '', 'group' => 'LeverTransaction'],

        // 'view lever-transaction-simulations' => ['display_name' => '查看模拟合约', 'description' => '', 'group' => 'TransactionsSimulations'],
        // 'create lever-transaction-simulations' => ['display_name' => '创建模拟合约', 'description' => '', 'group' => 'TransactionsSimulations'],
        // 'update lever-transaction-simulations' => ['display_name' => '编辑模拟合约', 'description' => '', 'group' => 'TransactionsSimulations'],
        // 'delete lever-transaction-simulations' => ['display_name' => '删除模拟合约', 'description' => '', 'group' => 'TransactionsSimulations'],

        'view micro-orders' => ['display_name' => '查看秒合约订单', 'description' => '', 'group' => 'SecondDeal'],
        'create micro-orders' => ['display_name' => '创建秒合约订单', 'description' => '', 'group' => 'SecondDeal'],
        'update micro-orders' => ['display_name' => '编辑秒合约订单', 'description' => '', 'group' => 'SecondDeal'],
        'delete micro-orders' => ['display_name' => '删除秒合约订单', 'description' => '', 'group' => 'SecondDeal'],

        // 'view micro-order-simulations' => ['display_name' => '查看模拟秒合约', 'description' => '', 'group' => 'MicroSimulations'],
        // 'create micro-order-simulations' => ['display_name' => '创建模拟秒合约', 'description' => '', 'group' => 'MicroSimulations'],
        // 'update micro-order-simulations' => ['display_name' => '编辑模拟秒合约', 'description' => '', 'group' => 'MicroSimulations'],
        // 'delete micro-order-simulations' => ['display_name' => '删除模拟秒合约', 'description' => '', 'group' => 'MicroSimulations'],

        'view micro-seconds' => ['display_name' => '查看秒数设置', 'description' => '', 'group' => 'MicroSecond'],
        'create micro-seconds' => ['display_name' => '创建秒数设置', 'description' => '', 'group' => 'MicroSecond'],
        'update micro-seconds' => ['display_name' => '编辑秒数设置', 'description' => '', 'group' => 'MicroSecond'],
        'delete micro-seconds' => ['display_name' => '删除秒数设置', 'description' => '', 'group' => 'MicroSecond'],

        'view users-wallets' => ['display_name' => '查看会员钱包', 'description' => '', 'group' => 'Users Wallets'],
        'create users-wallets' => ['display_name' => '创建会员钱包', 'description' => '', 'group' => 'Users Wallets'],
        'update users-wallets' => ['display_name' => '编辑会员钱包', 'description' => '', 'group' => 'Users Wallets'],
        'delete users-wallets' => ['display_name' => '删除会员钱包', 'description' => '', 'group' => 'Users Wallets'],

        'view account-logs' => ['display_name' => '查看财务记录', 'description' => '', 'group' => 'AccountLog'],
        'create account-logs' => ['display_name' => '创建财务记录', 'description' => '', 'group' => 'AccountLog'],
        'update account-logs' => ['display_name' => '编辑财务记录', 'description' => '', 'group' => 'AccountLog'],
        'delete account-logs' => ['display_name' => '删除财务记录', 'description' => '', 'group' => 'AccountLog'],

        'view projects' => ['display_name' => '查看理财管理', 'description' => '', 'group' => 'Projects'],
        'create projects' => ['display_name' => '创建理财管理', 'description' => '', 'group' => 'Projects'],
        'update projects' => ['display_name' => '编辑理财管理', 'description' => '', 'group' => 'Projects'],
        'delete projects' => ['display_name' => '删除理财管理', 'description' => '', 'group' => 'Projects'],

        'view project-orders' => ['display_name' => '查看理财订单', 'description' => '', 'group' => 'Project Orders'],
        'create project-orders' => ['display_name' => '创建理财订单', 'description' => '', 'group' => 'Project Orders'],
        'update project-orders' => ['display_name' => '编辑理财订单', 'description' => '', 'group' => 'Project Orders'],
        'delete project-orders' => ['display_name' => '删除理财订单', 'description' => '', 'group' => 'Project Orders'],

        'view borrows' => ['display_name' => '查看借贷设置', 'description' => '', 'group' => 'Borrows'],
        'create borrows' => ['display_name' => '创建借贷设置', 'description' => '', 'group' => 'Borrows'],
        'update borrows' => ['display_name' => '编辑借贷设置', 'description' => '', 'group' => 'Borrows'],
        'delete borrows' => ['display_name' => '删除借贷设置', 'description' => '', 'group' => 'Borrows'],

        'view borrow-orders' => ['display_name' => '查看借贷订单', 'description' => '', 'group' => 'Borrow Orders'],
        'create borrow-orders' => ['display_name' => '创建借贷订单', 'description' => '', 'group' => 'Borrow Orders'],
        'update borrow-orders' => ['display_name' => '编辑借贷订单', 'description' => '', 'group' => 'Borrow Orders'],
        'delete borrow-orders' => ['display_name' => '删除借贷订单', 'description' => '', 'group' => 'Borrow Orders'],

        // 'view wire-transfer-currencies' => ['display_name' => '查看电汇币种设置', 'description' => '', 'group' => 'TopUpSet'],
        // 'create wire-transfer-currencies' => ['display_name' => '创建电汇币种设置', 'description' => '', 'group' => 'TopUpSet'],
        // 'update wire-transfer-currencies' => ['display_name' => '编辑电汇币种设置', 'description' => '', 'group' => 'TopUpSet'],
        // 'delete wire-transfer-currencies' => ['display_name' => '删除电汇币种设置', 'description' => '', 'group' => 'TopUpSet'],

        // 'view wire-transfer-accounts' => ['display_name' => '查看电汇信息设置', 'description' => '', 'group' => 'TopUpSet'],
        // 'create wire-transfer-accounts' => ['display_name' => '创建电汇信息设置', 'description' => '', 'group' => 'TopUpSet'],
        // 'update wire-transfer-accounts' => ['display_name' => '编辑电汇信息设置', 'description' => '', 'group' => 'TopUpSet'],
        // 'delete wire-transfer-accounts' => ['display_name' => '删除电汇信息设置', 'description' => '', 'group' => 'TopUpSet'],

        'view digital-currency-addresses' => ['display_name' => '查看充值设置', 'description' => '', 'group' => 'DigitalCurrencyAddress'],
        'create digital-currency-addresses' => ['display_name' => '创建充值设置', 'description' => '', 'group' => 'DigitalCurrencyAddress'],
        'update digital-currency-addresses' => ['display_name' => '编辑充值设置', 'description' => '', 'group' => 'DigitalCurrencyAddress'],
        'delete digital-currency-addresses' => ['display_name' => '删除充值设置', 'description' => '', 'group' => 'DigitalCurrencyAddress'],

        'view charge-reqs' => ['display_name' => '查看充值订单', 'description' => '', 'group' => 'Recharge'],
        'create charge-reqs' => ['display_name' => '创建充值订单', 'description' => '', 'group' => 'Recharge'],
        'update charge-reqs' => ['display_name' => '编辑充值订单', 'description' => '', 'group' => 'Recharge'],
        'delete charge-reqs' => ['display_name' => '删除充值订单', 'description' => '', 'group' => 'Recharge'],

        // 'view charge-req-banks' => ['display_name' => '查看银行卡充值', 'description' => '', 'group' => 'TopUpSet'],
        // 'create charge-req-banks' => ['display_name' => '创建银行卡充值', 'description' => '', 'group' => 'TopUpSet'],
        // 'update charge-req-banks' => ['display_name' => '编辑银行卡充值', 'description' => '', 'group' => 'TopUpSet'],
        // 'delete charge-req-banks' => ['display_name' => '删除银行卡充值', 'description' => '', 'group' => 'TopUpSet'],

        'view digital-currency-sets' => ['display_name' => '查看提款设置', 'description' => '', 'group' => 'DigitalCurrencySet'],
        'create digital-currency-sets' => ['display_name' => '创建提款设置', 'description' => '', 'group' => 'DigitalCurrencySet'],
        'update digital-currency-sets' => ['display_name' => '编辑提款设置', 'description' => '', 'group' => 'DigitalCurrencySet'],
        'delete digital-currency-sets' => ['display_name' => '删除提款设置', 'description' => '', 'group' => 'DigitalCurrencySet'],

        // 'view digital-bank-sets' => ['display_name' => '查看法币设置', 'description' => '', 'group' => 'ExtractSet'],
        // 'create digital-bank-sets' => ['display_name' => '创建法币设置', 'description' => '', 'group' => 'ExtractSet'],
        // 'update digital-bank-sets' => ['display_name' => '编辑法币设置', 'description' => '', 'group' => 'ExtractSet'],
        // 'delete digital-bank-sets' => ['display_name' => '删除法币设置', 'description' => '', 'group' => 'ExtractSet'],

        'view users-wallet-outs' => ['display_name' => '查看提款订单', 'description' => '', 'group' => 'UsersWalletOut'],
        'create users-wallet-outs' => ['display_name' => '创建提款订单', 'description' => '', 'group' => 'UsersWalletOut'],
        'update users-wallet-outs' => ['display_name' => '编辑提款订单', 'description' => '', 'group' => 'UsersWalletOut'],
        'delete users-wallet-outs' => ['display_name' => '删除提款订单', 'description' => '', 'group' => 'UsersWalletOut'],

        // 'view users-wallet-out-banks' => ['display_name' => '查看银行卡提款', 'description' => '', 'group' => 'ExtractSet'],
        // 'create users-wallet-out-banks' => ['display_name' => '创建银行卡提款', 'description' => '', 'group' => 'ExtractSet'],
        // 'update users-wallet-out-banks' => ['display_name' => '编辑银行卡提款', 'description' => '', 'group' => 'ExtractSet'],
        // 'delete users-wallet-out-banks' => ['display_name' => '删除银行卡提款', 'description' => '', 'group' => 'ExtractSet'],

        'view admin-users' => ['display_name' => '查看管理账号', 'description' => '', 'group' => 'Admin'],
        'create admin-users' => ['display_name' => '创建管理账号', 'description' => '', 'group' => 'Admin'],
        'update admin-users' => ['display_name' => '编辑管理账号', 'description' => '', 'group' => 'Admin'],
        'delete admin-users' => ['display_name' => '删除管理账号', 'description' => '', 'group' => 'Admin'],

        'view roles' => ['display_name' => '查看权限', 'description' => '', 'group' => 'Role'],
        'create roles' => ['display_name' => '创建权限', 'description' => '', 'group' => 'Role'],
        'update roles' => ['display_name' => '编辑权限', 'description' => '', 'group' => 'Role'],
        'delete roles' => ['display_name' => '删除权限', 'description' => '', 'group' => 'Role'],
    ],
];
