<?php
/*
	Shopify PHP API
	Created: May 4th, 2010
	Modified: May 13th, 2010
	Version: 1.20100513.4
*/
	//this function is just to make the code a little cleaner
	function isEmpty($string){
		return (strlen(trim($string)) == 0);
	}
	
	//this function will url encode paramaters assigned to API calls
	function url_encode_array($params){
		$string = '';
		if (sizeof($params) > 0){
			foreach($params as $k => $v) if (!is_array($v)) $string .= $k.'='.$v.'&';
			$string = substr($string, 0, strlen($string) - 1);
		}
		return $string;
	}
	
	/*
		organizeArray applies some changes to the array that is generated from returned XML
		This is done so that traversing the result is easier to manipulate by setting the index
		of returned data to the actual ID of the record
	*/
	function organizeArray($array, $type){		
		if (FORMAT == ".json"){
			if (isset($array[$type . 's'])){
				$array[$type] = $array[$type . 's'];
				unset($array[$type . 's']);
			}
		}
		
		/* no organizing needed */
		if (!isset($array[$type][0])){
			$temp = $array[$type];
			$id = $temp['id'];
			$array[$type] = array();
			$array[$type][$id] = $temp;
		}else{
			foreach($array[$type] as $k => $v){
				$id = $v['id'];
				$array[$type][$id] = $v;
				unset($array[$type][$k]);
			}		
		}
		
		return $array;
	}
	
	function arrayToXML($array, $xml = ''){
		if ($xml == "") $xml = '<?xml version="1.0" encoding="UTF-8"?>';
		foreach($array as $k => $v){
			if (is_array($v)){
				$xml .= '<' . $k . '>';
				$xml = arrayToXML($v, $xml);
				$xml .= '</' . $k . '>';
			}else{
				$xml .= '<' . $k . '>' . $v . '</' . $k . '>';
			}
		}	
		return $xml;
	}
	
	function sendToAPI($url, $request = 'GET', $successCode = SUCCESS, $xml = array()){
		$xml = arrayToXML($xml);
		$ch = new miniCURL();
		$data = $ch->send($url, $request, $xml);
		if ($data[0] == $successCode) return $ch->loadString($data[1]);					
		return $data[0]; //returns the HTTP Code (200, 201 etc) if the expected $successCode was not met
	}
	
	function gzdecode($data){
		$g = tempnam('/tmp','ff');
		@file_put_contents($g, $data);
		ob_start();
		readgzfile($g);
		$d = ob_get_clean();
		unlink($g);
		return $d;
	}

	class ApplicationCharge{
		private $prefix = "/application_charges";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site .  $this->prefix;
		}
		
		public function get($cache = false){
			if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . FORMAT), 'record');
			return $this->array['record'];
		}
		
		public function create($fields){
			$fields = array('application-charge' => $fields);
			return sendToAPI($this->prefix . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function activate($id){
			return sendToAPI($this->prefix . "/" . $id . "/activate" . FORMAT, 'PUT', SUCCESS);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class RecurringApplicationCharge{
		private $prefix = "/recurring_application_charges";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site .  $this->prefix;
		}
		
		public function get($cache = false){
			if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . FORMAT), 'recurring-application-charge');		
			return $this->array['recurring-application-charge'];
		}
		
		public function create($fields){
			$fields = array('recurring-application-charge' => $fields);
			return sendToAPI($this->prefix . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function activate($id, $fields){
			$fields = array('recurring-application-charge' => $fields);
			return sendToAPI($this->prefix . "/" . $id . "/activate" . FORMAT, 'PUT', SUCCESS, $fields);
		}
		
		public function cancel($id){
			return sendToAPI($this->prefix . "/" . $id . FORMAT, 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}	

	class Article{
		private $prefix = "/blogs/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function get($blog_id, $article_id = 0, $cache = false, $params = array()){
			if ($article_id == 0){
				if (!$cache){
					$params = url_encode_array($params);
					$this->array = organizeArray(sendToAPI($this->prefix . $blog_id . '/articles.xml?' . $params), 'article');
				}
			
				return $this->array['article'];
			}else{
				if (!$cache){
					$temp = sendToAPI($this->prefix . $blog_id . '/articles/' . $article_id . '.xml');
					$this->array['article'][$article_id] = $temp;
				}			
				if (!isset($this->array['article'][$article_id])) throw new Exception("Article is not in cache. Set cache to false.");
				return $this->array['article'][$article_id];
			}
		}
		
		public function count($blog_id, $params = array()){
			$params = url_encode_array($params);
			return sendToAPI($this->prefix . $blog_id . '/articles/count.xml?' . $params);
		}
		
		public function create($blog_id, $fields){
			$fields = array('article' => $fields);
			return sendToAPI($this->prefix . $blog_id . "/articles" . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function modify($blog_id, $id, $fields){
			$fields = array('article' => $fields);
			return sendToAPI($this->prefix . $blog_id . "/articles/" . $id . FORMAT, 'PUT', SUCCESS, $fields);
		}
		
		public function remove($id){
			return sendToAPI($this->prefix . $blog_id . "/articles/" . $id . FORMAT, 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class Asset{
		private $prefix = "/assets";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
			
		public function get($key = '', $cache = false){
			if (isEmpty($key)){
				if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix), 'asset');		
				return $this->array['asset'];
			}else{
				if (!$cache){
					$temp = sendToAPI($this->prefix . FORMAT . '?asset[key]=' . $key);
					$this->array['asset'][$key] = $temp;
				}
				if (!isset($this->array['asset'][$key])) throw new Exception("Asset does not exist in cache. Change cache to false.");		
				return $this->array['asset'][$key];
			}
		}
			
		public function modify($fields){
			$fields = array('asset' => $fields);
			return sendToAPI($this->prefix, 'PUT', SUCCESS, $fields);
		}
		
		public function copy($fields){
			$fields = array('asset' => $fields);
			return sendToAPI($this->prefix, 'PUT', SUCCESS, $fields);			
		}
		
		public function remove($key){
			return sendToAPI($this->prefix . FORMAT . "?asset[key]=" . $key, 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}		
	}
	
	class Blog{
		private $prefix = "/blogs";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site .  $this->prefix;
		}
		
		public function get($id = 0, $cache = false){
			if ($id == 0){
				if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . FORMAT), 'blog');
				return $this->array['blog'];
			}else{
				if (!$cache){
					$temp = sendToAPI($this->prefix . "/" . $id . FORMAT);
					$this->array['blog'][$id] = $temp;
				}
				if (!isset($this->array['blog'][$id])) throw new Exception("Blog doesn't exist in cache. Turn cache to false.");		
				return $this->array['blog'][$id];
			}
		}
		
		public function count(){
			return sendToAPI($this->prefix . "/count" . FORMAT . "?");
		}

		public function create($fields){
			$fields = array('blog' => $fields);
			return sendToAPI($this->prefix . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function modify($id, $fields){
			$fields = array('blog' => $fields);
			return sendToAPI($this->prefix . "/" . $id . FORMAT, 'PUT', SUCCESS, $fields);
		}
		
		public function remove($id){
			return sendToAPI($this->prefix . "/" . $id . FORMAT, 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class CustomCollection{
		private $prefix = "/";	
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function get($id = 0, $params = array(), $cache = false){
			if ($id == 0){
				if (!$cache){
					$params = url_encode_array($params);			
					$this->array = organizeArray(sendToAPI($this->prefix . "custom_collections" . FORMAT . "?" . $params), 'custom-collection');
				}			
				return $this->array['custom-collection'];
			}else{
				if (!$cache){
					$temp = sendToAPI($this->prefix . "/custom_collections/" . $id . FORMAT);
					$this->array['custom-collection'][$id] = $temp;				
				}
				if (!isset($this->array['custom-collection'][$id])) throw new Exception("Collection not in the cache. Set cache to false.");			
				return $this->array['custom-collection'][$id];
			}
		}
		
		public function count($params = array()){
			$params = url_encode_array($params);			
			return sendToAPI($this->prefix . "custom_collections/count" . FORMAT . "?" . $params);
		}
		
		public function create($fields){
			$fields = array('custom-collection' => $fields);
			return sendToAPI($this->prefix . "custom_collections" . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function modify($id, $fields){
			$fields = array('custom-collection' => $fields);
			return sendToAPI($this->prefix . "custom_collections/" . $id . FORMAT, 'PUT', SUCCESS, $fields);
		}
		
		public function remove($id){
			return sendToAPI($this->prefix . "custom_collections/" . $id . FORMAT, 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class Collect{
		private $prefix = "/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function get($id = 0, $params = array(), $cache = false){
			if ($id == 0){
				if (!$cache){
					$params = url_encode_array($params);
					$this->array = organizeArray(sendToAPI($this->prefix . "collects" . FORMAT . "?" . $params), 'collect');
				}			
				return $this->array['collect'];
			}else{
				$collect = array();

				if (!$cache){
					$params = url_encode_array($params);
					if ($id > 0){
						$temp = sendToAPI($this->prefix . "collects/" . $id . "" . FORMAT . "?" . $params);
						$this->array['collect'][$id] = $temp;
						$collect = $temp;
					}else{
						if (isset($params['product_id']) && isset($params['collection_id'])){
							$temp = sendToAPI($this->prefix . "/collects" . FORMAT . "?" . $params);

							if (isset($temp['collect'][0])){
								$id = $temp['collect'][0]['id'];
								$this->array['collect'][$id] = $temp['collect'][0];
								$collect = $temp['collect'][0];
							}
						}else{
							throw new Exception("Must specify a collect id or product_id and collection_id.");										
						}
					}
				}

				return $collect;
			}
		}
		
		public function count($params = array()){
			$params = url_encode_array($params);
			return sendToAPI($this->prefix . "collects" . FORMAT . "?" . $params);
		}
		
		public function create($fields){
			$fields = array('collect' => $fields);
			return sendToAPI($this->prefix . "collects" . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function remove($id){
			return sendToAPI($this->prefix . "custom_collections" . FORMAT, 'POST', CREATED);
		}
					
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class Comment{
		private $prefix = "/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function get($id = 0, $params = array(), $cache = false){
			if ($id == 0){
				if (!$cache){
					$params = url_encode_array($params);
					$this->array = organizeArray(sendToAPI($this->prefix . "comments" . FORMAT . "?" . $params), 'comment');
				}			
				return $this->array['comment'];
			}else{
				if (!$cache){
					$temp = sendToAPI($this->prefix . "comments/" . $id . FORMAT);
					$this->array['comment'][$id] = $temp;
				}
				if (!isset($this->array['comment'][$id])) throw new Exception("Comment is not in cache. Set cache to false.");
				return $this->array['comment'][$id];
			}
		}
		
		public function count($params = array()){
			$params = url_encode_array($params);
			return sendToAPI($this->prefix . "comments/count" . FORMAT . "?" . $params);
		}
		
		public function create($fields){
			$fields = array('comment' => $fields);
			return sendToAPI($this->prefix . "comments" . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function modify($id, $fields){
			$fields = array('comment' => $fields);
			return sendToAPI($this->prefix . "comments/" . $id . FORMAT, 'POST', SUCCESS, $fields);
		}
		
		public function markAsSpam($id){
			return sendToAPI($this->prefix . "comments/" . $id . "/spam" . FORMAT, 'POST', SUCCESS);
		}

		public function approve($id){
			return sendToAPI($this->prefix . "comments/" . $id . "/approve" . FORMAT, 'POST', SUCCESS);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class Country{
		private $prefix = "/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function get($id = 0, $cache = false){
			if ($id == 0){
				if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . "countries" . FORMAT), 'country');
				return $this->array['country'];
			}else{
				if (!$cache){
					$temp = sendToAPI($this->prefix . "countries/" . $id . FORMAT);
					$this->array['country'][$id] = $temp;
				}			
				if (!isset($this->array['country'][$id])) throw new Exception("Country not in cache. Set cache to false.");		
				return $this->array['country'][$id];
			}
		}
		
		public function count(){
			return sendToAPI($this->prefix . "countries/count" . FORMAT);
		}
		
		public function create($fields){
			$fields = array('country' => $fields);
			return sendToAPI($this->prefix . "countries" . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function modify($id, $fields){
			$fields = array('country' => $fields);
			return sendToAPI($this->prefix . "countries/" . $id . FORMAT, 'PUT', SUCCESS, $fields);
		}
		
		public function remove($id){
			return sendToAPI($this->prefix . "countries/" . $id . FORMAT, 'DELETE', SUCCESS, $fields);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class Event{
		private $prefix = "/";
		private $array;
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function get($id = 0, $order = 0, $product = 0, $params = array()){
			if ($product == 0 && $order == 0){
				if ($event_id == 0){
					$params = url_encode_array($params);
					$this->array = organizrArray(sendToAPI($this->prefix . "events" . FORMAT . "?" . $params), 'event');			
					return $this->array['event'];
				}else{
					if (!$cache){
						$temp = sendToAPI($this->prefix . "events/" . $id . FORMAT);
						$this->array['event'][$id] = $temp;
					}			
					if (!isset($this->array['event'][$id])) throw new Exception("Event not found in the cache. Set cache to false.");
					return $this->array['event'][$id];
				}
			}
			else if ($product > 0 && $order == 0){
				$params = url_encode_array($params);			
				$this->array = organizeArray(sendToAPI($this->prefix . "products/" . $id . "/events" . FORMAT . "?" . $params), 'event');			
				return $this->array['event'];
			}
			else if ($product == 0 && $order > 0){
				$params = url_encode_array($params);
				$this->array = organizeArray(sendToAPI($this->prefix . "orders/" . $id . "/events" . FORMAT . "?" . $params), 'event');			
				return $this->array['event'];
			}
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class Fulfillment{
		private $prefix = "/orders/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
	
		public function get($order_id, $id = 0, $params = array(), $cache = false){
			if ($id == 0){
				if (!$cache){
					$params = url_encode_array($params);
					$this->array = organizeArray(sendToAPI($this->prefix . $order_id . "/fulfillments" . FORMAT . "?" . $params), 'fulfillment');
				}			
				return $this->array['fulfillment'];
			}else{
				if (!$cache){
					$temp = sendToAPI($this->prefix . $order_id . "/fulfillments/" . $id . FORMAT);
					$this->array['fulfillment'][$id] = $temp;
				}			
				if (!isset($this->array['fulfillment'][$id])) throw new Exception("Fulfillment not in cache. Set cache to false.");		
				return $this->array['fulfillment'][$id];
			}
		}
		
		public function count($order_id, $params = array()){
			$params = url_encode_array($params);
			return sendToAPI($this->prefix . $order_id . "/fulfillments/count" . FORMAT . "?" . $params);
		}
		
		public function create($order_id, $fields){
			$fields = array('fulfillment' => $fields);
			return sendToAPI($this->prefix . $order_id . "/fulfillments" . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function fulfill($order_id, $id, $fields){
			$fields = array('fulfillment' => $fields);
			return sendToAPI($this->prefix . $order_id . "/fulfillments" . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function modify($order_id, $id, $fields){
			$fields = array('article' => $fields);
			return sendToAPI($this->prefix . $order_id . "/fulfillments/" . $id . FORMAT, 'PUT', SUCCESS, $fields);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class Metafield{
		private $prefix = "/";
		private $array;
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function get($product_id = 0, $id = 0, $params = array(), $cache = false){
			if ($product_id == 0){
				if (!$cache){
					$params = url_encode_array($params);
					$xmlObj = ($product_id > 0) ? sendToAPI($this->prefix . "metafields" . FORMAT . "?" . $params) : sendToAPI($this->prefix . "products/" . $this->product_id . "/metafields" . FORMAT . "?" . $params);				
					$this->array = organizeArray($xmlObj, 'metafield');
				}			
				return $this->array['metafield'];
			}else{
				if ($id == 0) throw new Exception("You must have a product and metafield id.");
				if (!$cache){
					$temp = sendToAPI($this->prefix . "products/" . $product_id . "/metafields/" . $id . FORMAT);
					$this->array['metafield'][$id] = $temp;
				}			
				if (!isset($this->array['metafield'][$id])) throw new Exception("Metafield not found in cache. Set cache to false.");
				return $this->array['metafield'][$id];
			}
		}
		
		public function create($product_id, $fields){
			$fields = array('metafield' => $fields);
			return ($product_id > 0) ? sendToAPI($this->prefix . "products/" . $product_id . "/metafields" . FORMAT, 'POST', CREATED, $fields) : sendToAPI($this->prefix . "metafields" . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function modify($product_id, $id, $fields){
			$fields = array('metafield' => $fields);
			return ($product_id > 0) ? sendToAPI($this->prefix . "products/" . $product_id . "/metafields/" . $id . FORMAT, 'PUT', SUCCESS) : sendToAPI($this->prefix . "metafields/" . $id . FORMAT, 'PUT', SUCCESS);
		}
		
		public function remove($product_id, $id){
			return ($product_id > 0) ? sendToAPI($this->prefix . "products/" . $product_id . "/metafields/" . $id . FORMAT, 'DELETE', SUCCESS) : sendToAPI($this->prefix . "metafields/" . $id . FORMAT, 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
			unset($this->product_id);
		}
	}
	
	class Order{
		private $prefix = "/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function get($id = 0, $params = array(), $cache = false){
			if ($id == 0){
				if (!$cache){
					$params = url_encode_array($params);
					$this->array = organizeArray(sendToAPI($this->prefix . "orders" . FORMAT . "?" . $params), 'order');
				}			
				return $this->array['order'];
			}else{
				if (!$cache){
					$temp = semdToAPI($this->prefix . "orders/" . $id . FORMAT);
					$this->array['order'][$id] = $temp;
				}
				if (!isset($this->array['order'][$id])) throw new Exception("Order not in cache. Set cache to false.");			
				return $this->array['order'][$id];
			}
		}		
		
		public function count($params = array()){
			$params = url_encode_array($params);
			return sendToAPI($this->prefix . "orders/count" . FORMAT . "?" . $params);
		}
		
		public function open($id){
			return sendToAPI($this->prefix . "orders/" . $id . "/open" . FORMAT, 'POST', SUCCESS);
		}
		
		public function close($id){
			return sendToAPI($this->prefix . "orders/" . $id . "/close" . FORMAT, 'POST', SUCCESS);
		}
		
		public function modify($id, $fields){
			$fields = array('order' => $fields);
			return sendToAPI($this->prefix . "orders/" . $id . FORMAT, 'PUT', SUCCESS, $fields);
		}
		
		public function setNoteAttributes($id, $fields){
			$fields = array('order' => array('id' => $id, 'note-attributes' => array('note-attribute' => $fields)));
			return sendToAPI($this->prefix . "orders/" . $id . FORMAT, 'PUT', SUCCESS, $fields);
		}
			
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class Page{
		private $prefix = "/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;		
		}
		
		public function get($id = 0, $params = array(), $cache = false){
			if ($id == 0){
				$params = url_encode_array($params);
				if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . "pages" . FORMAT . "?" . $params), 'page');
				return $this->array['page'];
			}else{
				if (!$cache){
					$temp = sendToAPI($this->prefix . "pages/" . $id . FORMAT);
					$this->array['page'][$id] = $temp;
				}			
				if (!isset($this->array['page'][$id])) throw new Exception("Page not in cache. Set cache to false.");
				return $this->array['page'][$id];
			}
		}
		
		public function count($params = array()){
			return sendToAPI($this->prefix . "pages/count" . FORMAT . "?" . $params);
		}
		
		public function create($fields){
			$fields = array('page' => $fields);
			return sendToAPI($this->prefix . "pages" . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function modify($id, $fields){
			$fields = array('page' => $fields);
			return sendToAPI($this->prefix . "pages/" . $id .FORMAT, 'PUT', SUCCESS, $fields);
		}
		
		public function remove($id){
			return sendToAPI($this->prefix . "pages/" . $id . FORMAT, 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class Product{
		private $prefix = "/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function get($id = 0, $collection_id = 0, $params = array(), $cache = false){
			if ($id == 0){
				if (!$cache){
					$params = url_encode_array($params);
					$xmlObj = ($collection_id > 0) ? sendToAPI($this->prefix . "products.xml?collection_id=" . $collection_id . "&" . $params) : sendToAPI($this->prefix . "products" . FORMAT . "?" . $params);
					$this->array = organizeArray($xmlObj, 'product');
				}			
				return $this->array['product'];
			}else{
				if (!$cache){
					$temp = sendToAPI($this->prefix . "products/" . $id . FORMAT);
					$this->array['product'][$id] = $temp;
				}
				if (!isset($this->array['product'][$id])) throw new Exception("Product not in cache. Set cache to false.");		
				return $this->array['product'][$id];
			}
		}
		
		public function count($collection_id = 0, $params = array()){
			$params = url_encode_array($params);
			return ($collection_id > 0) ? sendToAPI($this->prefix . "products/count.xml?collection_id=" . $collection_id . "&" . $params) : sendToAPI($this->prefix . "products/count" . FORMAT . "?" . $params);
		}
				
		public function create($fields){
			$fields = array('product' => $fields);
			return sendToAPI($this->prefix . "product" . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function modify($id, $fields){
			$fields = array('product' => $fields);
			return sendToAPI($this->prefix . "products/" . $id . FORMAT, 'PUT', SUCCESS, $fields);
		}
		
		public function remove($id){
			return sendToAPI($this->prefix . "products/". $id . FORMAT, 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class ProductImage{
		private $prefix = "/products/";
		private $array;
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function get($product_id, $cache = false){
			if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . $product_id . "/images" . FORMAT), 'image');
			return $this->array['image'];
		}
		
		public function create($product_id, $fields){
			$fields = array('image' => $fields);
			return sendToAPI($this->prefix . $product_id . "/images" . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function remove($product_id, $id){
			return sendToAPI($this->prefix . $product_id . "/images/". $id . FORMAT, 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class ProductVariant{
		private $prefix = "/products/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;;
		}
		
		public function get($product_id, $id = 0, $cache = false){
			if ($id == 0){
				if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . $product_id . "/variants" . FORMAT . "?"), 'variant');
				return $this->array['variant'];
			}else{
				if (!$cache){
					$temp = sendToAPI($this->prefix . $product_id . "/variants/" . $id . FORMAT);
					$this->array['variant'][$id] = $temp;
				}			
				if (!isset($this->array['variant'][$id])) throw new Exception("Variant not in cache. Change cache to false.");
				return $this->array['variant'][$id];	
			}
		}
		
		public function count($product_id){
			return sendToAPI($this->prefix . $product_id . "/variants/count" . FORMAT);
		}
		
		public function create($product_id, $fields){
			$fields = array('variant' => $fields);
			return sendToAPI($this->prefix . $product_id . "/variants" . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function modify($product_id, $id, $fields){
			$fields = array('variant' => $fields);
			return sendToAPI($this->prefix . $product_id . "/variants/" . $id . FORMAT, 'PUT', SUCCESS, $fields);
		}
		
		public function remove($product_id, $id){
			return sendToAPI($this->prefix . $product_id . "/variants/" . $id . "xml", 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class Province{
		private $prefix = "/countries/";
		private $array = array();
		
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function get($country_id, $id = 0, $cache = false){
			if ($id == 0){
				if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . $country_id . "/provinces" . FORMAT), 'pronvince');
				return $this->array['province'];
			}else{
				if (!$cache){
					$temp = sendToAPI($this->prefix . $country_id . "/provinces/" . $id . FORMAT);
					$this->array['province'][$id] = $temp;
				}			
				if (!isset($this->array['province'][$id])) throw new Exception("Province not in cache. Set cache to false.");
				return $this->array['province'][$id];
			}
		}
		
		public function count($country_id){
			return sendToAPI($this->prefix . $country_id . "/provinces/count" . FORMAT);
		}
		
		public function modify($country_id, $id, $fields){
			$fields = array('province' => $fields);
			return sendToAPI($this->prefix . $country_id . "/provinces/" . $id . FORMAT, 'PUT', SUCCESS, $fields);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class Redirect{
		private $prefix = "/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function get($id = 0, $params = array(), $cache = false){
			if ($id == 0){
				if (!$cache){
					$params = url_encode_array($params);
					$this->array = organizeArray(sendToAPI($this->prefix . "redirects" . FORMAT . "?" . $params), 'redirect');
				}		
				return $this->array['redirect'];
			}else{
				if (!$cache){
					$temp = sendToAPI($this->prefix . "redirects/" . $id .FORMAT);
					$this->array['redirect'][$id] = $temp;
				}			
				if (!isset($this->array['redirect'][$id])) throw new Exception("Redirect not found in cache. Set cache to false.");
				return $this->array['redirect'][$id];
			}
		}
		
		public function count($params = array()){
			$params = url_encode_array($params);
			return sendToAPI($this->prefix . "redirects/count" . FORMAT . "?" . $params);
		}
		
		public function create($fields){
			$fields = array('redirect' => $fields);
			return sendToAPI($this->prefix . "redirects" . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function modify($id, $fields){
			$fields = array('redirect' => $fields);
			return sendToAPI($this->prefix . "redirects/" . $id . FORMAT, 'PUT', SUCCESS, $fields);
		}
		
		public function remove($id){
			return sendToAPI($this->prefix . "redirects/" . $id . FORMAT, 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class Shop{
		private $prefix = "/";
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function get(){
			return sendToAPI($this->prefix . "shop" . FORMAT);
		}
		
		public function __destruct(){
			unset($this->prefix);
		}
	}
	
	class SmartCollection{
		private $prefix = "/";	
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function get($id = 0, $params =  array(), $cache = false){
			if ($id == 0){
				if (!$cache){
					$params = url_encode_array($params);
					$this->array = organizeArray(sendToAPI($this->prefix . "smart_collections" . FORMAT . "?" . $params), 'smart-collection');
				}
				return $this->array['smart-collection'];
			}else{
				if (!$cache){
					$temp = sendToAPI($this->prefix . "/smart_collections/" . $id . FORMAT);
					$this->array['smart-collection'][$id] = $temp;				
				}			
				if (!isset($this->array['smart-collection'][$id])) throw new Exception("Collection not in the cache. Set cache to false.");
				return $this->array['smart-collection'][$id];
			}
		}
		
		public function count($params = array()){
			$params = url_encode_array($params);			
			return sendToAPI($this->prefix . "smart_collections/count" . FORMAT . "?" . $params);			
		}
		
		public function create($fields){
			$fields = array('smart-collection' => $fields);
			return sendToAPI($this->prefix . "smart_collections" . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function modify($id, $fields){
			$fields = array('smart-collection' => $fields);
			return sendToAPI($this->prefix . "smart_collections/" . $id . FORMAT, 'PUT', SUCCESS, $fields);	
		}
		
		public function delete($id){
			return sendToAPI($this->prefix . "smart_collections/" . $id . FORMAT, 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}		
	}
	
	class Transaction{
		private $prefix = "/orders/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function get($order_id, $id = 0, $cache = false){
			if ($id == 0){
				if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . $order_id . "/transactions" . FORMAT), 'transaction');			
				return $this->array['transaction'];
			}else{
				if (!$cache){
					$temp = sendToAPI($this->prefix . $order_id . "/transactions/" . $id . FORMAT);
					$this->array['transaction'][$id] = $temp;
				}			
				if (!isset($this->array['transaction'][$id])) throw new Exception("Transaction not in cache. Set cache to false.");			
				return $this->array['transaction'][$id];
			}
		}
		
		public function count($order_id){
			return sendToAPI($this->prefix . $order_id . "/transactions/count" . FORMAT);
		}

		public function create($order_id, $fields){
			$fields = array('transaction' => $fields);
			return sendToAPI($this->prefix . $order_id . "/transactions" . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class Webhook{
		private $prefix = "/";
		private $array = array();
		
		public function __construct($site){
			$this->prefix = $site . $this->prefix;
		}
		
		public function get($id = 0, $params = array(), $cache = false){
			if ($id == 0){
				if (!$cache) $this->array = organizeArray(sendToAPI($this->prefix . "webhooks" . FORMAT . "?" . $params), 'webhook');
				return $this->array['webhok'];
			}else{
				if (!$cache){
					$temp = sendToAPI($this->prefix . "webhooks/" . $id . FORMAT);
					$this->array['webhook'][$id] = $temp;
				}
				if (!isset($this->array['webhook'][$id])) throw new Exception("Webhook not in cache. Set cache to false.");
				return $this->array['webhook'][$id];
			}
		}
		
		public function count($params = array()){
			$xmlObj = new parser($this->prefix . "webhooks/count" . FORMAT . "?" . $params);
			return $xmlObj->resultArray();
		}
		
		public function create($fields){
			$fields = array('webhook' => $fields);
			return sendToAPI($this->prefix . "webhooks" . FORMAT, 'POST', CREATED, $fields);
		}
		
		public function modify($id, $fields){
			$fields = array('webhook' => $fields);
			return sendToAPI($this->prefix . "webhooks/" . $id . FORMAT, 'PUT', SUCCESS, $fields);
		}
		
		public function remove($id){
			return sendToAPI($this->prefix . "webhooks/". $id . FORMAT, 'DELETE', SUCCESS);
		}
		
		public function __destruct(){
			unset($this->prefix);
			unset($this->array);
		}
	}
	
	class Session{
		private $api_key;
		private $secret;
		private $protocol = 'https';
		
		private $url;
		private $token;
		private $name;
		
		public $application_charge;
		public $recurring_application_charge;
		public $article;
		public $asset;
		public $blog;
		public $collect;
		public $comment;
		public $country;
		public $custom_collection;
		public $event;
		public $fulfillment;
		public $metafield;
		public $order;
		public $page;
		public $product;
		public $product_image;
		public $product_variant;
		public $province;
		public $redirect;
		public $shop;
		public $smart_collection;
		public $transaction;
		public $webhook;
						
		/*
			BEGIN PUBLIC
		*/
		
		public function __construct($url, $token = '', $api_key, $secret, $params = array()){
			$this->url = $url;
			$this->token = (isEmpty($token)) ? $url : $token;
			$this->api_key = $api_key;
			$this->secret = $secret;
			if (isset($params['signature'])){
				$timestamp = $params['timestamp'];
				$expireTime = time() - (24 * 86400);
				if (!$this->validate_signature($params) || $expireTime > $timestamp){
					throw new Exception('Invalid signature: Possible malicious login.');
				}
			}
			$this->url = $this->prepare_url($this->url);
			
			if ($this->valid()){
				$this->application_charge 			= new ApplicationCharge($this->site());
				$this->recurring_application_charge = new RecurringApplicationCharge($this->site());;
				$this->article 						= new Article($this->site());
				$this->asset 						= new Asset($this->site());
				$this->blog 						= new Blog($this->site());
				$this->collect 						= new Collect($this->site());
				$this->comment 						= new Comment($this->site());
				$this->country 						= new Country($this->site());
				$this->custom_collection 			= new CustomCollection($this->site());
				$this->event 						= new Event($this->site());
				$this->fulfillment					= new Fulfillment($this->site());
				$this->metafield 					= new Metafield($this->site());
				$this->order 						= new Order($this->site());
				$this->page 						= new Page($this->site());
				$this->product 						= new Product($this->site());
				$this->product_image 				= new ProductImage($this->site());
				$this->product_variant 				= new ProductVariant($this->site());
				$this->province 					= new Province($this->site());
				$this->redirect 					= new Redirect($this->site());
				$this->shop							= new Shop($this->site());
				$this->smart_collection 			= new SmartCollection($this->site());
				$this->transaction 					= new Transaction($this->site());
				$this->webhook 						= new Webhook($this->site());
			}
		}
			
		public function create_permission_url(){
			return (isEmpty($this->url) || isEmpty($this->api_key)) ? '' : 'http://' . $this->url . '/admin/api/auth?api_key=' . $this->api_key;
		}
		
		/* Used to make all non-authetication calls */
		public function site(){
			return $this->protocol . '://' . $this->api_key . ':' . $this->computed_password() . '@' . $this->url . '/admin';
		}
		
		public function valid(){
			return (!isEmpty($this->url) && !isEmpty($this->token));
		}
			
		public function __destruct(){
			unset($this->api_key);
			unset($this->secret);
			unset($this->protocol);
			unset($this->format);
			unset($this->url);
			unset($this->token);
			unset($this->name);
			unset($this->application_charge);
			unset($this->recurring_application_charge);
			unset($this->article);
			unset($this->asset);
			unset($this->blog);
			unset($this->collect);
			unset($this->comment);
			unset($this->country);
			unset($this->custom_collection);
			unset($this->event);
			unset($this->fulfillment);
			unset($this->metafield);
			unset($this->order);
			unset($this->page);
			unset($this->product);
			unset($this->product_image);
			unset($this->product_variant);
			unset($this->province);
			unset($this->redirect);
			unset($this->shop);
			unset($this->smart_collection);
			unset($this->transaction);
			unset($this->webhook);
		}
		
		/*
			END PUBLIC
			BEGIN PRIVATE
		*/
		
		private function computed_password(){
			return md5($this->secret . $this->token);
		}
		
		private function prepare_url($url){
			if (isEmpty($url)) return '';
			$url = preg_replace('/https?:\/\//', '', $url);
			if (substr_count($url, '.myshopify.com') == 0 && substr_count($url, '.com') == 0){
				$url .= '.myshopify.com';
			}
			return $url;
		}
		
		private function validate_signature($params){	
			$this->signature = $params['signature'];
			$genSig = $this->secret;
			ksort($params);
			foreach($params as $k => $v){
				if ($k != "signature" && $k != "action" && $k != "controller" && !is_numeric($k)){
					$genSig .= $k . '=' . $v;
				}
			}
			return (md5($genSig) == $this->signature);
		}		

		/*
			END PRIVATE
		*/	
	}
	
	class miniCURL{
		
		private $ch;
		
		public function __construct(){
			if (!function_exists('curl_init')) die("Error: cURL does not exist! Please install cURL.");
		}
		
		public function send($url, $request = 'GET', $xml_payload = '', $headers = array('Accept-Encoding: gzip')){
			$this->ch = curl_init($url);
		
			// _HEADER _RETURNTRANSFER -- Return output as string including HEADER information
			$options = array(
				CURLOPT_HEADER => 0,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_CUSTOMREQUEST => $request,
				CURLOPT_HTTPHEADER => $headers
			);
			
			if ($request != "GET"){ 
				$options[CURLOPT_POSTFIELDS] = $xml_payload; 
				$options[CURLOPT_HTTPHEADER] = array('Content-Type: application/xml; charset=utf-8');
			}
			
			curl_setopt_array($this->ch, $options);
			curl_exec($this->ch);
			$data = ($headers[0] != "Accept-Encoding: gzip") ? curl_multi_getcontent($this->ch) : gzdecode(curl_multi_getcontent($this->ch));
			$code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
			curl_close($this->ch);
			
			return array($code, $data);
		}
		
		public function loadString($data){
			$array = array();
				
			if (FORMAT == ".xml"){
				$xml = simplexml_load_string($data);
				$this->recurseXML($xml, $array);
			}
			else if (FORMAT == ".json"){
				if (!function_exists('json_decode')) die("json library not installed. Either change format to .xml or upgrade your version of PHP");
				$array = json_decode($data, true);
				if (isset($array['count'])) $array = $array['count'];				
			}
			return $array;
		}
		
		public function recurseXML($xml, &$array){ 
	        $children = $xml->children(); 
	        $executed = false;

	        foreach ($children as $k => $v){ 
				if (is_array($array)){
	            	if (array_key_exists($k , $array)){ 		
	                	if (array_key_exists(0 ,$array[$k])){ 
	                    	$i = count($array[$k]); 
	                    	$this->recurseXML($v, $array[$k][$i]);     
	                	}else{ 
	                    	$tmp = $array[$k]; 
	                    	$array[$k] = array(); 
	                    	$array[$k][0] = $tmp; 
	                    	$i = count($array[$k]); 
	                    	$this->recurseXML($v, $array[$k][$i]); 
	                	} 
	            	}else{ 
	                	$array[$k] = array(); 
	                	$this->recurseXML($v, $array[$k]);    
	            	}
				}else{
					$array[$k] = array(); 
                	$this->recurseXML($v, $array[$k]);
				} 
				$executed = true; 
	        } 
	
	        if (!$executed && isEmpty($children->getName())){ 
	            $array = (string)$xml; 
	        } 
		}
		
		public function __destruct(){
			empty($this->ch);			
		}
	}
?>