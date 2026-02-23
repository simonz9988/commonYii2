
<button class="btn btn-primary btn-sm no-border sync-trigger-button" type="button"><i class="ace-icon fa fa-paper-plane"></i> Select</button>

<div class="sync-dialog modal" style="display: none; padding-right: 17px;">
    <input  type="hidden" id="is_select_good_multiple" value="<?=$is_multiple?>">
    <div class="modal-dialog" style="width: 900px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" aria-hidden="true" onclick=' $(this).closest(".sync-dialog").modal("hide");'>×</button>
                <h3 class="smaller lighter blue no-margin">Select Goods</h3>
            </div>

            <div class="modal-body sync-dialog-processing-list">


            </div>
        </div><!-- /.modal-content -->
    </div>
</div>

<script type="text/javascript">
    $(function(){
        $(".sync-dialog").modal({
            keyboard: false,
            backdrop: "static",
            show: false
        });
    });

    $(document).on("click", ".sync-trigger-button", function() {
        $(".sync-dialog").modal("hide");
        var dialog = $(this).next(".sync-dialog");


        $(".sync-dialog-processing-list", dialog).html("");

        dialog.modal("show");

        var is_multiple = $("#is_select_good_multiple").val();

        // 插件已经选中的商品的ID
        var product_ids = '';


        $(".selected_product_id").each(function(){
            product_ids += $(this).val()+',';
        });

        var search_name = $("input[name=search_name]").val();


        $.post(
            '/product/ajax-get-list',
            {product_ids:product_ids,search_name:search_name,'is_multiple':is_multiple},
            function(data){
                $(".sync-dialog-processing-list", dialog).html(data) ;
            }
        );


    });


</script>