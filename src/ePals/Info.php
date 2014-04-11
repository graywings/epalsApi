<?php
namespace ePals;

class Info {
    private $versionID = 'V2.5.0';
    
    public function display() {
        print ("ePals API Version: $this->versionID \n");
    }        
}
