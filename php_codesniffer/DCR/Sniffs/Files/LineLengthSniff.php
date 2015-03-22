<?php
/**
 * @file
 * DCR PHP_CodeSniffer Standard.
 *
 * Checks line length, respecting standard rules and excluding commented-out
 * code blocks.
 */

/**
 * DCR_Sniffs_Files_LineLengthSniff.
 *
 * Checks line length, respecting standard rules and excluding commented-out
 * code blocks.
 */
class DCR_Sniffs_Files_LineLengthSniff extends Generic_Sniffs_Files_LineLengthSniff {

  /**
   * The limit that the length of a line should not exceed.
   *
   * @var int
   */
  public $lineLimit = 80;

  /**
   * The limit that the length of a line must not exceed.
   * But just check the line length of comments....
   *
   * Set to zero (0) to disable.
   *
   * @var int
   */
  public $absoluteLineLimit = 0;


  /**
   * Checks if a line is too long.
   *
   * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
   * @param array $tokens The token stack.
   * @param int $stackPtr The first token on the next line.
   *
   * @return void
   */
  protected function checkLineLength(PHP_CodeSniffer_File $phpcsFile, $tokens, $stackPtr) {
    if (isset(PHP_CodeSniffer_Tokens::$commentTokens[$tokens[$stackPtr - 1]['code']]) === TRUE) {
      $doc_comment_tag = $phpcsFile->findFirstOnLine(T_DOC_COMMENT_TAG, $stackPtr - 1);
      if ($doc_comment_tag !== FALSE) {
        // Allow doc comment tags such as long @param tags to exceed the 80
        // character limit.
        return;
      }
      if ($tokens[($stackPtr - 1)]['code'] === T_COMMENT
        && (preg_match('/^[[:space:]]*\/\/ @.+/', $tokens[($stackPtr - 1)]['content']) === 1
          // Allow anything that does not contain spaces (like URLs) to be
          // longer.
          || strpos(trim($tokens[($stackPtr - 1)]['content'], "/ \n"), ' ') === FALSE)
      ) {
        // Allow @link and @see documentation to exceed the 80 character
        // limit.
        return;
      }

      // Code examples between @code and @endcode are allowed to exceed 80
      // characters.
      if (isset($tokens[$stackPtr]) === TRUE && $tokens[$stackPtr]['code'] === T_DOC_COMMENT_WHITESPACE) {
        $tag = $phpcsFile->findPrevious(array(
          T_DOC_COMMENT_TAG,
          T_DOC_COMMENT_OPEN_TAG,
        ), $stackPtr - 1);
        if ($tokens[$tag]['content'] === '@code') {
          return;
        }
      }

      // Drupal 8 annotations can have long translatable descriptions and we
      // allow them to exceed 80 characters.
      if ($tokens[$stackPtr - 2]['code'] === T_DOC_COMMENT_STRING && strpos($tokens[$stackPtr - 2]['content'], '@Translation(') !== FALSE) {
        return;
      }

      // Allow comments preceded by the line with @code and ended by the line
      // with @endcode to be excluded.
      if ($this->isInCodeExample($phpcsFile, $stackPtr) === TRUE) {
        return;
      }

      parent::checkLineLength($phpcsFile, $tokens, $stackPtr);
    }
  }//end checkLineLength()


  /**
   * Returns the length of a defined line.
   *
   * @return integer
   */
  public function getLineLength(PHP_CodeSniffer_File $phpcsFile, $currentLine) {
    $tokens = $phpcsFile->getTokens();

    $tokenCount = 0;
    $currentLineContent = '';

    $trim = (strlen($phpcsFile->eolChar) * -1);
    for (; $tokenCount < $phpcsFile->numTokens; $tokenCount++) {
      if ($tokens[$tokenCount]['line'] === $currentLine) {
        $currentLineContent .= $tokens[$tokenCount]['content'];
      }
    }

    return strlen($currentLineContent);
  }//end getLineLength()


  /**
   * Determines if a comment line is part of an @code/@endcode example.
   */
  protected function isInCodeExample(PHP_CodeSniffer_File $phpcs_file, $stack_ptr) {
    $tokens = $phpcs_file->getTokens();
    $prev_comment = $stack_ptr;
    $last_comment = $stack_ptr;
    while (($prev_comment = $phpcs_file->findPrevious(array(T_COMMENT), ($last_comment - 1), NULL, FALSE)) !== FALSE) {
      if ($tokens[$prev_comment]['line'] !== ($tokens[$last_comment]['line'] - 1)) {
        return FALSE;
      }

      if ($tokens[$prev_comment]['content'] === '// @code' . $phpcs_file->eolChar) {
        return TRUE;
      }

      if ($tokens[$prev_comment]['content'] === '// @endcode' . $phpcs_file->eolChar) {
        return FALSE;
      }

      $last_comment = $prev_comment;
    }

    return FALSE;
  }
}//end class

?>
