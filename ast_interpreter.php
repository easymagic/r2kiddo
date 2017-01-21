<?php 
 class ast_interpreter{
   
   private $function_lib = array();
   
    function __construct($function_lib){
      $this->function_lib = $function_lib;
    }

    function init($function_lib){
     $this->function_lib = $function_lib;	
    }

   
    function eval_($ast,&$scope=array()){
      
      if (is_array($ast)){
       return call_user_func_array(array($this,$ast['node']), array($ast,&$scope));
      }else{
      	//print_r($scope);
        if (isset($scope[$ast])){
          //echo 'set';	
          return $scope[$ast];
        }else{
        	//echo 'not_set';	
          return $ast;	
        }
      } 
    }


    function lib_plus($ast,$scope){
       return ($this->eval_($ast['left'],$scope) + $this->eval_($ast['right'],$scope));
    }


    function lib_minus($ast,$scope){
       return ($this->eval_($ast['left'],$scope) - $this->eval_($ast['right'],$scope));
    }


    function lib_mult($ast,$scope){
    	//print_r($ast);
       return ($this->eval_($ast['left'],$scope) * $this->eval_($ast['right'],$scope));
    }


    function lib_div($ast,$scope){
       return ($this->eval_($ast['left'],$scope) / $this->eval_($ast['right'],$scope));
    }

    function lib_paren($ast,$scope){
       return ($this->eval_($ast['right'],$scope));
    }

    function lib_block($ast,&$scope){
      $rr = $ast['right'];
      $r = null;
      $new_scope = array();
      foreach ($scope as $k=>$v){
        $new_scope[$k] =& $scope[$k];
      }
      foreach ($rr as $k=>$v){
         $r = $this->eval_($v,$new_scope);
      }
      //print_r($new_scope);
      if (is_array($r)){
        return $new_scope;
      }else{
        return $r;
      }
      // return $r;
    }


    // function lib_mult($ast,$scope){
    //    return ($this->eval_($ast['left'],$scope) * $this->eval_($ast['right'],$scope));
    // }

    function lib_print($ast,&$scope){
      $rr = array();
      foreach ($ast['right'] as $k=>$v){
        $rr[] = $this->eval_($v,$scope);
      }
      //print_r($scope);
      echo implode(' , ', $rr) . "\n";
    }

    function lib_concat($ast,$scope){
      return $this->eval_($ast['left'],$scope) . $this->eval_($ast['right'],$scope);
    }

    function lib_assignment($ast,&$scope){
      $scope[$ast['left']] = $this->eval_($ast['right'],$scope);
      return $scope[$ast['left']];
    }

    function lib_if($ast,$scope){
      
      if ($this->eval_($ast['cond'],$scope)){
        return $this->eval_($ast['action'],$scope);
      }else{
         if (isset($ast['else_part'])){
           return $this->eval_($ast['else_part'],$scope);
         }else{
         	return false;
         }
      }

    }


    function cond_equal($ast,$scope){
      return ($this->eval_($ast['left'],$scope) == $this->eval_($ast['right'],$scope));
    }

    //cond_less
    function cond_less($ast,$scope){
      return ($this->eval_($ast['left'],$scope) < $this->eval_($ast['right'],$scope));
    }

    //cond_great
    function cond_great($ast,$scope){
      return ($this->eval_($ast['left'],$scope) > $this->eval_($ast['right'],$scope));
    }


    //cond_equal_less
    function cond_equal_less($ast,$scope){
      return ($this->eval_($ast['left'],$scope) <= $this->eval_($ast['right'],$scope));
    }


    //cond_equal_great
    function cond_equal_great($ast,$scope){
      return ($this->eval_($ast['left'],$scope) >= $this->eval_($ast['right'],$scope));
    }

    //cond_equal_not
    function cond_equal_not($ast,$scope){
      return ($this->eval_($ast['left'],$scope) != $this->eval_($ast['right'],$scope));
    }

    //lib_function_call
    function lib_function_call($ast,&$scope){
     
     $args = $ast['args'];
     $args_activation = $ast['args_activation'];

     $aa = array();

     foreach ($args_activation as $k=>$v){
       $aa[] = $this->eval_($v,$scope);
     }


     $new_scope = array();
     foreach ($scope as $k=>$v) {
      	//if (!is_array($v)){
     	  //print_r($scope[$k]);
          $new_scope[$k] =& $scope[$k];  
      	//}     	
     	
     }

     //print_r($new_scope);

     foreach ($aa as $k=>$v){
      $new_scope[$args[$k]] =& $aa[$k];
     }

     //print_r($scope);

     return $this->eval_($scope[$ast['function_name']]['body'],$new_scope);

    }

    function lib_return($ast,&$scope){
      return $this->eval_($ast['right'],$scope);
    }

    function __function_declaration__($ast,&$scope){
     $scope[$ast['function_name']] = $ast['function_def'];
     return $scope[$ast['function_name']];
    }

    function lib_scope_debug($ast,&$scope){
      print_r($scope);
    }


    //lib_class
    function lib_class($ast,&$scope){
      
      $class_name = $ast['class_name'];
      $new_scope = array();
      
      foreach ($scope as $k=>$v){
      	$new_scope[$k] = $v;       
      }

      $scope[$class_name] = $this->eval_($ast['class_definition'],$new_scope);

      //$scope[$class_name] = $new_scope;

      //print_r($ast);
      //print_r($scope);

      return $scope[$class_name];

    }


    //lib_class_create
    function lib_class_create($ast,&$scope){
       
       //print_r($scope);
       
       $class_name = $ast['class_name'];
   
       $new_scope = array();

       $class_template = $scope[$class_name];

       foreach ($class_template as $k=>$v){
         $new_scope[$k] = $v;
       }

      return $new_scope;

    }


    //lib_dot_op
    function lib_dot_op($ast,&$scope){
      //lib_dot_op
      
      //print_r($ast);

      //print_r($scope);


    if ($ast['mode'] == 'get'){

      $scope_ext =& $scope[$ast['scope']];

      //print_r($scope_ext);

      return $this->eval_($ast['right'],$scope_ext);


    }	


    }



 }
?>
