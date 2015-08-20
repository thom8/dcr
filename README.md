# Drupal Code Review (DCR) [![Circle CI](https://circleci.com/gh/alexdesignworks/dcr.svg?style=svg)](https://circleci.com/gh/alexdesignworks/dcr)

# Please note that this project is not at release stage yet.

## What is it?
Drupal Code Review (DCR) is a command-line utility to check that produced code follows Drupal coding standards and best practices.

## More specifically
A wrapper around PHP_CodeSniffer and JSLint with Drupal-related code sniffs.

## Why should I use it?
Simply put - convenience. Run code review by only one command 'dcr'.

## Why does it exist?
The idea behind DCR is driven by the following workflow:
Write code -> Format in IDE -> Code Review before Commit -> Commit

Usually, code formatting in IDE covers about 80% of cases, and the other 20% are left to pre-commit code review, but they are the hardest to find.

## But what about code inspection in IDE?
Code inspection in IDE distracts while writing code. Lets concentrate on writing code first and fixing formatting later!

## I don't have time for code review on current project and the code is too messy anyway. I'll start using it on my next project.
No need to review whole project in one go - only recently changed files would go through DCR. Your existing codebase will progressively become clean. So, don't delay - start using it now!

## Why is it a separate tool?
There was a need in a dead-simple solution to review code using a single command, but also be flexible enough to handle project-based sniff rule customisations and pluggable into a CI pipeline.

DCR contains the following:
*	PHP_CodeSniffer with Drupal, Drupal Secure and Drupal Practice sniffs.
*	Additional Drupal Code Review sniff with more specific Drupal rules.
*	Custom project-related sniff - very handy for any custom inclusions.
*	JavaScript code review using JSLint.
*	Colour CLI output - easy to spot issues.

## Can it automatically review code on commit?
Yes. Use it in git pre-commit hook.

## Can it be used as a part of automated build?
Yes. It can even send emails to authors, whose commits do not comply with code standards.

## Does it automatically fix code?
No. Not yet.
