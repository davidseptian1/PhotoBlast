<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Print Collage</title>
  <style>html,body{height:100%;margin:0}img{display:block;max-width:100%;height:auto;width:100%}</style>
  <script>
    window.onload = function(){
      // Open print dialog, then attempt to close the window after short delay
      try { window.print(); } catch(e){}
      setTimeout(function(){ try { window.close(); } catch(e){} }, 1000);
    };
  </script>
</head>
<body>
  <img src="{{ $imageUrl }}" alt="collage">
</body>
</html>
