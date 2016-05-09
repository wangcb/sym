<?php
namespace Acme\DemoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Acme\DemoBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
class UserController extends CommonController{
	
	
	/**
	 * @Route("/lists",name="acme_demo_lists")
	 */
	public function listsAction(){
		$em		=	$this->getDoctrine()->getManager();
		$users	=	$em->getRepository("AcmeDemoBundle:User")->findAll();
		return $this->render('AcmeDemoBundle:default:lists.html.twig',array('users'=>$users));
	}
	
	/**
	 * @Route("/add",name="acme_demo_add")
	 */
	public function addAction(Request $request){
		$User	=	new User();
		$form = $this->createFormBuilder($User)
		->add("username",'text',array('label'=>'姓名：'))
		->add("email",'email',array('label'=>'邮箱：'))
		->add("password",'text',array('label'=>'密码：'))
		->add("mobile",'text',array('label'=>'手机：'))
		->add("qq",'text',array('label'=>'QQ：'))
		->add('sex', 'choice', array(
			'choices'  => array('1' => '男', '0' => '女'),
			'empty_value' => false,
			'required' => true,
			'expanded'=>true,
			'label'=>'性别：',
			'data'=>1
		))
		->add("提交","submit")
		->getForm();
		
		$form->handleRequest($this->getRequest());
		
		if ($request->isMethod("post") && $form->isValid()) {
			$em		=	$this->getDoctrine()->getManager();
			$em->persist($User);
			$em->flush();
			return $this->redirect($this->generateUrl('acme_demo_lists'));
		}
		return $this->render('AcmeDemoBundle:default:add.html.twig',array('form'=>$form->createView()));
		
		
		/* $user	=	$em->getRepository("AcmeDemoBundle:User")->findOneBy(array('id'=>1));
		$user->setQq('527373992');
		$em->persist($user);
		$em->flush(); */
		
		/* $User	=	new User();
		$User->setUsername("木木");
		$User->setEmail("wangcb615@163.com");
		$User->setMobile("18156821011");
		$User->setSex(1);
		$User->setQq("527373993");
		$User->setPassword("1234");
		
		$em->persist($User);
		$em->flush(); */
	}
	
	/**
	 * @Route("/edit/{id}",name="acme_demo_edit")
	 */
	public function editAction($id,Request $request){
		$em		=	$this->getDoctrine()->getManager();
		$User	=	$em->getRepository("AcmeDemoBundle:User")->findOneBy(array('id'=>$id));
		
		$form = $this->createFormBuilder($User)
		->add("username",'text',array('label'=>'姓名：'))
		->add("email",'email',array('label'=>'邮箱：'))
		->add("password",'text',array('label'=>'密码：'))
		->add("mobile",'text',array('label'=>'手机：'))
		->add("qq",'text',array('label'=>'QQ：'))
		->add('sex', 'choice', array(
				'choices'  => array('1' => '男', '0' => '女'),
				'empty_value' => false,
				'required' => true,
				'expanded'=>true,
				'label'=>'性别：'
		))
		->add("提交","submit")
		->getForm();
		
		$form->handleRequest($this->getRequest());
		
		if ($request->isMethod("post") && $form->isValid()) {
			$em->persist($User);
			$em->flush();
			return $this->redirect($this->generateUrl('acme_demo_lists'));
		}
		return $this->render('AcmeDemoBundle:default:edit.html.twig',array('user'=>$User,'form'=>$form->createView()));
	}
	
	/**
	 * @Route("/del/{id}",name="acme_demo_del")
	 */
	public function delAction($id){
		$em		=	$this->getDoctrine()->getManager();
		$User	=	$em->getRepository("AcmeDemoBundle:User")->findOneBy(array('id'=>$id));
		$em->remove($User);
		$em->flush();
		return $this->redirect($this->generateUrl('acme_demo_lists'));
	}
	
	/**
	 * @Route("/logout",name="acme_demo_logout")
	 */
	public function logoutAction(){
		$session = $this->getRequest()->getSession();//清除session
		$session->clear();
		return $this->redirect($this->generateUrl('acme_demo_login'));
	}
}