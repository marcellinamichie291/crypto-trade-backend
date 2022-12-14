<?php

namespace app\models;

use app\helpers\FunctionBox;
use yii\db\ActiveQuery;
use \yii\db\ActiveRecord;

class TradingLine extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'trading_line';
    }

    public function rules(): array
    {
        return [
            ['user_id', 'default', 'value' => FunctionBox::getIdentityId()],
            [
                [
                    'pair_id',
                    'exchange_id',
                    'sell_rate_step',
                    'buy_rate_step',
                    'first_order_amount'
                ],
                'required',
                'message' => 'The value cannot be empty.'
            ],
            [
                [
                    'commission_taker_percent',
                    'commission_maker_percent',
                ],
                'safe'
            ],
            ['first_order_amount', 'allowedAmount'],
            ['is_stopped', 'boolean', 'message' => 'This boolean value.']
        ];
    }

    public function extraFields(): array
    {
        return [
            'pair',
            'exchangePair',
            'exchangeRate',
            'currentOrders',
        ];
    }

    public function allowedAmount()
    {
        $pair = ExchangePair::findOne(['pair_id' => $this->pair_id]);

        if ($pair->min_amount && !($this->first_order_amount >= $pair->min_amount)) {
            $errorMsg = 'Некорректное значение поля. Значение должно быть больше '.$pair->min_amount;
            $this->addError('first_order_amount', $errorMsg);
        }

        if ($pair->max_amount && !($this->first_order_amount <= $pair->max_amount)) {
            $errorMsg = 'Некорректное значение поля. Значение должно быть меньше '.$pair->max_amount;
            $this->addError('first_order_amount', $errorMsg);
        }
    }

    public function getPair(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Pair::class, ['id' => 'pair_id']);
    }

    public function getExchangePair(): \yii\db\ActiveQuery
    {
        return $this->hasOne(ExchangePair::class, [
            'pair_id' => 'pair_id',
            'exchange_id' => 'exchange_id',
        ]);
    }

    public function getExchangeRate(): \yii\db\ActiveQuery
    {
        return $this->hasOne(ExchangeRate::class, [
                'pair_id' => 'pair_id',
                'exchange_id' => 'exchange_id',
            ])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(1);
    }

    public function getCurrentOrders(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Order::class, ['trading_line_id' => 'id'])
            ->onCondition(['is_canceled' => false, 'is_continued' => false]);
    }
}