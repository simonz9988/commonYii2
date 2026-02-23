$(function() {

    init_file_uploader('file_url', 'file_url');
});

$(function(){


    $("#addCateForm").validate({
        rules: {

            title: {
                required: true
            },

            sb_code: {
                required: true
            },
            publish_time:{
                required:true
            },
            yspg_time: {
                required: true
            },
            pingu_fabu_time:{
                required:true
            },
            pinguren:{
                required:true
            },
            ata:{
                required:true
            },
            address:{
                required:true
            }
        },
        submitHandler: function(form)
        {

            $.post(
                '/adminPage/kzList/doAdd',
                $('#addCateForm').serialize(),
                function(rs){
                    var arr = eval('('+rs+')');
                    if(arr.code == '1'){
                        alert("编辑成功");
                        location.reload();
                    }else{
                        alert(arr.msg);
                    }
                }
            );
        }
    });
})