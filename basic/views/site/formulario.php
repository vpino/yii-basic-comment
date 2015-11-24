<?php

/* libreria para trabajar con las url */
use yii\helpers\Url;
use yii\helpers\Html;

?>

<h1> Formulario </h1>

<h3> <?= $mensaje ?> </h3>

<!-- Crear un formulario:
    Con ::beginForm iniciamos la etiqueta <form>
    
    1. El action.
    2. Parametro el metodo que se enviaran los datos.
    3. Las clases que puede tener la equiqueta form.

    Con ::endForm() la cerramos </form>.

-->

<?= Html::beginForm(
        Url::toRoute("site/request"),
        "get",
        ['class' => 'form-inline']
        );

?>

<!-- Creamos un div con clases de bootstrap:
    1. El campo textInput recibe como parametro:
        1. EL nombre del input.
        2. El Valor.
        3. Un array con los diferente atributos o clases


-->

<div class="form-group">
    <?= Html::label("Introduce tu nombre", "nombre")?>
    <?= Html::textInput("nombre", null, ["class" => "form-control"]) ?>

</div>

<?= Html::submitButton("Enviar nombre", ["class" => "btn btn-primary"])?>

<?= Html::endForm(); ?>