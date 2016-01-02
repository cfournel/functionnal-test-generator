<?php
namespace huitiemesens\FunctionalTestGeneratorBundle\Tests\Command;


use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use huitiemesens\FunctionalTestGeneratorBundle\Command\FunctionalTestGeneratorCommand;

class FunctionalTestGeneratorCommandTest extends WebTestCase
{
    private $command;

    public function testExecute()
    {
        $kernel = $this->createKernel();
        $kernel->boot();
        $application = new Application($kernel);
        $application->add(new FunctionalTestGeneratorCommand());

        $command = $application->find('tests:generate');
        //$this->mockCommandDialogHelper($command);
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'      => $command->getName(),
            'bundle'         => 'huitiemesens:FunctionalTestGeneratorBundle'
        ));
        
        echo $commandTester->getDisplay();
        $this->assertRegExp('/.../', $commandTester->getDisplay());
    }

    /**
     * Mocking command input (not to show command prompt)
     * @param Command $command
     */
    private function mockCommandDialogHelper(FunctionalTestGeneratorCommand $command)
    {
        $dialog = $this->getMock('Symfony\Component\Console\Helper\DialogHelper', array('ask'));
        $dialog->expects($this->at(0))
            ->method('ask')
            ->will($this->returnValue('y'));
        $command->getHelperSet()->set($dialog, 'dialog');
    }
}