<?php

namespace Qwik\Kernel\App\Module\File;

class Javascript extends File {
	
	public function __toString(){
		$config = $this->getConfig();
		return ' <script type="text/javascript" src="'.$config['path'].'"></script>';
	}
	
}
	