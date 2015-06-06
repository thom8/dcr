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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\Table;

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
      // Arguments.
      new InputArgument('filename', InputArgument::OPTIONAL, 'File or directory to review', NULL),
      // Options.
      new InputOption('main-branch', 'm', InputOption::VALUE_REQUIRED, 'Main branch. Defaults to \'auto\' meaning that it will be discovered according to branch-development conventions.', 'master'),
      new InputOption('standard', 's', InputOption::VALUE_REQUIRED, 'Set standards to use. Example: Drupal,DrupalPractice', FALSE),
      new InputOption('standard-list', NULL, InputOption::VALUE_NONE, 'Show available standards'),
      new InputOption('mine', NULL, InputOption::VALUE_NONE, 'Scan files committed by current user only.'),
      new InputOption('limit', NULL, InputOption::VALUE_REQUIRED, 'Set a limits on number of files to be scanned. By default, limit is set to 10. Set to "no" to avoid any limit.', 10),
      new InputOption('report', 'r', InputOption::VALUE_REQUIRED, 'Save results as report file.', FALSE),
      new InputOption('report-split', NULL, InputOption::VALUE_NONE, 'Save results as multiple report files for each committer.'),
      new InputOption('sendmail', NULL, InputOption::VALUE_REQUIRED, 'Flag to send emails. Value is a pipe-separated string of subject, body and from fields. Can be left blank to use default field values.' . "\r\n" . "Supported placeholders:\r\n  !author - commit author\r\n  !branch - current branch name\r\n  !report - current report. Example value: /path/to/sendmail"),
      new InputOption('include-empty', NULL, InputOption::VALUE_NONE, 'Include empty results in report generation.'),
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
<info>dcr --main-branch=custom_branch</info>

Compare current branch with master branch and save output as a report file.
<info>dcr --report=/path/to/report.txt</info>

Compare current branch with master branch and save results as report files, one for each account.
<info>dcr --reports=/path/to/reports/directory</info>

Send reports to respected authors' emails using pipe-separated email parameters.
<info>dcr --sendmail=Code review for !branch|Hello !author\\nBelow is a code review result for branch !branch: !report|do-not-reply@example.com</info>

Compare current branch with master branch using only Drupal standard.
<info>dcr --standard=Drupal</info>

List standards available in the system.
<info>dcr --standard-list</info>
EOT
      );
  }

  /**
   * Execution handler.
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $options = $input->getOptions();
    // Show standards.
    if ($options['standard-list']) {
      $this->outputAvailableStandards($output);

      return;
    }

    // Discover files if not explicitly specified as argument.
    if (!$files = $input->getArgument('filename')) {
      $files = $this->discoverFiles($options['main-branch'], $options['mine']);
    }
    // Review files with set limit.
    $results = $this->reviewFiles($files, $options['limit']);

    $this->outputResults($results, $output);

    if ($options['report']) {
      $this->produceReports($results, $options['report'], $options['report-split']);
    }

    if ($options['sendmail']) {
      $this->sendMail($results, $options['sendmail']);
    }
  }

  /**
   * Ouput available standards as a table.
   *
   * @param OutputInterface $output
   *   Output interface.
   */
  protected function outputAvailableStandards(OutputInterface $output) {
    $standards = $this->findAvailableStandards(ROOT_DIR);

    $rows = array();
    foreach ($standards as $path => $name) {
      $rows[] = array($name, $path);
    }

    $table = new Table($output);
    $table
      ->setStyle('compact')
      ->setRows($rows)
      ->render();
  }

  /**
   * Find all standards in directory and all subdirectories.
   *
   * @param string $dir
   *   Directory to search through.
   *
   * @return array
   *   Associated array of standards paths as keys and names as values.
   */
  protected function findAvailableStandards($dir) {
    $found_standards = array();
    // Find all occurrences of ruleset.xml.
    $finder = new Finder();
    $files = $finder->files()
      ->in($dir)
      ->name('ruleset.xml');
    foreach ($files as $path => $file) {
      $standard_xml = simplexml_load_file($path);
      $found_standards[$path] = (string) $standard_xml->attributes()->name;
    }

    return $found_standards;
  }

  /**
   * Review provided files.
   */
  protected function reviewFiles($files) {
    return '';
  }

  protected function discoverFiles($main_branch) {
    $discovered_files = array();

    return $discovered_files;
  }

  /**
   * Output results, including sending emails.
   *
   * @param OutputInterface $output
   */
  protected function outputResults($results, OutputInterface $output) {
  }

  protected function produceReports() {
  }

  protected function sendMail() {
  }
}
