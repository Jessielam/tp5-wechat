<?php

namespace app\lib\enum;

class OrderStatus
{
    // 未支付
    const UNPAY = 1;

    // 已支付
    const PAID = 2;

    // 已发货
    const DELIVERED = 3;

    // 已支付但是库存不足
    const PAID_BUT_OUT_OF_STOCK = 4;
}