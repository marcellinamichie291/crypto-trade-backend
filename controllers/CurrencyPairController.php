<?php

namespace app\controllers;

use app\actions\IndexAction;
use app\models\Pair;

class CurrencyPairController extends BaseApiController
{
    public $modelClass = Pair::class;

    public function actions(): array
    {
        $actions = parent::actions();
        $actions['index']['class'] = IndexAction::class;

        return $actions;
    }
}