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

class SiteController extends Controller {

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
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
    public function actions() {
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
    public function actionIndex() {

        return $this->render('index');
    }

    public function actionTest() {

        echo "<br><br>";
        $request = Yii::$app->request;

        if ($request->isPost) {
            echo "<br>Se recibio post";
            $filePost = new Uploader();
            // Ruta para procesar todas las imagenes, dicha carpeta debe tener permisos 777
            $basePath = '/var/www/html/basic/web/uploads'; 
            $filePost->setUploadPath($basePath);
            $filePost->setMakeThumbnail(true);
//            echo "<pre>";
//            var_dump($_FILES);
//            echo "</pre>";
            $errores = $filePost->save('upload');
        } else {
            echo "<br>NO se  recibio post";
        }


//        $error = $filePost->getError();
//        if(isset($error)){
//             echo $error;die();
//        }
//        $documento = $filePost->getResult();
//        if(isset($documento))
//        { 
//
//            
//            $guiaArchivo = new GuiaArchivo();
//            $guiaArchivo->ruta = $documento['normal'];
//            if(isset($documento['thumbnail'])) {
//                $guiaArchivo->ruta_thumbnail = $documento['thumbnail'];
//            }
//            
//            $guiaArchivo->save();
//            
//            $guiaArchivoRelacion = new GuiaArchivoRelacion();
//            $guiaArchivoRelacion->guia_id = $guia->id;        
//            $guiaArchivoRelacion->guia_archivo_id = $guiaArchivo->id;
//            $guiaArchivoRelacion->save();
//                    
//            $url = Yii::getAlias('@webFiles'). $documento['normal'];
//            $message = "Mensaje de prueba de carga exitosa";
//            echo '<script>'
//                    . 'window.parent.CKEDITOR.tools.callFunction('.$funcNum.', "'.$url.'", "'.$message.'");'
//                    //.' alert("mensaje de prueba hola hoal hola hola"); '
//                    .' window.parent.ajustarGuiaID('.$guia->id.'); '
//                . '</script>';
//            exit;
//            
//
//        }

        return $this->render('_test');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin() {
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
    public function actionLogout() {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact() {
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
    public function actionAbout() {
        return $this->render('about');
    }
}
