<?php 
 class input_stream{
    
    private $pointer = -1;
    private $tokens = array();
    private $ch = '';
    private $code = '';
    private $keywords = array();

    private $line = 1;
    private $col = 0;
    private $new_line = "
    ";


    function __construct($code=''){
      $this->code = $code;
      $this->load_keywords();
    }


    private function load_keywords(){
      $this->keywords[] = ';';
      $this->keywords[] = '.';
      $this->keywords[] = '$';
      $this->keywords[] = ',';
      $this->keywords[] = '+';
      $this->keywords[] = '-';
      $this->keywords[] = '*';
      $this->keywords[] = '/';
      $this->keywords[] = '=';
      $this->keywords[] = '(';
      $this->keywords[] = ')';
      $this->keywords[] = '[';
      $this->keywords[] = ']';
      $this->keywords[] = '{';
      $this->keywords[] = '}';
      $this->keywords[] = '<';
      $this->keywords[] = '>';
      $this->keywords[] = '|';
      $this->keywords[] = '&';
      $this->keywords[] = '!';
    }


    private function eof(){
    	return ($this->pointer >= strlen($this->code));
    }

    private function next(){
    	++$this->pointer;
    	if ($this->eof()){
          return;
    	}
    	$this->ch = $this->code{$this->pointer};// substr($this->code, $this->pointer,1);  //$this->code[$this->pointer];

    	if ($this->ch == "\n"){
    		//echo 'seen.';
          ++$this->line;
          $this->col = 1;
    	}else{
    	  ++$this->col;	
    	}

    }

    private function peek(){
    	return $this->ch;
    }

    private function peek_next(){
    	return $this->code{$this->pointer + 1};
    }

    function read_all(){
    	
    	$this->next();
    	while (!$this->eof()){


    	 if ($this->peek() == '"'){
    	 	//echo 'cl1';
            $this->read_closers('"');
    	 }else if ($this->peek() == "'"){
    	 	//echo 'cl2';
             $this->read_closers("'"); 
         }else if ($this->read_doubles('=','=')){
              
    	 }else if ($this->read_doubles('<','=')){

    	 }else if ($this->read_doubles('>','=')){

         }else if ($this->read_doubles('!','=')){    	 	

         }else if ($this->read_doubles('|','|')){         	

         }else if ($this->read_doubles('&','&')){   

         }else if ($this->peek() == "/" && $this->peek_next() == '/'){      	
            $this->skip_single_line_comments();
         }else if ($this->peek() == "/" && $this->peek_next() == '*'){      	
            $this->skip_multiline_comments();
    	 }else{

            $this->read_idt();

    	 }	
          
         $this->next();

    	}

    }

    function skip_single_line_comments(){
    	$this->next();
    	while (!$this->eof() && $this->peek() != "\n"){
          $this->next();
    	}
    }

    function skip_multiline_comments(){
    	$this->next();
    	$this->next();

    	while(!$this->eof() && (($this->peek() . $this->peek_next()) != "*/")){
          $this->next();
    	}
    	
    	//echo $this->peek() . 'PPK';
    	
    	if ($this->peek() == "*"){
           $this->next();
           $this->next();
    	}

    }


    function read_doubles($c1,$c2){
      
      $str = '';
      $r = false;
      if (($this->pointer + 1) < strlen($this->code) && $this->peek() == $c1 && $this->peek_next() == $c2){
        $str.=$this->peek();
        $this->next();
        $str.=$this->peek();
        $this->tokens[] = $str;
        $r = true;
      }


      return $r;

    }


    function read_closers($closer){
      $this->next();
      $str = '';
      while (!$this->eof() && $this->peek() != $closer){
         if ($this->peek() == "\\"){
           $str.=$this->peek();
           $this->next();
           $str.=$this->peek();
           $this->next();
         }else{
           $str.=$this->peek();
           $this->next();
         }
      }
      if (!empty($str)){
        $this->tokens[] = $str;
      }
      
    }


    function read_idt(){
      $str = "";
      while (!$this->eof() && !is_null($this->peek()) && $this->peek() != " " && $this->peek() != "\n" && !in_array($this->peek(), $this->keywords)){
           
           $str.=$this->peek();
           $this->next();

      }


     $str = trim($str);
      if ($str != ""){
        $this->tokens[] = $str;
      } 

      if (in_array($this->peek(), $this->keywords)){

         if ($this->read_doubles('=','=')){
              
    	 }else if ($this->read_doubles('<','=')){

    	 }else if ($this->read_doubles('>','=')){

         }else if ($this->read_doubles('!','=')){    	 	

         }else if ($this->read_doubles('|','|')){         	

         }else if ($this->read_doubles('&','&')){

         }else{
           $this->tokens[] = $this->peek();	
         }         	
         
      }


    }


    function get_tokens(){
    	return $this->tokens;
    }


 }
?>
