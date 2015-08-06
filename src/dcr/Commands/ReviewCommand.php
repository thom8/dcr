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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Class ReviewCommand
 *
 * Provides interface for command description.
 *
 * @package dcr\Commands
 */
class ReviewCommand extends Command {
  /**
   * Defines normal exit code.
   */
  const EXIT_CODE_OK = 0;

  /**
   * Defines code review success code.
   */
  const EXIT_CODE_REVIEW_SUCCESS = self::EXIT_CODE_OK;

  /**
   * Defines code review failed code.
   */
  const EXIT_CODE_REVIEW_FAILED = 1;

  /**
   * Defines application error code.
   */
  const EXIT_CODE_APPLICATION_ERROR = 127;

  /**
   * @var int
   *   Review exit code.
   */
  protected $reviewExitCode = self::EXIT_CODE_APPLICATION_ERROR;

  /**
   * Set review exit code.
   */
  protected function setReviewExitCode($code) {
    $this->reviewExitCode = $code;
  }

  /**
   * Get review exit code.
   */
  protected function getReviewExitCode() {
    return $this->reviewExitCode;
  }

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
      new InputOption('standard', NULL, InputOption::VALUE_REQUIRED, 'Set standards to use. Example: Drupal,DrupalPractice', FALSE),
      new InputOption('standard-list', NULL, InputOption::VALUE_NONE, 'Show available standards'),
      new InputOption('mine', NULL, InputOption::VALUE_NONE, 'Scan files committed by current user only.'),
      new InputOption('limit', NULL, InputOption::VALUE_REQUIRED, 'Set a limits on number of files to be scanned. By default, limit is set to 10. Set to "no" to avoid any limit.', 10),
      new InputOption('report', 'r', InputOption::VALUE_REQUIRED, 'Save results as report file.', FALSE),
      new InputOption('report-split', NULL, InputOption::VALUE_NONE, 'Save results as multiple report files for each committer.'),
      new InputOption('sendmail', NULL, InputOption::VALUE_REQUIRED, 'Flag to send emails. Value is a pipe-separated string of subject, body and from fields. Can be left blank to use default field values.' . "\r\n" . "Supported placeholders:\r\n  !author - commit author\r\n  !branch - current branch name\r\n  !report - current report. Example value: /path/to/sendmail"),
      new InputOption('include-empty', NULL, InputOption::VALUE_NONE, 'Include empty results in report generation.'),
      new InputOption('sniff-codes', 's', InputOption::VALUE_NONE, 'Show sniff codes in all reports.'),
    ))
      ->setHelp(<<<EOT
Drupal Code Review (DCR) is a command-line utility to check that produced code follows Drupal coding standards and best practices.

It performs code review in files changed between 2 branches and sends emails to authors in case of found code problems.

Usage:
Compare current branch with 'master' branch and output results to stdout.
<info>dcr</info>

Review code within specified file and output results to stdout. File path within Drupal root will be resolved automatically.
<info>dcr /absolute/path/to/file</info>
<info>dcr relative/path/to/file</info>

Compare custom main branch with current branch and output results to stdout.
<info>dcr --main-branch=custom_branch</info>

Compare current branch with 'master' branch and save output as a report file.
<info>dcr --report=/path/to/report.txt</info>

Compare current branch with 'master' branch and save results as report files, one for each account.
<info>dcr --reports=/path/to/reports/directory</info>

Send reports to respected authors' emails using pipe-separated email parameters.
<info>dcr --sendmail=Code review for !branch|Hello !author\\nBelow is a code review result for branch !branch: !report|do-not-reply@example.com</info>

Compare current branch with 'master' branch using only Drupal standard.
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

      return self::EXIT_CODE_OK;
    }

    if ($files = $input->getArgument('filename')) {
      $files = $this->discoverFilesDir($files);
      $this->outputFilesInfo($files, $output);
    }
    // Discover files if not explicitly specified as argument.
    else {
      $files = $this->discoverFilesGitChanged($options['main-branch'], $options['mine']);
      $this->outputFilesInfoFromCommits($files, $output);
    }

    $results = $this->reviewFiles($files, $options['limit'], $options['sniff-codes'], $output);

    $this->outputResults($results, $output);

    if ($options['report']) {
      $this->produceReports($results, $options['report'], $options['report-split']);
    }

    if ($options['sendmail']) {
      $this->sendMail($results, $options['sendmail']);
    }

    return $this->getReviewExitCode();
  }

  /**
   * Discover files from provided sources.
   *
   * @param array $sources
   *   Array of source files or dirs to discover from.
   *
   * @return array
   *   Array of all found files.
   */
  protected function discoverFilesDir($sources) {
    $discovered_files = array();

    $sources = is_array($sources) ? $sources : array($sources);
    foreach ($sources as $source) {
      // Source is a file.
      if (is_file($source)) {
        $discovered_files[] = $source;
      }
      // Source is a directory - find all files in all subdirs.
      elseif (is_dir($source)) {
        $finder = new Finder();
        $finder_results = $finder->files()
          ->in($source)
          ->name('*');

        $discovered_files = array_merge($discovered_files, array_keys(iterator_to_array($finder_results)));
      }
      else {
        // Skip this incorrect source.
        throw new \RuntimeException('Unable to find review source file ' . $source);
      }
    }

    return $discovered_files;
  }

  /**
   * Output information about found files.
   *
   * @param $files
   * @param OutputInterface $output
   */
  protected function outputFilesInfo($files, OutputInterface $output) {
    $output->writeLn($this->formatString('Found @count_files files', array(
      '@count_files' => count($files),
    )));
  }

  /**
   * Output information about found files from commits.
   *
   * @param $commits
   * @param OutputInterface $output
   */
  protected function outputFilesInfoFromCommits($commits, OutputInterface $output) {
    $total_commit_files = 0;
    foreach ($commits as $sha => $commit) {
      $total_commit_files += count($commit['files']);
    }

    $output->writeLn($this->formatString('Found @count_files files in @count_commits commits', array(
      '@count_files' => $total_commit_files,
      '@count_commits' => count($commits),
    )));
  }

  private function formatString($string, $args = array()) {
    return strtr($string, $args);
  }

  /**
   * Output available standards as a table.
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
   * Return path to default standard.
   */
  protected function getDefaultStandard() {
    $standards = $this->findAvailableStandards(ROOT_DIR);

    // @todo: Add reading of default standard from the config file.
    $default_standard_name = 'DCR';

    if (!in_array($default_standard_name, $standards)) {
      throw new \RuntimeException('Unable to find default standard ' . $default_standard_name);
    }

    return array_search($default_standard_name, $standards);
  }

  /**
   * Review provided files and output review progress.
   *
   * @param array $files
   *   Array of files to review.
   * @param int $error_threshold
   *   Optional integer error threshold. Defaults to 0 meaning no threshold.
   * @param bool $show_sniff_codes
   *   Optional flag to show sniff codes within a review. Defaults to FALSE.
   * @param OutputInterface $output
   *   Output interface for progress bar.
   *
   * @return array
   *   Array of review results with file paths as keys and strings of review
   *   results as values.
   */
  protected function reviewFiles($files, $error_threshold = 0, $show_sniff_codes = FALSE, $output) {
    // Exit code that will be returned to the caller. This will contain a
    // boolean sum of all reviews.
    $exit_code_final = self::EXIT_CODE_REVIEW_SUCCESS;

    // Create a new progress bar.
    $progress_bar = new ProgressBar($output, count($files));

    $results = array();
    $error_count = 0;
    foreach ($files as $file) {
      // Stop processing if maximum review error count is reached.
      if ($error_threshold > 0 && $error_count >= $error_threshold) {
        break;
      }

      // Review single file and get results.
      $result = $this->reviewFile($file, $show_sniff_codes);

      $exit_code_final = $exit_code_final | $result['code'];
      $error_count = $result['code'] === self::EXIT_CODE_REVIEW_FAILED ? $error_count + 1 : $error_count;

      // Store output results for each file separately.
      $results[$file] = $result['output'];

      // Advance the progress bar.
      $progress_bar->advance();
    }

    // Stop and reset the progress bar.
    $progress_bar->finish();
    $progress_bar->clear();

    // Store all command exit codes to return to the main calling process.
    $this->setReviewExitCode($exit_code_final);

    // Capture and return command output.
    return $results;
  }

  /**
   * Review a single file.
   *
   * @param string $file
   *   Absolute path to the file.
   * @param bool $show_sniff_codes
   *   Optional flag to show sniff codes within a review. Defaults to FALSE.
   *
   * @return array
   *   Array of review results with the following keys:
   *   - output: String output from review command.
   *   - code: Integer exit code from the review command.
   */
  protected function reviewFile($file, $show_sniff_codes = FALSE) {
    // Build command string.
    $command = $this->buildReviewCommand($file, $show_sniff_codes);

    $process = new Process($command);
    $exit_code = $process->run();

    // Track review result separately to any other phpcs application errors.
    if ($exit_code != self::EXIT_CODE_REVIEW_SUCCESS && $exit_code != self::EXIT_CODE_REVIEW_FAILED) {
      throw new \RuntimeException('Error occurred while performing file ' . $file . ' review. ' . $process->getOutput());
    }

    return array(
      'output' => trim($process->getOutput()),
      'code' => $exit_code,
    );
  }

  /**
   * Build review command string for specified file.
   */
  protected function buildReviewCommand($file, $show_sniff_codes) {
    $binary = $this->binaryFilenameLookup('phpcs');

    $options = array(
      'standard' => $this->getDefaultStandard(),
      'colors' => NULL,
    );

    if ($show_sniff_codes) {
      $options['s'] = NULL;
    }
    $options = $this->commandOptionsStringify($options);

    return $binary . ' ' . $options . ' ' . $file;
  }

  /**
   * Lookup binary filename and return full path.
   *
   * @param string $name
   *   Binary name.
   *
   * @return string|boolean
   *   Absolute path for binary or FALSE if binary was not found.
   */
  protected function binaryFilenameLookup($name) {
//    static $files = array();
//    if (!isset($files[$name])) {
//      $files[$name] = $this->executeCommand('which ' . $name, 'Unable to find full path for command ' . $name);
//    }
//
//    return $files[$name];
    return $name;
  }

  /**
   * Returns information about discovered files.
   *
   * @param string $main_branch
   *   Main branch to get file difference.
   * @param bool|string $committer
   *   Committer name as string or TRU for auto-discovery from git settings
   *   for current user. Defaults to FALSE which means that no filtering will
   *   be applied.
   *
   * @return array
   *   Array of discovered files information.
   */
  protected function discoverFilesGitChanged($main_branch, $committer = FALSE) {
    $discovered_files = array();

    $current_branch = $this->getGitCurrentBranch();

    // Nothing to work on.
    if ($current_branch == $main_branch) {
      return $discovered_files;
    }

    // Fallback to auto discoverable current user committer.
    $committer = is_bool($committer) ? $this->getGitCurrentUserConfigField('email') : $committer;

    // Return list of changed files as info line followed by changed files list.
    $discovered_files = $this->getGitChangedFiles($main_branch, $committer);

    return $discovered_files;
  }

  /**
   * Get changed files comparing to another git branch, filtered by committer.
   *
   * @param string $compare_to_branch
   *   Branch name to compare to.
   * @param string $author_mail
   *   Optional author's email.
   *
   * @return array
   *   Associative array of commits information, keyed by commit's SHA. Each
   *   element of array has the following keys:
   *   - sha: A copy of commit sha from the key.
   *   - subject: Commit subject.
   *   - email: String committer email.
   *   - timestamp: Integer commit timestamp.
   *   - files: Array of changed files.
   */
  protected function getGitChangedFiles($compare_to_branch, $author_mail = '') {
    // Create special prefix to distinguish as a start of information section in
    // the output.
    $prefix = 'Info: ';
    // Return list of changed files as info line followed by changed files list.
    $command = "git show --committer=" . $author_mail . " --pretty='format:" . $prefix . "%H|%ce|%s|%ct' --name-only $( git cherry -v " . $compare_to_branch . " | grep '^+' | awk '{ print($2) }' ) | egrep -v '^(commit |Author:|Date:|\s|$)'";
    $output = $this->executeCommand($command);
    $output = explode("\n", $output);

    $commits = array();
    $sha = FALSE;
    foreach ($output as $line) {
      if (strpos($line, $prefix) === 0) {
        $line = substr($line, strlen($prefix));
        $items = explode('|', $line);
        $sha = $items[0];
        $commits[$sha] = array(
          'sha' => $sha,
          'email' => $items[1],
          'subject' => $items[2],
          'timestamp' => $items[3],
          'files' => array(),
        );
      }
      elseif ($sha) {
        // Add files.
        $filename = $this->getGitRootPath() . '/' . trim($line);
        if (file_exists($filename)) {
          $commits[$sha]['files'][$filename] = '';
        }
      }
    }

    // Filter-out old files to return only the latest committer-per file.
    $commits_reverted = array_reverse($commits);
    $files = array();
    foreach ($commits_reverted as $sha => $commit) {
      foreach ($commit['files'] as $filename => $tmp) {
        if (in_array($filename, $files)) {
          unset($commits_reverted[$sha]['files'][$filename]);
        }
        else {
          $files[] = $filename;
        }
      }
    }
    $commits = array_reverse($commits_reverted);

    return $commits;
  }

  /**
   * Helper to return system path to topmost (root) directory in git repository.
   *
   * @return string
   *   System path.
   */
  protected function getGitRootPath() {
    return $this->executeCommand('git rev-parse --show-toplevel');
  }

  protected function getGitCurrentBranch() {
    return $this->executeCommand('git rev-parse --abbrev-ref HEAD');
  }

  /**
   * Helper to return current git repository user field.
   *
   * @return string
   *   String value of specified user field.
   */
  protected function getGitCurrentUserConfigField($field) {
    return $this->executeCommand('git config user.' . $field);
  }

  /**
   * Execute CLI command and return the output.
   *
   * @param string $command
   *   Command to execute.
   * @param string $error_message
   *   Optional error message to pass to exception if command fails.
   *
   * @return string Command output.
   * Command output.
   */
  protected function executeCommand($command, $error_message = '') {
    $process = new Process($command);
    $process->run();

    if (!$process->isSuccessful()) {
      $error_message = empty($error_message) ? $process->getErrorOutput() : $error_message;
      throw new \RuntimeException($error_message);
    }

    return trim($process->getOutput());
  }

  /**
   * Convert array of CLI options to a string suitable for command.
   *
   * @param array $options
   *   Array of options with option names as keys and option values as values.
   *   Any single-letter option names are considered options shorthand (-a).
   *
   * @return string
   *   String suitable for command.
   */
  protected function commandOptionsStringify($options) {
    $output = array();
    foreach ($options as $option => $value) {
      // Options shorthand.
      if (strlen($option) == 1) {
        $output[] = is_null($value) ? '-' . $option : '-' . $option . ' ' . $value;
      }
      // Full options.
      else {
        $output[] = is_null($value) ? '--' . $option : '--' . $option . '=' . $value;
      }
    }

    return implode(' ', $output);
  }

  /**
   * Output results, including sending emails.
   *
   * @param OutputInterface $output
   */
  protected function outputResults($results, OutputInterface $output) {
    $output->write("\n");
    $output->write(implode("\n", $results));
  }

  /**
   * Produce reports.
   *
   * @param array $results
   *   Array of review results.
   * @param string $filename
   *   Report filename.
   * @param bool $multiple_files
   *   Optional flag to store reports as multiple files.
   */
  protected function produceReports($results, $filename, $multiple_files = FALSE) {
    // @todo: Implement this.
  }

  /**
   * Send mail to committers.
   *
   * @param $results
   *   Array of results.
   * @param array $options
   *   Array of sendmail options.
   */
  protected function sendMail($results, $options) {
    // @todo: Implement this.
  }
}
