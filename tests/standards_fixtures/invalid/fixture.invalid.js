(function() {
  var a = 1;
  var b = 1;

  var abc = [1, 2, 4];
  for (var i in abc) {
    abc[i] = 1;
  }

  var callback = function ()
  {
    return 1;
  };
});
