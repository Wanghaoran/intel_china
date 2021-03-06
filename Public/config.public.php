<?php
if (!defined('THINK_PATH')) exit();
return array(
    'SHOW_PAGE_TRACE' =>false, // 页面Trace信息
    'OUTPUT_ENCODE' => true, // 页面压缩输出

    'LANG_SWITCH_ON' => true,  //开启语言包
    'DEFAULT_LANG' => 'zh-cn', // 默认语言

    'TMPL_L_DELIM' => '<-{',  //模板左分隔符
    'TMPL_R_DELIM' => '}->',  //模板右分隔符

    'DB_TYPE' => 'mysql',  //数据库类型
    'DB_HOST' => 'localhost',  //服务器地址
    'DB_NAME' => 'intelchina',  //数据库名
    'DB_USER' => 'root',  //用户名
    'DB_PWD' => 'jilexingqiu',  //密码
    'DB_PORT' => '3306',  //端口
    'DB_PREFIX' => 'intel_',  //数据库表前缀

    'WB_AKEY' => '1077506463',
    'WB_SKEY' => '9abc7f6e37a4811d37c1871f91ba7d4e',
    'WB_CALLBACK_URL' => 'http://www.cnhtk.cn/intel_china/index/shows',

    'UPLOAD_PATH' => './Upload', //文件上传地址
    'TMPL_PARSE_STRING'  =>array(
        '__UPLOAD__' => __ROOT__ . '/Upload',
    ),

);