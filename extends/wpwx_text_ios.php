<?php
/**
 *  extend_name:苹果产品信息查询
 *  extend_url:http://midoks.cachecha.com/
 *	author: midoks
 *	version:0.1
 *	email:midoks@163.com
 *	description: 关心你的IPhone
 */

//手动查询地址
//https://selfsolve.apple.com/agreementWarrantyDynamic.do?locale=zh_CN
class wpwx_text_ios{

	public $obj;

	public function __construct($obj){
		$this->obj = $obj;
	}

	public function start($kw){

		if(substr(strtolower($kw), 0, 2) == 'i:'){
			$key = substr($kw, 2);
			$res = $this->getAppleInfoByIMEI($key);	
			
			if(!$res){
				return $this->obj->toMsgText('很抱歉，此序列号无效。请检查您的信息，然后再试。');
			}else{
				
				$info = array(
			
					array(
						'title'	=> '苹果产品信息查询',
						'desc'	=> '苹果产品信息查询',
						'pic'	=> $res['PicUrl'],
						'link'	=> 'http://midoks.duapp.com',
						),
					array(
						'title'	=> $res['Description'],
						'desc'	=> $res['Description'],
						'pic'	=> '',
						'link'	=> '',
					),
						
				);
				return $this->obj->toMsgTextPic($info);
			}
		}
	}


	/**
	 *	通过IPhone IMEI 获取信息
	 */
	public function getAppleInfoByIMEI($sn){
		$rnd = rand(100,999999);
		$post = "sn=$sn&cn=&locale=&caller=&num=$rnd"; 
		$url = "https://selfsolve.apple.com/wcResults.do";

		
		$header = $header = array( 
			'CLIENT-IP:58.68.44.61', 
			'X-FORWARDED-FOR:58.68.44.61', 
		);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
		curl_setopt($ch, CURLOPT_URL, $url );
		curl_setopt($ch, CURLOPT_HEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$result=curl_exec($ch);
		curl_close($ch);


		$result = str_replace("'","",$result);
		preg_match('#setClassAndShow\((.*?)\)#is',          $result, $RegistrationInfo);//注册信息
		preg_match('#displayProductInfo\((.*?)\)#is',       $result, $ProductInfo);     //产品信息
		preg_match('#displayPHSupportInfo\((.*?)\)#is',     $result, $PHSupportInfo);   //电话支持
		preg_match('#displayHWSupportInfo\((.*?)\)#is',     $result, $HWSupportInfo);   //硬件保修

		if (empty($RegistrationInfo)){
			return false;
			//return "很抱歉，此序列号无效。请检查您的信息，然后再试。";
		}
		
		$registration = explode(",",$RegistrationInfo[1]);
		$product = explode(",",$ProductInfo[1]);
		$phsupport = explode(",",$PHSupportInfo[1]);
		$hwsupport = explode(",",$HWSupportInfo[1]);

		$phsupport_date = "";   //提取电话支持日期
		if (trim($phsupport['0']) == "true"){
			preg_match('#Estimated Expiration Date:(.*?)<br/>#is', $phsupport['2'].$phsupport['3'], $phsupport_date); 
		}
		$hwsupport_date = "";   //提取保修服务日期
		if (trim($hwsupport['0']) == "true"){
			preg_match('#Estimated Expiration Date:(.*?)<br/>#is', $hwsupport['2'].$hwsupport['3'], $hwsupport_date); 
		}

		$title = "苹果产品信息查询";
		$description = "设备名称：".trim($product['1'])."\n".
		((preg_match("/^\d{8,20}$/",$sn))?"IMEI号":"序列号")."：".$product['3']."\n".
		"购买日期：".((trim($registration['2']) == "registration-true")?"已验证":"无效")."\n".
		"电话支持：".((trim($phsupport['0']) == "true")?"有效[".trim($phsupport_date['1'])."]":"已过期")."\n".
		"保修服务：".((trim($hwsupport['0']) == "true")?"有效[".trim($hwsupport_date['1'])."]":"已过期")."\n".
		"\n数据来自苹果公司官方网站";
		$picurl = $product['0'];
		
		$months = array(
			"01-"=>"January ", "02-"=>"February ", "03-"=>"March ", 
			"04-"=>"April ", "05-"=>"May ", "06-"=>"June ",
			"07-"=>"July ", "08-"=>"August ", "09-"=>"September ",
			"10-"=>"October ", "11-"=>"November ", "12-"=>"December ",
		);
		foreach($months as $key=>$value){
			$description = str_ireplace($value, $key, $description);
		}

		$result = array(); 
		$result =  array(
			"Title"			=>	$title,
			"Description" 	=>	trim($description),
			"PicUrl" 		=>	$picurl,
			"Url" 			=>	$url,
			'Post'			=>  $post,
		);
		return $result;
	}

}
