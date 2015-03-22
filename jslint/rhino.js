// rhino.js
// 2009-09-11
/*
Copyright (c) 2002 Douglas Crockford  (www.JSLint.com) Rhino Edition
*/

// Modified by Alex Skrypnyk (alex.designworks@gmail.com)  to accept command
// line switches.
// @example
// jslint.js file.js --unparam --indent=2 --vars

// This is the Rhino companion to fulljslint.js.

/*global JSLINT */
/*jslint rhino: true, strict: false */

(function (a) {
  var e, i, input;
  if (!a[0]) {
    print("Usage: jslint.js file.js [jslint options]");
    quit(1);
  }
  input = readFile(a[0]);
  if (!input) {
    print("jslint: Couldn't open file '" + a[0] + "'.");
    quit(1);
  }

  var defaults = {
    bitwise: true,
    eqeqeq: true,
    immed: true,
    newcap: true,
    nomen: true,
    onevar: true,
    plusplus: true,
    regexp: true,
    rhino: true,
    undef: true,
    white: true
  };

  var options = args2obj(a.length > 1 ? a.slice(1) : []);
  options = merge_options(options, defaults);
  if (!JSLINT(input, options)) {
    for (i = 0; i < JSLINT.errors.length; i += 1) {
      e = JSLINT.errors[i];
      if (e) {
        print('Lint at line ' + e.line + ' character ' +
          e.character + ': ' + e.reason);
        print((e.evidence || '').
          replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1"));
        print('');
      }
    }
    quit(2);
  }
  else {
    print("jslint: No problems found in " + a[0]);
    quit();
  }

  /**
   * Convert a string of command line arguments to object.
   * @param args
   *   String of command line arguments.
   * @return
   *   Command line arguments as an object.
   */
  function args2obj(args) {
    var obj = {};
    if (!args.length) {
      return obj;
    }

    for (var i in args) {
      var key, val, pair;
      pair = args[i].split('=');
      key = pair[0].trim().replace('--', '');
      val = pair.length > 1 ? pair[1].trim() : true;
      obj[key] = val;
    }

    return obj;
  }

  /**
   * Overwrites obj1's values with obj2's and adds obj2's if non existent in obj1.
   * @param obj1
   * @param obj2
   * @return obj3 a new object based on obj1 and obj2
   */
  function merge_options(obj1, obj2) {
    var obj3 = {};
    for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
    for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
    return obj3;
  }
}(arguments));