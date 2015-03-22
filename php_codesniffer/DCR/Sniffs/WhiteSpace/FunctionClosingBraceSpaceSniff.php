<?php
/**
 * @file
 * DCR PHP_CodeSniffer Standard.
 *
 * Checks that there is no empty lines before the closing brace of a function.
 */

/**
 * DCR_Sniffs_WhiteSpace_FunctionClosingBraceSpaceSniff.
 *
 * Checks that there is no empty lines before the closing brace of a function.
 */
class DCR_Sniffs_WhiteSpace_FunctionClosingBraceSpaceSniff implements PHP_CodeSniffer_Sniff {

  /**
   * A list of tokenizers this sniff supports.
   */
  public $supportedTokenizers = array(
    'PHP',
    'JS',
  );

  /**
   * Returns an array of tokens this test wants to listen for.
   */
  public function register() {
    return array(T_FUNCTION);
  }

  /**
   * Processes this test, when one of its tokens is encountered.
   */
  public function process(PHP_CodeSniffer_File $phpcs_file, $stack_ptr) {
    $tokens = $phpcs_file->getTokens();

    if (isset($tokens[$stack_ptr]['scope_closer']) === FALSE) {
      // Probably an interface method.
      return;
    }

    $close_brace = $tokens[$stack_ptr]['scope_closer'];
    $prev_content = $phpcs_file->findPrevious(T_WHITESPACE, ($close_brace - 1), NULL, TRUE);

    // Special case for empty JS functions.
    if ($phpcs_file->tokenizerType === 'JS' && $prev_content === $tokens[$stack_ptr]['scope_opener']) {
      // In this case, the opening and closing brace must be
      // right next to each other.
      if ($tokens[$stack_ptr]['scope_closer'] !== ($tokens[$stack_ptr]['scope_opener'] + 1)) {
        $error = 'The opening and closing braces of empty functions must be directly next to each other; e.g., function () {}';
        $phpcs_file->addError($error, $close_brace, 'SpacingBetween');
      }

      return;
    }

    $brace_line = $tokens[$close_brace]['line'];
    $prev_line = $tokens[$prev_content]['line'];

    $found = ($brace_line - $prev_line - 1);
    if ($phpcs_file->hasCondition($stack_ptr, T_FUNCTION) === TRUE || isset($tokens[$stack_ptr]['nested_parenthesis']) === TRUE) {
      // Nested function.
      if ($found < 0) {
        $error = 'Closing brace of nested function must be on a new line';
        $phpcs_file->addError($error, $close_brace, 'ContentBeforeClose');
      }
      else {
        if ($found > 0) {
          $error = 'Expected 0 blank lines before closing brace of nested function; %s found';
          $data = array($found);
          $phpcs_file->addError($error, $close_brace, 'SpacingBeforeNestedClose', $data);
        }
      }
    }
    else {
      if ($found !== 0) {
        $error = 'Expected 0 blank line before closing function brace; %s found';
        $data = array($found);
        $phpcs_file->addError($error, $close_brace, 'SpacingBeforeClose', $data);
      }
    }
  }
}
