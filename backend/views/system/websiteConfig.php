<style>
    input{width:500px;}
</style>
<div style="margin-top:40px;margin-left:40px;">
    <h2>网站配置</h2>
    <p>Fill the form and submit it.</p>
    <div style="margin:20px 0;"></div>
    <form id="ff" method="post" action="/adminPage/system/doWebsiteConfig">
        <div class="easyui-panel" title="New Topic" style="width:1200px">
            <div style="padding:10px 60px 20px 60px">

                <table cellpadding="5">

                    <tr>
                        <td>网站名称:</td>
                        <td><input class="easyui-textbox" type="text" value="<?=$info?$info['website_name']:''?>"name="website_name" data-options="required:true"></input></td>
                    </tr>
                    <tr>
                        <td>Title:</td>
                        <td><input class="easyui-textbox" type="text" value="<?=$info?$info['seo_title']:''?>"name="seo_title" data-options="required:true"></input></td>
                    </tr>
                    <tr>
                        <td>Keywords:</td>
                        <td><input class="easyui-textbox" type="text" value="<?=$info?$info['seo_keywords']:''?>"name="seo_keywords" data-options="required:true"></input></td>
                    </tr>
                    <tr>
                        <td>Description:</td>
                        <td><input class="easyui-textbox" type="text" value="<?=$info?$info['seo_description']:''?>"name="seo_description" data-options="required:true"></input></td>
                    </tr>

                    <tr>
                        <td>疾病列表页Title:</td>
                        <td><input class="easyui-textbox" type="text" value="<?=$info?$info['jibing_seo_title']:''?>"name="jibing_seo_title" data-options="required:true"></input></td>
                    </tr>
                    <tr>
                        <td>疾病列表页Keywords:</td>
                        <td><input class="easyui-textbox" type="text" value="<?=$info?$info['jibing_seo_keywords']:''?>"name="jibing_seo_keywords" data-options="required:true"></input></td>
                    </tr>
                    <tr>
                        <td>疾病列表页Description:</td>
                        <td><input class="easyui-textbox" type="text" value="<?=$info?$info['jibing_seo_description']:''?>"name="jibing_seo_description" data-options="required:true"></input></td>
                    </tr>

                    <tr>
                        <td>症状列表页Title:</td>
                        <td><input class="easyui-textbox" type="text" value="<?=$info?$info['zhenzhuang_seo_title']:''?>"name="zhenzhuang_seo_title" data-options="required:true"></input></td>
                    </tr>
                    <tr>
                        <td>症状列表页Keywords:</td>
                        <td><input class="easyui-textbox" type="text" value="<?=$info?$info['zhenzhuang_seo_keywords']:''?>"name="zhenzhuang_seo_keywords" data-options="required:true"></input></td>
                    </tr>
                    <tr>
                        <td>症状列表页Description:</td>
                        <td><input class="easyui-textbox" type="text" value="<?=$info?$info['zhenzhuang_seo_description']:''?>"name="zhenzhuang_seo_description" data-options="required:true"></input></td>
                    </tr>

                    <tr>
                        <td>病理检查列表页Title:</td>
                        <td><input class="easyui-textbox" type="text" value="<?=$info?$info['jiancha_seo_title']:''?>"name="jiancha_seo_title" data-options="required:true"></input></td>
                    </tr>
                    <tr>
                        <td>病理检查列表页Keywords:</td>
                        <td><input class="easyui-textbox" type="text" value="<?=$info?$info['jiancha_seo_keywords']:''?>"name="jiancha_seo_keywords" data-options="required:true"></input></td>
                    </tr>
                    <tr>
                        <td>病理检查列表页Description:</td>
                        <td><input class="easyui-textbox" type="text" value="<?=$info?$info['jiancha_seo_description']:''?>"name="jiancha_seo_description" data-options="required:true"></input></td>
                    </tr>

                    <tr>
                        <td>健康知识列表页Title:</td>
                        <td><input class="easyui-textbox" type="text" value="<?=$info?$info['zhishi_seo_title']:''?>"name="zhishi_seo_title" data-options="required:true"></input></td>
                    </tr>
                    <tr>
                        <td>健康知识列表页Keywords:</td>
                        <td><input class="easyui-textbox" type="text" value="<?=$info?$info['zhishi_seo_keywords']:''?>"name="zhishi_seo_keywords" data-options="required:true"></input></td>
                    </tr>
                    <tr>
                        <td>健康知识列表页Description:</td>
                        <td><input class="easyui-textbox" type="text" value="<?=$info?$info['zhishi_seo_description']:''?>"name="zhishi_seo_description" data-options="required:true"></input></td>
                    </tr>

                </table>

                <div style="text-align:center;padding:5px">
                    <input type="submit" href="javascript:void(0)" class="easyui-linkbutton" value="保存"></input>

                </div>
            </div>
        </div>
    </form>
</div>
