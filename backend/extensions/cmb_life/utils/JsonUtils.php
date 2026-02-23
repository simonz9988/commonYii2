<?PHP
namespace backend\extensions\cmb_life\utils;
/**
 * @author CMB_CCD_Developer
 * V1.0.1
 */
class JsonUtils{

    /**
     * 将JsonObject转为map
     *
     * @param json
     * @return
     */
    public function stringToObject($json)
    {
        return json_decode($json, 10);
    }

    /**
     * 将map转为JsonObject
     * @param array
     * @return
     */
    public function objectToString($array)
    {
        foreach($array as $key=>$value)
               {
                    $array[$key] = urlencode($value);
               }
        return urldecode(json_encode($array));
    }
    
}
?>