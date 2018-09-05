<?php

//  +---------------------------------
//  微信相关配置
//  +---------------------------------

return [
    // 小程序的appid
    'app_id' => 'wx7a5b8d337dd8aad5',

    // 小程序 secret
    'app_secret' => 'bb5d370c54fb5253de7404ea078c379c',

    // 微信使用code换取用户openid及session_key的url地址
    'login_url' => "https://api.weixin.qq.com/sns/jscode2session?" .
    "appid=%s&secret=%s&js_code=%s&grant_type=authorization_code",

     // 微信获取access_token的url地址
     'access_token_url' => "https://api.weixin.qq.com/cgi-bin/token?" .
     "grant_type=client_credential&appid=%s&secret=%s",

     // 自定义 token加密的salt
     'token_salt' => 'HdouyDFjoidfdsOAjodiq',

     'token_expire_in' => 7200
];