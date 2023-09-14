<?php
 use yii\helpers\Html;

echo Html::beginForm([''],
        'post', ['enctype' => 'multipart/form-data']
);
echo Html::input('File', 'upload');

echo Html::submitButton('Cargar', ['class' => 'btn btn-primary']);

echo Html::endForm();
?>
