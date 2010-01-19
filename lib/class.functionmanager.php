<?php
	require_once('class.exslfunction.php');	
	require_once('FirePHPCore/fb.php');
	
 class FunctionManager {
	private $functions = array();
	private $page;
	

	function __construct($context) {
		$this->page = $context['page'];

	}
	
	public function createDelegate() {
		// Create Delegate

		$this->page->ExtensionManager->notifyMembers(
			'ManageEXSLFunctions', '/frontend/', 	array('manager' => &$this)
			);
	fb("Delegate Created");
	}
	
	
	public function createStream() {
		// Register Stream Wrapper
		stream_wrapper_register("efm", "XslTemplateLoaderStream");
		$exsl = $this->getFunctions();
		fb($exsl, "Stream EXSL");
		$opts = array(
		   'efm' => array(
		       'namespaces' => $exsl['declarations'],
				'functions' => $exsl['functions']
				//'functions' => print_r($exsl)
		   )
		);
		$streamContext = stream_context_create($opts);
		libxml_set_streams_context($streamContext);
		fb("Stream Created.");
	}
		

	// For use in subscribed delegates
	public function addFunction($strName, $strURI, $strHandle = NULL){
				fb("addFunction called.");
		//Register function with PHP
		$this->page->registerPHPFunction($strName);
		
		//Create a new EXSL function object
		$function = new EXSLFunction($strName, $strURI, $strHandle);
		
		//Add to Manager's function array, which groups by namespace URI
		$this->functions[$strURI][] = $function;
	}

	
	private function getFunctions(){
				fb($this->functions, "getFunctions called.");
		$strFunctions = "";
		$strDeclarations = "";
		$i = 0;
		foreach ($this->functions as $namespace){
			$prefix = 'fn' . $i;
			$strDeclarations .= $namespace[0]->getDeclarations($prefix); //Get the declaration from the first EXSL object in the array
			foreach ($namespace as $function){
				$strFunctions .= $function->getFunction($prefix);
			}
		$i++;
		}
		return array ('declarations' => $strDeclarations, 'functions' => $strFunctions);
		
	}
		

}