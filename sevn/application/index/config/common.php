<?php
// 佣金比例等通用配置
return [
    'commission' => getenv('COMMISSION') ?: '10.00',
];
