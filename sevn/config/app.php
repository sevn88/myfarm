<?php

return [
    // 应用调试模式
    'app_debug'                 => true,
    // 应用Trace调试
    'app_trace'                 => false,
    // 0按名称成对解析 1按顺序解析
    'url_param_type'            => 1,
    // 当前 ThinkAdmin 版本号
    'thinkadmin_ver'            => 'v5',

    'empty_controller'          => 'Error',

    'pwd_str'                   => '!qws6F!xffD2vx80?95jt',  //盐

    'pwd_error_num'             => 10,    //密码连续错误次数

    'allow_login_min'           => 5,     //密码连续错误达到次数后的冷却时间，分钟

    'default_filter'            => 'trim',

    'version'=>'202108',  //版本号



    //下面都是新加
    // 是否开启多语言
    'lang_switch_on'         => true,
    'default_lang'              => 'en-us',
    
    //自动侦测语言
    'lang_auto_detect'       => true,
    // 默认语言切换变量
    'VAR_LANGUAGE'           => 'lang',
];
