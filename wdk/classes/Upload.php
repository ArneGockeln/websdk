<?php
/**
 * Author: Arne Gockeln, Webchef
 * Date: 19.11.15
 */

namespace WebSDK;


class Upload
{
    /**
     * The $_FILES['userfile'] array
     * @var array
     */
    private $fileArray;
    /**
     * The destination filename WITHOUT extension, default is the upload file name
     * The extension will be attached depending on the files mime type
     * @var string $destName
     */
    private $destName;
    /**
     * The original filename
     * @var string $originalFilename
     */
    private $originalFilename;
    /**
     * Shall we encrypt the destination filename with md5 string?
     * default is true
     * @var bool $enableFilenameEncryption
     */
    private $enableFilenameEncryption = true;
    /**
     * Maximum filesize in MB. Defaults to 2
     * @var int $maxFileSize
     */
    private $maxFilesize = 2; // MB
    /**
     * Defaults to CFG_UPLOAD_DIR
     * @var string $uploadDir
     */
    private $uploadDir;
    /**
     * Which mime types are allowed for the upload?
     * Defaults to:
     * array(
     *  'jpg' => 'image/jpeg',
     *  'png' => 'image/png',
     *  'gif' => 'image/gif',
     * )
     * @var array $allowMimeTypes
     */
    private $allowMimeTypes;
    /**
     * We check if the destination file already exists,
     * if set to true, the destination file will be overwritten
     * default is false
     * @var bool $enableOverwriteDestFile
     */
    private $enableOverwriteDestFile = false;


    /**
     * Upload constructor.
     * @param array $file $_FILES['userfile']
     */
    public function __construct($file = null){
        if(!is_null($file) && is_array($file)){
            $this->setFileArray($file);
            $this->setUploadDir(getOption('CFG_UPLOAD_DIR'));
            $this->setAllowMimeTypes(array(
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
            ));
            $this->setEnableFilenameEncryption(true);
            $this->setEnableOverwriteDestFile(false);
            $this->setOriginalFilename(getValue($file, 'name'));
        }
    }

    /**
     * Try to upload the file
     * @return bool returns true if file is uploaded!
     * @throws \Exception
     */
    public function upload(){
        // does upload directory exist?
        if(!is_dir($this->getUploadDir())){
            throw new \Exception(_('Fehler: Upload Verzeichnis exisitert nicht!'));
        }

        // is upload directory write able?
        if(!is_writable($this->getUploadDir())){
            throw new \Exception(_('Fehler: Upload Verzeichnis ist schreibgeschützt!'));
        }

        // is upload directory equals document root?
        if($this->getUploadDir() == getDocRoot()){
            throw new \Exception(_('Fehler: Upload Verzeichnis kann nicht das Root Verzeichnis sein!'));
        }

        // Check for file upload errors
        switch ($this->fileArray['error']) {
            case UPLOAD_ERR_OK:
                // everything is fine! do nothing here
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new \Exception(_('Fehler: Es wurde keine Datei zum hochladen gefunden!'));
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new \Exception(_('Fehler: Die Datei ist größer als das Limit'));
            default:
                throw new \Exception(_('Es ist ein unbekannter Fehler aufgetreten!'));
        }

        // Check mime types
        $ext = null;
        if(function_exists('finfo_open')){
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimetype = finfo_file($finfo, getValue($this->fileArray, 'tmp_name'));
            finfo_close($finfo);
            if(!in_array($mimetype, $this->getAllowMimeTypes())){
                throw new \Exception(_('Fehler 1: Mime Typ ist nicht erlaubt!'));
            }
            $ext = strtolower(array_search($mimetype, $this->getAllowMimeTypes()));
        } else {
            // old school check php4/5
            $ext = strtolower(array_pop(explode('.', getValue($this->fileArray, 'name'))));
            if(!array_key_exists($ext, $this->allowMimeTypes)){
                throw new \Exception(_('Fehler 2: Mime Typ ist nicht erlaubt!'));
            }
        }

        // check filesize
        $filesize = filesize(getValue($this->fileArray, 'tmp_name'));
        $filesizeMB = $filesize / pow(1024, 2);
        if($filesizeMB > $this->getMaxFilesize()){
            throw new \Exception(sprintf(_('Fehler: Die Datei ist größer als das erlaubte Maximum von %s MB!'), $this->getMaxFilesize()));
        }

        // Rename file?
        $destinationName = getValue($this->fileArray, 'name');
        if(!is_null($this->getDestName()) && !is_empty($this->getDestName())){
            if(isSecureFilename($this->getDestName())){
                $destinationName = sprintf("%s.%s", $this->getDestName(), $ext);
            } else {
                throw new \Exception(_('Fehler: Der Zieldateiname enthält ungültige Zeichen!'));
            }
        }

        // Shall we encrypt the filename?
        if($this->isEnableFilenameEncryption()){
            if(!is_null($ext)){
                if(function_exists('md5')){
                    $destinationName = sprintf("%s.%s", md5($destinationName), $ext);
                } else {
                    throw new \Exception(_('Fehler: Die Funktion md5 ist nicht verfügbar!'));
                }
            } else {
                throw new \Exception(_('Fehler: Dateiendung ist null!'));
            }
        }

        // check if filename is secure
        if(!isSecureFilename($destinationName)){
            throw new \Exception(_('Fehler: Dateiname ist nicht sicher!'));
        }

        // if we do not allow overwriting destination files, check if destination file exists
        if(!$this->isEnableOverwriteDestFile()){
            if(is_file(getTrailingSlash($this->getUploadDir()) . $destinationName)){
                throw new \Exception(_('Fehler: Die Datei existiert bereits!'));
            }
        }

        // Upload
        if(!move_uploaded_file(getValue($this->fileArray, 'tmp_name'), getTrailingSlash($this->getUploadDir()) . $destinationName)){
            throw new \Exception(_('Fehler: Beim Upload ist ein unbekannter Fehler aufgetreten!'));
        }

        // finally set last destination name to get it outside of the project
        $this->setDestName($destinationName);

        return true;
    }

    /**
     * @return mixed
     */
    public function getFileArray()
    {
        return $this->fileArray;
    }

    /**
     * @param mixed $fileArray
     */
    public function setFileArray($fileArray)
    {
        $this->fileArray = $fileArray;
    }

    /**
     * @return mixed
     */
    public function getDestName()
    {
        return $this->destName;
    }

    /**
     * @param mixed $destName
     */
    public function setDestName($destName)
    {
        $this->destName = $destName;
    }

    /**
     * @return boolean
     */
    public function isEnableFilenameEncryption()
    {
        return $this->enableFilenameEncryption;
    }

    /**
     * @param boolean $enableFilenameEncryption
     */
    public function setEnableFilenameEncryption($enableFilenameEncryption)
    {
        $this->enableFilenameEncryption = $enableFilenameEncryption;
    }

    /**
     * @return int
     */
    public function getMaxFilesize()
    {
        return $this->maxFilesize;
    }

    /**
     * @param int $maxFilesize
     */
    public function setMaxFilesize($maxFilesize)
    {
        $this->maxFilesize = $maxFilesize;
    }

    /**
     * @return mixed
     */
    public function getUploadDir()
    {
        return $this->uploadDir;
    }

    /**
     * @param mixed $uploadDir
     */
    public function setUploadDir($uploadDir)
    {
        $this->uploadDir = $uploadDir;
    }

    /**
     * @return array
     */
    public function getAllowMimeTypes()
    {
        return $this->allowMimeTypes;
    }

    /**
     * @param array $allowMimeTypes
     */
    public function setAllowMimeTypes($allowMimeTypes)
    {
        $this->allowMimeTypes = $allowMimeTypes;
    }

    /**
     * @return boolean
     */
    public function isEnableOverwriteDestFile()
    {
        return $this->enableOverwriteDestFile;
    }

    /**
     * @param boolean $enableOverwriteDestFile
     */
    public function setEnableOverwriteDestFile($enableOverwriteDestFile)
    {
        $this->enableOverwriteDestFile = $enableOverwriteDestFile;
    }

    /**
     * @return string
     */
    public function getOriginalFilename()
    {
        return $this->originalFilename;
    }

    /**
     * @param string $originalFilename
     */
    public function setOriginalFilename($originalFilename)
    {
        $this->originalFilename = $originalFilename;
    }
}