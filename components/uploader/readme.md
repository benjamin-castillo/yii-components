# Documentación de componente Upload

## Introducción

La presente es una librería de Yii framework capaz de subir archivos al servidor
contiene algunos meotodos para redimencionar imagenes mediante parámetros consigurables.

## Librerias

Este componente requiere la siguiente libreria para poder procesar imágenes

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

## Ejemplo de uso con varios tipos de archivos

La url para entrar será "web/site/upload-all-files"

## Ejemplo para procesar imagenes

La url para entrar será "web/site/upload"

## Errores

Algunos tipos de archivos no los procesa, archivos grandes.
