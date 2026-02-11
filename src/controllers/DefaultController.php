<?php

namespace pragmatic\cookies\controllers;

use craft\web\Controller;
use yii\web\Response;

class DefaultController extends Controller
{
    protected int|bool|array $allowAnonymous = false;

    public function actionIndex(): Response
    {
        return $this->redirect('pragmatic-cookies/general');
    }

    public function actionGeneral(): Response
    {
        return $this->renderTemplate('pragmatic-cookies/general');
    }

    public function actionOptions(): Response
    {
        return $this->renderTemplate('pragmatic-cookies/options');
    }
}
