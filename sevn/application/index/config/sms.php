<?php
// 短信平台配置
return [
    'sms_code_account' => getenv('SMS_ACCOUNT') ?: 'cs_vtvkey',
    'sms_code_key'     => getenv('SMS_KEY') ?: 'tyh12345',
    'sms_code_zone'    => getenv('SMS_ZONE') ?: '91',
];
