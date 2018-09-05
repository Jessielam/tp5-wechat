<?php

return [
    // 绑定支付的APPID （必须配置，开户邮件中可查看）
    // 'app_id' => 'wx7a5b8d337dd8aad5',

    // MCHID：商户号 （必须配置，开户邮件中可查看）
    'merchant_id' => '',

    //  KEY：商户支付密钥
    'mech_key' => '',

    //APPSECRET：公众帐号secert（仅JSAPI支付的时候需要配置)
    // 'app_secret' => 'bb5d370c54fb5253de7404ea078c379c',

    'notify_url' => 'http://dev.tplay.io/api/v1/pay/notify'
];