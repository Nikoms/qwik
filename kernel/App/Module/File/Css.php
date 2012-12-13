<?php

namespace Qwik\Kernel\App\Module\File;

class Css extends File {
	
	public function __toString(){
		$config = $this->getConfig();
		return '<link rel="stylesheet" type="text/css" href="'.$config['path'].'">';
	}
	
}
	