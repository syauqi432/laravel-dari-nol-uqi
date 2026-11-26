<?php

return [
    'paper_trading' => [
        'buy_fee_rate' => (float) env('PAPER_TRADING_BUY_FEE_RATE', 0.0015),
        'sell_fee_rate' => (float) env('PAPER_TRADING_SELL_FEE_RATE', 0.0025),
    ],
];
