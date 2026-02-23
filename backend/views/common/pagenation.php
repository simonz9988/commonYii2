
<?if($page_data):?>
	<?php
    $current_page = isset($_GET['page'])? $_GET['page'] : 1 ;
    $page = $page_data ;?>
    <div class="col-xs-3">
        <div class="tables-info" id="sample-table-2_info">
            当前第<?php echo $a = ($current_page-1)*$page['listRows']+1?>-<?php $b = ($current_page)*$page['listRows'] ;if($b>$page['totalRows']){echo $page['totalRows'];}else{echo $b ;}?>条 共<?=$page['totalpage']?>页,<?=$page['totalRows']?>条
        </div>
    </div>
    <div class="col-xs-9">
        <div class="tables-paginate paging_bootstrap">
            <ul class="pagination  pull-right">

                <?php foreach($page['page'] as $link_k=>$link_v){?>
                    <li class="<?php if($current_page==$link_k){echo 'active' ;}?>">
                        <a href="<?php echo $link_v ;?>">
                            <?php echo $link_k ;?>
                        </a>
                    </li>
                <?php } ?>

                <li class="next"><a href="<?php if ($page['next']['href']){echo $page['next']['href'];}else{?>javascript:;<?php }?>"><i class="fa fa-angle-right"></i></a></li>
                <li class="next"><a href="<?php if ($page['endpage']['href']){echo $page['endpage']['href'];}else{?>javascript:;<?php }?>"><i class="fa fa-angle-double-right"></i></a></li>


                &nbsp; 到第 <input type="text" id="page_No" class="goto-page-input" value="1" size="3" maxlength="10"> 页 <input type="button" id="btn_goto_page" class="btn btn-xs btn-primary" value="跳 转">
                <script type="text/javascript">
                    $("#btn_goto_page").on("click",function(){
                        var  p  = parseInt($.trim($("#page_No").val()));
                        location.href="<?php $page['baseurl']?>?p="+p ;
                    }) ;

                </script>

            </ul>

        </div>
    </div>
<?endif;?>