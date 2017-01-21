<?php 
ini_set('auto_detect_line_endings', true);
require_once("input_stream.php");
require_once("ast_gen.php");
require_once("ast_interpreter.php");

if (isset($_REQUEST['run_code'])){
  
  $input_stream = new input_stream($_POST['code_box']);
  $input_stream->read_all();

  $tokens = $input_stream->get_tokens();
  $ast_gen = new ast_gen($tokens);
  $ast_interpreter = new ast_interpreter($ast_gen->get_function_libs());




}else{
	$_REQUEST['code_box'] = '';
}


?>
<!DOCTYPE html>
<html>
<head>
	<title>R2Kiddo</title>
	<style type="text/css">
		body{
			background-color: #000;
			color: #fff;
			font-size: 17px;
		}

		body *{
			margin-bottom: 11px;
			margin-top: 11px;
			font-size: 20px;
		}
	</style>
</head>
<body>
  
   <form method="post">

      <div>
      	<h2 align="center">R2Kiddo</h2>
      </div>
   	
   	  <div style="padding: 11px;">
   	  	
   	  	<textarea name="code_box" style="width: 100%;height: 300px;margin: auto;display: inline-block;"><?php
  
           echo $_REQUEST['code_box'];

   	  	 ?></textarea>

   	  </div>

   	  <div align="center">
   	  	 
   	  	 <input type="submit" name="run_code" value="RUN" style="padding: 11px;font-size: 15px;" />

   	  </div>

      <?php 
        if (isset($input_stream)){
      ?>
   	  <div style="padding: 11px;"> 
   	  	
         <textarea name="code_output" style="background-color: #aaa;color: #000;width: 100%;height: 200px;margin: auto;display: inline-block;"><?php
              //print_r($ast_gen->run());
              

              $ast = $ast_gen->run();
              //print_r($ast);
              //$ast_interpreter->new_scope();
              //print_r($ast_gen->get_function_libs());
              //$ast_interpreter->init($ast_gen->get_function_libs());
              $ast_interpreter->eval_($ast);
              //print_r($ast_interpreter->get_scopes_debug());
              //print_r($input_stream->get_tokens());
          ?></textarea>   	  	

   	  </div>
   	  <?php 
        }
   	  ?>



   	  




   </form>



</body>
</html>
