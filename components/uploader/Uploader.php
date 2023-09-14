<?php

namespace app\components\uploader;

use yii\base\Component;
use yii\web\UploadedFile;
use yii\imagine\Image;

/**
 *  Uploader
 *  Componente Yii para manejo de archivos
 * @version 1.0 Septiembre 2023
 * @author <benjamin.castillo.arriaga@gmail.com>
 */
class Uploader extends Component {

    const TEMP_DIR = '/temp';

    /**
     *  Define la carpeta donde se guardaran 
     */
    const FILE_DIR = '/files';
    const THUMB_DIR = '/thumbnails';

    public $errors;

    /**
     * Indica si se debe conservar el nombre original del archivo, pore seguridad
     * por defadul es false pero se puede cambiar en caso de ser necesario
     * @var boolean
     * @default false
     */
    public $preserveBaseName = false;

    /**
     * Indica si es necesario crear arhivo thumbnail
     * @var boolean
     */
    public $thumbnail = false;
    private $fileName;
    private $fileThumbName;
    private $fileExtension;
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
    public $preserveOriginalFile = false;
    
    /**
     * @var integer tamaño del thumbnail en pixeles
     * @default 128
     */
    public $thumbnailBaseSize = 128;

    /**
     * Metodo lógico para guardar archivos
     */
    public function save($post) {

        $oFile = UploadedFile::getInstanceByName($post);
        echo "<pre>";
        var_dump($oFile);
        echo "</pre>";

        if (empty($_FILES)) {
            $this->errors .= "No se recibió el archivo";
            echo "<br>No se recibió el archivo";
            return;
        } else {
            echo "<br>Si se recibio archivo";
            // Obtiene nombre de archivo
            $this->fileName = $this->preserveBaseName ? $oFile->getBaseName() : date('dmy') . time() . rand();
            // Obtiene nombre de archivo thumb
            $this->fileThumbName = $this->thumbnail ? $this->fileName . '_thumb' : NULL;
            //Obtiene nombre de extension
            $this->fileExtension = $oFile->getExtension();

            // Generar carpeta para archivo original
            $this->filePath = $this->generatePath(self::FILE_DIR);

            // Generar carpeta para imagen miniatura
            $this->thumbnailPath = $this->generatePath(self::THUMB_DIR);
            $this->fileRoute = $this->filePath . '/' . $this->fileName . '.' . $this->fileExtension;
            $this->thumbRoute = $this->thumbnailPath . '/' . $this->fileThumbName . '.' . $this->fileExtension;
            $this->fileType = $oFile->type;
            

            if (strpos($oFile->type, 'image') !== FALSE) { //Si el tipo es cualquier tipo de imágen
                try {
                    $this->saveImage($oFile);
                } catch (\Imagine\Exception\RuntimeException $e) {
                    die("<br>Se ha producido un error al intentar renderizar la imagen");
                    return NULL;
                }
            } else { // Si no es un archivo de imagen
                echo "<br>El archivo recibido no es una imagen la images es de tipo: " . $oFile->type;
                //$oFile->saveAs(Yii::getAlias('@uploads') . '/' . $this->fileRoute);
            }

        }
    }

    /**
     *  Procesa la imágen para redimensional
     * @param type $filePost
     */
    public function saveImage($filePost) {
        echo "<br>Redomensionando imagen";
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
    }

    /**
     * Obtiene cual es la parte mas larga de la imagen, para verificar la posición de la imagen.
     * Define el nuevo height y width en base a configuraciones predeterminadas o personalizadas. 
     * @param int $width
     * @param int $height
     * @param bool $thumbnail Booleano que indica si es la imagen mÃ¡s grande o thumbnail
     * @return array - ['width' => $width, 'height' => $height]
     */
    public function getNewImageSize($width, $height, $thumbnail = false) {
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
    public function getImgSize($imgPath) {
        $imagine = new Image();
        $imageTemp = $imagine->getImagine()->open($imgPath);

        return [
            'width' => $imageTemp->getSize()->getWidth(),
            'height' => $imageTemp->getSize()->getHeight()
        ];
    }

    public function getUploadPath() {
        return $this->basePath;
    }

    public function setUploadPath($basePath) {
        $this->basePath = $basePath;
        return;
    }
    
    /**
     * Define si se va a crear automáticamente imagen de miniatura
     * @param Boolan $makeThumnail
     */
    public function setMakeThumbnail($makeThumbnail){
        $this->thumbnail = $makeThumbnail; 
    }

    /**
     * Generar carpetas 
     * @param type $path
     * @return null
     */
    public function generatePath($path) {
        $pathTemp = $this->getUploadPath() . $path;
        if (!file_exists($pathTemp)) {
            if (!mkdir($pathTemp, 0755, true)) {
                die("error al crear " . $pathTemp);
                return NULL;
            }
        }
        return $path;
    }
}
