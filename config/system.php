<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2018 http://www.donglixia.net All rights reserved.
// +----------------------------------------------------------------------
// | Author: 十万马 <962863675@qq.com>
// +----------------------------------------------------------------------
// | DateTime: 2019/2/28 14:13
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 接口密钥设置
// +----------------------------------------------------------------------

return [
    // 当前环境 or 版本：dev; test; product
    'version' => 'product',

     //当前域名
    'domain'            => [
        'dev'       => 'http://ts-www.junshi.cm',
        'test'       => 'http://www.szsjunshi.com',
        'product'   => 'http://www.szsjunshi.com'
    ],

    // 密码加盐
    'default_salt'           => 'dlx',


];
