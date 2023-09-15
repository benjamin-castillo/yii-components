# Documentación de componente Upload

## Introducción
La presente es una librería de Yii framework capaz de subir imagenes al servidor
y redimencionarlas mediante parámetros consigurables, hasta la version 1.0.

## Librerias
Este componente requiere la siguiente libreria

```
"yiisoft/yii2-imagine": "*"
```

## Creación de carpeta de publicación

Para los ejemplos debe tener la siguiente carpeta. "uploads" dentro de la ruta de
su proyecto /web/uploads puede usar el comando.

```
mkdir /var/www/html/yiicomponents/web/uploads/
```
Debe dar permiso de escritura a a la carpeta, 

Ejemplo:

```
 chmod -R 777 /var/www/html/yiicomponents/web/uploads/ 
```

## Ejemplo de uso

La url para entrar será "web/site/upload"

En la vista encontrará el formulario:

"views/site/_upload"

En el controller "SiteController.php" y dentro del metodo "actionUpload()" se
encuentra el ejemplo de uso. 




## Errores

Algunos tipos de archivos no los procesa, archivos grandes.