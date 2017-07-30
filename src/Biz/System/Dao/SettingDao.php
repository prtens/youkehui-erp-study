<?php

namespace Biz\System\Dao;

interface SettingDao
{
    public function findByOwnerId($ownerId);

    public function deleteByNameAndOwnerId($name, $ownerId);
}
