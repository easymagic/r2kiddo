<?php

 class ast_gen{
   
     private $pointer = -1;
     private $tokens = array();
     private $token = null;
     private $symbols = array();
     private $chr = '';

     private $function_lib = array();

     function __construct($tks){
      $this->tokens = $tks;
      $this->load_libs();
     }

     function next(){
       ++$this->pointer;

       if ($this->pointer >= count($this->tokens)){
         return;
       }

       if (!isset($this->symbols[$this->tokens[$this->pointer]])){
       	$tks = $this->tokens[$this->pointer];
         $this->add_symbol($this->tokens[$this->pointer],array(
           "bp"=>0,
           "nud"=>function() use ($tks){
           	return $tks;
           },
           "led"=>function($left) use ($tks){
           	return $tks;
           }
         ));
       }

       $this->token = $this->symbols[$this->tokens[$this->pointer]];

       $this->chr = $this->tokens[$this->pointer];

       return $this->token;

     }

     function peek(){
     	return $this->chr;
     }

     function add_symbol($sym,$callback){
       $this->symbols[$sym] = $callback;
     }

     private function load_libs(){
     	

        //plus (+)
     	$this->add_symbol('+',array(
          "bp"=>10,
          "led"=>function($left){
             $ast = array();
             $ast['left'] = $left;
             $ast['right'] = $this->expr(10);
             $ast['node'] = 'lib_plus';
             $this->skip_semicolon();
             return $ast;
           }
     	));




        //minus (-)
     	$this->add_symbol('-',array(
          "bp"=>10,
          "led"=>function($left){
             $ast = array();
             $ast['left'] = $left;
             $ast['right'] = $this->expr(10);
             $ast['node'] = 'lib_minus';
             $this->skip_semicolon();
             return $ast;
           }
     	));


        //mult (*)
     	$this->add_symbol('*',array(
          "bp"=>20,
          "led"=>function($left){
             $ast = array();
             $ast['left'] = $left;
             $ast['right'] = $this->expr(20);
             $ast['node'] = 'lib_mult';
             $this->skip_semicolon();
             return $ast;
           }
     	));


        //div (/)
     	$this->add_symbol('/',array(
          "bp"=>20,
          "led"=>function($left){
             $ast = array();
             $ast['left'] = $left;
             $ast['right'] = $this->expr(20);
             $ast['node'] = 'lib_minus';
             $this->skip_semicolon();
             return $ast;
           }
     	));


        //parenthesis (())
     	$this->add_symbol('(',array(
          "bp"=>0,
          "nud"=>function(){
             $ast = array();
             $ast['right'] = $this->expr(0);
             $this->next();//skip the ) -> symbol.
             $ast['node'] = 'lib_paren';
             $this->skip_semicolon();
             return $ast;
           }
     	));


        //parenthesis (())
     	$this->add_symbol('=',array(
          "bp"=>5,
          "led"=>function($left){
             $ast = array();
             $ast['left'] = $left;
             $ast['right'] = $this->expr(5);
             //$this->next();//skip the ) -> symbol.
             $ast['node'] = 'lib_assignment';
             $this->skip_semicolon();
             return $ast;
           }
     	));


        //block (begin-end)
     	$this->add_symbol('begin',array(
          "bp"=>0,
          "nud"=>function(){
             $ast = array();

             $rht = array();

             do{
              
               $rht[] = $this->expr(0);
            
             }while($this->chr != 'end');

             if ($this->chr == 'end'){
               $this->next();
             }

             

             $ast['right'] =  $rht;
             //$this->next();//skip the ) -> symbol.
             $ast['node'] = 'lib_block';
             $this->skip_semicolon();
             return $ast;
           }
     	));

     	
     	//print
     	$this->add_symbol('print',array(
         "bp"=>0,
         "nud"=>function(){
         	$ast = array();
         	$ast['node'] = 'lib_print';
         	$this->next();
         	$rr = array();
         	do{
              
              $rr[] = $this->expr(0);
              
              if ($this->chr == ','){
               $this->next();
              }
              
         	}while($this->chr != ')');
         	
         	if ($this->chr == ')'){
              $this->next();
         	}

         	$this->skip_semicolon();

         	$ast['right'] = $rr;
         	return $ast;

         }
     	));


     	//concat $
     	$this->add_symbol('$',array(
         "bp"=>6,
         "led"=>function($left){
            $ast = array();
            $ast['node'] = 'lib_concat';
            $ast['left'] = $left;
            $ast['right'] = $this->expr(6);
            $this->skip_semicolon();
            return $ast;
         }
     	));



     	//if 
     	$this->add_symbol('if',array(
         "bp"=>0,
         "nud"=>function(){
            $ast = array();
            $ast['node'] = 'lib_if';
            $this->next(); //(
            $ast['cond'] = $this->expr(0);
            $this->next(); //)
            $ast['action'] = $this->expr(0);
            if ($this->chr == 'else'){
              $this->next();	
              $ast['else_part'] = $this->expr(0);
            }
            $this->skip_semicolon();
            return $ast;
         }
     	));

     	//cond_equal
     	$this->add_symbol('==',array(
          "bp"=>6,
          "led"=>function($left){
             $ast = array();
             $ast['node'] = 'cond_equal';
             $ast['left'] = $left;
             $ast['right'] = $this->expr(6);
             $this->skip_semicolon();
             return $ast;
          }
     	));



     	//cond_less
     	$this->add_symbol('<',array(
          "bp"=>6,
          "led"=>function($left){
             $ast = array();
             $ast['node'] = 'cond_less';
             $ast['left'] = $left;
             $ast['right'] = $this->expr(6);
             $this->skip_semicolon();
             return $ast;
          }
     	));

     	//cond_great
     	$this->add_symbol('>',array(
          "bp"=>6,
          "led"=>function($left){
             $ast = array();
             $ast['node'] = 'cond_great';
             $ast['left'] = $left;
             $ast['right'] = $this->expr(6);
             $this->skip_semicolon();
             return $ast;
          }
     	));



     	//cond_equal_less
     	$this->add_symbol('<=',array(
          "bp"=>6,
          "led"=>function($left){
             $ast = array();
             $ast['node'] = 'cond_equal_less';
             $ast['left'] = $left;
             $ast['right'] = $this->expr(6);
             $this->skip_semicolon();
             return $ast;
          }
     	));


     	//cond_equal_great
     	$this->add_symbol('>=',array(
          "bp"=>6,
          "led"=>function($left){
             $ast = array();
             $ast['node'] = 'cond_equal_great';
             $ast['left'] = $left;
             $ast['right'] = $this->expr(6);
             $this->skip_semicolon();
             return $ast;
          }
     	));


     	//cond_equal_not
     	$this->add_symbol('!=',array(
          "bp"=>6,
          "led"=>function($left){
             $ast = array();
             $ast['node'] = 'cond_equal_not';
             $ast['left'] = $left;
             $ast['right'] = $this->expr(6);
             $this->skip_semicolon();
             return $ast;
          }
     	));


     	//lib_function_call
     	//function_lib
     	$this->add_symbol('function',array(
          "bp"=>0,
          "nud"=>function(){
             $ast = array();
             $ast['function_name'] = $this->chr;
             $this->next();
             $this->next();

             $body = array(); //hold pointer to this variable ...

             $args = array();
             
             do{
               if ($this->chr == ','){
                 $this->next();
               }else{
                 $args[] = $this->expr(0);
               }   
             }while($this->chr != ')');

             if ($this->chr == ')'){
               $this->next();
             }

             //$ast['args'] = $args;

             $function_name = $ast['function_name'];

             $this->add_symbol($function_name,array(
               "bp"=>0,
               "nud"=>function() use ($function_name,$args){
               	 
               	  $ast_ = array();
               	  $ast_['node'] = 'lib_function_call';
               	  $ast_['args'] = $args;
               	  $ast_['function_name'] = $function_name;
               	  $this->next();

		             $args_activation = array();
		             
		             do{
		               if ($this->chr == ','){
		                 $this->next();
		               }else{
		                 $args_activation[] = $this->expr(0);
		               }   
		             }while($this->chr != ')');

		             if ($this->chr == ')'){
		               $this->next();
		             }

		          $ast_['args_activation'] = $args_activation;

		          $this->skip_semicolon();

                  return $ast_;

               }

             ));

             $body = $this->expr(0);

             //register a reference to this function.
             $this->function_lib[$function_name] = array(
              "args"=>$args,
              "body"=>$body
             );


             

             //$ast['body'] = $this->expr(0);
             $this->skip_semicolon();
             return array(
              "node"=>'__function_declaration__',
              "function_name"=>$function_name,
              "function_def"=>$this->function_lib[$function_name]
             );

             //return '__function_declaration__';

          }
     	));

     	//lib_return
     	$this->add_symbol('return',array(
         "bp"=>0,
         "nud"=>function(){
         	$ast = array();
            $ast['node'] = 'lib_return';
            $ast['right'] = $this->expr(0);
         	$this->skip_semicolon();
         	return $ast;
         }
     	));
    
        //lib_scope_debug
        $this->add_symbol('scope_debug',array(
         "bp"=>0,
         "nud"=>function(){
         	$ast = array();
         	$ast['node'] = 'lib_scope_debug';
         	$this->next();
         	$this->next();
         	$this->skip_semicolon();
         	return $ast;
         }
        ));



        //lib_class
        $this->add_symbol('class',array(
          "bp"=>0,
          "nud"=>function(){
          	$ast = array();
          	$ast['node'] = 'lib_class';
          	$ast['class_name'] = $this->peek();
          	$this->next();
          	$ast['class_definition'] = $this->expr(0);
          	$this->skip_semicolon();
          	return $ast;
          }
        ));


        //lib_class_create
        $this->add_symbol('new',array(
          "bp"=>0,
          "nud"=>function(){
          	$ast = array();
          	$ast['node'] = 'lib_class_create';
          	$ast['class_name'] = $this->peek();
          	$this->next();
          	$this->skip_semicolon();
          	return $ast;
          }
        ));

        //lib_dot_op
        $this->add_symbol('.',array(
         "bp"=>90,
         "led"=>function($left){
           $ast = array();
           $ast['node'] = 'lib_dot_op';
           $ast['scope'] = $left;
           //$right = $this->peek();
           //$this->next();

           $ast['right'] = $this->expr(0);
           

           if ($this->chr == '='){
            $ast['mode'] = 'set';
            $this->next();
            $ast['set_value'] = $this->expr(0);
           }else{
            $ast['mode'] = 'get';
           } 

           return $ast;
           // if ($this->peek() == '('){

           // }

         }
        ));







     }

     private function skip_semicolon(){
     	if ($this->chr == ';'){
         $this->next();
     	}
     }

     function expr($bp){
       
       $tok = $this->token;

       $this->next();

       $left = $tok['nud']();

       while ($bp < $this->token['bp']){

       	  $tk = $this->token;
       	  $this->next();

       	  $left = $tk['led']($left);

       } 

       return $left;

     }


     function run(){
     	$this->next();
     	$ast = $this->expr(0);
     	return $ast;
     }


     function get_function_libs(){
     	return $this->function_lib;
     }




 }




?>
