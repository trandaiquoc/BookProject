<?php
class Controller 
{
    public function loadView($view, $data = []) {
        extract($data);
        include('views/layout.php');
    }
}
?>