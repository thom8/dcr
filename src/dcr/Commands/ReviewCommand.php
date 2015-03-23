<?php

namespace dcr\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class ReviewCommand extends Command {

  protected function configure() {
    $this->setName('dcr:review');

    $this->setDescription('Reviews code in files changed between 2 branches and sends emails to authors in case of found code problems.');

    $this->setDefinition(array(
      new InputArgument('name', InputArgument::OPTIONAL, 'Main branch. Defaults to \'auto\' meaning that it will be discovered according to branch-development conventions.', 'master'),
      new InputArgument('mine', InputArgument::OPTIONAL, 'Scan files committed by current user only.', FALSE),
      new InputArgument('quiet', InputArgument::OPTIONAL, 'Suppress output to stdout.', FALSE),
      new InputArgument('report', InputArgument::OPTIONAL, 'Save as report file.', FALSE),
      new InputArgument('sendmail', InputArgument::OPTIONAL, 'Flag to send emails. Value is a pipe-separated string of subject, body and from fields. Can be left blank to use default field values.' . "\r\n" . "Supported placeholders:\r\n  !author - commit author\r\n  !branch - current branch name\r\n  !report - current report. Example value: /path/to/sendmail"),
      new InputArgument('sniffs', InputArgument::OPTIONAL, 'Read available or set sniffs to use. To list all available sniffs, do no specify any parameters. Use comma separated list of sniffs names to specify which sniffs to use. Example: Drupal,DrupalPractice'),
    ));


//      'include-empty' => array(
//      'description' => 'Include empty results in report generation. Disabled by default.',
//      'value' => 'optional',
//      'db' => TRUE,
//    ),
//      'nolimit' => array(
//      'description' => 'Remove any limits on number of files to be scanned. Limit is set to 10',
//      'value' => 'optional',
//      'db' => TRUE,
//    ),

//    $start = 0;
//    $stop = 100;
//
//    $this->setName("phpmaster:fibonacci")
//      ->setDescription("Display the fibonacci numbers between 2 given numbers")
//      ->setDefinition(array(
//        new InputOption('start', 's', InputOption::VALUE_OPTIONAL, 'Start number of the range of Fibonacci number', $start),
//        new InputOption('stop', 'e', InputOption::VALUE_OPTIONAL, 'stop number of the range of Fibonacci number', $stop)
//      ))
//      ->setHelp(<<<EOT
//Display the fibonacci numbers between a range of numbers given as parameters
//
//Usage:
//
//<info>php console.php phpmaster:fibonacci 2 18 <env></info>
//
//You can also specify just a number and by default the start number will be 0
//<info>php console.php phpmaster:fibonacci 18 <env></info>
//
//If you don't specify a start and a stop number it will set by default [0,100]
//<info>php console.php phpmaster:fibonacci<env></info>
//EOT
//      );
  }

  protected function execute(InputInterface $input, OutputInterface $output) {

    $output->writeln('HELLO WORLD REVIEW');
  }
}
