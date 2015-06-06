<?php
/**
 * @file
 * Implementation of review command.
 */
namespace dcr\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * Class ReviewCommand
 *
 * Provides interface for command description.
 *
 * @package dcr\Commands
 */
class ReviewCommand extends Command {
  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('dcr:review');

    $this->setDescription('Reviews code in files changed between 2 branches and sends emails to authors in case of found code problems.');

    $this->setDefinition(array(
      new InputOption('main', NULL, InputOption::VALUE_OPTIONAL, 'Main branch. Defaults to \'auto\' meaning that it will be discovered according to branch-development conventions.', 'master'),
      new InputOption('sniffs', NULL, InputOption::VALUE_OPTIONAL, 'Read available or set sniffs to use. To list all available sniffs, do no specify any parameters. Use comma separated list of sniffs names to specify which sniffs to use. Example: Drupal,DrupalPractice', FALSE),
      new InputOption('mine', NULL, InputOption::VALUE_OPTIONAL, 'Scan files committed by current user only.', FALSE),
      new InputOption('nolimit', NULL, InputOption::VALUE_OPTIONAL, 'Remove any limits on number of files to be scanned. By default, limit is set to 10.', FALSE),
      new InputOption('report', NULL, InputOption::VALUE_OPTIONAL, 'Save results as report file.', FALSE),
      new InputOption('reports', NULL, InputOption::VALUE_OPTIONAL, 'Save results as multiple report files for each committer.', FALSE),
      new InputOption('sendmail', NULL, InputOption::VALUE_OPTIONAL, 'Flag to send emails. Value is a pipe-separated string of subject, body and from fields. Can be left blank to use default field values.' . "\r\n" . "Supported placeholders:\r\n  !author - commit author\r\n  !branch - current branch name\r\n  !report - current report. Example value: /path/to/sendmail"),
      new InputOption('include-empty', NULL, InputOption::VALUE_OPTIONAL, 'Include empty results in report generation.', FALSE),
    ))
      ->setHelp(<<<EOT
Drupal Code Review (DCR) is a command-line utility to check that produced code follows Drupal coding standards and best practices.

Reviews code in files changed between 2 branches and sends emails to authors in case of found code problems.

Usage:
Compare current branch with master branch and output results to stdout.
<info>dcr</info>

Review code within specified file and output results to stdout. File path within Drupal root will be resolved automatically.
<info>dcr /absolute/path/to/file</info>
<info>dcr relative/path/to/file</info>

Compare custom main branch with current branch and output results to stdout.
<info>dcr --main=custom_branch</info>

Compare current branch with master branch and save output as a report file.
<info>dcr --report=/path/to/report.txt</info>

Compare current branch with master branch and save results as report files, one for each account.
<info>dcr --reports=/path/to/reports/directory</info>

Send reports to respected authors' emails using pipe-separated email parameters.
<info>dcr --sendmail=Code review for !branch|Hello !author\\nBelow is a code review result for branch !branch: !report|do-not-reply@example.com</info>

Compare current branch with master branch using only Drupal sniff.
<info>dcr --sniffs=Drupal</info>

List sniffs available in the system.
<info>dcr --sniffs</info>
EOT
      );
  }

  /**
   * Execution handler.
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $output->writeln('CODE REVIEW EXECUTION');
  }
}
