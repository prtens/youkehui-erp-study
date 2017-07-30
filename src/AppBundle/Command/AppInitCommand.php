<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Biz\User\CurrentUser;

class AppInitCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('app:init')
            ->setDescription('Init application.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('====Init Application====');

        $this->initAdminAccount($output);
        $this->initPhpCsFixerHook($output);
        $this->initRole($output);
        $this->initSetting($output);

        $output->writeln('====Init Finished====');
    }

    protected function initAdminAccount($output)
    {
        $regions = array(
            array('id' => '1', 'parent_id' => '0', 'name' => '浙江', 'seq' => 0, 'depth' => 1),
            array('id' => '2', 'parent_id' => '1', 'name' => '杭州', 'seq' => 2, 'depth' => 2),
        );

        foreach ($regions as $region) {
            $this->getRegionService()->createRegion($region);
        }

        $registration = array(
            'id' => 1,
            'nickname' => 'admin',
            'type' => 'alias',
            'mobile' => '13612345678',
            'password' => 'ceshi',
            'roles' => array('ROLE_SUPER_ADMIN'),
            'region_id' => 2,
            'login_ip' => '127.0.0.1',
        );

        try {
            $this->getUserService()->register($registration);
            $output->writeln(array(
                "<info>Admin Username: {$registration['nickname']}</info>",
                "<info>Admin Password: {$registration['password']}</info>",
            ));
        } catch (\Exception $e) {
            $output->writeln('<info>'.$e->getMessage().', skipped</info>');
        }

        $biz = $this->getBiz();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($registration);
        $biz['user'] = $currentUser;
    }

    protected function initRole($output)
    {
        $roles = array(
            array('name' => '超级管理员', 'code' => 'ROLE_SUPER_ADMIN', 'access_rules' => array(), 'owner_id' => 1, 'is_system' => 1),
            array('name' => '加盟商主账号', 'code' => 'ROLE_ADMIN', 'access_rules' => array(), 'owner_id' => 1, 'is_system' => 1),
        );

        foreach ($roles as $role) {
            try {
                $role = $this->getRoleService()->createRole($role);
                $output->writeln(array(
                    "<info>初始化角色成功: {$role['name']} {$role['code']}</info>",
                ));
            } catch (\Exception $e) {
                $output->writeln('<info>'.$e->getMessage().', skipped</info>');
            }
        }
    }

    protected function initSetting($output)
    {
        $this->getSettingService()->set('order_tax_rate', '10');
        $this->getSettingService()->set('order_service_rate', '10');
        $output->writeln(array(
            '<info>初始化设置成功</info>',
        ));
    }

    protected function initPhpCsFixerHook($output)
    {
        $biz = $this->getBiz();
        $sourcePath = realpath($biz['root_directory']).'/.pre-push';
        $distPath = realpath($biz['root_directory']).'/.git/hooks/pre-push';

        if (!file_exists($distPath)) {
            copy($sourcePath, $distPath);
            chmod($distPath, 0755);
            $output->writeln('<info>'.$distPath.' created successfully</info>');
        } else {
            $output->writeln('<info>'.$distPath.' found, skipped</info>');
        }
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return \Biz\Permission\Service\RoleService
     */
    protected function getRoleService()
    {
        return $this->createService('Permission:RoleService');
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return \Biz\Region\Service\RegionService
     */
    protected function getRegionService()
    {
        return $this->createService('Region:RegionService');
    }
}
