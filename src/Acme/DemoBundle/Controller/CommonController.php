<?php
namespace Acme\DemoBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
class CommonController extends Controller{
	
	function __construct(){
		$request = new Request();
		$session	=	$request->getSession();
		$uid = (null === $session) ? 0 : $session->get('uid');
		if ($uid) {
			return $this->redirect("lists");
		}else{
			return $this->redirect("login");
		}
	}
	
}