<?php

namespace app\models;
use Yii;
use yii\base\Model;

class FormSearch extends Model{

	public $q;

	public function rules()
	{
		return [
			["q", "match", "pattern" => "/^[0-9a-záéíóúñ]+$/i", "message" => "Solo se aceptan letras y numeros"],
		];

	}

	public function attributeLabels()
	{
		
		return [
			'q' => "Buscar:"
		];

	}
}