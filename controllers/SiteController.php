<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

use app\components\uploader\Uploader;
use yii\web\Request;

class SiteController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {

        return $this->render('index');
    }

    public function actionUploadImage()
    {
        echo "<br><br>";
        echo "<h1>Ejemplo para procesar imagenes</h1>";
        $request = Yii::$app->request;
        echo "<br>Variables relacionadas con configuración de servidor";
        echo "<br>Tamaño máximo de carga de archivos: (upload_max_filesize)" . ini_get('upload_max_filesize');
        echo "<br>Tamaño máximo de recepción de archivos (post_max_size): " . ini_get('post_max_size');
        echo "<br>Limite de memoria (memory_limit): " . ini_get('memory_limit');
        echo "<br>Tiempo maximo de ejecución (max_execution_time): " . ini_get('max_execution_time');



        if ($request->isPost) {
            echo "<br>Se recibio post";
            $filePost = new Uploader();
            // Ruta para procesar todas las imagenes, dicha carpeta debe tener permisos 777
            $basePath = '/var/www/html/yiicomponents/web/uploads';
            $filePost->setUploadPath($basePath);
            $filePost->setMakeThumbnail(false);
            //            echo "<pre>";
//            var_dump($_FILES);
//            echo "</pre>";
            $errores = $filePost->save('upload');

            if (empty($errores)) {
                echo "<br>Archivo procesado con exito:";
                echo "<br>Carpeta de archivos procesados:" . $basePath . "/files/";
                echo "<br>Nombre de archivo procesado:" . $filePost->fileName . "." . $filePost->fileExtension;
            } else {
                echo "<br>No se logro cargar el archivo";
            }

        } else {
            echo "<br>NO se  recibio post";
        }

        return $this->render('_upload_examaple');
    }


    public function actionUploadAllFiles()
    {
        echo "<br><br>";
        echo "<h1>Ejemplo para procesar varios tipos de archivos</h1>";
        $request = Yii::$app->request;
        echo "<br>Variables relacionadas con configuración de servidor";
        echo "<br>Tamaño máximo de carga de archivos: (upload_max_filesize)" . ini_get('upload_max_filesize');
        echo "<br>Tamaño máximo de recepción de archivos (post_max_size): " . ini_get('post_max_size');
        echo "<br>Limite de memoria (memory_limit): " . ini_get('memory_limit');
        echo "<br>Tiempo maximo de ejecución (max_execution_time): " . ini_get('max_execution_time');


        if ($request->isPost) {
            echo "<br>Se recibio post";
            $filePost = new Uploader();
            // Ruta para procesar todas las imagenes, dicha carpeta debe tener permisos 777
            $basePath = '/var/www/html/yiicomponents/web/uploads';
            $filePost->setUploadPath($basePath);
            $filePost->setPreserveOriginalFile(false);
            $filePost->setMakeThumbnail(false);
            $filePost->setFileName("chuchito2");
            //$filePost->setMakeThumbnail(true);
            //            echo "<pre>";
//            var_dump($_FILES);
//            echo "</pre>";
            $errores = $filePost->save('upload');

            echo $filePost->logDebug;

            if (empty($errores)) {
                echo "<br>Archivo procesado con exito:";
                echo "<br>Carpeta de archivos procesados:" . $basePath . "/files/";
                echo "<br>Nombre de archivo procesado:" . $filePost->fileName . "." . $filePost->fileExtension;
            } else {
                echo "<br>No se logro cargar el archivo";
            }

        } else {
            echo "<br>NO se  recibio post";
        }

        return $this->render('_upload_examaple');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}