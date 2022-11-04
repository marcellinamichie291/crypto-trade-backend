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
                    'exchange_rate_step',
                    'amount'
                ],
                'required',
                'message' => 'The value cannot be empty.'
            ],
            ['amount', 'allowedAmount'],
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
        $pair = Pair::findOne(['id' => $this->pair_id]);

        if (!($this->amount >= $pair->min_amount
            && $this->amount <= $pair->max_amount)) {
            $errorMsg = 'Invalid value for the amount field for the currency pair. The value must be between '.$pair->min_amount.' and '.$pair->max_amount.'.';
            $this->addError('amount', $errorMsg);
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