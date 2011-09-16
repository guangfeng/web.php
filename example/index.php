<?php 

require 'inc.php';

$urls = array(
              '/'      => 'hello',
             );


class hello {        
    
    function GET()
    {
		$code = <<<DEMO
<?php
//载入引导文件
require 'inc.php';

//正则表达式 => 控制器
\$urls = array('/' => 'hello',); 

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
    Web::run(\$urls,\$devMode = true); 
} catch (RequestErrorException \$e) {
	//内部错误，跳转到合理的错误
    \$e->ViewError();
}
DEMO;

		$code = highlight_string($code);
		
        return Web::render('index.html',array('title' => "Hello,I am web.php!",'code'=>$code));
        
    }                          
    
    function POST($p)
    {        
        // like you just posted a form
        /*
        $input = Web::input();
        if ($email = $input->post->testEmail('email')) {
            // wow email is valid!
             save to db...
             Web::redirect('/gohere');            
        }
        */        
        
        echo 'request via POST';
    }  
    
    function AJAX($p)
    {
        echo "requested via AJAX";
    }                            
}                          

                                                             

try {
    Web::run($urls,$devMode = true); 
} catch (RequestErrorException $e) {
    $e->ViewError();
}
