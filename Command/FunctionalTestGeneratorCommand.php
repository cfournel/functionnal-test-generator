<?php
namespace huitiemesens\FunctionalTestGeneratorBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class FunctionalTestGeneratorCommand extends ContainerAwareCommand
{
    protected $em;
    protected $entities = array();
    protected $namespace;
    
    protected function configure()
    {
        $this
            ->setName('tests:generate')
            ->setDescription('Generate PHPUnit skeletons tests for symfony2 bundles')
            ->setDefinition(array(
                new InputArgument('bundle', InputArgument::REQUIRED, 'Specify which bundle to operate'),
                new InputOption('step', null, InputOption::VALUE_NONE, 'If defined, the generation will ask for each entity generation')
            ))
            ->setHelp(<<<EOT
The <info>tests:generate</info> command generate all Sonata admin files in order to manage all entities included in a defined bundle:
  <info>php app/console tests:generate recetas:myBundle</info>
This interactive will generate all Sonata admin stuff included in myBundle.
EOT
            )
        ;
    }

    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = explode( ':' , $input->getArgument('bundle') );
        $this->namespace =   $data[0];
        $targetBundle = (!empty($data[1])) ? $data[1] : '';
        if ( $targetBundle )
        {
            $output->writeln( "Every controllers included in $targetBundle will be generated");
        }
        
        $routes = $this->getContainer()->get('router')->getRouteCollection()->all();
        $match  = $this->namespace . $targetBundle ;
        foreach ( $routes as $route) {
            $controller = $route->getDefault('_controller');
            if (0 === strpos( $controller, $this->namespace . $targetBundle)) {
                $controllerName = explode (":", str_ireplace( $match, "", $controller ));
                if ( !empty ( $controllerName ) ){
                    $controllers[$controllerName[1]][] = $route;
                }
            }
            

        }
        
        if ( !empty( $controllers ) )
        {
            $output->writeln( "Writing controller test");
            foreach ( $controllers as $name => $routes )
            {
                $addTest = $this->createControllerTestFile($targetBundle, $name);
                $questionHelper = $this->getHelper('question');

                if ($input->isInteractive()) {
                    $question = new ChoiceQuestion("<question>Do you confirm generation of {$name} controller ? [y] Yes [n] No <question>", array('y', 'n'));
                    $choice   = $questionHelper->ask($input, $output, $question);
                    if ($choice !== 'y' ) {
                        $output->writeln('<error>Command aborted</error>');
                        return ;
                    }
                    foreach ( $routes as $route )
                    {
                        $match  = $this->namespace . $targetBundle;
                        $ctrl = explode (":", str_ireplace( $match, "", $route->getDefault('_controller') ) );
                        $actionName = end( $ctrl );
                        $skip = 0;
                        $output->writeln("\r\n<info>Generation test for route $actionName ...</info>");                        
                        if ( $skip == 0)
                        {
                            $dir =  dirname($this->getContainer()->getParameter('kernel.root_dir')).'/src/'.$this->namespace."/".$targetBundle;
                            if ( !is_dir ( $dir."/Tests" ) ){
                                $output->writeln( "\r\n Generating directory..." );
                                mkdir($dir."/Tests", 0755, true);
                            }
                            if ( !is_dir( $dir."/Tests/Controller")){
                                $output->writeln( "\r\n Generating tests controller directory..." );
                                mkdir($dir."/Tests/Controller", 0755, true);
                            }
  
                            if ( !file_exists($dir."/Tests/SetUpFunctionalTest.php")){
                                $output->writeln( "\r\n Generating SetUpFunctionalTest in {$targetBundle}.php ..." );
                                file_put_contents($dir."/Tests/SetUpFunctionalTest.php", $this->generateSetupFunctionTest($targetBundle));
                            }

                            $addTest .= $this->addTestAction($actionName, $route);
                        }
                    }

                    $addTest .= "
}
";
                file_put_contents($dir."/Tests/Controller/" . $name . "Test.php", $addTest);
                }
            }
        }
    }

    public function createControllerTestFile( $targetBundle, $name )
    {
        return "
<?php
namespace ".$this->namespace."\\".$targetBundle."\\Tests\\Controller;

use ".$this->namespace."\\".$targetBundle."\\Tests\\SetUpFunctionalTest;
/**
 * Generated tests for $targetBundle
 */
class " . $name ."Test extends SetUpFunctionalTest
{

    /**
     * Set up test
     */
    public function setUp()
    {
        // setup sqlite database via fixtures
        \$this->setUpClientAndUser();
    }
";   
    }

    public function addTestAction($actionName, $route)
    {
        return "
    /**
     * Tests the " . $actionName . " page
     */
    public function test" . $actionName ."()
    {
        //set up the test. Client is : \$this->client;
        \$crawler = \$this->client->request('GET', '" . $route->getPath() . "');
        \$response = \$this->client->getResponse();

        // Test page is available (code 2**)
        \$this->assertTrue(\$this->client->getResponse()->isSuccessful());

        // Checks if right controller
        \$this->assertEquals('overlord\AppBundle\Controller\CalendarController::indexAction',
            \$this->client->getRequest()->attributes->get('_controller'));

    }

";
    }

    public function addFixtures()
    {
        return "
    /**
     * Executes fixtures
     * @param \Doctrine\Common\DataFixtures\Loader \$loader
     */
    protected function executeFixtures(Loader \$loader)
    {
        \$purger = new ORMPurger();
        \$executor = new ORMExecutor(\$this->em, \$purger);
        \$executor->execute(\$loader->getFixtures());
    }
";
    }

    public function loadFixtures()
    {
        return "
    /**
     * Load and execute fixtures from a directory
     * @param string \$directory
     */
    protected function loadFixturesFromDirectory(\$directory)
    {
        \$loader = new Loader();
        \$loader->loadFromDirectory(\$directory);
        \$this->executeFixtures(\$loader);
    }
";
    }

    public function addEntityManager()
    {
        return "
    /**
     * Returns the doctrine orm entity manager
     *
     * @return object
     */
    protected function getEntityManager()
    {
        return \$this->container->get(\$this->entityManagerServiceId);
    }
";
    }

    public function addConstructor()
    {
        return "
    /**
     * Constructor
     *
     * @param string|null \$name     Test name
     * @param array       \$data     Test data
     * @param string      \$dataName Data name
     */
    public function __construct(\$name = null, array \$data = array(), \$dataName = '')
    {
        parent::__construct(\$name, \$data, \$dataName);
        if (!static::\$kernel) {
            static::\$kernel = self::createKernel(array('environment' => \$this->environment,'debug' => \$this->debug));
            static::\$kernel->boot();
        }

        \$this->container = static::\$kernel->getContainer();
        \$this->em = \$this->getEntityManager();
    }
";
    }

    public function generateSetupFunctionTest($targetBundle)
    {
        return "<?php
namespace ".$this->namespace."\\".$targetBundle."\\Tests;
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
    protected \$client;
    protected \$uEmail;
    protected \$uPassword;
    protected \$container;
    protected \$em;
    protected \$environment = 'test';
    protected \$debug = true;
    protected \$entityManagerServiceId = 'doctrine.orm.entity_manager';

" . $this->addConstructor() . "
" . $this->addFixtures() . "
" . $this->loadFixtures() . "
" . $this->addEntityManager() . "

    /**
     * Sets up client and user for tests
     */
    public function setUpClientAndUser()
    {
        \$this->uPassword = \$this->container->getParameter('unit_test_password');
        \$this->uEmail = \$this->container->getParameter('unit_test_email');

        // creates client with already authenticated user
        \$this->client = static::makeClient(true);
        // Load all necessary fixtures (WIP)
        \$this->loadFixtures(array());
    }
}
";
    }
}

