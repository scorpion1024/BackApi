<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class User extends BaseController
{

    function __construct(){
        $this->key = env('APP_KEY');
    }

    public function do_login(Request $request){
        $input_param = $request->input();
        $results = DB::table('users')->where([['account',$input_param['userName']],['password',$input_param['passWord']]])->first();
        if(!DB::table('users')->where('account',$input_param['userName'])->exists()){
            return response()->json(['code'=>1,'msg'=>'用户名不存在']);
        }
        if(empty($results)){
            return response()->json(['code'=>2,'msg'=>'密码错误']);
        }else{
            return response()->json(['code'=>0,'msg'=>self::en_secret($results)]);
        }
        
    }

    public function get_userinfo(Request $request){
        $user = $request->input();
        unset($user['s']);
        $results = DB::table('users')->where($user)->first();
        if(empty($results)){
            return response()->json(['code'=>2,'msg'=>'token已更改，请重新登录']);
        }else{
            return response()->json(['code'=>0,'msg'=>self::en_secret($results)]);
        }
    }

    public function get_list(Request $request){
        $search_param = $request->input();
        $cols = self::get_column(false);
        $where=$search_param['where']?$search_param['where']:[['id','>=',1]];
        $total = DB::table('users')->where($where)->count();
        $offset = ($search_param['page']-1)*$search_param['size'];
        $results = DB::table('users')->select($cols)->where($where)->offset($offset)->limit($search_param['size'])->orderBy('id', 'asc')->get();
        return response()->json(['result'=>$results,'total'=>$total]);
    }

    public function change_admin(Request $request){
        $input_param = $request->input();
        $affected = DB::table('users')
              ->where('id', $input_param['id'])
              ->update(['is_admin' => $input_param['is_admin']==='1'?'0':'1']);
        return response()->json($affected);
    }

    public function do_change(Request $request){
        $col_arr = self::get_column();
        $insert = [];
        foreach ($request->input() as $key => $value) {
            if(in_array($key,$col_arr)){
                $insert[$key] = $value;
            }
        }
        $affected=0;
        if(!empty($insert)){
            if(isset($insert['id'])&&!empty($insert['id'])){
                $affected = DB::table('users')->where('id',$insert['id'])->update($insert);
            }else{
                $affected = DB::table('users')->insert($insert);
            }
        }
        return response()->json($affected);
    }

    public static function do_delete(Request $request){
        $id =$request->input('id');
        $affected=0;
        if(isset($id)&&!empty($id)){
            $affected = DB::table('users')->where('id',$id)->delete();
        }
        return response()->json($affected);
    }

    private function en_secret(Object $payload){
        $json_load = json_encode($payload);
        $offset = date('d');
        $base_code = base64_encode($json_load);
        $en_code = '';
        foreach(str_split($base_code) as $k=>$v){
            if($k%$this->key == 0&&$k!=0){
                $en_code .= $v.self::getRandomStr($offset);
            }else{
                $en_code .= $v;
            }
        }
        return $en_code;
    }

    private function de_secret(String $secret){
        $key = $this->key;
        $de_code = substr($secret,0,$key+1);
        $offset= $index= date('d');
        $token_str=substr($secret,$key+1);
        while ($index <= strlen($token_str)) {
            $de_code.=substr($token_str,$index,$key);
            $index+=($offset+$key);
        }
        $de_payload = json_decode(base64_decode($de_code),true);
        return $de_payload;
    }

    private function get_column($need_pwd = true){
        $results = DB::select('select COLUMN_NAME as col from information_schema.COLUMNS where table_name = "users" and COLUMN_NAME not in("CURRENT_CONNECTIONS","TOTAL_CONNECTIONS","USER")');
        $arr= [];
        foreach ($results as $key => $value) {
            if(!$need_pwd&&$value->col==='password'){
                continue;
            }
            $arr[]=$value->col;
        }
        return $arr;
    }

    public static function get_weather_data(Request $request){
        $result = DB::table('city_weather')->orderBy('fall_time','ASC')->limit(100)->get();
        $highchart = [];
        $echart = [];
        if(!empty($result)){
            foreach ($result as $key => $value) {
                $name=$value->city;
                $highchart[$name]['name']=$name;
                $highchart[$name]['data'][]=['x'=>strtotime($value->fall_time)*1000,'y'=>$value->rain_num,'name'=>$value->fall_time.':降雨量'];
                $name=$value->city;
                $echart[$name]['name']=$name;
                $echart[$name]['data'][]=[strtotime($value->fall_time)*1000,$value->rain_num];
            }
        }
        //echo '<pre>';print_r(array_values($return_data));exit;
        return response()->json(['highchart'=>array_values($highchart),'echart'=>array_values($echart)]); 
    }

    public function get_menu(Request $request,$need_pwd = false){
        $city_arr= ['北京','成都','上海'];
        $insert_arr=[];
        for ($i=0; $i < 9999; $i++) {
            $rad_num = mt_rand(100,999);
            $rad_time = date('Y-m-d H:i:s',mt_rand(strtotime('2021-01-01'),time()));
            $rad_city = $city_arr[array_rand($city_arr)];
            array_push($insert_arr,['city'=>$rad_city,'rain_num'=>$rad_num,'fall_time'=>$rad_time]);
        }
        // DB::table('city_weather')->insertOrIgnore($insert_arr);
        echo '<pre>';print_r(base64_encode('THZYb2lvt09TWERvZU5oUWlsRDJTdz09oxlvaGlsTThqMWw4ZGFFeA=='));
        //return date('Y-m-d H:i:s',$rad_time).'----'.$rad_city;
    }
   
    private function getRandomStr($len, $special=true){
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9"
        );

        if($special){
            $chars = array_merge($chars, array(
                "!", "@", "#", "$", "?", "|", "{", "/", ":", ";",
                "%", "^", "&", "*", "(", ")", "-", "_", "[", "]",
                "}", "<", ">", "~", "+", "=", ",", "."
            ));
        }

        $charsLen = count($chars) - 1;
        shuffle($chars);
        $str = '';
        for($i=0; $i<$len; $i++){
            $str .= $chars[mt_rand(0, $charsLen)];
        }
        return $str;
    }
}
