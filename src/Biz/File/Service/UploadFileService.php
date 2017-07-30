<?php

namespace Biz\File\Service;

use Symfony\Component\HttpFoundation\File\File;

interface UploadFileService
{
    const GROUP_DEFAULT = 'default';

    const GROUP_USER = 'user';

    const GROUP_TMP = 'tmp';

    const SCOPE_PUBLIC = 'public';

    const SCOPE_PRIVATE = 'private';

    public function uploadFile(File $uploadFile, $group = self::GROUP_DEFAULT, $scope = self::SCOPE_PUBLIC);

    public function uploadCoverImage(File $uploadFile, $options, $group = self::GROUP_DEFAULT, $scope = self::SCOPE_PUBLIC);
}
