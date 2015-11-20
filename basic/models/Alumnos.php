<?php

namespace app\models;
use Yii;
use yii\db\ActiveRecord;

class Alumnos extends ActiveRecord{

	public static function getDb()
	{
		//Acceder a los datos de la conexion con la base de datos
		return Yii::$app->db;

	}

	public static function tableName()
	{
		return 'alumnos';
	}




}