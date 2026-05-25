<?php
/**
 * Documents module for HiPanel
 *
 * @link      https://hipanel.com/
 * @package   hipanel-module-document
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016-2018, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\modules\document\controllers;

use hipanel\actions\IndexAction;
use hipanel\actions\SmartCreateAction;
use hipanel\actions\SmartDeleteAction;
use hipanel\actions\SmartUpdateAction;
use hipanel\actions\ValidateFormAction;
use hipanel\actions\ViewAction;
use hipanel\base\CrudController;
use hipanel\filters\EasyAccessControl;
use hipanel\modules\document\models\Document;
use hipanel\modules\finance\actions\GenerateDocumentAction;
use hiqdev\hiart\ResponseErrorException;
use Yii;
use yii\db\BaseActiveRecord;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class DocumentController.
 */
class DocumentController extends CrudController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access-document' => [
                'class' => EasyAccessControl::class,
                'actions' => [
                    'create,import,copy'    => 'document.create',
                    'update'                => 'document.update',
                    'delete'                => 'document.delete',
                    'replace'               => 'document.replace',
                    '*'                     => 'document.read',
                ],
            ],
        ]);
    }

    public function actions()
    {
        return array_merge(parent::actions(), [
            'index' => [
                'class' => IndexAction::class,
                'data' => fn() => $this->getAdditionalData(),
                'on beforePerform' => $this->getBeforePerformClosure(),
            ],
            'generate-document' => [
                'class' => GenerateDocumentAction::class
            ],
            'create' => [
                'class' => SmartCreateAction::class,
                'success' => Yii::t('hipanel:document', 'Document was created'),
                'data' => fn() => $this->getAdditionalData(),
            ],
            'view' => [
                'class' => ViewAction::class,
                'on beforePerform' => function ($event) {
                    /** @var ViewAction $action */
                    $action = $event->sender;

                    $action->getDataProvider()->query->details()->showDeleted();
                },
                'data' => function () {
                    $data = $this->getAdditionalData();
                    $id = Yii::$app->request->get('id');
                    $fileHistory = [];
                    if ($id && Yii::$app->user->can('document.see-history')) {
                        try {
                            $fileHistory = Document::perform('get-file-history', ['id' => $id]);
                        } catch (ResponseErrorException) {
                            $fileHistory = [];
                        }
                    }
                    $data['fileHistory'] = is_array($fileHistory) ? $fileHistory : [];
                    return $data;
                },
            ],
            'update' => [
                'class' => SmartUpdateAction::class,
                'success' => Yii::t('hipanel:document', 'Document was updated'),
                'on beforeFetch' => $this->getBeforePerformClosure(),
                'data' => fn() => $this->getAdditionalData(),
            ],
            'delete' => [
                'class' => SmartDeleteAction::class,
                'success' => Yii::t('hipanel:document', 'Document was deleted'),
            ],
            'validate-single-form' => [
                'class' => ValidateFormAction::class,
                'validatedInputId' => false,
            ],
        ]);
    }

    private function getAdditionalData(): array
    {
        return [
            'states' => $this->getStateData(),
            'types' => $this->getTypeData(),
            'statuses' => $this->getStatusesData(),
        ];
    }

    public function getStateData()
    {
        return $this->getRefs('state,document', 'hipanel:document');
    }

    public function getTypeData()
    {
        return $this->getRefs('type,document', 'hipanel:document');
    }

    public function getStatusesData()
    {
        return $this->getRefs('status,document', 'hipanel:document');
    }

    private function getBeforePerformClosure(): \Closure
    {
        return function ($event) {
            /** @var ViewAction $action */
            $action = $event->sender;

            $action->getDataProvider()->query->details();
        };
    }

    public function actionReplace(int $id): Response|string
    {
        $models = Document::find()->where(['id' => $id])->details()->all();
        $model = reset($models);
        if (!$model) {
            throw new NotFoundHttpException();
        }
        $model->scenario = Document::SCENARIO_REPLACE;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                // Trigger FileBehavior to upload the file and populate file_id.
                $model->trigger(BaseActiveRecord::EVENT_BEFORE_UPDATE);

                try {
                    Document::perform('replace-file', [
                        'id'      => $model->id,
                        'file_id' => $model->file_id,
                        'reason'  => $model->reason,
                    ]);
                    Yii::$app->session->setFlash('success', Yii::t('hipanel:document', 'Document file was replaced'));

                    return $this->redirect(['@document/view', 'id' => $model->id]);
                } catch (ResponseErrorException $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('replace', ['model' => $model]);
    }

    public function actionArchive()
    {
        $response = Yii::$app->response;
        if (empty(Yii::$app->request->get())) {
            Yii::$app->getSession()->setFlash('error', Yii::t('hipanel:document', 'Filter document first'));

            return $this->redirect(Yii::$app->request->referrer);
        }
        try {
            $get = Yii::$app->request->get();
            $data = Document::perform('export', array_shift($get), ['batch' => true]);
        } catch (ResponseErrorException $e) {
            Yii::$app->getSession()->setFlash('error', Yii::t('hipanel:document', 'Error during creating archive'));

            return $this->redirect(Yii::$app->request->referrer);
        }

        $response->sendContentAsFile($data, 'archive.zip')->send();
    }

    public function actionGetCachedFile(string $uuid): Response
    {
        try {
            $response = Document::perform('get-cached-file', ['uuid' => $uuid]);
        } catch (ResponseErrorException $e) {
            return $this->asJson([
                'error' => $e->getMessage(),
            ]);
        }
        $this->response->format = Response::FORMAT_RAW;

        return $this->response->sendContentAsFile(
            $response,
            implode('.', [$uuid, 'pdf']),
            ['inline' => true, 'mimeType' => 'application/pdf']
        );
    }
}
