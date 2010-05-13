<?php
	class Template{
		private $contents = '';
		
		public function __construct($file){
			$this->contents = file_get_contents($file);
		}
		
		public function output($contentArray, $loopArray = array()){
			foreach($contentArray as $k => $v){
				if (is_array($v)){
					foreach($v as $tag){
						$startOfLoop = strpos($this->contents, '{'.$tag.'}') + strlen($tag) + 2;
						if ($startOfLoop !== false){
							/* startOfLoop offset makes it so closing tag is always after starting tag */
							$endOfLoop = strpos($this->contents, '{/'.$tag.'}');
							
							$loopText = substr($this->contents, $startOfLoop, $endOfLoop - $startOfLoop);
							$resultText = array();
							
							 for ($r = 0; $r < sizeof($loopArray[$tag]); $r++){
								$resultText[$r] = $loopText;
								
								foreach($loopArray[$tag][$r] as $lk => $lv){
									$resultText[$r] = str_replace('{'.$tag.'_'.$lk.'}', $lv, $resultText[$r]);
								}
								
								$this->contents = str_replace($loopText, $resultText[$r] . $loopText, $this->contents);
							} 
							
							$this->contents = str_replace($loopText, '', $this->contents);
							$this->contents = str_replace('{'.$tag.'}', '', $this->contents);
							$this->contents = str_replace('{/'.$tag.'}', '', $this->contents);
						}
					}
				}else{
					$this->contents = str_replace('{'.$k.'}', $v, $this->contents);
				}
			}
			
			return $this->contents;
		}
		
		public function __destruct(){
			empty($this->contents);
		}
	}

?>