<?php
 
namespace app\controllers;
 
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\ValidarFormulario;
use app\models\ValidarFormularioAjax;
use yii\widgets\ActiveForm;
use yii\web\Response;
use app\models\FormAlumnos;
use app\models\Alumnos;
use app\models\FormSearch;
use yii\helpers\Html;
use yii\data\Pagination;
use yii\helpers\Url;
 
class SiteController extends Controller
{   
    public function actionUpdate()
    {
        $model = new FormAlumnos;
        $msg = null;

        if($model->load(Yii::$app->request->post()))
        {

            if($model->validate())
            {

                $table = Alumnos::findOne($model->id_alumno);

                if($table)
                {

                    /* Le asignamos a cada columna su nuevo valor */
                    $table->nombre = $model->nombre;
                    $table->apellidos = $model->apellidos;
                    $table->clase = $model->clase;
                    $table->nota_final = $model->nota_final;

                    /* Llamamos al metodo update */
                    if($table->update())
                    {
                        $msg = "El alumno ha sido actualizado correctamente";

                    }
                    else
                    {
                        $msg = "El alumno no ha podido ser modificado, intentelo mas tarde";
                    }

                }
                else
                {
                    $msg = "El alumno seleccionado no ha sido encontrado ";
                }


            }
            else 
            {
                return getErrors();
            }

        }


        if(Yii::$app->request->get("id_alumno"))
        {

            $id_alumno = Html::encode($_GET["id_alumno"]);

            if( (int) $id_alumno)
            {
                /* Guardamos en table la clave primaria con el metodo
                findOne del objeto Alumno */
                $table = Alumnos::findOne($id_alumno);

                /* Validamos que el registro exista */
                if($table)
                {
                    /* Cargamos la data en los campos del formulario */
                    $model->id_alumno = $table->id_alumno;
                    $model->nombre = $table->nombre;
                    $model->apellidos = $table->apellidos;
                    $model->clase = $table->clase;
                    $model->nota_final = $table->nota_final;

                }
                else
                {
                    return $this->redirect(["site/view"]);
                }

            }
            else
            {
                return $this->redirect(["site/view"]);
            }

        }
        else
        {
            return $this->redirect(["site/view"]);
        }

        return $this->render("update", ["model" => $model, "msg" => $msg]);
    }

    public function actionDelete()
    {

        /* Si el formulario es enviado por POST */
        if(Yii::$app->request->post())
        {  
            /* Guardamos en una variable el id enviado por post con el metodo
             de Html::encode($_POST["el nombre del id"]) */
            $id_alumno = Html::encode($_POST["id_alumno"]);

            /* Validamos que el valor enviado sea entero */
            if( (int) $id_alumno)
            {

                /* Si es un entero procedemos a eliminarlo 
                Accediendo al objeto Alumnos y al metodo deleteAll el cual
                recibe 2 parametros el campo del WHERE y su valor */
                if(Alumnos::deleteAll("id_alumno=:id_alumno", [":id_alumno" => $id_alumno]))
                {
                    echo "Alumno con id $id_alumno eliminado con éxito, redireccionando ... ";
                    /* Redireccionamos a los 3 segundos con la etiqueta meta */
                    echo "<meta http-equiv='refresh' content='3; ". Url::toRoute("site/view") ."'>";

                }
                else 
                {
                    echo "En estos momentos no se puede eliminar al alumnos
                    por favor intentelo mas tarde, redireccionando ... ";
                
                /* Redireccionamos a los 3 segundos con la etiqueta meta */
                echo "<meta http-equiv='refresh' content='3; ". Url::toRoute("site/view") ."'>";
                }

            }
            else
            {
                echo "En estos momentos no se puede eliminar al alumnos
                por favor intentelo mas tarde, redireccionando ... ";
                
                /* Redireccionamos a los 3 segundos con la etiqueta meta */
                echo "<meta http-equiv='refresh' content='3; ". Url::toRoute("site/view") ."'>";

            }

        }
        else 
        {
            /* Redireccionamos a la vista se coloca dentro 
            de corchetes el nombre del controlador / la funcion */
            return $this->redirect(["site/view"]);
        }

    }

    public function actionView()
    {
       
        $form = new FormSearch;
        $search = null;

        /* Si se envia un formulario por get*/
        if($form->load(Yii::$app->request->get()))
        {
            if($form->validate())
            {
                /* Metodo encode para evitar ataques del tipo xss */
                $search = Html::encode($form->q);

                /* Realizamos la consulta */
                $table =  Alumnos::find()
                        ->where(["like", "id_alumno", $search])
                        ->orWhere(["like", "nombre", $search])
                        ->orWhere(["like", "apellidos", $search]);

                /* Clonamos al objeto table el cual tiene los registros para realizar 
                la paginacion */
                $count = clone $table;
                
                /* La paginacion revise 2 parametros:
                    
                    1. Indica el numero de registro que 
                    se mostraran por pagina 

                    2. Total de registro que tendra la consulta */
                $pages = new Pagination([
                    "pageSize" => 1,
                    "totalCount" => $count->count()

                    ]);

                /* Le pasamos al modelo la tabla con la paginacion el offset
                que sera cuantos registros muestras por paginas y el limit
                el limite de registro que mostrara */
                $model = $table
                        ->offset($pages->offset)
                        ->limit($pages->limit)
                        ->all();

            }
            else
            {

                $form->getErros();

            }
        }
        else 
        {
            /* Mostamos todos los registros de la tabla alumnos */
            $table = Alumnos::find();
            $count = clone $table;

            $pages = new Pagination([
                    "pageSize" => 1,
                    "totalCount" => $count->count(),
                ]);

            $model = $table
                    ->offset($pages->offset)
                    ->limit($pages->limit)
                    ->all();
        }


        return $this->render("view", ['model' => $model, "form" => $form, "search" => $search, "pages" => $pages]);

    }

    public function actionViewViejo()
    {
        $table = new Alumnos;
        /* Metodo que me trae todos los registro de la tabla */
        $model = $table->find()->all();

        $form = new FormSearch;

        /* Variable que contendra la busqueda */
        $search = null;

        /* Si el formulario es envio por get*/
        if($form->load(Yii::$app->request->get()))
        {
            if($form->validate())
            {
                /* Metodo encode para evitar ataques del tipo xss */
                $search = Html::encode($form->q);

                /* Consulta sql */
                $query = "SELECT * FROM alumnos WHERE id_alumno LIKE '%$search%' OR ";
                $query .= "nombre LIKE '%$search%' OR apellidos LIKE '%$search%'";

                /* Guardamos en la variable model los datos que nos traera la consulta */
                $model = $table->findBySql($query)->all();

            }
            else
            {
                $form->getErros();
            }
        }


        return $this->render("view", ['model' => $model, "form" => $form, "search" => $search]);

    }

    public function actionCreate()
    {
        $model = new FormAlumnos;
        $msg = null;

        //Si el formulario es enviado por method post
        if($model->load(Yii::$app->request->post()))
        {

            //si pasa las validaciones
            if($model->validate())
            {

                /* Con la instancia de la table alumno, en el objeto
                queda guardaa todas las columnas que tenga la tabla */
                $table = new Alumnos;

                /* Le asignamos a cada columna su valor */
                $table->nombre = $model->nombre;
                $table->apellidos = $model->apellidos;
                $table->clase = $model->clase;
                $table->nota_final = $model->nota_final;

                /* Si insertamos los datos correctamente */
                if ($table->insert())
                {
                    $msg = "Registro guardado correctamente";

                    /* Limpiamos los campos */
                    $model->nombre = null;
                    $model->apellidos = null;
                    $model->clase = null;
                    $model->nota_final = null;
                }
                else 
                {
                    $msg = "En estos no se puede registrar al alumno, intentelo mas tarde";
                }

            }
            else
            {
                $model->getErrors();
            }

        }

        return $this->render("create", ['model' => $model, 'msg' => $msg]);
    }

    public function actionSaluda($get = "Tutorial Yii")
    {
        $mensaje = "Hola Mundo"; 
        $numeros = [0, 1, 2, 3, 4, 5];
        return $this->render("saluda",
                [
                    "saluda" => $mensaje,
                    "numeros" => $numeros,
                    "get" => $get,
                ]);
    }
     
    public function actionFormulario($mensaje = null)
    {
        return $this->render("formulario", ["mensaje" => $mensaje]);
    }
     
    public function actionRequest()
    {
        $mensaje = null;
         if (isset($_REQUEST["nombre"]))
         {
             $mensaje = "Bien, has enviando tu nombre correctamente: " . $_REQUEST["nombre"];
         }
         $this->redirect(["site/formulario", "mensaje" => $mensaje]);
     }
      
     public function actionValidarformulario()
     {
  
   $model = new ValidarFormulario;
    
   if ($model->load(Yii::$app->request->post()))
   {
       if($model->validate())
             {
                 //Por ejemplo, consultar en una base de datos
             }
             else
             {
                 $model->getErrors();
             }
   }
    
         return $this->render("validarformulario", ["model" => $model]);
     }
      
     public function actionValidarformularioajax()
     {
         $model = new ValidarFormularioAjax;
         $msg = null;
          
         if ($model->load(Yii::$app->request->post()) && Yii::$app->request->isAjax)
         {
             Yii::$app->response->format = Response::FORMAT_JSON;
             return ActiveForm::validate($model);
         }
          
         if ($model->load(Yii::$app->request->post()))
         {
             if ($model->validate())
             {
                 //Por ejemplo hacer una consulta a una base de datos
                 $msg = "Enhorabuena formulario enviado correctamente";
                 $model->nombre = null;
                 $model->email = null;
             }
             else
             {
                 $model->getErrors();
             }
         }
          
         return $this->render("validarformularioajax", ['model' => $model, 'msg' => $msg]);
     }
      
     public function behaviors()
     {
         return [
             'access' => [
                 'class' => AccessControl::className(),
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
                 'class' => VerbFilter::className(),
                 'actions' => [
                     'logout' => ['post'],
                 ],
             ],
         ];
     }
  
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
  
     public function actionIndex()
     {
         return $this->render('index');
     }
  
     public function actionLogin()
     {
        if (!\Yii::$app->user->isGuest) {
             return $this->goHome();
         }
  
         $model = new LoginForm();
         if ($model->load(Yii::$app->request->post()) && $model->login()) {
             return $this->goBack();
         } else {
             return $this->render('login', [
                 'model' => $model,
             ]);
         }
     }
  
     public function actionLogout()
     {
         Yii::$app->user->logout();
  
         return $this->goHome();
     }
  
     public function actionContact()
     {
         $model = new ContactForm();
         if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
             Yii::$app->session->setFlash('contactFormSubmitted');
  
             return $this->refresh();
         } else {
             return $this->render('contact', [
                 'model' => $model,
             ]);
         }
     }
  
     public function actionAbout()
     {
         return $this->render('about');
     }
 }