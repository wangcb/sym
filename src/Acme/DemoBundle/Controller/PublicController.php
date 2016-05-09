<?php
namespace Acme\DemoBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Acme\DemoBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
class PublicController extends Controller{
	
	/**
	 * @Route("/login",name="acme_demo_login")
	 */
	public function loginAction(Request $request){
		$User		=	new User();
		$form 		= 	$this->createFormBuilder($User)
		->add("email",'email',array('label'=>'邮箱：'))
		->add("password",'text',array('label'=>'密码：'))
		->add("登录","submit")
		->getForm();
	
		$form->handleRequest($this->getRequest());
	
		if ($request->isMethod("post") && $form->isValid()) {
			$session	=	$request->getSession();
			$em		=	$this->getDoctrine()->getManager();
			$u		=	$em->getRepository("AcmeDemoBundle:User")->findOneBy(array('email'=>$User->getEmail()));
			if (empty($u)) {
				echo '用户名不存在';
			}elseif ($User->getPassword()!=$u->getPassword()){
				echo "密码错误";
			}else{
				$session->set('uid',$u->getId());
				return $this->redirect($this->generateUrl('acme_demo_lists'));
			}
		}
		return $this->render('AcmeDemoBundle:default:login.html.twig',array('form'=>$form->createView()));
		//return new Response('hello world!');
	}
}