<?php
/**
 * @file
 * DCR PHP_CodeSniffer Standard.
 *
 * Checks JS files using JSLint. Respects standard rules and provides
 * additional parameters to be passed to JSLint.
 */


/**
 * DCR_Sniffs_Debug_JSLintSniff.
 *
 * Checks JS files using JSLint.
 */
class DCR_Sniffs_Debug_JSLintSniff implements PHP_CodeSniffer_Sniff {

  /**
   * A list of tokenizers this sniff supports.
   *
   * @var array
   */
  public $supportedTokenizers = array('JS');


  /**
   * Returns the token types that this sniff is interested in.
   */
  public function register() {
    return array(T_OPEN_TAG);
  }

  /**
   * Processes the tokens that this sniff is interested in.
   */
  public function process(PHP_CodeSniffer_File $phpcs_file, $stack_ptr) {
    $file_name = $phpcs_file->getFilename();

    $rhino_path = PHP_CodeSniffer::getConfigData('rhino_path');
    $jslint_path = PHP_CodeSniffer::getConfigData('jslint_path');
    if ($rhino_path === NULL || $jslint_path === NULL) {
      return;
    }

    // JSLint options.
    // @see http://www.jslint.com/lint.html (table of options at the bottom).
    $jslint_options = '--unparam --indent=2 --vars --todo --browser';

    $cmd = $rhino_path . ' ' . '"' . $jslint_path . '"' . ' ' . '"' . $file_name . '"' . ' ' . $jslint_options;
    exec($cmd, $output, $retval);

    if (is_array($output) === TRUE) {
      $tokens = $phpcs_file->getTokens();

      foreach ($output as $finding) {
        $matches = array();
        $num_matches = preg_match('/Lint at line ([0-9]+).*:(.*)$/', $finding, $matches);
        if ($num_matches === 0) {
          continue;
        }

        $line = (int) $matches[1];
        $message = 'jslint says: ' . trim($matches[2]);

        // Find the token at the start of the line.
        $line_token = NULL;
        foreach ($tokens as $ptr => $info) {
          if ($info['line'] === $line) {
            $line_token = $ptr;
            break;
          }
        }

        if ($line_token !== NULL) {
          $phpcs_file->addWarning($message, $line_token, 'ExternalTool');
        }
      }
    }
  }
}
