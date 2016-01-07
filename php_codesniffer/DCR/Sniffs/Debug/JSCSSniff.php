<?php
/**
 * @file
 * DCR PHP_CodeSniffer Standard.
 *
 * Checks JS files using JSCS. Respects standard rules and provides
 * additional parameters to be passed to JSCS.
 */

/**
 * DCR_Sniffs_Debug_JSCSSniff.
 *
 * Checks JS files using JSCS.
 */
class DCR_Sniffs_Debug_JSCSSniff implements PHP_CodeSniffer_Sniff {

  /**
   * A list of tokenizers this sniff supports.
   *
   * This narrows down the application of this sniff only to files supported
   * by tokenizers (i.e. no need to exclude all files .
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

    $jscs_path = PHP_CodeSniffer::getConfigData('jscs_path');
    if ($jscs_path === NULL) {
      return;
    }

    // JSCS options to generate an output that can be parsed by the script
    // below.
    // @see http://jscs.info/overview.
    $jscs_options = '--reporter=text';

    $cmd = '"' . $jscs_path . '/jscs' . '"' . ' ' . '"' . $file_name . '"' . ' ' . $jscs_options;
    exec($cmd, $output, $retval);

    if (is_array($output) === TRUE) {
      $tokens = $phpcs_file->getTokens();

      $messages = $this->parseMessages($output);

      foreach ($messages as $output) {
        $line_number = $this->parseLineNumber($output);
        $message = $this->parseMessage($output);

        // Find the token at the start of the line.
        $line_token = NULL;
        foreach ($tokens as $ptr => $info) {
          if ($line_number == $info['line']) {
            $line_token = $ptr;
            break;
          }
        }

        $phpcs_file->addWarning($message, $line_token, 'ExternalTool');
      }
    }
  }

  /**
   * Parse output into separate messages.
   *
   * @param array $lines Array of CLI output lines.
   *
   * @return array Array of arrays of message lines.
   */
  protected function parseMessages($lines) {
    $messages = array();

    $message = array();
    foreach ($lines as $line) {
      if (empty($line) & !empty($message)) {
        $messages[] = $message;
        $message = array();
        continue;
      }
      $message[] = $line;
    }

    return $messages;
  }

  /**
   * Parse error message.
   *
   * @param array $lines Array of CLI output lines.
   *
   * @return string Error message string.
   */
  protected function parseMessage($lines) {
    // Actual message.
    // Replace " at {filename}:" with ":" as we already know the file name.
    $lines[0] = preg_replace('/\s+at\s+.+\:$/', ':', reset($lines));

    return implode("\n", $lines) . "\n";
  }

  /**
   * Parse line number.
   *
   * @param array $lines Array of CLI output lines.
   *
   * @return int Line number where error occurred.
   */
  protected function parseLineNumber($lines) {
    $number = 0;

    foreach ($lines as $k => $line) {
      if (strpos($line, '----') === 0) {
        $string = trim($lines[$k - 1]);
        $string = explode('|', $string);
        $number = trim($string[0]);
        break;
      }
    }

    return (int) $number;
  }
}
