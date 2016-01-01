<?php
namespace huitiemesens\FunctionalTestGeneratorBundle\Tests\Command;
require_once __DIR__.'/../../vendor/autoload.php';

use huitiemesens\functionalTestGeneratorbundle\Command\FunctionalTestGeneratorCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
//use Symfony\Component\DependencyInjection\Container;

class FunctionalTestGeneratorCommandTest extends \PHPUnit_Framework_TestCase
{
    private $command;

    public function testExecute()
    {
        $application = new Application();
        $application->add(new FunctionalTestGeneratorCommand());

        $command = $application->find('tests:generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $this->assertRegExp('/.../', $commandTester->getDisplay());
    }
}