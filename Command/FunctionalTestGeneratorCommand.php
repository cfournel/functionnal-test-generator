<?php
namespace huitiemesens\FunctionalTestGeneratorBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
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
         //   $text = "here is the list of controllers found:" ;
            foreach ( $controllers as $name => $routes )
            {
                $ControllerTest = "<?php
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
";              $questionHelper = $this->getHelper('question');

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
                        /*if (!$question->askConfirmation($output, '<question>Do you confirm generation? [Y] Yes [N] No <question>', true)) {
                            $skip = 1;
                        }*/
                        
                        if ( $skip ){
                            $output->writeln("<error>Skipping $route ...</error>");
                        }else
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
                                $setUpFile = "<?php
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
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected \$container;
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected \$em;

    /**
     * @var string
     */
    protected \$environment = 'test';

    /**
     * @var bool
     */
    protected \$debug = true;

    /**
     * @var string
     */
    protected \$entityManagerServiceId = 'doctrine.orm.entity_manager';

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
            static::\$kernel = self::createKernel(array(
                'environment' => \$this->environment,
                'debug' => \$this->debug,
            ));
            static::\$kernel->boot();
        }

        \$this->container = static::\$kernel->getContainer();
        \$this->em = \$this->getEntityManager();
    }

    /**
     * Executes fixtures
     *
     * @param \Doctrine\Common\DataFixtures\Loader \$loader
     */
    protected function executeFixtures(Loader \$loader)
    {
        \$purger = new ORMPurger();
        \$executor = new ORMExecutor(\$this->em, \$purger);
        \$executor->execute(\$loader->getFixtures());
    }

    /**
     * Load and execute fixtures from a directory
     *
     * @param string \$directory
     */
    protected function loadFixturesFromDirectory(\$directory)
    {
        \$loader = new Loader();
        \$loader->loadFromDirectory(\$directory);
        \$this->executeFixtures(\$loader);
    }

    /**
     * Returns the doctrine orm entity manager
     *
     * @return object
     */
    protected function getEntityManager()
    {
        return \$this->container->get(\$this->entityManagerServiceId);
    }

    /**
     * Sets up client and user for tests
     */
    public function setUpClientAndUser()
    {

        // todo : à update avec les params unit test
        \$this->uPassword = \$this->container->getParameter('unit_test_password');
        \$this->uEmail = \$this->container->getParameter('unit_test_email');

        // creates client with already authenticated user
        \$this->client = static::makeClient(true);

        // Load all necessary fixtures (WIP)
        \$this->loadFixtures(array(
            
        ));

    }

}
?>
";
    
                    file_put_contents($dir."/Tests/SetUpFunctionalTest.php", $setUpFile);
                            }

                        $ControllerTest .= "
    /**
     * Tests the " . $actionName . " page
     */
    public function test" . $actionName ."()
    {
        //set up the test. Client is : \$this->client;
        \$crawler = \$this->client->request('GET', '" . $route->getPath() . "');
        \$response = \$this->client->getResponse();

        // Test page is available (code 2**)
        //print_r(\$this->client->getResponse()->getContent());die;
        \$this->assertTrue(\$this->client->getResponse()->isSuccessful());

        // Checks if right controller
        \$this->assertEquals('overlord\AppBundle\Controller\CalendarController::indexAction',
            \$this->client->getRequest()->attributes->get('_controller'));

        // Test generated content is available (at least one content)
        // TODO : régler le pb de la fonction custom DQL rand() qui  n'est pas loadée
        // (et donc pas de conseils)
        //\$this->assertGreaterThan(0, \$crawler->filter('#conseils ul li p')->count());

        // Test some static content
        \$this->client->reload();

        // Assert that the title is correct
        \$html = \$crawler->filter('h1')->text();
        \$this->assertNotEmpty(\$html);
    }

";
                    }
                }
                $ControllerTest .= "
}
?>";
                file_put_contents($dir."/Tests/Controller/" . $name . "Test.php", $ControllerTest);
            }
        }
    }
}
}
