<?php

namespace Biz\File\Service\Impl;

use AppBundle\Common\FileToolkit;
use AppBundle\Common\StringToolkit;
use Biz\Common\Exception\InvalidArgumentException;
use Biz\File\Service\UploadFileService;
use Codeages\Biz\Framework\Service\BaseService;
use Symfony\Component\HttpFoundation\File\File;

class UploadFileServiceImpl extends BaseService implements UploadFileService
{
    public function uploadFile(File $uploadFile, $group = self::GROUP_DEFAULT, $scope = self::SCOPE_PUBLIC)
    {
        $extension = FileToolkit::getFileExtension($uploadFile);
        $filename = FileToolkit::generateFilename($extension);
        $relativeDir = $this->generateRelativeSaveDir($group);

        $uploadFile->move($this->getUploadDir($scope).'/'.$relativeDir, $filename);

        return $this->generateFileUri($scope, $relativeDir.'/'.$filename);
    }

    public function uploadCoverImage(File $uploadFile, $options = array(), $group = self::GROUP_DEFAULT, $scope = self::SCOPE_PUBLIC)
    {
        $options = array_merge($options, array(
            'x' => '0',
            'y' => '0',
            'w' => '600',
            'h' => '600',
            'width' => '600',
            'height' => '600',
            'imgs' => array(
                'large' => array('600', '600'),
                'medium' => array('180', '180'),
                'small' => array('48', '48'),
            ),
        ));

        $fileUri = $this->uploadFile($uploadFile, $group, $scope);
        $parsed = $this->parseFileUri($fileUri);

        $filePaths = FileToolKit::cropImage($parsed['fullpath'], $options);
        foreach ($filePaths as &$filePath) {
            $filePath = $this->generateFileUri($scope, $filePath);
        }

        return $filePaths;
    }

    public function parseFileUri($fileUri)
    {
        $parsed = [];
        $parts = explode('://', $fileUri);
        if (empty($parts) || count($parts) != 2) {
            throw new InvalidArgumentException(sprintf('fileUri parsed failed: %s', $fileUri));
        }

        $parsed['scope'] = $parts[0];
        $parsed['path'] = $parts[1];
        $parsed['filename'] = basename($parsed['path']);
        $parsed['ext'] = substr(strrchr($parsed['path'], '.'), 1);

        $parsed['fullpath'] = realpath($this->getUploadDir($parsed['scope']).'/'.$parsed['path']);

        return $parsed;
    }

    protected function generateRelativeSaveDir($group)
    {
        return trim($group, '/').'/'.date('Y').'/'.date('m-d');
    }

    protected function generateFileUri($scope, $filePath)
    {
        $baseUploadDir = realpath($this->getUploadDir($scope)).'/';

        return $scope.'://'.StringToolkit::ltrimString($filePath, $baseUploadDir);
    }

    protected function getUploadDir($scope)
    {
        if ($scope == self::SCOPE_PRIVATE) {
            return $this->biz['upload.private_directory'];
        } else {
            return $this->biz['upload.public_directory'];
        }
    }
}
