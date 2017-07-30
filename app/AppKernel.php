<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Loader\LoaderInterface;
use Codeages\Biz\Framework\Provider\DoctrineServiceProvider;
use Codeages\Biz\Framework\Provider\MonologServiceProvider;
use Biz\User\CurrentUser;

class AppKernel extends Kernel
{
    /**
     * @var Request
     */
    protected $request;

    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);
        date_default_timezone_set('Asia/Shanghai');
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    public function boot()
    {
        if (true === $this->booted) {
            return;
        }

        if ($this->loadClassCache) {
            $this->doLoadClassCache($this->loadClassCache[0], $this->loadClassCache[1]);
        }

        // init bundles
        $this->initializeBundles();

        // init container
        $this->initializeContainer();

        $this->initializeBiz();

        foreach ($this->getBundles() as $bundle) {
            $bundle->setContainer($this->container);
            $bundle->boot();
        }

        $this->booted = true;
    }

    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Sentry\SentryBundle\SentryBundle(),
            new AppBundle\AppBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'), true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }

    protected function initializeBiz()
    {
        $biz = $this->getContainer()->get('biz');
        $biz['migration.directories'][] = dirname(__DIR__).'/migrations';
        $biz['env'] = array(
            'base_url' => $this->request->getSchemeAndHttpHost().$this->request->getBasePath(),
        );

        $currentUser = new CurrentUser();
        $currentUser->fromArray(
            array(
                'id' => 0,
                'nickname' => '游客',
                'login_ip' => $this->request->getClientIp() ?: '127.0.0.1',
                'roles' => array(),
                'region_id' => 0,
                'parent_id' => 0,
            )
        );
        $biz['user'] = $currentUser;

        $biz->register(new DoctrineServiceProvider());
        $biz->register(new MonologServiceProvider());
        $biz->register(new \Biz\DefaultServiceProvider());

        $providerCollector = $this->getContainer()->get('biz.service_provider.collector');
        foreach ($providerCollector->all() as $provider) {
            $biz->register($provider);
        }

        $subscriberCollector = $this->getContainer()->get('biz.event_subscriber.collector');
        foreach ($subscriberCollector->all() as $subscriber) {
            $biz['dispatcher']->addSubscriber($subscriber);
        }

        $biz->boot();
    }
}
