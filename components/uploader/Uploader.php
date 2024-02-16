<?php

namespace app\components\uploader;

use yii\base\Component;
use yii\web\UploadedFile;
use yii\imagine\Image;


/**
 * Uploader
 * Componente Yii para control de carga de archivos en servidor
 * @version 1.01 16 de Febrero 2024 for PHP 8.0 > 8.2
 * @author Benjamin Castillo Arriaga <benjamin.castillo.arriaga@gmail.com>
 * 
 *  Usted puede consulta la libreria original del autor en: https://github.com/benjamin-castillo/yii-components/
 *  
 */
class Uploader extends Component
{

    const TEMP_DIR = '/temp';

    /**
     *  Define la carpeta donde se guardaran 
     */
    //const FILE_DIR = '/files';
    const THUMB_DIR = '/thumbnails';

    public $errors;

    /**
     * Indica si se debe conservar el nombre original del archivo, por seguridad
     * por defaul es false pero se puede cambiar en caso de ser necesario
     * @var boolean
     * @default false
     */
    public $preserveBaseName = false;

    /**
     * Indica si es necesario crear arhivo thumbnail sólo para el caso de carga de imagenes
     * @var boolean
     */
    public $thumbnail = false;
    public $fileName;
    private $fileThumbName;
    public $fileExtension;
    private $thumbnailPath;
    private $fileRoute;
    private $thumbRoute;
    private $fileType;

    /**
     * Establecer ruta donde se alojarán los archivos
     * @var type
     */
    public $basePath;

    /**
     * @var string directorio donde se almacena el archivo
     */
    private $filePath = '';

    /**
     * @var integer tamaño de la imagen
     * @default 800
     */
    public $imageBaseSize = 800;

    /**
     * @var bool por si deseamos conservar originales
     * @default false
     */
    public $preserveOriginalFile = true;

    /**
     * @var integer tamaño del thumbnail en pixeles
     * @default 128
     */
    public $thumbnailBaseSize = 128;

    /**
     * Metodo para almacenar el debug.
     */
    public $logDebug = "";

    /**
     * Metodo lógico para guardar archivos
     */
    public function save($post)
    {

        $this->basePath ? '' : die("<br>Ruta base ausente o vacia");

        $oFile = UploadedFile::getInstanceByName($post);
        // echo "<pre>";
        // var_dump($oFile);
        // echo "</pre>";

        if (empty($_FILES)) {
            $this->errors .= "No se recibió el archivo desde el POST";
            die("Error, no se recibió el archivo en el POST.");
            return;
        } else {

            if (empty($this->fileName)) { //Si no se existe nombre personalizado
                $this->fileName = $this->preserveBaseName ? $oFile->getBaseName() : date('dmy') . time() . rand();
            }

            // Obtiene nombre de archivo thumb
            $this->fileThumbName = $this->thumbnail ? $this->fileName . '_thumb' : NULL;

            //Obtiene nombre de extension
            $this->fileExtension = $oFile->getExtension();

            // Generar carpeta para archivo original
            //$this->filePath = $this->generatePath(self::FILE_DIR);
            //$this->logDebug .= "<br>Carpeta para archivo original:" . $this->filePath;


            if ($this->thumbnail === true) { // Si desean generar miniatura
                // Generar carpeta para imagen miniatura
                $this->thumbnailPath = $this->generatePath(self::THUMB_DIR);
                $this->logDebug .= "<br>Ruta minuatura:" . $this->thumbnailPath;
            }

            $this->fileRoute = $this->filePath . '/' . $this->fileName . '.' . $this->fileExtension;
            $this->logDebug .= "<br>fileRoute:" . $this->fileRoute;
            // Agrega la ruta thumb
            $this->thumbRoute = $this->thumbnailPath . '/' . $this->fileThumbName . '.' . $this->fileExtension;
            $this->logDebug .= "<br>Ruta thumb:" . $this->thumbRoute;

            // Setea extensión de archivo
            $this->fileType = $oFile->type;
            $this->logDebug .= "<br>Tipo de archivo recibido:" . $oFile->type . "";

            if (strpos($oFile->type, 'image') !== FALSE) { //Si el tipo es cualquier tipo de imágen
                try {
                    $this->saveImage($oFile);
                } catch (\Imagine\Exception\RuntimeException $e) {
                    die("<br>Se ha producido un error al intentar procesar el archivo de la imagen");
                    var_dump($e);
                    return NULL;
                }
            } else { // Si no es un archivo de imagen

                $this->saveFile($oFile);
            }

        }
    }

    /**
     *  Guarda archivo de cualquier extencion sin procesarlo
     * @param type $filePost
     */
    public function saveFile($filePost)
    {
        $tempPath = $this->getUploadPath();
        $tempImg = $tempPath . '/' . $this->fileName . '.' . $this->fileExtension;

        $this->logDebug .= "<br>Archivo a guardar:" . $tempImg;


        if ($filePost->saveAs($tempImg)) { //Crea una copia del archivo original recibido por post
            $this->logDebug .= "<br>Archivo cargado con exito en:" . $tempImg;
        } else {
            die("<br>Error no se logro guardar el archivo");
        }
    }


    /**
     *  Procesa de manera opcional las imágenes para redimensionarlas y bajar la calidad
     * Probado con los formatos png
     * 
     * @param type $filePost
     */
    public function saveImage($filePost)
    {

        if ($this->thumbnail === true) { // Si desean generar miniatura
            $tempPath = $this->getUploadPath() . $this->generatePath(self::TEMP_DIR);
            $tempImg = $tempPath . '/' . $this->fileName . '.' . $this->fileExtension;
            if ($filePost->saveAs($tempImg)) { //Crea una copia del archivo original
                //Obtiene tamaño de imagen
                $tempImgSize = $this->getImgSize($tempImg);
                //Redimeciona tamaño de imagen
                $newImgSize = $this->getNewImageSize($tempImgSize['width'], $tempImgSize['height']);

                $image = new Image(); // Crear un objeto de tipo imagen
                // redimensiona la imagen
                $image->getImagine()
                    ->open($tempImg)
                    ->thumbnail(new \Imagine\Image\Box($newImgSize['width'], $newImgSize['height']))
                    ->save($this->getUploadPath() . $this->fileRoute, ['quality' => 100]);

                if ($this->thumbnail) {
                    $newImgThumbSize = $this->getNewImageSize($tempImgSize['width'], $tempImgSize['height'], TRUE);
                    $imageThumb = new Image();
                    $imageThumb->getImagine()
                        ->open($tempImg)
                        ->thumbnail(new \Imagine\Image\Box($newImgThumbSize['width'], $newImgThumbSize['height']))
                        ->save($this->getUploadPath() . $this->thumbRoute, ['quality' => 100]);
                }

                if (!$this->preserveOriginalFile) {
                    unlink($tempImg);
                }
            }
        } else {// Si no se desea procesar la imagen
            $tempPath = $this->getUploadPath();
            $tempImg = $tempPath . '/' . $this->fileName . '.' . $this->fileExtension;

            $this->logDebug .= "<br>Archivo a guardar:" . $tempImg;


            if ($filePost->saveAs($tempImg)) { //Crea una el archivo recibido por post en el servidor
                $this->logDebug .= "<br>Archivo cargado con exito en:" . $tempImg;
            } else {
                die("<br>Error no se logro guardar el archivo en el servidor web");
            }
        }

    }

    /**
     * Obtiene cual es la parte mas larga de la imagen, para verificar la posición de la imagen.
     * Define el nuevo height y width en base a configuraciones predeterminadas o personalizadas. 
     * @param int $width
     * @param int $height
     * @param bool $thumbnail Booleano que indica si es la imagen mÃ¡s grande o thumbnail
     * @return array - ['width' => $width, 'height' => $height]
     */
    public function getNewImageSize(int $width, int $height, bool $thumbnail = false)
    {
        $base = $thumbnail ? $this->thumbnailBaseSize : $this->imageBaseSize;

        if (($height > $width) && ($height > $base)) {
            $newHeight = $base;
            $newWidth = ($width * $newHeight) / $height;
        } else if (($width > $height) && ($width > $base)) {
            $newWidth = $base;
            $newHeight = ($height * $newWidth) / $width;
        } else if (($width == $height) && (($width > $base) && ($height > $base))) {
            $newWidth = $base;
            $newHeight = $base;
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        return ['width' => $newWidth, 'height' => $newHeight];
    }

    /**
     * Obtiene las medidas de una imagen
     * @param type $imgPath
     * @return type
     */
    public function getImgSize($imgPath)
    {
        $imagine = new Image();
        $imageTemp = $imagine->getImagine()->open($imgPath);

        return [
            'width' => $imageTemp->getSize()->getWidth(),
            'height' => $imageTemp->getSize()->getHeight()
        ];
    }

    public function getUploadPath()
    {
        return $this->basePath;
    }

    public function setUploadPath(string $basePath)
    {
        $this->basePath = $basePath;
        return;
    }

    /**
     * Define si se va a crear automáticamente imagen de miniatura
     * @param Boolan $makeThumnail
     */
    public function setMakeThumbnail(bool $makeThumbnail)
    {
        $this->thumbnail = $makeThumbnail;
    }

    /**
     * Genera carpeta en base a la ruta que se pasa como parámetro
     * @param string $path // Debe ser la ruta absoluta
     * @return null
     */
    public function generatePath(string $path)
    {
        $pathTemp = $this->getUploadPath() . $path;
        if (!file_exists($pathTemp)) {
            //if (!mkdir($pathTemp, 0755, true)) {
            if (!mkdir($pathTemp, 0777, true)) { // Cambio de tipo de permiso version beta
                die("error al crear " . $pathTemp);
                return NULL;
            }
        }
        return $path;
    }


    public function setPreserveOriginalFile(bool $preserve)
    {
        $this->preserveOriginalFile = $preserve;
    }

    public function getPreserveOriginalFile()
    {
        return $this->preserveOriginalFile;
    }

    /**
     * Retorna informacion si se genera un error
     * @return null
     * */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Ingresar el nombre del archivo. No debe llevar nombre de extension
     * @param string $path
     * @return null
     */
    public function setFileName(string $filename)
    {
        $this->fileName = $filename;
    }

    /**
     * Obtiene el nombre del archivo
     * @param type $path
     * @return null
     */
    public function getFileName(string $filename)
    {
        return $this->fileName;
    }

    /**
     * Ontiene información importánte del ambiente
     * @param void
     * @return array
     */
    public function getInfoEnviroment()
    {
        $enviroment = [
            "upload_max_filesize" => ini_get('upload_max_filesize'),
            "post_max_size" => ini_get('post_max_size'),
            "memory_limit" => ini_get('memory_limit'),
            "max_execution_time" => ini_get('max_execution_time')
        ];
        return $enviroment;
    }


}