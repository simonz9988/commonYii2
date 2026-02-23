<?php
namespace common\components;

//私有玩家

use common\models\Member;

class PrivateUser
{
    public function init(){
        #TODO
    }

    // 返回所有地址对应的私钥信息
    public  function returnPrivateAddress(){
        $a1 = [
            '0x3276daA703fc843Ab1a64da1C6a1FB4B23633B3c'=>'ZZZZZ',
            '0xF9D5B7a6a3da5807e405350Af50bCa2E1E769591'=>'ZZZZZ',
            '0xCEcf76dFf7154E5006d7699bD9E4F2Ccf8b91728'=>'ZZZZZ',
            '0xe687F43E9D21b851238e8D54D4CCFA2fEbaF3F74'=>'ZZZZZ',
            '0x48e6666cfaD530C8dc6961D1e4F2164fCC48590C'=>'ZZZZZ',
            '0x3D122908d56eEaaB6C68aC4f1dA4efa40E21bB75'=>'ZZZZZ',
            '0xd875Bf6F091055041e50e977405E4a5ac4C9bf73'=>'ZZZZZ',
            '0xb5DebF2952aA08e5b1271455d901992A63061a88'=>'ZZZZZ',
            '0xdB7E296f826b419704A849e812A175aBc014010c'=>'ZZZZZ',
            '0xd4C38988bB70F5A0aD82174f25E03dD97e1183ee'=>'ZZZZZ',
            '0x04d04dcC3EB08524232d76c65625994B3fe17aE5'=>'ZZZZZ',
            '0xF43FAF21001D78817B3b28f4E81a390B86aE0833'=>'ZZZZZ',
            '0xD99dae5d5f7bf859021B123A75CCba4d6744e395'=>'ZZZZZ',
        ];

        $a2 = [
            '0xB787fF79978d97635D1A27F90895a9B507fFE9D1'=> 'ZZZZZ',
            '0x3fd6960e7da2996c9e1f52cbfbd1672134479e15'=> 'ZZZZZ',
			'0x3641A67D1a4948158AFeD6D19F6cE2df13bD3822'=> 'ZZZZZ',
			'0x9c1c61Fdf874899B536383248073b070AA9be3D9'=> 'ZZZZZ',
			'0x222362A3e0A90029807Ed8036896E832f9655889'=> 'ZZZZZ',
			'0x15B4822b0C133A9034472f23A80CF36fbA0D9E88'=> 'ZZZZZ',
			'0x49A11Bc1EFEc890fa0B4910Bd88295e0A30e7475'=> 'ZZZZZ',
			'0x668f832aa230f200C05d6c0cc265a0358FD4Fe4d'=> 'ZZZZZ',
			'0x338123b45df331773cEF42DA8Cbebe23E3996F9f'=> 'ZZZZZ',
			'0xf0963Bf40f662151d2e0131C1178A0f924620747'=> 'ZZZZZ',
			'0x58A0E6a5983AF92A7306114abE4CA87145E7D92b'=> 'ZZZZZ',
			'0x6023376272759d1462C3aC155A0d573297Fc3ec3'=> 'ZZZZZ',
			'0xB8365312628bE12C1f176eaDB64f4047CdF85e69'=> 'ZZZZZ',
        ];


        $a3 = [
            '0xe341b4c9Cc344e8ff4622897EC64D7e623A9377B' =>'ZZZZZ',
            '0x3612743697Df7769a63ea993A8fb95922C939D18' =>'ZZZZZ',
			'0x3285C65fF848aC23C6e589ba3f255a4f9f3c0593' =>'ZZZZZ',
			'0xDe6afd2701f32E00Ac47a36060B96fC2BF9D4036' =>'ZZZZZ',
			'0x66A1f7F40fF3A1Be41ec257Ec22452De244E2Abd' =>'ZZZZZ',
			'0x7a0E7Fb6eF56113BE1C046286F2f4BaF5BcDd284' =>'ZZZZZ',
			'0x2b6FCd60871EC20c2Adb10c4719347022c0ac0Bc' =>'ZZZZZ',
			'0x58d8dBB03A5b3c76D27d87a048052AA3114185c9' =>'ZZZZZ',
			'0xA3182aEa512A825715E248cCC72333e068b02CAD' =>'ZZZZZ',
			'0x97281cF9B211B98559beD16A610cFF43FDE24C6d' =>'ZZZZZ',
			'0xB790350f00c1d64E33E57EF39E1B688296f267c1' =>'ZZZZZ',
			'0xe9A753435040647fD2c54aED3027A907dc169c7e' =>'ZZZZZ',
			'0xB70de3400af1A1C7C06B1206d34271DdEEa7D5B7' =>'ZZZZZ',
        ] ;

        $bind1 = array_merge($a1,$a2) ;
        return array_merge($bind1,$a3) ;
    }


    // 返回自动插入的用户地址私钥信息
    public function returnAutoInsertAddress(){

        #TODO 需要最终确认是使用那个用户
        return [
            '0xb924e2ff6Ff77D8AAC97219f4c820d83c279A5D7' =>'2f1d70db6827d2d9896dd9c90abb06dc12cb8bcf6058dcd638be6c40b43968c4'
        ];
    }

    /**
     * 根据用户地址，返回自动插入用的key值
     * @param $address
     * @return mixed|string
     */
    public function getAutoInsertUserKey($address){
        $address = strtolower($address) ;
        $all = $this->returnAutoInsertAddress() ;
        foreach($all as $k=>$v){
            $k = strtolower($k);
            if($k == $address){
                return $v ;
            }
        }
        return '';
    }

    // 批量创建为顶级账户
    public function createUserByList(){
        //  返回所有用户列表信息
        $address_list = array_keys($this->returnPrivateAddress());
        foreach($address_list as $address){

            $this->createByAddress($address) ;
        }
    }

    public function createAutoInsertUserByList(){

        //  返回所有用户列表信息
        $address_list = array_keys($this->returnAutoInsertAddress());
        foreach($address_list as $address){

            $this->createByAddress($address) ;
        }
    }

    /**
     * 根据地址创建用户
     * @param $address
     * @return mixed
     */
    public function createByAddress($address){

        $model = new Member();
        $params['cond'] = 'eth_address=:eth_address';
        $params['args'] = [':eth_address'=>$address];
        $info = $model->findOneByWhere('sea_user',$params);
        if($info){
            return true ;
        }

        $email = $this->createRandUsername() ;
        $password = $this->createPassword() ;
        //新增用户
        $add_data['email'] = $email ;
        $add_data['username'] = $email ;
        $add_data['password'] = md5($password);
        $add_data['reg_from'] = 'ADMIN';
        $add_data['audit_status'] = 'SUCCESS';
        $add_data['audit_idea'] = '';
        $add_data['mobile'] = '';
        $add_data['inviter_user_id'] = 0;
        $add_data['inviter_username'] =   0;
        $add_data['user_root_path'] =   '--0--';
        $add_data['user_level'] =   0;
        $add_data['is_open'] = 1;
        $add_data['eth_address'] = strtolower($address);
        $add_data['nickName'] = '';
        $add_data['avatarUrl'] = '';
        $add_data['type'] = 'PERSON';
        $add_data['invite_code'] = $this->createInviteCode();
        $add_data['last_login'] = date('Y-m-d H:i:s');
        $add_data['create_time'] = date('Y-m-d H:i:s');
        $add_data['modify_time'] = date('Y-m-d H:i:s');
        $model = new Member();
        return $model->baseInsert('sea_user',$add_data);

    }

    /**
     * 创建随机强密码
     * @return string
     */
    public function createPassword(){
        $string = '123456789qwertyupkjhgfdsazxcvbnm!@#$%^&*()_+./*-+';
        $password = '';
        for ( $i = 0; $i < 10; $i++ ){
            $password .= $string[ mt_rand(0, strlen($string) - 1) ];
        }
        return $password ;
    }
    /**
     * 创建随机用户名
     * @return string
     */
    private function createRandUsername(){

        $string = '123456789qwertyupkjhgfdsazxcvbnm';
        $password = '';
        for ( $i = 0; $i < 10; $i++ ){
            $password .= $string[ mt_rand(0, strlen($string) - 1) ];
        }
        $password = $password.'@gmail.com' ;

        $model = new Member() ;
        $params['cond'] = 'username =:username';
        $params['args'] = [':username' =>$password];
        $info = $model->findOneByWhere('sea_user',$params) ;
        if(!$info){
            return strtolower($password);
        }else{
            $this->createRandUsername() ;
        }
    }


    /**
     * 创建用户邀请码
     * @return string
     */
    public function createInviteCode(){
        $string = '123456789qwertyupkjhgfdsazxcvbnm';
        $password = '';
        for ( $i = 0; $i < 5; $i++ ){
            $password .= $string[ mt_rand(0, strlen($string) - 1) ];
        }
        $model = new Member() ;
        $params['cond'] = 'invite_code =:invite_code';
        $params['args'] = [':invite_code' =>$password];
        $info = $model->findOneByWhere('sea_user',$params) ;
        if(!$info){
            return strtoupper($password);
        }else{
            $this->createInviteCode() ;
        }

    }


}