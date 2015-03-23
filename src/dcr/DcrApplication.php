<?php
/**
 * @file
 * DCR application.
 */

namespace dcr;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use dcr\Commands\ReviewCommand;

class DcrApplication extends Application {

  /**
   * Default command implementation.
   */
  protected function getCommandName(InputInterface $input) {
    return 'dcr:review';
  }

  /**
   * Gets the default commands that should always be available.
   */
  protected function getDefaultCommands() {
    // Keep the core default commands to have the HelpCommand
    // which is used when using the --help option.
    $defaultCommands = parent::getDefaultCommands();
    $defaultCommands[] = new ReviewCommand();

    return $defaultCommands;
  }

  /**
   * Gets the InputDefinition related to this Application.
   *
   * Overridden so that the application doesn't expect the command
   * name to be the first argument.
   */
  public function getDefinition() {
    $inputDefinition = parent::getDefinition();
    // Clear out the normal first argument, which is the command name.
    $inputDefinition->setArguments(array());

    return $inputDefinition;
  }
}
