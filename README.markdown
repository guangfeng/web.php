---
Title: README
---

# README

Web.php 是一个REST风格的轻量级web开发框架。

它的设计灵感来自web.py,一个以小但是功能全面而著称的Python敏捷开发框架.我们不打算解决所有web开发中遇到的问题，而仅仅把问题域
局限在服务器端开发场景，例如web service。

web.php针对URI及其实际请求它的方法(GET,HTTP)来构建开发模型。与传统的MVC所不同的是，我们更像是Method-Templat
e.这天然支持了restful的架构设计。考虑到实际开发的需要，我们把AJAX也定义成一种"方法".因此一个标准的web.php的控制器类可以(但
不是必须)具有GET,POST,AJAX三个方法。

就像web.py实现的那样，每一个URI所映射到的控制器类都可以根据需要实现PreRun和PostRun方法，它们分别在实际的method(GET.
POST.AJAX)方法运行之前和其后执行。这可以用来实现登陆鉴权或者资源的释放。

对于web请求的外部参数，我们提供统一的访问方法Web::input(),它包含了来自GET,POST,SERVER,ENV,COOKIE的参数信息。
具体可以参考实例。

模板引擎我们选择了广泛使用的Smarty,并使用统一Web::render()方法来渲染你的模板.


好了!相比那些大而全的框架,我们所提供功能就和这README一样简洁。请参考example开始你的web.php之旅。



✂------✂------✂------✂------✂------✂------✂------✂------✂------✂------

# USEAGE: 

* 一个典型的目录结构：
 |-- compiled   模板编译目录，需要读写权限
 |-- inc.php     
 |-- index.php  控制器代码 
 |-- modules    逻辑或者其他需要封装的代码
 |   `-- hello
 |       `-- world.class.php
 |-- templates  模板目录
 |   `-- index.html
 `-- webphp     不解释

* 如何使用静态文件(css,js)?
请放在根目录下的static目录下。


# DEMO:
	
<code>	
	//载入引导文件
	require 'inc.php';

	//正则表达式 => 控制器
	$urls = array('#^$#' => 'hello',); 
	
	class hello {
	
	/**
	 *处理控制器的GET请求
	 */
	function GET(){
		
		//Web::render 支持Smarty模板引擎的渲染方法
		return Web::render('index.html',array('title'=>'模板的标题'));
		
	}
	
	/**
	 *处理控制器的POST请求
	 */
	function POST(){
		
		//Web::input 包含了来自外部GET,POST以及SERVER和其他参数信息
		print_r(Web::input()->post);
	}
	
	/**
	 *处理控制器的Ajax请求
	 */
	function AJAX(){
		echo "我是一个ajax";
	}
	}

	try {
	//运行实例，devMode打开运行时消息
    Web::run(\urls,$devMode = true); 
	} catch (RequestErrorException $e) {
	//内部错误，跳转到合理的错误
    $e->ViewError();
	}
</code>