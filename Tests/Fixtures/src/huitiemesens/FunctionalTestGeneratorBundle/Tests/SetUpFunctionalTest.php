<?php
namespace huitiemesens\FunctionalTestGeneratorBundle\Tests;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * Handle configuration and set up of unit tests for api
 * and loads doctrine fixtures using the symfony configuration
 */
abstract class SetUpFunctionalTest extends WebTestCase
{
    protected $client;
    protected $uEmail;
    protected $uPassword;
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $environment = 'test';

    /**
     * @var bool
     */
    protected $debug = true;

    /**
     * @var string
     */
    protected $entityManagerServiceId = 'doctrine.orm.entity_manager';

    /**
     * Constructor
     *
     * @param string|null $name     Test name
     * @param array       $data     Test data
     * @param string      $dataName Data name
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        if (!static::$kernel) {
            static::$kernel = self::createKernel(array(
                'environment' => $this->environment,
                'debug' => $this->debug,
            ));
            static::$kernel->boot();
        }

        $this->container = static::$kernel->getContainer();
        $this->em = $this->getEntityManager();
    }

    /**
     * Executes fixtures
     *
     * @param \Doctrine\Common\DataFixtures\Loader $loader
     */
    protected function executeFixtures(Loader $loader)
    {
        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->em, $purger);
        $executor->execute($loader->getFixtures());
    }

    /**
     * Load and execute fixtures from a directory
     *
     * @param string $directory
     */
    protected function loadFixturesFromDirectory($directory)
    {
        $loader = new Loader();
        $loader->loadFromDirectory($directory);
        $this->executeFixtures($loader);
    }

    /**
     * Returns the doctrine orm entity manager
     *
     * @return object
     */
    protected function getEntityManager()
    {
        return $this->container->get($this->entityManagerServiceId);
    }

    /**
     * Sets up client and user for tests
     */
    public function setUpClientAndUser()
    {

        // todo : à update avec les params unit test
        $this->uPassword = $this->container->getParameter('unit_test_password');
        $this->uEmail = $this->container->getParameter('unit_test_email');

        // creates client with already authenticated user
        $this->client = static::makeClient(true);

        // Load all necessary fixtures (WIP)
        $this->loadFixtures(array(
            
        ));

    }

}
?>
