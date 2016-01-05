<?php
namespace huitiemesens\FunctionalTestGeneratorBundle\Tests\Command;


use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\HelperSet;
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
        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream('yes\\n'));
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'      => $command->getName(),
            'bundle'         => 'huitiemesens:FunctionalTestGeneratorBundle'
        ));
        
        //echo $commandTester->getDisplay();
        $this->assertRegExp('/./', $commandTester->getDisplay());
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}
